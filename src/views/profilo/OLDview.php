<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni
 * @category   CategoryName
 */

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\datecontrol\DateControl;
use yii\helpers\Url;
use lispa\amos\core\forms\ContextMenuWidget;
use lispa\amos\organizzazioni\widgets\maps\PlaceWidget;
use lispa\amos\organizzazioni\Module;
use lispa\amos\core\forms\Tabs;

/**
 *
 * @var yii\web\View $this
 * @var lispa\amos\organizzazioni\models\Profilo $model
 */
$this->title = strip_tags($model);
$this->params['breadcrumbs'][] = ['label' => 'Organizzazioni', 'url' => ['/organizzazioni']];
$this->params['breadcrumbs'][] = ['label' => 'Organizzazioni', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="organizzazioni col-xs-12 nop">

    <?php
    $idTabGeneral = 'tab-general';
    $idTabAttachment = 'tab-attachment';

    $url  = '/img/img_default.jpg';
    if (!is_null($model->logoOrganization)) {
        $url = $model->logoOrganization->getUrl('original', [
            'class' => 'img-responsive'
        ]);
    }


    // TAB
    $this->beginBlock($idTabGeneral);
    // TAB SCHEDA
    ?>

    <section class="section-data">
        <div class="col-xs-12 nop">
            <div class="pull-right">
                <?= ContextMenuWidget::widget([
                    'model' => $model,
                    'actionModify' => "/organizzazioni/profilo/update?id=" . $model->id,
                    'disableDelete' => true
                ]) ?>
            </div>

            <div class="col-lg-2 col-xs-12 nop">
                <div class="container-round-img">
                    <img class="img-responsive" src="<?= $url ?>" alt="<?= $model->name ?>">
                </div>
            </div>

            <div class="col-lg-10 col-xs-12 nop">
                <div class="col-lg-4 col-xs-12 nop">
                    <label><?= $model->getAttributeLabel('name') . ' : ' ?></label>
                    <span><?= $model->name ?></span>
                </div>
                <div class="col-lg-3 col-xs-12 nop">
                    <label><?= $model->getAttributeLabel('partita_iva') . ' : ' ?></label>
                    <span><?= $model->partita_iva ?></span>
                </div>
                <div class="col-lg-3 col-xs-12 nop">
                    <label><?= $model->getAttributeLabel('codice_fiscale') . ' : ' ?></label>
                    <span><?= $model->codice_fiscale ?></span>
                </div>
                <div class="col-xs-12 nop">
                    <label><?= $model->getAttributeLabel('presentazione_della_organizzaz') . ' : ' ?></label>
                    <span><?= $model->presentazione_della_organizzaz ?></span>
                </div>
            </div>

            <div class="col-lg-6 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('tipologia_di_organizzazione') . ' : ' ?></label>
                <span><?= !empty($model->tipologiaDiOrganizzazione) ? $model->tipologiaDiOrganizzazione->name  : ''?></span>
            </div>
            <div class="col-lg-6 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('forma_legale') . ' : ' ?></label>
                <span><?= !empty($model->formaLegale) ? $model->formaLegale->name : "" ?></span>
            </div>

            <div class="clearfix"></div>

            <hr>

            <div class="col-lg-12 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('sito_web') . ' : ' ?></label>
                <span><?= $model->sito_web ?></span>
            </div>
            <div class="col-lg-6 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('facebook') . ' : ' ?></label>
                <span><?= $model->facebook ?></span>
            </div>
            <div class="col-lg-6 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('twitter') . ' : ' ?></label>
                <span><?= $model->twitter ?></span>
            </div>
            <div class="col-lg-6 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('linkedin') . ' : ' ?></label>
                <span><?= $model->linkedin ?></span>
            </div>
            <div class="col-lg-6 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('google') . ' : ' ?></label>
                <span><?= $model->google ?></span>
            </div>

            <hr>

            <div class="col-lg-12 col-xs-12 nop">
                    <label><?= $model->getAttributeLabel('indirizzo') . ' : ' ?></label>
                    <span><?= $model->getAddressField() ?></span>
            </div>
            <?php
            $sedeIndirizzo = $model->sedeIndirizzo;
            if($sedeIndirizzo) {?>
                <div class="col-lg-6 nop">
                    <?php
                        echo \lispa\amos\core\forms\MapWidget::widget([
                            'coordinates' => [
                                'lat' => $sedeIndirizzo->latitude,
                                'lng' => $sedeIndirizzo->longitude,
                            ],
                    'zoom' =>17
                    ]);?>
                </div>
            <?php } ?>

            <hr>

            <div class="col-lg-6 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('telefono') . ' : ' ?></label>
                <span><?= $model->telefono ?></span>
            </div>
            <div class="col-lg-6 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('email') . ' : ' ?></label>
                <span><?= $model->email ?></span>
            </div>
            <div class="col-lg-6 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('fax') . ' : ' ?></label>
                <span><?= $model->fax ?></span>
            </div>
            <div class="col-lg-6 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('pec') . ' : ' ?></label>
                <span><?= $model->pec ?></span>
            </div>

            <hr>

            <?php if (!$model->la_sede_legale_e_la_stessa_del): ?>
                <div class="col-lg-12 col-xs-12 nop">
                    <label><?= $model->getAttributeLabel('sede_legale_indirizzo') . ' : ' ?></label>
                    <span><?= $model->getAddressFieldSedeLegale() ?></span>
                </div>
                <?php
                $sedeLegaleIndirizzo = $model->sedeLegaleIndirizzo;
                if($sedeLegaleIndirizzo) {?>
                    <div class="col-lg-6 nop">
                        <?php
                        echo \lispa\amos\core\forms\MapWidget::widget([
                            'coordinates' => [
                                'lat' => $sedeLegaleIndirizzo->latitude,
                                'lng' => $sedeLegaleIndirizzo->longitude,
                            ],
                            'zoom' =>17
                        ]);?>
                    </div>
                <?php } ?>

                <hr>

                <div class="col-lg-6 col-xs-12 nop">
                    <label><?= $model->getAttributeLabel('sede_legale_telefono') . ' : ' ?></label>
                    <span><?= $model->sede_legale_telefono ?></span>
                </div>
                <div class="col-lg-6 col-xs-12 nop">
                    <label><?= $model->getAttributeLabel('sede_legale_email') . ' : ' ?></label>
                    <span><?= $model->sede_legale_email ?></span>
                </div>
                <div class="col-lg-6 col-xs-12 nop">
                    <label><?= $model->getAttributeLabel('sede_legale_fax') . ' : ' ?></label>
                    <span><?= $model->sede_legale_fax ?></span>
                </div>
                <div class="col-lg-6 col-xs-12 nop">
                    <label><?= $model->getAttributeLabel('sede_legale_pec') . ' : ' ?></label>
                    <span><?= $model->sede_legale_pec ?></span>
                </div>

                <hr>

            <?php endif; ?>

            <div class="col-lg-4 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('responsabile') . ' : ' ?></label>
                <span><?= $model->responsabile ?></span>
            </div>
            <div class="col-lg-4 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('rappresentante_legale') . ' : ' ?></label>
                <span><?= !empty($model->rappresentanteLegale) ? $model->rappresentanteLegale->nomeCognome  : ""?></span>
            </div>
            <div class="col-lg-4 col-xs-12 nop">
                <label><?= $model->getAttributeLabel('referente_operativo') . ' : ' ?></label>
                <span><?= !empty($model->referenteOperativo) ? $model->referenteOperativo->nomeCognome : ""?></span>
            </div>

        </div>
    </section>

    <?php
    $this->endBlock($idTabGeneral);
    $itemsTab[] = [
        'label' => Module::t('amosorganizzazioni', 'Generale'),
        'content' => $this->blocks[$idTabGeneral],
        'options' => ['id' => $idTabGeneral],
    ];
    ?>

    <?php
    $this->beginBlock($idTabAttachment);
    // TAB SCHEDA
    ?>

    <section class="section-data">
        <div class="col-xs-12 nop">
            <div class="pull-right">
                <?= ContextMenuWidget::widget([
                    'model' => $model,
                    'actionModify' => "/organizzazioni/profilo/update?id=" . $model->id,
                    'disableDelete' => true,
                ]) ?>
            </div>
            <h3><?= Module::t('amosorganizzazioni', '#attachments')?></h3>
            <div class="clearfix"></div>
            <?= \lispa\amos\attachments\components\AttachmentsTableWithPreview::widget([
                'model' => $model,
                'attribute' => 'allegati'
            ]) ?>
        </div>
    </section>

    <?php
    $this->endBlock($idTabAttachment);
    $itemsTab[] = [
        'label' => Module::t('amosorganizzazioni', 'Allegati'),
        'content' => $this->blocks[$idTabAttachment],
        'options' => ['id' => $idTabAttachment],
    ];
    ?>

    <?php echo Tabs::widget(
        [
            //'encodeLabels' => false,
            'items' => $itemsTab
        ]
    );
    ?>

</div>
