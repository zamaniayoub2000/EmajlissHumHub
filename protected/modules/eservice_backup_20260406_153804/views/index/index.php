<?php

use yii\helpers\Url;

/** @var yii\web\View $this */

\humhub\modules\eservice\assets\EServiceAsset::register($this);
$this->title = 'E-Services';
?>

<div class="container">
    <!-- Header -->
    <div class="es-header">
        <h1><i class="fa fa-concierge-bell"></i> E-SERVICES</h1>
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
                <i class="fa fa-hotel"></i>
            </div>
            <div class="es-card-title">H&eacute;bergement</div>
            <div class="es-card-desc">Demande de r&eacute;servation d'h&eacute;bergement pour les manifestations</div>
            <a href="<?= Url::to(['/eservice/request/create', 'type' => 'hebergement']) ?>" class="es-card-btn es-card-btn--bordeaux">
                Consulter <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        <!-- 2. BILLET D'AVION -->
        <div class="es-card es-card--olive">
            <div class="es-card-icon">
                <i class="fa fa-plane"></i>
            </div>
            <div class="es-card-title">Billet d'avion</div>
            <div class="es-card-desc">Demande de r&eacute;servation de billets d'avion</div>
            <a href="<?= Url::to(['/eservice/request/create', 'type' => 'billet_avion']) ?>" class="es-card-btn es-card-btn--olive">
                Consulter <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        <!-- 3. DEMANDE DE DOCUMENTS -->
        <div class="es-card es-card--dark-olive">
            <div class="es-card-icon">
                <i class="fa fa-file-alt"></i>
            </div>
            <div class="es-card-title">Demande de documents</div>
            <div class="es-card-desc">Acc&eacute;dez aux diff&eacute;rents types de demandes documentaires</div>
            <a href="<?= Url::to(['/eservice/request/documents']) ?>" class="es-card-btn es-card-btn--dark-olive">
                Consulter <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        <!-- 4. SUIVI DES INDEMNITES -->
        <div class="es-card es-card--teal">
            <div class="es-card-icon">
                <i class="fa fa-money-bill-wave"></i>
            </div>
            <div class="es-card-title">Suivi des indemnit&eacute;s</div>
            <div class="es-card-desc">Suivi et demande d'indemnit&eacute;s li&eacute;es aux manifestations</div>
            <a href="<?= Url::to(['/eservice/request/create', 'type' => 'indemnite']) ?>" class="es-card-btn es-card-btn--teal">
                Consulter <i class="fa fa-arrow-right"></i>
            </a>
        </div>

        <!-- 5. DEMANDE DE SUPPORT -->
        <div class="es-card es-card--steel">
            <div class="es-card-icon">
                <i class="fa fa-question-circle"></i>
            </div>
            <div class="es-card-title">Demande de support</div>
            <div class="es-card-desc">Assistance technique et demandes de support</div>
            <a href="<?= Url::to(['/eservice/request/create', 'type' => 'support']) ?>" class="es-card-btn es-card-btn--steel">
                Consulter <i class="fa fa-arrow-right"></i>
            </a>
        </div>

    </div>
</div>
