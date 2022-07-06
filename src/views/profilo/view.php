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

/**
 * @var yii\web\View $this
 * @var open20\amos\organizzazioni\models\Profilo $model
 */

$moduleL = \Yii::$app->getModule('layout');
if (!empty($moduleL)) {
    OrganizzazioniAsset::register($this);
}

$this->title = strip_tags($model);
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

$communityPresent = (!is_null($model->community) && is_null($model->community->deleted_at));

$showButton = false;
$waitingOkUser = false;
$button = [
    'title' => '',
    'url' => '#',
    'options' => [
        'class' => 'btn btn-navigtion-secondary',
    ]
];

if ($organizzazioniModule->enableCommunityCreation) {
    if (!$communityPresent) {
        if (in_array($loggedUserId, [$model->rappresentante_legale, $model->referente_operativo])) {
            $button['title'] = Module::t('amosorganizzazioni', '#create_community_for_organization');
            $button['url'] = ['/' . Module::getModuleName() . '/profilo/create-community/', 'id' => $model->id];
            $button['options'] = [
                'class' => 'btn btn-navigation-secondary',
                'title' => Module::t('amosorganizzazioni', '#create_community_for_organization_title'),
                'data-confirm' => Module::t('amosorganizzazioni', '#create_community_for_organization_question')
            ];
            $showButton = true;
        }
    } else {
        $userInList = false;
        foreach ($model->communityUserMm as $userCommunity) { // User not yet subscribed to the event
            if ($userCommunity->user_id == $loggedUserId) {
                $userInList = true;
                $userStatus = $userCommunity->status;
                break;
            }
        }
        
        if ($userInList === true) {
            $showButton = true;
            switch ($userStatus) {
                case CommunityUserMm::STATUS_WAITING_OK_COMMUNITY_MANAGER:
                    $button['title'] = Module::t('amosorganizzazioni', '#request_sent');
                    $button['options']['class'] .= ' disabled';
                    break;
                case CommunityUserMm::STATUS_WAITING_OK_USER:
                    $waitingOkUser = true;
                    $button['title'] = Module::t('amosorganizzazioni', '#accept_invitation');
                    $button['url'] = [
                        '/community/community/accept-user',
                        'communityId' => $model->community_id,
                        'userId' => $loggedUserId
                    ];
                    $button['options']['data']['confirm'] = Module::t('amosorganizzazioni', '#accept_invitation_question');
                    break;
                case CommunityUserMm::STATUS_ACTIVE:
                    $createUrlParams = [
                        '/community/join',
                        'id' => $model->community_id
                    ];
                    $button['title'] = Module::t('amosorganizzazioni', '#visit_community_btn_title');
                    $button['url'] = \Yii::$app->urlManager->createUrl($createUrlParams);
                    break;
            }
        }
    }
}
?>

<div class="organizzazioni-view col-xs-12 nop">
    <div class="col-xs-12 info-view-header">
        <div class="col-md-3 col-xs-12 nop">
            <?php
            $url = '/img/img_default.jpg';
            if (!is_null($model->logoOrganization)) {
                $url = $model->logoOrganization->getUrl('original', [
                    'class' => 'img-responsive'
                ]);
            }
            ?>
            <img class="img-responsive" src="<?= $url ?>" alt="<?= $model->name ?>">
            <div class="col-xs-12 subsection-info-view-header nop">
                <p class="organization-title"><?= $model->name ?></p>
                <span class="organization-site">
                <?php if (!empty($model->sito_web)): ?>
                    <?php $btnTitle = Module::t('amosorganizzazioni', '#visit_website_btn_title'); ?>
                    <?= Html::a($btnTitle, $model->sito_web, [
                        'title' => $btnTitle,
                        'class' => 'btn btn-navigation-secondary',
                        'target' => 'blank'
                    ]); ?>
                <?php endif; ?>
                </span>
                
                <?php if (!$organizzazioniModule->enableWorkflow || ($organizzazioniModule->enableWorkflow && ($model->status == $model->getValidatedStatus()))): ?>
                    <?php if ($showButton): ?>
                        <span class="organization-community">
                        <?php if ($waitingOkUser): ?>
                            <?= JoinCommunityWidget::widget(['model' => $model->community]); ?>
                        <?php else: ?>
                            <?= Html::a($button['title'], $button['url'], $button['options']); ?>
                        <?php endif; ?>
                    </span>
                    <?php endif; ?>
                    <?php if (!$model->userIsEmployee($loggedUserId) && Yii::$app->user->can('ASSOCIATE_ORGANIZZAZIONI_TO_USER', ['model' => $loggedUserProfile])): ?>
                        <span class="organization-community">
                        <?= JoinProfiloWidget::widget([
                            'model' => $model,
                            'userId' => $loggedUserId,
                            'btnClass' => 'btn btn-navigation-secondary',
                            'customBtnLabel' => Module::t('amosorganizzazioni', '#ask_to_be_employee'),
                        ]) ?>
                    </span>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-5 col-xs-12 info-body nop">
            <!--            <div class="col-lg-12 col-xs-12 nop">-->
            <!--                <div class="col-xs-4 nop info-label">-->
            <?php //echo $model->getAttributeLabel('name') ?><!--</div>-->
            <!--                <div class="col-xs-8 nop info-value">--><?php //echo $model->name ?><!--</div>-->
            <!--            </div>-->
            <?php
            $profiloEntiTypeNotNull = (!is_null($model->profiloEntiType));
            $profiloTipoStrutturaNotNull = (!is_null($model->tipologia_struttura_id));
            ?>
            <?php if ($organizzazioniModule->enableProfiloEntiType === true): ?>
                <div class="col-md-12 col-xs-12 nop">
                    <div class="col-xs-4 nop info-label"><?= $model->getAttributeLabel('profilo_enti_type_id') ?></div>
                    <div class="col-xs-8 nop info-value"><?= $profiloEntiTypeNotNull ? $model->profiloEntiType->name : '' ?></div>
                </div>
            <?php endif; ?>
            <?php if ($organizzazioniModule->enableProfiloTipologiaStruttura === true): ?>
                <div class="col-md-12 col-xs-12 nop">
                    <div class="col-xs-4 nop info-label"><?= $model->getAttributeLabel('tipologia_struttura_id') ?></div>
                    <div class="col-xs-8 nop info-value"><?= $profiloTipoStrutturaNotNull ? $model->tipologiaStruttura->name : '' ?></div>
                </div>
            <?php endif; ?>
            <!-- if without else because the entity type must be present -->
            <?php if ($profiloEntiTypeNotNull && ($organizzazioniModule->enableProfiloEntiType === true)): ?>
                <?php if ($model->isMunicipality()): ?>
                    <div class="col-md-12 col-xs-12 nop">
                        <div class="col-xs-4 nop info-label"><?= $model->getAttributeLabel('istat_code') ?></div>
                        <div class="col-xs-8 nop info-value"><?= !empty($model->istat_code) ? $model->istat_code : '' ?></div>
                    </div>
                <?php elseif ($model->isOtherEntity()): ?>
                    <div class="col-md-12 col-xs-12 nop">
                        <div class="col-xs-4 nop info-label"><?= $model->getAttributeLabel('tipologia_di_organizzazione') ?></div>
                        <div class="col-xs-8 nop info-value"><?= !empty($model->tipologiaDiOrganizzazione) ? $model->tipologiaDiOrganizzazione->name : '-' ?></div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <div class="col-md-12 col-xs-12 nop">
                <div class="col-xs-4 nop info-label"><?= $model->getAttributeLabel('forma_legale') ?></div>
                <div class="col-xs-8 nop info-value"><?= !empty($model->formaLegale) ? $model->formaLegale->name : '-' ?></div>
            </div>
            <div class="col-md-12 col-xs-12 nop">
                <div class="col-xs-4 nop info-label"><?= $model->getAttributeLabel('partita_iva') ?></div>
                <div class="col-xs-8 nop info-value"><?= ($model->partita_iva ? $model->partita_iva : '-') ?></div>
            </div>
            <div class="col-md-12 col-xs-12 nop">
                <div class="col-xs-4 nop info-label"><?= $model->getAttributeLabel('codice_fiscale') ?></div>
                <div class="col-xs-8 nop info-value"><?= ($model->codice_fiscale ? $model->codice_fiscale : '-') ?></div>
            </div>
            <div class="col-md-12 col-xs-12 m-t-15 nop">
                <div class="col-xs-4 nop info-label"><?= $emptyProfiloSedi->getAttributeLabel('email') ?></div>
                <div class="col-xs-8 nop info-value"><?= $hasOperativeHeadquarter ? $operativeHeadquarter->email : '-' ?></div>
            </div>
            <div class="col-md-12 col-xs-12 nop">
                <div class="col-xs-4 nop info-label"><?= $emptyProfiloSedi->getAttributeLabel('pec') ?></div>
                <div class="col-xs-8 nop info-value"><?= $hasOperativeHeadquarter ? $operativeHeadquarter->pec : '-' ?></div>
            </div>
            <div class="col-md-12 col-xs-12 nop">
                <div class="col-xs-4 nop info-label"><?= $emptyProfiloSedi->getAttributeLabel('phone') ?></div>
                <div class="col-xs-8 nop info-value"><?= $hasOperativeHeadquarter ? $operativeHeadquarter->phone : '-' ?></div>
            </div>
            <div class="col-md-12 col-xs-12 nop">
                <div class="col-xs-4 nop info-label"><?= $emptyProfiloSedi->getAttributeLabel('fax') ?></div>
                <div class="col-xs-8 nop info-value"><?= $hasOperativeHeadquarter ? $operativeHeadquarter->fax : '-' ?></div>
            </div>
            <!--            <div class="col-lg-12 col-xs-12 nop">-->
            <!--                <div class="col-xs-4 nop info-label">-->
            <?php //echo $model->getAttributeLabel('sito_web') ?><!--</div>-->
            <!--                <div class="col-xs-8 nop info-value">--><?php //echo $model->sito_web ?><!--</div>-->
            <!--            </div>-->
            <div class="col-md-12 col-xs-12 m-t-15 nop">
                <div class="col-xs-4 nop info-label"><?= $emptyProfiloSedi->getAttributeLabel('address') ?></div>
                <div class="col-xs-8 nop info-value"><?= $model->getAddressFieldForView() ?></div>
            </div>
        </div>

        <div class="col-md-4 col-xs-12 nop">
            <?= ContextMenuWidget::widget([
                'model' => $model,
                'actionModify' => $model->getFullUpdateUrl(),
                'actionDelete' => $model->getFullDeleteUrl()
            ]) ?>
            <?php
            if (!$organizzazioniModule->oldStyleAddressEnabled) {
                $sedeIndirizzo = $model->sedeIndirizzo;
                if ($sedeIndirizzo) {
                    echo Html::tag('div',
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
        
        <?php if ($organizzazioniModule->enableMembershipOrganizations && !is_null($model->parent)): ?>
            <div class="col-xs-12 father-organization-container">
                <div class="col-xs-12 father-organization-content">
                    <div class="father-organization-image col-md-4 col-xs-12 nop">
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
                        <div>
                            <span><?= Module::t('amosorganizzazioni', '#father_organization'); ?></span>
                            <p><?= $model->parent->name ?></p>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12 nop">
                        <span><?= $model->parent->getAttributeLabel('responsabile') ?></span>
                        <span><?= $model->parent->responsabile ?></span>
                    </div class="col-md-4 col-xs-12 nop">
                    <div>
                        <span>
                            <?= $model->parent->sito_web ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <div class="col-xs-12 info-view-body">
        <div class="col-md-4 col-xs-12 nop">
            <div class="col-xs-4 nop info-label"><?= $model->getAttributeLabel('responsabile') ?></div>
            <div class="col-xs-8 nop info-value"><?= $model->responsabile ?></div>
        </div>
        <div class="col-md-4 col-xs-12 nop">
            <div class="col-xs-4 nop info-label"><?= $model->getAttributeLabel('rappresentante_legale') ?></div>
            <?php if ($organizzazioniModule->enableRappresentanteLegaleText): ?>
                <div class="col-xs-8 nop info-value"><?= !empty($model->rappresentante_legale_text) ? $model->rappresentante_legale_text : "" ?></div>
            <?php else: ?>
                <div class="col-xs-8 nop info-value"><?= !empty($model->rappresentanteLegale) ? $model->rappresentanteLegale->nomeCognome : "" ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-4 col-xs-12 nop">
            <div class="col-xs-4 nop info-label"><?= $model->getAttributeLabel('referente_operativo') ?></div>
            <div class="col-xs-8 nop info-value"><?= !empty($model->referenteOperativo) ? $model->referenteOperativo->nomeCognome : "" ?></div>
        </div>
        <div class="col-md-12 col-xs-12 nop">
            <div class="col-xs-4 nop info-label description-label"><?= $model->getAttributeLabel('presentazione_della_organizzaz') ?></div>
            <div class="col-xs-8 nop info-value"><?= $model->presentazione_della_organizzaz ?></div>
        </div>
    </div>

    <div class="col-md-8 col-xs-12">
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
        
        $accordionSedeLegale .= Html::tag('div',
            $mapSedeLegale,
            ['class' => 'col-md-5 col-xs-6']
        );
        
        if ($hasLegalHeadquarter) {
            $slIndirizzo = Html::tag('div',
                Html::tag('div',
                    $legalHeadquarter->getAttributeLabel('address'),
                    ['class' => 'col-xs-4 nop info-label']) .
                Html::tag('div',
                    $model->getAddressFieldSedeLegaleForView(),
                    ['class' => 'col-xs-8 nop info-value']),
                ['class' => 'col-xs-12 nop']);
            
            $slEmail = Html::tag('div',
                Html::tag('div',
                    $legalHeadquarter->getAttributeLabel('email'),
                    ['class' => 'col-xs-4 nop info-label']) .
                Html::tag('div',
                    $legalHeadquarter->email,
                    ['class' => 'col-xs-8 nop info-value']),
                ['class' => 'col-xs-12 nop']);
            
            $slPec = Html::tag('div',
                Html::tag('div',
                    $legalHeadquarter->getAttributeLabel('pec'),
                    ['class' => 'col-xs-4 nop info-label']) .
                Html::tag('div',
                    $legalHeadquarter->pec,
                    ['class' => 'col-xs-8 nop info-value']),
                ['class' => 'col-xs-12 nop']);
            
            $slTelefono = Html::tag('div',
                Html::tag('div',
                    $legalHeadquarter->getAttributeLabel('phone'),
                    ['class' => 'col-xs-4 nop info-label']) .
                Html::tag('div',
                    $legalHeadquarter->phone,
                    ['class' => 'col-xs-8 nop info-value']),
                ['class' => 'col-xs-12 nop']);
            
            $slFax = Html::tag('div',
                Html::tag('div',
                    $legalHeadquarter->getAttributeLabel('fax'),
                    ['class' => 'col-xs-4 nop info-label']) .
                Html::tag('div',
                    $legalHeadquarter->fax,
                    ['class' => 'col-xs-8 nop info-value']),
                ['class' => 'col-xs-12 nop']);
            
            $accordionSedeLegale .= Html::tag('div',
                $slIndirizzo . $slEmail . $slPec . $slTelefono . $slFax,
                ['class' => 'col-md-7 col-xs-6']
            );
        }
        
        ?>
        <?= AccordionWidget::widget([
            'items' => [
                [
                    'header' => Module::t('amosorganizzazioni', '#view_accordion_sede_legale'),
                    'content' => $accordionSedeLegale,
                ]
            ],
            'headerOptions' => ['tag' => 'h2'],
            'clientOptions' => [
                'collapsible' => true,
                'active' => false,
                'icons' => [
                    'header' => 'ui-icon-amos am am-plus-square',
                    'activeHeader' => 'ui-icon-amos am am-minus-square',
                ]
            ],
            'options' => [
                'class' => 'sede-accordion'
            ]
        ]);
        ?>
        
        <?php if ($organizzazioniModule->enableSocial): ?>
            <?php
            $accordionSocial = '';
            
            $accordionSocial .= Html::tag('div',
                AmosIcons::show('facebook-box') . Html::tag('span', $model->facebook),
                ['class' => 'col-sm-6 col-xs-12']);
            $accordionSocial .= Html::tag('div',
                AmosIcons::show('twitter-box') . Html::tag('span', $model->twitter),
                ['class' => 'col-sm-6 col-xs-12']);
            $accordionSocial .= Html::tag('div',
                AmosIcons::show('google-plus-box') . Html::tag('span', $model->google),
                ['class' => 'col-sm-6 col-xs-12']);
            $accordionSocial .= Html::tag('div',
                AmosIcons::show('linkedin-box') . Html::tag('span', $model->linkedin),
                ['class' => 'col-sm-6 col-xs-12']);
            ?>
            <?= AccordionWidget::widget([
                'items' => [
                    [
                        'header' => Module::t('amosorganizzazioni', '#view_accordion_social'),
                        'content' => $accordionSocial,
                    ]
                ],
                'headerOptions' => ['tag' => 'h2'],
                'clientOptions' => [
                    'collapsible' => true,
                    'active' => false,
                    'icons' => [
                        'header' => 'ui-icon-amos am am-plus-square',
                        'activeHeader' => 'ui-icon-amos am am-minus-square',
                    ]
                ],
                'options' => [
                    'class' => 'social-accordion'
                ]
            ]);
            ?>
        <?php endif; ?>
        
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
                'active' => false,
                'icons' => [
                    'header' => 'ui-icon-amos am am-plus-square',
                    'activeHeader' => 'ui-icon-amos am am-minus-square',
                ]
            ],
        ]); ?>
    </div>
    <div class="col-md-4 col-xs-12">
        <?php if ($organizzazioniModule->enableMembershipOrganizations): ?>
            <?php $childrenOrganizations = $model->children; ?>
            <?php if (!empty($childrenOrganizations)): ?>
                <?php
                $childrenH2Content = AmosIcons::show('building', ['class' => 'm-r-5'], 'dash');
                $childrenH2Content .= Module::t('amosorganizzazioni', '#children_organizations');
                $childrenH2Content .= ' (' . count($childrenOrganizations) . ')';
                $counter = 1;
                ?>
                <div class="col-xs-12 children-organizations-section-sidebar nop">
                    <?= Html::tag('h2', $childrenH2Content) ?>
                    <div class="col-xs-12">
                        <?php foreach ($childrenOrganizations as $childrenOrganization): ?>
                            <div class="children-organizations-list-item col-xs-12 nop">
                                <div class="children-organizations-list-item-image col-xs-3 nop">
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
                                </div>
                                <div class="children-organizations-list-item-name col-xs-9 nop">
                                    <span><?= Module::t('amosorganizzazioni', '#child_organization') . ' ' . $counter; ?></span>
                                    <p><?= $childrenOrganization->name; ?></p>
                                </div>
                            </div>
                            <?php $counter++; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (\Yii::$app->getModule('tag')): ?>
            <div class="col-xs-12 tags-section-sidebar nop" id="section-tags">
                <?= Html::tag('h2', AmosIcons::show('tag', [], 'dash') . Module::t('amosorganizzazioni', '#tags_title')) ?>
                <div class="col-xs-12">
                    <?= ListTagsWidget::widget([
                        'userProfile' => $model->id,
                        'className' => $model->className(),
                        'viewFilesCounter' => true,
                    ]);
                    ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="col-xs-12 attachment-section-sidebar nop">
            <?= Html::tag('h2', AmosIcons::show('paperclip', [], 'dash') . Module::t('amosorganizzazioni', '#attachments_title')) ?>
            <div class="col-xs-12">
                <?= AttachmentsList::widget([
                    'model' => $model,
                    'attribute' => 'allegati',
                    'viewDeleteBtn' => false,
                    'viewDownloadBtn' => true,
                    'viewFilesCounter' => true,
                ]) ?>
            </div>
        </div>
    </div>
</div>
