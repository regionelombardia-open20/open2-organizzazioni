<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\organizzazioni\views\profilo
 * @category   CategoryName
 */

use open20\amos\attachments\components\AttachmentsList;
use open20\amos\community\models\CommunityUserMm;
use open20\amos\community\widgets\JoinCommunityWidget;
use open20\amos\core\forms\AccordionWidget;
use open20\amos\core\forms\ContextMenuWidget;
use open20\amos\core\forms\ListTagsWidget;
use open20\amos\core\forms\MapWidget;
use open20\amos\core\helpers\Html;
use open20\amos\core\icons\AmosIcons;
use open20\amos\core\user\User;
use open20\amos\organizzazioni\assets\OrganizzazioniAsset;
use open20\amos\organizzazioni\models\ProfiloSedi;
use open20\amos\organizzazioni\Module;
use open20\amos\organizzazioni\widgets\JoinProfiloWidget;
use open20\amos\core\utilities\StringUtils;


/**
 * @var yii\web\View $this
 * @var open20\amos\organizzazioni\models\Profilo $model
 */

$moduleL = \Yii::$app->getModule('layout');
if (!empty($moduleL)) {
    OrganizzazioniAsset::register($this);
}

$jsReadMore = <<< JS

$("#moreTextJs .changeContentJs > .actionChangeContentJs").click(function(){
    $("#moreTextJs .changeContentJs").toggle();
    $('html, body').animate({scrollTop: $('#moreTextJs').offset().top - 120},1000);
});
JS;
$this->registerJs($jsReadMore);

$this->title = strip_tags($model->title);
$this->params['breadcrumbs'][] = $this->title;

/** @var Module $organizzazioniModule */
$organizzazioniModule = Yii::$app->getModule(Module::getModuleName());

/** @var ProfiloSedi $emptyProfiloSedi */
$emptyProfiloSedi = Module::instance()->createModel('ProfiloSedi');

$operativeHeadquarter = $model->operativeHeadquarter;
$hasOperativeHeadquarter = !empty($operativeHeadquarter);

$loggedUserId = Yii::$app->user->id;
/** @var User $loggedUser */
$loggedUser = Yii::$app->user->identity;
$loggedUserProfile = $loggedUser->userProfile;

$legalHeadquarter = $model->legalHeadquarter;
$hasLegalHeadquarter = !is_null($legalHeadquarter);


?>

<div class="organizzazioni-view">

    <div class="container">


        <!-- new temp -->
        <div class="row">
            <div class="col-xs-12 actions m-b-30">
                <?php if (!$organizzazioniModule->enableWorkflow || ($organizzazioniModule->enableWorkflow && ($model->status == $model->getValidatedStatus()))) : ?>
                    <?php if (!$model->userIsEmployee($loggedUserId) && Yii::$app->user->can('ASSOCIATE_ORGANIZZAZIONI_TO_USER', ['model' => $loggedUserProfile])) : ?>

                        <?= JoinProfiloWidget::widget([
                            'model' => $model,
                            'userId' => $loggedUserId,
                            'btnClass' => 'btn btn-navigation-secondary',
                            'customBtnLabel' => Module::t('amosorganizzazioni', '#ask_to_be_employee'),
                        ]) ?>

                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="row m-t-15">
            <div class="col-xs-12 col-md-8">
                <?php if (!empty($model->sito_web)) : ?>
                    <?php $btnTitle = Module::t('amosorganizzazioni', '#visit_website_btn_title'); ?>
                    <?= Html::a(
                        AmosIcons::show('globe-alt') . ' ' . $model->sito_web,
                        $model->sito_web,
                        [
                            'title' => $btnTitle,
                            'class' => '',
                            'target' => '_blank'
                        ]
                    ); ?>
                <?php endif; ?>
            </div>
            <div class="col-xs-12 col-md-4">
                <?php if ($organizzazioniModule->enableSocial) : ?>
                    <?php
                    AmosIcons::show('facebook-box') . Html::tag('span', $model->facebook);
                    AmosIcons::show('twitter-box') . Html::tag('span', $model->twitter);
                    AmosIcons::show('linkedin-box') . Html::tag('span', $model->linkedin);
                    ?>
                    <?= $accordionSocial ?>
                <?php endif; ?>
            </div>
        </div>
        <hr class="m-t-30 m-b-30 border-dotted" style="border-style:dotted">
        <div class="row">
            <div class="col-xs-12 col-md-4">
                <div class="row">
                    <div class="col-xs-12 info-label"><?= $model->getAttributeLabel('responsabile') ?></div>
                    <div class="col-xs-12 info-value"><?= $model->responsabile ?></div>
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <div class="row">
                    <div class="col-xs-12 info-label">
                        <?= $model->getAttributeLabel('rappresentante_legale') ?>
                    </div>
                    <?php if ($organizzazioniModule->enableRappresentanteLegaleText) : ?>
                        <div class="col-xs-12 info-value"><?= !empty($model->rappresentante_legale_text) ? $model->rappresentante_legale_text : "" ?></div>
                    <?php else : ?>
                        <div class="col-xs-12 info-value"><?= !empty($model->rappresentanteLegale) ? $model->rappresentanteLegale->nomeCognome : "" ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <div class="row">
                    <div class="col-xs-12 info-label"><?= $model->getAttributeLabel('referente_operativo') ?></div>
                    <div class="col-xs-12 info-value"><?= !empty($model->referenteOperativo) ? $model->referenteOperativo->nomeCognome : "" ?></div>
                </div>
            </div>
        </div>

        <hr class="m-t-30 m-b-30 border-dotted" style="border-style:dotted">

        <?php
        $classSectionSedi = (!$organizzazioniModule->forceSameSede && $hasLegalHeadquarter) ? 'col-md-6' : '';
        ?>
        <div class="row">
            <div class="col-xs-12 <?= $classSectionSedi ?>">

                <div class="callout">
                    <div class="callout-title">
                        <?= Module::t('amosorganizzazioni', 'Sede operativa') ?>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 info-label"><?= $emptyProfiloSedi->getAttributeLabel('email') ?></div>
                        <div class="col-xs-12 col-sm-8 info-value"><?= $hasOperativeHeadquarter ? $operativeHeadquarter->email : '-' ?></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 info-label"><?= $emptyProfiloSedi->getAttributeLabel('pec') ?></div>
                        <div class="col-xs-12 col-sm-8 info-value"><?= $hasOperativeHeadquarter ? $operativeHeadquarter->pec : '-' ?></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 info-label"><?= $emptyProfiloSedi->getAttributeLabel('phone') ?></div>
                        <div class="col-xs-12 col-sm-8 info-value"><?= $hasOperativeHeadquarter ? $operativeHeadquarter->phone : '-' ?></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 info-label"><?= $emptyProfiloSedi->getAttributeLabel('fax') ?></div>
                        <div class="col-xs-12 col-sm-8 info-value"><?= $hasOperativeHeadquarter ? $operativeHeadquarter->fax : '-' ?></div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 info-label"><?= $emptyProfiloSedi->getAttributeLabel('address') ?></div>
                        <div class="col-xs-12 col-sm-8 info-value"><?= $model->getAddressFieldForView() ?></div>
                    </div>

                    <div class="map m-t-30">
                        <?php
                        if (!$organizzazioniModule->oldStyleAddressEnabled) {
                            $sedeIndirizzo = $model->sedeIndirizzo;
                            if ($sedeIndirizzo) {
                                echo Html::tag(
                                    'div',
                                    MapWidget::widget([
                                        'coordinates' => [
                                            'lat' => $sedeIndirizzo->latitude,
                                            'lng' => $sedeIndirizzo->longitude,
                                        ],
                                        'zoom' => 17
                                    ]),
                                    ['class' => 'organization-header-map']
                                );
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- < ?php if (!$organizzazioniModule->forceSameSede) : ?> -->

            <div class="col-xs-12 <?= $classSectionSedi ?>">

                <div class="callout callout-secondary">
                    <div class="callout-title">
                        <?= Module::t('amosorganizzazioni', 'Sede legale') ?>
                    </div>


                    <?php
                    $accordionSedeLegale = '';
                    $sedeLegaleIndirizzo = '';
                    $mapSedeLegale = '';

                    if (!$organizzazioniModule->oldStyleAddressEnabled) {
                        $sedeLegaleIndirizzo = $model->sedeLegaleIndirizzo;
                        if ($sedeLegaleIndirizzo) {
                            $mapSedeLegale = MapWidget::widget([
                                'coordinates' => [
                                    'lat' => $sedeLegaleIndirizzo->latitude,
                                    'lng' => $sedeLegaleIndirizzo->longitude,
                                ],
                                'zoom' => 17
                            ]);
                        }
                    }

                    if ($hasLegalHeadquarter) {
                        $slIndirizzo = Html::tag(
                            'div',
                            Html::tag(
                                'div',
                                $legalHeadquarter->getAttributeLabel('address'),
                                ['class' => 'col-xs-12 col-sm-4 info-label']
                            ) .
                                Html::tag(
                                    'div',
                                    $model->getAddressFieldSedeLegaleForView(),
                                    ['class' => 'col-xs-12 col-sm-8 info-value']
                                ),
                            ['class' => 'col-xs-12 nop']
                        );

                        $slEmail = Html::tag(
                            'div',
                            Html::tag(
                                'div',
                                $legalHeadquarter->getAttributeLabel('email'),
                                ['class' => 'col-xs-12 col-sm-4 info-label']
                            ) .
                                Html::tag(
                                    'div',
                                    $legalHeadquarter->email,
                                    ['class' => 'col-xs-12 col-sm-8 info-value']
                                ),
                            ['class' => 'col-xs-12 nop']
                        );

                        $slPec = Html::tag(
                            'div',
                            Html::tag(
                                'div',
                                $legalHeadquarter->getAttributeLabel('pec'),
                                ['class' => 'col-xs-12 col-sm-4 info-label']
                            ) .
                                Html::tag(
                                    'div',
                                    $legalHeadquarter->pec,
                                    ['class' => 'col-xs-12 col-sm-8 info-value']
                                ),
                            ['class' => 'col-xs-12 nop']
                        );

                        $slTelefono = Html::tag(
                            'div',
                            Html::tag(
                                'div',
                                $legalHeadquarter->getAttributeLabel('phone'),
                                ['class' => 'col-xs-12 col-sm-4 info-label']
                            ) .
                                Html::tag(
                                    'div',
                                    $legalHeadquarter->phone,
                                    ['class' => 'col-xs-12 col-sm-8 info-value']
                                ),
                            ['class' => 'col-xs-12 nop']
                        );

                        $slFax = Html::tag(
                            'div',
                            Html::tag(
                                'div',
                                $legalHeadquarter->getAttributeLabel('fax'),
                                ['class' => 'col-xs-12 col-sm-4 info-label']
                            ) .
                                Html::tag(
                                    'div',
                                    $legalHeadquarter->fax,
                                    ['class' => 'col-xs-12 col-sm-8 info-value']
                                ),
                            ['class' => 'col-xs-12 nop']
                        );

                        $accordionSedeLegale .= Html::tag(
                            'div',
                            $slEmail . $slPec . $slTelefono . $slFax . $slIndirizzo,
                            ['class' => 'row']
                        );

                        $accordionSedeLegale .= Html::tag(
                            'div',
                            $mapSedeLegale,
                            ['class' => 'm-t-30']
                        );
                    }

                    ?>
                    <?= $accordionSedeLegale ?>

                </div>
                <!-- < ?php endif; ?> -->

            </div>

            <div class="col-xs-12">
                <?= AccordionWidget::widget([
                    'items' => [
                        [
                            'header' => Module::t('amosorganizzazioni', '#other_headquarters'),
                            'content' => $this->render('_other_headquarters', ['model' => $model, 'isView' => true]),
                        ]
                    ],
                    'headerOptions' => ['tag' => 'h2'],
                    'clientOptions' => [
                        'collapsible' => true,
                        'active' => 1,
                        'icons' => [
                            'header' => 'ui-icon-amos am am-plus-square',
                            'activeHeader' => 'ui-icon-amos am am-minus-square',
                        ]
                    ],
                ]); ?>
            </div>
        </div>

        <hr class="m-t-30 m-b-30 border-dotted" style="border-style:dotted">

        <div class="row">
            <div class="col-xs-12">
                <?php
                $profiloEntiTypeNotNull = (!is_null($model->profiloEntiType));
                $profiloTipoStrutturaNotNull = (!is_null($model->tipologia_struttura_id));
                ?>
                <?php if ($organizzazioniModule->enableUniqueSecretCodeForInvitation && Yii::$app->user->can('PROFILO_UPDATE', ['model' => $model])) : ?>
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 info-label"><?= $model->getAttributeLabel('unique_secret_code') ?></div>
                        <div class="col-xs-12 col-sm-8 info-value"><?= $model->unique_secret_code; ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($organizzazioniModule->enableProfiloEntiType === true) : ?>
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 info-label"><?= $model->getAttributeLabel('profilo_enti_type_id') ?></div>
                        <div class="col-xs-12 col-sm-8 info-value"><?= $profiloEntiTypeNotNull ? $model->profiloEntiType->name : '' ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($organizzazioniModule->enableProfiloTipologiaStruttura === true) : ?>
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 info-label"><?= $model->getAttributeLabel('tipologia_struttura_id') ?></div>
                        <div class="col-xs-12 col-sm-8 info-value"><?= $profiloTipoStrutturaNotNull ? $model->tipologiaStruttura->name : '' ?></div>
                    </div>
                <?php endif; ?>
                <!-- if without else because the entity type must be present -->
                <?php if ($profiloEntiTypeNotNull && ($organizzazioniModule->enableProfiloEntiType === true)) : ?>
                    <?php if ($model->isMunicipality()) : ?>
                        <div class="row">
                            <div class="col-xs-12 col-sm-4 info-label"><?= $model->getAttributeLabel('istat_code') ?></div>
                            <div class="col-xs-12 col-sm-8 info-value"><?= !empty($model->istat_code) ? $model->istat_code : '' ?></div>
                        </div>
                    <?php elseif ($model->isOtherEntity() && ($organizzazioniModule->enableTipologiaOrganizzazione === true)) : ?>
                        <div class="row">
                            <div class="col-xs-12 col-sm-4 info-label"><?= $model->getAttributeLabel('tipologia_di_organizzazione') ?></div>
                            <div class="col-xs-12 col-sm-8 info-value"><?= !empty($model->tipologiaDiOrganizzazione) ? $model->tipologiaDiOrganizzazione->name : '-' ?></div>
                        </div>
                    <?php endif; ?>
                <?php elseif ($organizzazioniModule->enableTipologiaOrganizzazione === true) : ?>
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 info-label"><?= $model->getAttributeLabel('tipologia_di_organizzazione') ?></div>
                        <div class="col-xs-12 col-sm-8 info-value"><?= !empty($model->tipologiaDiOrganizzazione) ? $model->tipologiaDiOrganizzazione->name : '-' ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($organizzazioniModule->enableFormaLegale === true) : ?>
                    <div class="row">
                        <div class="col-xs-12 col-sm-4 info-label"><?= $model->getAttributeLabel('forma_legale') ?></div>
                        <div class="col-xs-12 col-sm-8 info-value"><?= !empty($model->formaLegale) ? $model->formaLegale->name : '-' ?></div>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <div class="col-xs-12 col-sm-4 info-label"><?= $model->getAttributeLabel('partita_iva') ?></div>
                    <div class="col-xs-12 col-sm-8 info-value"><?= ($model->partita_iva ? $model->partita_iva : '-') ?></div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-4 info-label"><?= $model->getAttributeLabel('codice_fiscale') ?></div>
                    <div class="col-xs-12 col-sm-8 info-value"><?= ($model->codice_fiscale ? $model->codice_fiscale : '-') ?></div>
                </div>
            </div>
        </div>

        <hr class="m-t-30 m-b-30 border-dotted" style="border-style:dotted">

        <?php if ($organizzazioniModule->enableMembershipOrganizations && !is_null($model->parent)) : ?>
            <div class="row">
                <div class="father-organization-image col-md-4 col-xs-12">
                    <?php
                    $urlParent = '/img/img_default.jpg';
                    if (!is_null($model->parent->logoOrganization)) {
                        $urlParent = $model->parent->logoOrganization->getUrl('original', [
                            'class' => 'img-responsive'
                        ]);
                    }
                    ?>
                    <?= Html::img($urlParent, [
                        'alt' => $model->parent->name
                    ]) ?>
                </div>
                <div class="col-xs-12 m-t-15">
                    <span><?= $model->parent->getAttributeLabel('responsabile') ?></span>
                    <span><?= $model->parent->responsabile ?></span>
                </div>
                <div>
                    <span><?= Module::t('amosorganizzazioni', '#father_organization'); ?></span>
                    <p><?= $model->parent->name ?></p>
                    <?= $model->parent->sito_web ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($organizzazioniModule->enableMembershipOrganizations) : ?>
            <?php $childrenOrganizations = $model->children; ?>
            <?php if (!empty($childrenOrganizations)) : ?>
                <?php
                $childrenH2Content = AmosIcons::show('building', ['class' => 'm-r-5'], 'dash');
                $childrenH2Content .= Module::t('amosorganizzazioni', '#children_organizations');
                $childrenH2Content .= ' (' . count($childrenOrganizations) . ')';
                $counter = 1;
                ?>
                <div class="row">
                    <div class="col-xs-12">
                        <?= Html::tag('h2', $childrenH2Content) ?>
                    </div>
                    <?php foreach ($childrenOrganizations as $childrenOrganization) : ?>
                        <div class="col-xs-12">
                            <?php
                            $urlChild = '/img/img_default.jpg';
                            if (!is_null($childrenOrganization->logoOrganization)) {
                                $urlChild = $childrenOrganization->logoOrganization->getUrl('original', [
                                    'class' => 'img-responsive'
                                ]);
                            }
                            ?>
                            <?= Html::img($urlChild, [
                                'class' => 'gridview-image',
                                'alt' => $childrenOrganization->name
                            ]) ?>
                            <span><?= Module::t('amosorganizzazioni', '#child_organization') . ' ' . $counter; ?></span>
                            <p><?= $childrenOrganization->name; ?></p>
                        </div>
                        <?php $counter++; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-xs-12">
                <?= AccordionWidget::widget([
                    'items' => [
                        [
                            'header' => Module::t('amosorganizzazioni', '#employees'),
                            'content' => $this->render('organization-employees', ['model' => $model, 'isView' => true]),
                        ]
                    ],
                    'headerOptions' => ['tag' => 'h2'],
                    'clientOptions' => [
                        'collapsible' => false,
                        'active' => 1,
                        'icons' => [
                            'header' => 'ui-icon-amos am am-plus-square',
                            'activeHeader' => 'ui-icon-amos am am-minus-square',
                        ]
                    ],
                ]); ?>
            </div>
        </div>

        <hr class="m-t-30 m-b-30 border-dotted" style="border-style:dotted">

        <?php if (\Yii::$app->getModule('tag')) : ?>
            <div class="row">
                <div class="col-xs-12">
                    <?php
                    $attachmentsWidget = '';
                    $attachmentsWidget = AttachmentsList::widget([
                        'model' => $model,
                        'attribute' => 'attachments',
                        'viewDeleteBtn' => false,
                        'viewDownloadBtn' => true,
                        'viewFilesCounter' => true,
                    ]);
                    ?>
                    <?= $attachmentsWidget ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($organizzazioniModule->enableOrganizationAttachments) : ?>
            <div class="row">
                <div class="col-xs-12">
                    <?php
                    $tagsWidget = '';
                    $tagsWidget = \open20\amos\core\forms\ListTagsWidget::widget([
                        'userProfile' => $model->id,
                        'className' => $model->className(),
                        'viewFilesCounter' => true,
                    ]);
                    ?>
                    <?= $tagsWidget ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

</div>