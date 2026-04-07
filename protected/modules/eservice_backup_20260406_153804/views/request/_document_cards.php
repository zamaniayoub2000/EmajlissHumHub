<?php

use yii\helpers\Url;

/** @var yii\web\View $this */

\humhub\modules\eservice\assets\EServiceAsset::register($this);
$this->title = 'Demande de Documents';
?>

<div class="container">
    <!-- Header -->
    <div class="es-header">
        <h1><i class="fa fa-file-alt"></i> DEMANDE DE DOCUMENTS</h1>
        <p>S&eacute;lectionnez le type de demande documentaire</p>
    </div>

    <!-- Navigation -->
    <div class="es-nav">
        <a href="<?= Url::to(['/eservice/index/index']) ?>" class="es-back-btn">
            <i class="fa fa-arrow-left"></i> Retour aux services
        </a>
        <a href="<?= Url::to(['/eservice/index/dashboard']) ?>" class="es-back-btn">
            <i class="fa fa-list-alt"></i> Mes demandes
        </a>
    </div>

    <!-- Document Type Cards -->
    <div class="es-doc-cards">

        <!-- 1. Reservation d'un ouvrage -->
        <div class="es-doc-card">
            <div class="es-doc-card-icon">
                <i class="fa fa-book"></i>
            </div>
            <div class="es-doc-card-title">R&eacute;servation d'un ouvrage pour pr&ecirc;t physique</div>
            <a href="<?= Url::to(['/eservice/request/create-document', 'subType' => 'reservation']) ?>" class="es-doc-card-btn">
                Demander <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        <!-- 2. Bulletin Officiel -->
        <div class="es-doc-card">
            <div class="es-doc-card-icon">
                <i class="fa fa-newspaper"></i>
            </div>
            <div class="es-doc-card-title">Demande d'un Bulletin Officiel</div>
            <a href="<?= Url::to(['/eservice/request/create-document', 'subType' => 'bulletin']) ?>" class="es-doc-card-btn">
                Demander <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        <!-- 3. Dossier documentaire -->
        <div class="es-doc-card">
            <div class="es-doc-card-icon">
                <i class="fa fa-folder-open"></i>
            </div>
            <div class="es-doc-card-title">Demande de constitution d'un dossier documentaire</div>
            <a href="<?= Url::to(['/eservice/request/create-document', 'subType' => 'dossier']) ?>" class="es-doc-card-btn">
                Demander <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        <!-- 4. Documentation diverse -->
        <div class="es-doc-card">
            <div class="es-doc-card-icon">
                <i class="fa fa-books"></i>
            </div>
            <div class="es-doc-card-title">Demande de documentation diverse</div>
            <a href="<?= Url::to(['/eservice/request/create-document', 'subType' => 'documentation']) ?>" class="es-doc-card-btn">
                Demander <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        <!-- 5. Proposition de titres -->
        <div class="es-doc-card">
            <div class="es-doc-card-icon">
                <i class="fa fa-lightbulb"></i>
            </div>
            <div class="es-doc-card-title">Proposition de titres d'ouvrages pour acquisition</div>
            <a href="<?= Url::to(['/eservice/request/create-document', 'subType' => 'proposition']) ?>" class="es-doc-card-btn">
                Demander <i class="fa fa-arrow-right"></i>
            </a>
        </div>

    </div>
</div>
