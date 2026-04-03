<?php

namespace humhub\modules\externalHtmlStream\services;

use Yii;
use humhub\modules\externalHtmlStream\models\MajlissPost;
use humhub\modules\externalHtmlStream\models\SyncLog;
use humhub\modules\space\models\Space;
use humhub\modules\content\models\Content;

/**
 * MajlissSyncService — Logique complète de synchronisation Majliss → HumHub.
 *
 * Adapté depuis le script standalone PHP d'origine.
 *
 * Ce service :
 *  1. Se connecte à la base WordPress Majliss
 *  2. Récupère les posts publiés non encore synchronisés
 *  3. Téléverse les images miniatures vers HumHub (File model)
 *  4. Crée les posts dans le stream d'un espace HumHub (ContentActiveRecord)
 *  5. Enregistre le statut de chaque synchronisation + logs en DB
 */
class MajlissSyncService
{
    /** @var \mysqli Connexion à la base Majliss */
    private \mysqli $majlissDb;

    /** @var string Préfixe des tables WordPress */
    private string $prefix;

    /** @var string URL de base pour normaliser les images */
    private string $baseUrl;

    /** @var int ID de l'espace HumHub cible */
    private int $spaceId;

    /** @var int Nombre max de posts par batch */
    private int $batchLimit;

    /** @var string Image de remplacement */
    private string $fallbackImage;

    /** @var int Timeout pour le téléchargement d'images */
    private int $imageTimeout;

    /**
     * Initialise le service avec la configuration du module.
     *
     * @throws \Exception Si la connexion MySQL échoue
     */
    public function __construct()
    {
        $module = Yii::$app->getModule('external-html-stream');

        $dbConfig = $module->getMajlissDbConfig();

        $this->prefix        = $dbConfig['prefix'];
        $this->baseUrl       = $module->getSetting('majlissBaseUrl', 'https://intranet.csefrs.ma');
        $this->spaceId       = (int) $module->getSetting('targetSpaceId', 1);
        $this->batchLimit    = (int) $module->getSetting('batchLimit', 10);
        $this->fallbackImage = $module->getSetting('fallbackImage', '');
        $this->imageTimeout  = (int) $module->getSetting('imageDownloadTimeout', 20);

        // Connexion à la base WordPress Majliss
        $this->majlissDb = new \mysqli(
            $dbConfig['host'],
            $dbConfig['user'],
            $dbConfig['pass'],
            $dbConfig['name']
        );

        if ($this->majlissDb->connect_error) {
            throw new \Exception(
                "Connexion MySQL Majliss échouée : " . $this->majlissDb->connect_error
            );
        }

        $this->majlissDb->set_charset('utf8mb4');
    }

    /**
     * Exécute la synchronisation complète.
     *
     * @return array Résumé : ['total', 'success', 'errors', 'log']
     */
    public function sync(): array
    {
        SyncLog::info("=== Démarrage synchronisation Majliss → HumHub ===");

        $result = ['total' => 0, 'success' => 0, 'errors' => 0, 'log' => []];

        try {
            // 1. Récupérer les IDs déjà synchronisés
            $syncedIds = MajlissPost::getSyncedWpIds();
            SyncLog::info(count($syncedIds) . " post(s) déjà synchronisé(s).");

            // 2. Récupérer les nouveaux posts depuis Majliss
            $wpPosts = $this->fetchNewPosts($syncedIds);
            $result['total'] = count($wpPosts);
            SyncLog::info($result['total'] . " nouveau(x) post(s) trouvé(s) dans Majliss.");

            if (empty($wpPosts)) {
                SyncLog::info("Rien à synchroniser.");
                return $result;
            }

            // 3. Vérifier que l'espace cible existe
            $space = Space::findOne($this->spaceId);
            if (!$space) {
                throw new \Exception("Espace HumHub ID={$this->spaceId} introuvable.");
            }

            // 4. Traiter chaque post
            foreach ($wpPosts as $wpPost) {
                try {
                    SyncLog::info("Traitement : [{$wpPost['wp_id']}] {$wpPost['name_post']}");

                    $this->syncSinglePost($wpPost, $space);
                    $result['success']++;

                } catch (\Exception $e) {
                    $result['errors']++;
                    SyncLog::error("Échec sync WP#{$wpPost['wp_id']} : " . $e->getMessage(), [
                        'wp_id' => $wpPost['wp_id'],
                        'title' => $wpPost['name_post'],
                    ]);

                    $this->recordSyncError($wpPost, $e->getMessage(), $space);
                }
            }

        } catch (\Exception $e) {
            SyncLog::error("ERREUR FATALE : " . $e->getMessage());
            $result['errors']++;
        }

        SyncLog::info("=== Fin sync. Succès: {$result['success']}, Erreurs: {$result['errors']} ===");

        return $result;
    }

    /**
     * Synchronise un seul post WordPress vers HumHub.
     *
     * @param array $wpPost Données du post WP
     * @param Space $space  Espace HumHub cible
     */
    private function syncSinglePost(array $wpPost, Space $space): void
    {
        // Vérification anti-doublon
        if (MajlissPost::isAlreadySynced((int) $wpPost['wp_id'])) {
            SyncLog::info("  → WP#{$wpPost['wp_id']} déjà synchronisé, on passe.");
            return;
        }

        // Créer le modèle MajlissPost
        $post = new MajlissPost();
        $post->wp_post_id  = (int) $wpPost['wp_id'];
        $post->title       = $wpPost['name_post'];
        $post->content     = $this->cleanContent($wpPost['content'] ?? '');
        $post->category    = $wpPost['category'] ?? '';
        $post->wp_date     = $wpPost['date'] ?? null;
        $post->space_id    = $space->id;
        $post->sync_status = MajlissPost::SYNC_PENDING;

        // Traiter l'image
        $imageUrl = $this->normalizeImageUrl($wpPost['image_url'] ?? '');
        $post->image_url = $imageUrl;

        if (!empty($imageUrl)) {
            $fileGuid = $this->uploadImageToHumHub($imageUrl);
            if ($fileGuid) {
                $post->image_file_guid = $fileGuid;
                SyncLog::info("  Image téléversée : $fileGuid");
            } else {
                SyncLog::warn("  Échec upload image pour WP#{$wpPost['wp_id']}, post créé sans image.");
            }
        }

        // Assigner le content container (espace) + visibilité
        $post->content->container  = $space;
        $post->content->visibility = Content::VISIBILITY_PUBLIC;

        // Sauvegarder dans la DB HumHub + Stream
        if ($post->save()) {
            $post->markSuccess();
            SyncLog::info("  Post créé dans HumHub (ID: {$post->id}, WP#{$post->wp_post_id})");
        } else {
            $errors = implode(', ', array_map(function ($e) {
                return implode('; ', $e);
            }, $post->getErrors()));
            throw new \Exception("Validation échouée : $errors");
        }
    }

    /**
     * Enregistre un post échoué pour traçabilité.
     */
    private function recordSyncError(array $wpPost, string $error, Space $space): void
    {
        try {
            if (MajlissPost::isAlreadySynced((int) $wpPost['wp_id'])) {
                return;
            }

            $post = new MajlissPost();
            $post->wp_post_id  = (int) $wpPost['wp_id'];
            $post->title       = $wpPost['name_post'] ?? 'Sans titre';
            $post->content     = '';
            $post->space_id    = $space->id;
            $post->sync_status = MajlissPost::SYNC_ERROR;
            $post->sync_error  = mb_substr($error, 0, 500);

            $post->content->container  = $space;
            $post->content->visibility = Content::VISIBILITY_PRIVATE;

            $post->save(false);
        } catch (\Exception $e) {
            SyncLog::error("Impossible d'enregistrer l'erreur sync : " . $e->getMessage());
        }
    }

    /**
     * Récupère les posts publiés depuis la base WordPress Majliss
     * qui n'ont pas encore été synchronisés.
     *
     * Requête identique au script d'origine.
     *
     * @param int[] $excludeIds IDs WP à exclure
     * @return array
     */
    private function fetchNewPosts(array $excludeIds): array
    {
        $p = $this->majlissDb->real_escape_string($this->prefix);

        // Filtre d'exclusion (IDs déjà synchronisés)
        $excludeSql = '';
        if (!empty($excludeIds)) {
            $ids = implode(',', array_map('intval', $excludeIds));
            $excludeSql = "AND p.ID NOT IN ($ids)";
        }

        $limit = (int) $this->batchLimit;

        // Requête identique au script d'origine Majliss
        $query = "
            SELECT DISTINCT
                p.ID                AS wp_id,
                p.post_title        AS name_post,
                p.post_date         AS date,
                t.name              AS category,
                p.post_content      AS content,
                img.guid            AS image_url
            FROM
                {$p}posts           AS p
            JOIN
                {$p}term_relationships AS tr  ON p.ID = tr.object_id
            JOIN
                {$p}term_taxonomy      AS tt  ON tr.term_taxonomy_id = tt.term_taxonomy_id
            JOIN
                {$p}terms              AS t   ON tt.term_id = t.term_id
            LEFT JOIN
                {$p}postmeta           AS pm  ON p.ID = pm.post_id
                                              AND pm.meta_key = '_thumbnail_id'
            LEFT JOIN
                {$p}posts              AS img ON pm.meta_value = img.ID
                                              AND img.post_type = 'attachment'
            WHERE
                p.post_status = 'publish'
                AND p.post_type  = 'post'
                AND tt.taxonomy  = 'category'
                $excludeSql
            ORDER BY p.post_date DESC
            LIMIT $limit
        ";

        $result = $this->majlissDb->query($query);
        if (!$result) {
            throw new \Exception("Erreur SQL Majliss : " . $this->majlissDb->error);
        }

        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }

        return $posts;
    }

    /**
     * Téléverse une image distante vers HumHub via le système File interne.
     *
     * @param string $imageUrl URL de l'image
     * @return string|null GUID du fichier ou null si échec
     */
    private function uploadImageToHumHub(string $imageUrl): ?string
    {
        if (empty($imageUrl)) {
            return null;
        }

        try {
            // Télécharger l'image via cURL
            $ch = curl_init($imageUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_TIMEOUT        => $this->imageTimeout,
                CURLOPT_USERAGENT      => 'HumHub-MajlissSync/1.0',
            ]);
            $imageData = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr   = curl_error($ch);
            curl_close($ch);

            if ($curlErr || $httpCode !== 200 || empty($imageData)) {
                SyncLog::warn("Téléchargement image échoué ($httpCode): $curlErr", [
                    'url' => $imageUrl,
                ]);
                return null;
            }

            // Sauvegarder temporairement
            $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
            $tempFile = Yii::$app->runtimePath . '/majliss_temp_' . uniqid() . '_' . $filename;
            file_put_contents($tempFile, $imageData);

            // Utiliser le système de fichiers HumHub
            $file = new \humhub\modules\file\models\File();
            $file->file_name = $filename;
            $file->mime_type = $this->guessMime($filename);
            $file->size      = strlen($imageData);

            if ($file->save()) {
                $file->store->set($tempFile);
                @unlink($tempFile);
                return $file->guid;
            }

            @unlink($tempFile);
            return null;

        } catch (\Exception $e) {
            SyncLog::error("Upload image échoué : " . $e->getMessage(), [
                'url' => $imageUrl,
            ]);
            return null;
        }
    }

    /**
     * Normalise l'URL de l'image (domaine + suppression /preprod/).
     * Logique identique au script d'origine.
     *
     * @param string $url
     * @return string
     */
    private function normalizeImageUrl(string $url): string
    {
        $url = strip_tags(trim($url));
        if (empty($url)) {
            return $this->fallbackImage;
        }

        $url = str_replace(
            'https://majliscom.csefrs.ma/wp-content/uploads/',
            $this->baseUrl . '/wp-content/uploads/',
            $url
        );
        $url = str_replace(
            $this->baseUrl . '/preprod/wp-content/uploads/',
            $this->baseUrl . '/wp-content/uploads/',
            $url
        );

        return $url;
    }

    /**
     * Nettoie le contenu HTML du post WordPress.
     * Logique identique au script d'origine.
     *
     * @param string $html
     * @return string Texte propre
     */
    private function cleanContent(string $html): string
    {
        // Supprimer les shortcodes WordPress [...]
        $text = preg_replace('/\[[^\]]+\]/', '', $html);
        // Convertir <br> en sauts de ligne
        $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
        // Supprimer les autres balises HTML
        $text = strip_tags($text);
        // Nettoyer les espaces multiples
        $text = preg_replace('/[ \t]{2,}/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }

    /**
     * Devine le type MIME à partir de l'extension.
     *
     * @param string $filename
     * @return string
     */
    private function guessMime(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'webp'        => 'image/webp',
            default       => 'application/octet-stream',
        };
    }

    /**
     * Teste la connexion à la base Majliss.
     *
     * @return array ['success' => bool, 'message' => string, 'post_count' => int]
     */
    public function testConnection(): array
    {
        try {
            $p = $this->prefix;

            $query = "SELECT COUNT(*) as total FROM {$p}posts WHERE post_status = 'publish' AND post_type = 'post'";
            $result = $this->majlissDb->query($query);

            if (!$result) {
                return [
                    'success'    => false,
                    'message'    => 'Erreur SQL : ' . $this->majlissDb->error,
                    'post_count' => 0,
                ];
            }

            $row = $result->fetch_assoc();
            $total = (int) $row['total'];

            $syncedCount = count(MajlissPost::getSyncedWpIds());

            return [
                'success'     => true,
                'message'     => "Connexion réussie. $total posts publiés dans Majliss, $syncedCount déjà synchronisés.",
                'post_count'  => $total,
                'synced_count' => $syncedCount,
                'remaining'   => $total - $syncedCount,
            ];

        } catch (\Exception $e) {
            return [
                'success'    => false,
                'message'    => $e->getMessage(),
                'post_count' => 0,
            ];
        }
    }

    /**
     * Retente la synchronisation d'un post en erreur.
     *
     * @param int $majlissPostId ID dans la table majliss_synced_post
     * @return bool
     */
    public function retrySync(int $majlissPostId): bool
    {
        $post = MajlissPost::findOne($majlissPostId);
        if (!$post || $post->sync_status !== MajlissPost::SYNC_ERROR) {
            return false;
        }

        $space = Space::findOne($post->space_id);
        if (!$space) {
            return false;
        }

        $wpId = $post->wp_post_id;
        $post->delete();

        try {
            $wpPosts = $this->fetchNewPosts(
                array_diff(MajlissPost::getSyncedWpIds(), [$wpId])
            );

            foreach ($wpPosts as $wpPost) {
                if ((int) $wpPost['wp_id'] === $wpId) {
                    $this->syncSinglePost($wpPost, $space);
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            SyncLog::error("Retry sync échoué pour WP#$wpId : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ferme la connexion MySQL Majliss.
     */
    public function __destruct()
    {
        if (isset($this->majlissDb) && $this->majlissDb instanceof \mysqli) {
            $this->majlissDb->close();
        }
    }
}
