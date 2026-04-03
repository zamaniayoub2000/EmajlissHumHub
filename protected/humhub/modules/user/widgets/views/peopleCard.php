<?php
use humhub\helpers\Html;
use humhub\modules\user\models\User;
use humhub\modules\user\widgets\Image;
use humhub\modules\user\widgets\PeopleActionButtons;
use humhub\modules\user\widgets\PeopleDetails;
use humhub\modules\user\widgets\PeopleTagList;
use yii\web\View;

/* @var $this View */
/* @var $user User */

$hasCover     = $user->getProfileBannerImage()->hasImage();
$coverUrl     = $hasCover ? $user->getProfileBannerImage()->getUrl() : null;
$userUrl      = $user->getUrl();
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap');

.tc-people-card {
  --tc-hover:  #3c5f8c;
  --tc-border: rgba(15,23,42,.08);
  --tc-shadow: 0 2px 8px rgba(0,0,0,.04), 0 12px 32px rgba(0,0,0,.06);
  --tc-title:  rgba(17,24,39,.95);
  --tc-muted:  rgba(71,85,105,.85);
  --tc-radius: 20px;
  --tc-cover-h: 140px;
  --tc-body-h:  180px;
  font-family: 'Ubuntu', sans-serif !important;
}

.tc-people-card-link {
  display: block;
  text-decoration: none !important;
  color: inherit !important;
  border-radius: var(--tc-radius);
  outline-offset: 3px;
}
.tc-people-card-link:focus-visible {
  outline: 2px solid #3c5f8c;
}

/* Kill inner <a> from Image::widget() */
.tc-people-card .tc-avatar a {
  pointer-events: none !important;
  cursor: default !important;
  display: contents !important;
}

.tc-people-card.card-panel {
  border-radius: var(--tc-radius) !important;
  padding: 0 !important;
  border: 1px solid var(--tc-border) !important;
  background: #fff !important;
  box-shadow: var(--tc-shadow) !important;
  overflow: hidden !important;
  position: relative !important;
  display: flex !important;
  flex-direction: column !important;
  width: 100% !important;
}

/* -- Cover --------------------------------------------------- */
.tc-people-card .tc-cover {
  position: relative;
  /* Use hard px values — no CSS variables here so nothing can break */
  height: 140px;
  min-height: 140px;
  max-height: 140px;
  flex: 0 0 140px;
  width: 100%;
  overflow: hidden;
}

/* The background image div fills the cover completely */
.tc-people-card .tc-cover__bg {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  /* Fallback gradient — overridden by inline style when cover exists */
  background-image: linear-gradient(
    135deg,
    rgba(165,53,53,.22) 0%,
    rgba(6,56,59,.22) 55%,
    rgba(205,133,63,.16) 100%
  );
}

.tc-people-card .tc-cover__shade {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background:
    linear-gradient(180deg, rgba(0,0,0,.02) 0%, rgba(0,0,0,.08) 40%, rgba(0,0,0,.26) 100%),
    linear-gradient(135deg, rgba(165,53,53,.08) 0%, transparent 50%);
  pointer-events: none;
  z-index: 1;
}

/* Avatar pinned to bottom-left of cover */
.tc-people-card .tc-top {
  position: absolute;
  left: 18px;
  bottom: 14px;
  z-index: 2;
}

.tc-people-card .tc-avatar .profile-user-photo,
.tc-people-card .tc-avatar img {
  width: 80px !important;
  height: 80px !important;
  border-radius: 16px !important;
  border: 4px solid rgba(255,255,255,.97) !important;
  box-shadow: 0 4px 14px rgba(0,0,0,.18), 0 14px 36px rgba(0,0,0,.22) !important;
  background: #fff !important;
  object-fit: cover !important;
}

/* -- Body ---------------------------------------------------- */
.tc-people-card .tc-body {
  position: relative;
  z-index: 1;
  font-family: 'Ubuntu', sans-serif !important;
  background: #fff;
  border-top: 1px solid rgba(15,23,42,.06);
  border-bottom-left-radius: var(--tc-radius);
  border-bottom-right-radius: var(--tc-radius);
  /* Hard px — no variables */
  flex: 0 0 180px;
  height: 180px;
  min-height: 180px;
  max-height: 180px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.tc-people-card .tc-body-inner {
  display: flex;
  flex-direction: column;
  flex: 1 1 auto;
  overflow: hidden;
}

/* Name */
.tc-people-card .tc-name {
  font-family: 'Ubuntu', sans-serif !important;
  font-size: 17px;
  font-weight: 700;
  color: var(--tc-title);
  letter-spacing: -0.01em;
  margin: 0;
  padding: 0 20px;
  background: #fff;
  height: 52px;
  min-height: 52px;
  max-height: 52px;
  flex: 0 0 52px;
  line-height: 52px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
  transition: color .3s ease;
}
.tc-people-card-link:hover .tc-name { color: var(--tc-hover); }

/* Separator */
.tc-people-card .tc-separator {
  border: none;
  height: 1px;
  flex: 0 0 1px;
  background: rgba(15,23,42,.08);
  margin: 0 20px;
}

/* Sub/details */
.tc-people-card .tc-sub-wrap {
  padding: 10px 20px 8px;
  background: #fff;
  overflow: hidden;
  flex: 1 1 auto;
}

.tc-people-card .tc-sub {
  font-family: 'Ubuntu', sans-serif !important;
  font-size: 13px;
  color: var(--tc-muted);
  line-height: 1.55;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.tc-people-card .card-details {
  font-family: 'Ubuntu', sans-serif !important;
  font-size: 12.5px;
  color: var(--tc-muted);
  line-height: 1.55;
  margin-top: 3px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Tags */
.tc-people-card .card-tags {
  display: flex;
  flex-wrap: nowrap;
  gap: 6px;
  padding: 0 20px 12px;
  overflow: hidden;
  flex: 0 0 auto;
}

.tc-people-card .card-tags .label,
.tc-people-card .card-tags .badge,
.tc-people-card .card-tags a {
  font-family: 'Ubuntu', sans-serif !important;
  display: inline-flex !important;
  align-items: center !important;
  padding: 4px 10px !important;
  border-radius: 999px !important;
  border: 1px solid rgba(15,23,42,.08) !important;
  background: rgba(248,250,252,.8) !important;
  color: rgba(51,65,85,.8) !important;
  font-weight: 500 !important;
  font-size: 11px !important;
  text-decoration: none !important;
  white-space: nowrap;
  flex-shrink: 0;
}

.tc-people-card .tc-footer { display: none !important; }

/* -- Mobile -------------------------------------------------- */
@media (max-width: 768px) {
  .tc-people-card { --tc-radius: 16px; }

  .tc-people-card .tc-cover {
    height: 120px;
    min-height: 120px;
    max-height: 120px;
    flex: 0 0 120px;
  }
  .tc-people-card .tc-body {
    flex: 0 0 155px;
    height: 155px;
    min-height: 155px;
    max-height: 155px;
  }
  .tc-people-card .tc-name {
    font-size: 15px;
    padding: 0 16px;
    line-height: 46px;
    height: 46px;
    min-height: 46px;
    max-height: 46px;
    flex: 0 0 46px;
  }
  .tc-people-card .tc-sub-wrap { padding: 8px 16px 8px; }
  .tc-people-card .tc-sub      { font-size: 12px; }
  .tc-people-card .tc-separator { margin: 0 16px; }
  .tc-people-card .tc-avatar .profile-user-photo,
  .tc-people-card .tc-avatar img { width: 64px !important; height: 64px !important; }
  .tc-people-card .card-tags { padding: 0 16px 10px; }
}

@media (prefers-reduced-motion: reduce) {
  .tc-people-card .tc-name { transition: none !important; }
}
</style>

<a href="<?= Html::encode($userUrl) ?>"
   class="tc-people-card-link"
   aria-label="<?= Html::encode($user->displayName) ?>">

    <div class="card-panel tc-people-card"
         data-user-guid="<?= Html::encode($user->guid) ?>">

        <div class="tc-cover">
            <div class="tc-cover__bg"
                 style="background-image: <?= $hasCover
                     ? 'url(\'' . Html::encode($coverUrl) . '\')'
                     : 'linear-gradient(135deg, rgba(165,53,53,.22) 0%, rgba(6,56,59,.22) 55%, rgba(205,133,63,.16) 100%)'
                 ?>">
            </div>
            <div class="tc-cover__shade" aria-hidden="true"></div>
            <div class="tc-top">
                <div class="tc-avatar">
                    <?= Image::widget([
                        'user'                => $user,
                        'width'               => 80,
                        'showSelfOnlineStatus' => false,
                        'link'                => false,
                        'htmlOptions'         => [],
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="tc-body">
            <div class="tc-body-inner">
                <div class="tc-name"><?= Html::encode($user->displayName) ?></div>
                <?php
                $hasSub      = !empty($user->displayNameSub);
                $detailsHtml = PeopleDetails::widget(['user' => $user, 'template' => '{lines}', 'separator' => '<br>']);
                $hasDetails  = trim(strip_tags($detailsHtml)) !== '';
                ?>
                <hr class="tc-separator">
                <div class="tc-sub-wrap">
                    <?php if ($hasSub): ?><p class="tc-sub"><?= Html::encode($user->displayNameSub) ?></p><?php endif; ?>
                    <?php if ($hasDetails): ?><div class="card-details"><?= $detailsHtml ?></div><?php endif; ?>
                </div>
                <?= PeopleTagList::widget([
                    'user'     => $user,
                    'template' => '<div class="card-tags">{tags}</div>',
                ]) ?>
            </div>
        </div>

        <?= PeopleActionButtons::widget([
            'user'     => $user,
            'template' => '<div class="tc-footer">{buttons}</div>',
        ]); ?>

    </div>

</a>