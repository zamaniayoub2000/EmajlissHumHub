<?php

namespace humhub\modules\externalHtmlStream\jobs;

use Yii;
use humhub\modules\queue\ActiveJob;
use humhub\modules\externalHtmlStream\models\ExternalPost;

/**
 * Job de rafraîchissement des publications HTML externes (API).
 *
 * Exécuté via le système de queue HumHub.
 */
class RefreshExternalPostsJob extends ActiveJob
{
    /** @var int|null ID spécifique d'un post à rafraîchir (null = tous) */
    public $postId;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->postId !== null) {
            $this->refreshSinglePost($this->postId);
            return;
        }

        $this->refreshAllPosts();
    }

    protected function refreshSinglePost(int $postId): void
    {
        $post = ExternalPost::findOne($postId);
        if ($post === null) {
            Yii::warning("RefreshJob: Post #{$postId} introuvable.", 'external-html-stream');
            return;
        }

        if ($post->fetchContent()) {
            Yii::info("RefreshJob: Post #{$postId} rafraîchi.", 'external-html-stream');
        } else {
            Yii::warning("RefreshJob: Échec pour le post #{$postId}.", 'external-html-stream');
        }
    }

    protected function refreshAllPosts(): void
    {
        $posts = ExternalPost::findNeedingRefresh();
        $success = 0;
        $failed = 0;

        foreach ($posts as $post) {
            if ($post->fetchContent()) {
                $success++;
            } else {
                $failed++;
            }
        }

        Yii::info(
            "RefreshJob: {$success} post(s) rafraîchi(s), {$failed} échec(s).",
            'external-html-stream'
        );
    }
}
