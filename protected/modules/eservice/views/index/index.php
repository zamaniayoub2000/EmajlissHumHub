<?php

use yii\helpers\Url;

/** @var yii\web\View $this */

\humhub\modules\eservice\assets\EServiceAsset::register($this);
$this->title = 'E-Services';
?>

<div class="container">
    <!-- Header -->
    <div class="es-header">
        <h1><i class="fa fa-cogs"></i> E-SERVICES</h1>
        <p>Portail de gestion des demandes de services</p>
    </div>

    <!-- Navigation -->
    <div class="es-nav">
        <a href="<?= Url::to(['/eservice/index/dashboard']) ?>" class="es-btn-secondary">
            <i class="fa fa-list-alt"></i> Mes demandes
        </a>
    </div>

    <!-- Service Cards Grid -->
    <div class="es-cards-grid">

        <!-- 1. HEBERGEMENT -->
        <div class="es-card es-card--bordeaux">
            <div class="es-card-icon">
                <i class="fa fa-bed"></i>
            </div>
            <div class="es-card-title">H&eacute;bergement</div>
            <div class="es-card-desc">Demande de r&eacute;servation d'h&eacute;bergement</div>
            <a href="<?= Url::to(['/eservice/request/create', 'type' => 'hebergement']) ?>" class="es-card-btn es-card-btn--bordeaux">
                Demander <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        <!-- 2. BILLET D'AVION -->
        <div class="es-card es-card--olive">
            <div class="es-card-icon">
                <i class="fa fa-plane"></i>
            </div>
            <div class="es-card-title">Billet d'avion</div>
            <div class="es-card-desc">Formuler votre demande de r&eacute;servation de billets d'avion</div>
            <a href="<?= Url::to(['/eservice/request/create', 'type' => 'billet_avion']) ?>" class="es-card-btn es-card-btn--olive">
                Demander <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        <!-- 3. DEMANDE DE DOCUMENTS -->
        <div class="es-card es-card--dark-olive">
            <div class="es-card-icon">
                <i class="fa fa-file-text"></i>
            </div>
            <div class="es-card-title">Demande de documents</div>
            <div class="es-card-desc">Formuler votre demande de documents</div>
            <a href="<?= Url::to(['/eservice/request/documents']) ?>" class="es-card-btn es-card-btn--dark-olive">
                Demander <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        <!-- 4. DEPOT DE DOCUMENTS ET JUSTIFICATIFS -->
        <div class="es-card es-card--teal">
            <div class="es-card-icon">
                <i class="fa fa-folder-open"></i>
            </div>
            <div class="es-card-title">D&eacute;p&ocirc;t de documents</div>
            <div class="es-card-desc">D&eacute;poser vos justificatifs et documents administratifs</div>
            <a href="<?= Url::to(['/eservice/request/create', 'type' => 'indemnite']) ?>" class="es-card-btn es-card-btn--teal">
                Demander <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        <!-- 5. DEMANDE DE SUPPORT -->
        <div class="es-card es-card--steel">
            <div class="es-card-icon">
                <i class="fa fa-life-ring"></i>
            </div>
            <div class="es-card-title">Demande de support</div>
            <div class="es-card-desc">Formuler votre demande d'assistance ou de support technique</div>
            <a href="<?= Url::to(['/eservice/request/create', 'type' => 'support']) ?>" class="es-card-btn es-card-btn--steel">
                Demander <i class="fa fa-arrow-right"></i>
            </a>
        </div>

    </div>
</div>
