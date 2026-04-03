<?php

namespace humhub\modules\externalHtmlStream\services;

use Yii;
use humhub\modules\externalHtmlStream\models\MajlissPost;
use humhub\modules\externalHtmlStream\models\SyncLog;
use humhub\modules\space\models\Space;
use humhub\modules\content\models\Content;

/**
 * MajlissSyncService — Synchronisation Majliss → HumHub via l'API REST WordPress.
 *
 * Ce service :
 *  1. Appelle l'API REST WP (GET /wp-json/wp/v2/posts)
 *  2. Récupère les posts publiés non encore synchronisés
 *  3. Récupère l'image à la une via l'API médias (/wp-json/wp/v2/media/{id})
 *  4. Récupère le nom de la catégorie via l'API catégories
 *  5. Téléverse les images miniatures vers HumHub (File model)
 *  6. Crée les posts dans le stream d'un espace HumHub (ContentActiveRecord)
 *  7. Enregistre le statut de chaque synchronisation + logs en DB
 *
 * AVANTAGE vs connexion directe :
 *  - Pas besoin d'accès MySQL à la base WP
 *  - Fonctionne même si WP est sur un autre serveur
 *  - Respecte les permissions et filtres WP
 *  - Compatible avec n'importe quel hébergement WordPress
 */
class MajlissSyncService
{
    /** @var string URL de base de l'API WP REST */
    private string $apiBaseUrl;

    /** @var string Endpoint de l'API */
    private string $apiEndpoint;

    /** @var string Méthode d'auth : none, basic, jwt, application_password */
    private string $authMethod;

    /** @var string User pour Basic Auth */
    private string $authUser;

    /** @var string Password pour Basic Auth / Application Password */
    private string $authPassword;

    /** @var string Token JWT */
    private string $jwtToken;

    /** @var int Timeout API en secondes */
    private int $apiTimeout;

    /** @var string URL de base pour normaliser les images */
    private string $imageBaseUrl;

    /** @var int ID de l'espace HumHub cible */
    private int $spaceId;

    /** @var int Nombre max de posts par batch */
    private int $batchLimit;

    /** @var string Image de remplacement */
    private string $fallbackImage;

    /** @var int Timeout pour le téléchargement d'images */
    private int $imageTimeout;

    /** @var string Filtre catégories (IDs séparés par virgule) */
    private string $categoryFilter;

    /** @var array Cache des catégories WP (id => name) */
    private array $categoriesCache = [];

    /**
     * Initialise le service avec la configuration du module.
     */
    public function __construct()
    {
        $module = Yii::$app->getModule('external-html-stream');

        $apiConfig = $module->getWpApiConfig();

        $this->apiBaseUrl    = $apiConfig['base_url'];
        $this->apiEndpoint   = $apiConfig['endpoint'];
        $this->authMethod    = $apiConfig['auth_method'];
        $this->authUser      = $apiConfig['auth_user'];
        $this->authPassword  = $apiConfig['auth_pass'];
        $this->jwtToken      = $apiConfig['jwt_token'];
        $this->apiTimeout    = $apiConfig['timeout'];

        $this->imageBaseUrl  = $module->getSetting('imageBaseUrl', 'https://intranet.csefrs.ma');
        $this->spaceId       = (int) $module->getSetting('targetSpaceId', 1);
        $this->batchLimit    = (int) $module->getSetting('batchLimit', 10);
        $this->fallbackImage = $module->getSetting('fallbackImage', '');
        $this->imageTimeout  = (int) $module->getSetting('imageDownloadTimeout', 20);
        $this->categoryFilter = $module->getSetting('wpCategoryFilter', '');
    }

    /**
     * Exécute la synchronisation complète.
     *
     * @return array Résumé : ['total', 'success', 'errors', 'log']
     */
    public function sync(): array
    {
        SyncLog::info("=== Démarrage synchronisation Majliss → HumHub (via API REST) ===");

        $result = ['total' => 0, 'success' => 0, 'errors' => 0, 'log' => []];

        try {
            // 1. Récupérer les IDs WP déjà synchronisés
            $syncedIds = MajlissPost::getSyncedWpIds();
            SyncLog::info(count($syncedIds) . " post(s) déjà synchronisé(s).");

            // 2. Récupérer les posts depuis l'API REST WordPress
            $wpPosts = $this->fetchPostsFromApi();
            SyncLog::info(count($wpPosts) . " post(s) récupéré(s) depuis l'API WordPress.");

            // 3. Filtrer les posts déjà synchronisés
            $newPosts = array_filter($wpPosts, function ($post) use ($syncedIds) {
                return !in_array((int) $post['id'], $syncedIds);
            });

            $result['total'] = count($newPosts);
            SyncLog::info($result['total'] . " nouveau(x) post(s) à synchroniser.");

            if (empty($newPosts)) {
                SyncLog::info("Rien à synchroniser.");
                return $result;
            }

            // 4. Vérifier que l'espace cible existe
            $space = Space::findOne($this->spaceId);
            if (!$space) {
                throw new \Exception("Espace HumHub ID={$this->spaceId} introuvable.");
            }

            // 5. Traiter chaque post
            foreach ($newPosts as $wpPost) {
                try {
                    $wpId = (int) $wpPost['id'];
                    $title = $this->extractTitle($wpPost);
                    SyncLog::info("Traitement : [WP#{$wpId}] {$title}");

                    $this->syncSinglePost($wpPost, $space);
                    $result['success']++;

                } catch (\Exception $e) {
                    $result['errors']++;
                    $wpId = $wpPost['id'] ?? 0;
                    $title = $this->extractTitle($wpPost);
                    SyncLog::error("Échec sync WP#{$wpId} : " . $e->getMessage(), [
                        'wp_id' => $wpId,
                        'title' => $title,
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
     * Récupère les posts depuis l'API REST WordPress.
     *
     * GET /wp-json/wp/v2/posts?per_page=X&status=publish&_embed=true
     *
     * Le paramètre _embed inclut les médias et catégories directement
     * dans la réponse, évitant des appels supplémentaires.
     *
     * @return array Liste de posts WP (format JSON décodé)
     */
    private function fetchPostsFromApi(): array
    {
        $params = [
            'per_page' => $this->batchLimit,
            'status'   => 'publish',
            'orderby'  => 'date',
            'order'    => 'desc',
            '_embed'   => 'true',  // Inclut featured media + catégories
        ];

        // Filtrer par catégories si configuré
        if (!empty($this->categoryFilter)) {
            $params['categories'] = trim($this->categoryFilter);
        }

        $url = $this->buildApiUrl('/posts', $params);
        SyncLog::info("Appel API : $url");

        $response = $this->apiRequest($url);

        if ($response === null) {
            throw new \Exception("Aucune réponse de l'API WordPress.");
        }

        if (!is_array($response)) {
            throw new \Exception("Réponse API invalide (pas un tableau JSON).");
        }

        // Vérifier si c'est une erreur WP REST
        if (isset($response['code']) && isset($response['message'])) {
            throw new \Exception("Erreur API WP : [{$response['code']}] {$response['message']}");
        }

        return $response;
    }

    /**
     * Synchronise un seul post WordPress vers HumHub.
     *
     * @param array $wpPost Données du post WP (format API REST)
     * @param Space $space Espace HumHub cible
     */
    private function syncSinglePost(array $wpPost, Space $space): void
    {
        $wpId = (int) $wpPost['id'];

        // Vérification anti-doublon
        if (MajlissPost::isAlreadySynced($wpId)) {
            SyncLog::info("  → WP#{$wpId} déjà synchronisé, on passe.");
            return;
        }

        // Extraire les données depuis la réponse API
        $title    = $this->extractTitle($wpPost);
        $content  = $this->extractContent($wpPost);
        $category = $this->extractCategory($wpPost);
        $wpDate   = $wpPost['date'] ?? null;
        $imageUrl = $this->extractFeaturedImage($wpPost);

        // Créer le modèle MajlissPost
        $post = new MajlissPost();
        $post->wp_post_id    = $wpId;
        $post->title         = $title;
        $post->post_content  = $this->cleanContent($content);
        $post->category      = $category;
        $post->wp_date       = $wpDate;
        $post->space_id      = $space->id;
        $post->sync_status   = MajlissPost::SYNC_PENDING;

        // Traiter l'image à la une
        $imageUrl = $this->normalizeImageUrl($imageUrl);
        $post->image_url = $imageUrl;

        if (!empty($imageUrl)) {
            $fileGuid = $this->uploadImageToHumHub($imageUrl);
            if ($fileGuid) {
                $post->image_file_guid = $fileGuid;
                SyncLog::info("  Image téléversée : $fileGuid");
            } else {
                SyncLog::warn("  Échec upload image pour WP#{$wpId}, post créé sans image.");
            }
        }

        // Assigner le content container (espace) + visibilité
        // IMPORTANT : utiliser getContent() (la relation) pas le champ DB
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

    // ═══════════════════════════════════════════════════════════
    //  EXTRACTION DES DONNÉES DEPUIS LA RÉPONSE API WP REST
    // ═══════════════════════════════════════════════════════════

    /**
     * Extrait le titre du post depuis la réponse API.
     * L'API retourne le titre dans { "title": { "rendered": "..." } }
     *
     * @param array $wpPost
     * @return string
     */
    private function extractTitle(array $wpPost): string
    {
        if (isset($wpPost['title']['rendered'])) {
            return html_entity_decode(strip_tags($wpPost['title']['rendered']), ENT_QUOTES, 'UTF-8');
        }
        return $wpPost['title'] ?? 'Sans titre';
    }

    /**
     * Extrait le contenu du post depuis la réponse API.
     * L'API retourne le contenu dans { "content": { "rendered": "..." } }
     *
     * On peut aussi utiliser "excerpt" pour un résumé.
     *
     * @param array $wpPost
     * @return string HTML du contenu
     */
    private function extractContent(array $wpPost): string
    {
        if (isset($wpPost['content']['rendered'])) {
            return $wpPost['content']['rendered'];
        }
        return $wpPost['content'] ?? '';
    }

    /**
     * Extrait la catégorie principale du post.
     *
     * Avec _embed, les catégories sont dans :
     * { "_embedded": { "wp:term": [ [{ "name": "..." }] ] } }
     *
     * Sans _embed, on utilise les IDs dans "categories" et on fait un appel API.
     *
     * @param array $wpPost
     * @return string Nom de la catégorie
     */
    private function extractCategory(array $wpPost): string
    {
        // Méthode 1 : via _embedded (si _embed=true dans la requête)
        if (isset($wpPost['_embedded']['wp:term'])) {
            foreach ($wpPost['_embedded']['wp:term'] as $termGroup) {
                if (is_array($termGroup)) {
                    foreach ($termGroup as $term) {
                        if (isset($term['taxonomy']) && $term['taxonomy'] === 'category' && $term['name'] !== 'Uncategorized') {
                            return html_entity_decode($term['name'], ENT_QUOTES, 'UTF-8');
                        }
                    }
                    // Si toutes sont "Uncategorized", prendre la première quand même
                    if (!empty($termGroup[0]['name'])) {
                        return html_entity_decode($termGroup[0]['name'], ENT_QUOTES, 'UTF-8');
                    }
                }
            }
        }

        // Méthode 2 : via les IDs de catégories (appel API supplémentaire)
        if (!empty($wpPost['categories'])) {
            $catId = $wpPost['categories'][0];
            return $this->fetchCategoryName($catId);
        }

        return '';
    }

    /**
     * Récupère le nom d'une catégorie via l'API WP REST.
     * Résultat mis en cache pour éviter les appels redondants.
     *
     * GET /wp-json/wp/v2/categories/{id}
     *
     * @param int $categoryId
     * @return string
     */
    private function fetchCategoryName(int $categoryId): string
    {
        // Vérifier le cache
        if (isset($this->categoriesCache[$categoryId])) {
            return $this->categoriesCache[$categoryId];
        }

        try {
            $url = $this->buildApiUrl("/categories/{$categoryId}");
            $data = $this->apiRequest($url);

            if (isset($data['name'])) {
                $name = html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8');
                $this->categoriesCache[$categoryId] = $name;
                return $name;
            }
        } catch (\Exception $e) {
            SyncLog::warn("Impossible de récupérer la catégorie #{$categoryId} : " . $e->getMessage());
        }

        return '';
    }

    /**
     * Extrait l'URL de l'image à la une (featured image).
     *
     * Avec _embed, l'image est dans :
     * { "_embedded": { "wp:featuredmedia": [{ "source_url": "..." }] } }
     *
     * Sans _embed, on utilise "featured_media" (ID) et on fait un appel API.
     *
     * @param array $wpPost
     * @return string URL de l'image
     */
    private function extractFeaturedImage(array $wpPost): string
    {
        // Méthode 1 : via _embedded (recommandé, pas d'appel supplémentaire)
        if (isset($wpPost['_embedded']['wp:featuredmedia'][0]['source_url'])) {
            return $wpPost['_embedded']['wp:featuredmedia'][0]['source_url'];
        }

        // Méthode 1 bis : taille intermédiaire pour les performances
        if (isset($wpPost['_embedded']['wp:featuredmedia'][0]['media_details']['sizes']['large']['source_url'])) {
            return $wpPost['_embedded']['wp:featuredmedia'][0]['media_details']['sizes']['large']['source_url'];
        }

        // Méthode 2 : via l'ID du média (appel API supplémentaire)
        $mediaId = $wpPost['featured_media'] ?? 0;
        if ($mediaId > 0) {
            return $this->fetchMediaUrl($mediaId);
        }

        return $this->fallbackImage;
    }

    /**
     * Récupère l'URL d'un média via l'API WP REST.
     *
     * GET /wp-json/wp/v2/media/{id}
     *
     * @param int $mediaId
     * @return string URL du média
     */
    private function fetchMediaUrl(int $mediaId): string
    {
        try {
            $url = $this->buildApiUrl("/media/{$mediaId}");
            $data = $this->apiRequest($url);

            // Préférer la taille "large" pour les performances
            if (isset($data['media_details']['sizes']['large']['source_url'])) {
                return $data['media_details']['sizes']['large']['source_url'];
            }

            // Sinon URL originale
            if (isset($data['source_url'])) {
                return $data['source_url'];
            }
        } catch (\Exception $e) {
            SyncLog::warn("Impossible de récupérer le média #{$mediaId} : " . $e->getMessage());
        }

        return $this->fallbackImage;
    }

    // ═══════════════════════════════════════════════════════════
    //  APPELS API HTTP
    // ═══════════════════════════════════════════════════════════

    /**
     * Effectue un appel GET vers l'API WordPress REST.
     *
     * Supporte 4 méthodes d'authentification :
     *  - none : API publique (défaut WP)
     *  - basic : Basic Auth (user:password)
     *  - application_password : Application Passwords WP 5.6+
     *  - jwt : JWT Authentication (header Bearer)
     *
     * @param string $url URL complète
     * @return array|null Réponse JSON décodée
     */
    private function apiRequest(string $url): ?array
    {
        $ch = curl_init($url);

        $headers = [
            'Accept: application/json',
            'User-Agent: HumHub-MajlissSync/2.0',
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_TIMEOUT        => $this->apiTimeout,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        // Authentification selon la méthode configurée
        switch ($this->authMethod) {
            case 'basic':
            case 'application_password':
                curl_setopt($ch, CURLOPT_USERPWD, "{$this->authUser}:{$this->authPassword}");
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                break;

            case 'jwt':
                $token = !empty($this->jwtToken) ? $this->jwtToken : $this->authPassword;
                $headers[] = "Authorization: Bearer {$token}";
                break;

            case 'none':
            default:
                // Pas d'authentification
                break;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        $curlErrNo = curl_errno($ch);
        curl_close($ch);

        // Log l'appel
        SyncLog::info("API GET $url → HTTP $httpCode", [
            'response_size' => strlen($raw ?: ''),
        ]);

        // Erreur cURL
        if ($curlErr) {
            SyncLog::error("Erreur cURL #{$curlErrNo} : {$curlErr}", ['url' => $url]);
            throw new \Exception("Erreur de connexion API : {$curlErr}");
        }

        // HTTP non-200
        if ($httpCode < 200 || $httpCode >= 300) {
            $errorBody = mb_substr($raw ?: '', 0, 500);
            SyncLog::error("API HTTP {$httpCode} : {$errorBody}", ['url' => $url]);
            throw new \Exception("API WordPress a répondu HTTP {$httpCode}");
        }

        // Décoder le JSON
        $decoded = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            SyncLog::error("Réponse non-JSON : " . mb_substr($raw, 0, 200), ['url' => $url]);
            throw new \Exception("Réponse API non-JSON (HTTP $httpCode)");
        }

        return $decoded;
    }

    /**
     * Construit l'URL complète de l'API WP REST.
     *
     * @param string $path Ex: '/posts', '/categories/5'
     * @param array $params Paramètres GET
     * @return string
     */
    private function buildApiUrl(string $path, array $params = []): string
    {
        $url = rtrim($this->apiBaseUrl, '/') . $this->apiEndpoint . $path;

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    // ═══════════════════════════════════════════════════════════
    //  UPLOAD IMAGE VERS HUMHUB
    // ═══════════════════════════════════════════════════════════

    /**
     * Téléverse une image distante vers HumHub via le système File interne.
     *
     * Utilise UploadedFile simulé pour être compatible avec
     * StorageManager::set() qui attend un UploadedFile.
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
                CURLOPT_USERAGENT      => 'HumHub-MajlissSync/2.0',
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

            // Nettoyer le nom de fichier
            $filename = basename(parse_url($imageUrl, PHP_URL_PATH));
            $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
            if (empty($filename) || $filename === '_') {
                $filename = 'image_' . uniqid() . '.jpg';
            }

            // Sauvegarder temporairement
            $tempDir  = Yii::$app->runtimePath . '/majliss_uploads';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0775, true);
            }
            $tempFile = $tempDir . '/' . uniqid('img_') . '_' . $filename;
            file_put_contents($tempFile, $imageData);

            // Créer un UploadedFile simulé (requis par StorageManager::set())
            $uploadedFile = new \yii\web\UploadedFile();
            $uploadedFile->name     = $filename;
            $uploadedFile->tempName = $tempFile;
            $uploadedFile->type     = $this->guessMime($filename);
            $uploadedFile->size     = strlen($imageData);
            $uploadedFile->error    = UPLOAD_ERR_OK;

            // Créer le fichier HumHub
            $file = new \humhub\modules\file\models\File();
            $file->file_name = $filename;
            $file->mime_type = $this->guessMime($filename);
            $file->size      = strlen($imageData);

            if ($file->save()) {
                // StorageManager::set() attend un UploadedFile
                $file->store->set($uploadedFile);
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

    // ═══════════════════════════════════════════════════════════
    //  UTILITAIRES
    // ═══════════════════════════════════════════════════════════

    /**
     * Normalise l'URL de l'image.
     * Redirige les URLs Majliss vers l'URL configurée.
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

        // Réécrire les URLs si imageBaseUrl est configuré
        if (!empty($this->imageBaseUrl)) {
            $url = str_replace(
                'https://majliscom.csefrs.ma/wp-content/uploads/',
                rtrim($this->imageBaseUrl, '/') . '/wp-content/uploads/',
                $url
            );
            $url = str_replace(
                rtrim($this->imageBaseUrl, '/') . '/preprod/wp-content/uploads/',
                rtrim($this->imageBaseUrl, '/') . '/wp-content/uploads/',
                $url
            );
        }

        return $url;
    }

    /**
     * Nettoie le contenu HTML du post WordPress.
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
        // Supprimer les balises HTML
        $text = strip_tags($text);
        // Nettoyer les entités HTML
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
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
            'svg'         => 'image/svg+xml',
            default       => 'application/octet-stream',
        };
    }

    /**
     * Enregistre un post échoué pour traçabilité.
     */
    private function recordSyncError(array $wpPost, string $error, Space $space): void
    {
        try {
            $wpId = (int) ($wpPost['id'] ?? 0);
            if ($wpId > 0 && MajlissPost::isAlreadySynced($wpId)) {
                return;
            }

            $post = new MajlissPost();
            $post->wp_post_id    = $wpId;
            $post->title         = $this->extractTitle($wpPost);
            $post->post_content  = '';
            $post->space_id      = $space->id;
            $post->sync_status = MajlissPost::SYNC_ERROR;
            $post->sync_error  = mb_substr($error, 0, 500);

            $post->content->container  = $space;
            $post->content->visibility = Content::VISIBILITY_PRIVATE;

            $post->save(false);
        } catch (\Exception $e) {
            SyncLog::error("Impossible d'enregistrer l'erreur sync : " . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    //  FONCTIONS PUBLIQUES (pour le backoffice)
    // ═══════════════════════════════════════════════════════════

    /**
     * Teste la connexion à l'API WordPress.
     *
     * Fait un appel GET /wp-json/wp/v2/posts?per_page=1 pour vérifier
     * que l'API est accessible et fonctionne.
     *
     * @return array ['success' => bool, 'message' => string, 'post_count' => int]
     */
    public function testConnection(): array
    {
        try {
            // Test 1 : vérifier que l'API est accessible
            $url = $this->buildApiUrl('/posts', [
                'per_page' => 1,
                'status'   => 'publish',
            ]);

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_TIMEOUT        => $this->apiTimeout,
                CURLOPT_HEADER         => true,
            ]);

            // Auth
            switch ($this->authMethod) {
                case 'basic':
                case 'application_password':
                    curl_setopt($ch, CURLOPT_USERPWD, "{$this->authUser}:{$this->authPassword}");
                    break;
                case 'jwt':
                    $token = !empty($this->jwtToken) ? $this->jwtToken : $this->authPassword;
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer {$token}"]);
                    break;
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($curlErr) {
                return [
                    'success'    => false,
                    'message'    => "Erreur de connexion : {$curlErr}",
                    'post_count' => 0,
                ];
            }

            if ($httpCode < 200 || $httpCode >= 300) {
                return [
                    'success'    => false,
                    'message'    => "L'API a répondu HTTP {$httpCode}. Vérifiez l'URL et l'authentification.",
                    'post_count' => 0,
                ];
            }

            // Extraire le header X-WP-Total (nombre total de posts)
            $headers = substr($response, 0, $headerSize);
            $totalPosts = 0;
            if (preg_match('/X-WP-Total:\s*(\d+)/i', $headers, $matches)) {
                $totalPosts = (int) $matches[1];
            }

            $syncedCount = count(MajlissPost::getSyncedWpIds());

            return [
                'success'      => true,
                'message'      => "Connexion API réussie ! {$totalPosts} posts publiés dans WordPress, {$syncedCount} déjà synchronisés.",
                'post_count'   => $totalPosts,
                'synced_count' => $syncedCount,
                'remaining'    => max(0, $totalPosts - $syncedCount),
                'api_url'      => $url,
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
            // Récupérer ce post spécifique via l'API
            $url = $this->buildApiUrl("/posts/{$wpId}", ['_embed' => 'true']);
            $wpPost = $this->apiRequest($url);

            if (!$wpPost || isset($wpPost['code'])) {
                SyncLog::error("Post WP#{$wpId} introuvable via l'API.");
                return false;
            }

            $this->syncSinglePost($wpPost, $space);
            return true;

        } catch (\Exception $e) {
            SyncLog::error("Retry sync échoué pour WP#$wpId : " . $e->getMessage());
            return false;
        }
    }
}
