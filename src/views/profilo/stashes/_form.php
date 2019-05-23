<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\views\profilo
 * @category   CategoryName
 */

use lispa\amos\admin\AmosAdmin;
use lispa\amos\admin\models\UserProfile;
use lispa\amos\attachments\components\AttachmentsInput;
use lispa\amos\attachments\components\AttachmentsList;
use lispa\amos\attachments\components\CropInput;
use lispa\amos\core\forms\AccordionWidget;
use lispa\amos\core\forms\ActiveForm;
use lispa\amos\core\forms\CloseSaveButtonWidget;
use lispa\amos\core\forms\editors\Select;
use lispa\amos\core\forms\RequiredFieldsTipWidget;
use lispa\amos\core\forms\TextEditorWidget;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\cwh\widgets\DestinatariPlusTagWidget;
use lispa\amos\organizzazioni\assets\OrganizzazioniAsset;
use lispa\amos\organizzazioni\models\ProfiloEntiType;
use lispa\amos\organizzazioni\models\ProfiloLegalForm;
use lispa\amos\organizzazioni\models\ProfiloTypesPmi;
use lispa\amos\organizzazioni\Module;
use lispa\amos\organizzazioni\utility\OrganizzazioniUtility;
use lispa\amos\organizzazioni\widgets\maps\PlaceWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\JsExpression;

/**
 * @var yii\web\View $this
 * @var lispa\amos\organizzazioni\models\Profilo $model
 * @var yii\widgets\ActiveForm $form
 * @var lispa\amos\organizzazioni\models\ProfiloSediLegal $mainSedeLegale
 * @var lispa\amos\organizzazioni\models\ProfiloSediOperative $mainSedeOperativa
 */

$this->registerJs("    
    verifySameSede();
    $('#profilo-la_sede_legale_e_la_stessa_del input').on('change', function() {
        verifySameSede();
    });
    function verifySameSede() {
    var attrib = $(\"#profilo-la_sede_legale_e_la_stessa_del input[type='radio']:checked\").val();
        if(attrib == 1){
            $('#same_sede').hide();
        } else {
            $('#same_sede').show();
        }
    }
    ", \yii\web\View::POS_READY);

$moduleL = \Yii::$app->getModule('layout');
if (!empty($moduleL)) {
    OrganizzazioniAsset::register($this);
}

/** @var Module $organizzazioniModule */
$organizzazioniModule = Yii::$app->getModule(Module::getModuleName());

$profiloEntiTypeElementId = Html::getInputId($model, 'profilo_enti_type_id');
$istatCodeElementId = Html::getInputId($model, 'istat_code');
$tipologiaDiOrganizzazione = Html::getInputId($model, 'tipologia_di_organizzazione');
$typeMunicipality = ProfiloEntiType::TYPE_MUNICIPALITY;
$sameHeadquarterElementId = Html::getInputId($model, 'la_sede_legale_e_la_stessa_del');
$legalHeadquarterAddressElementId = Html::getInputId($model, 'mainLegalHeadquarterAddress');

$js = <<<JS
var profiloEntiTypeElement = $('#$profiloEntiTypeElementId');
var istatCodeElement = $('#$istatCodeElementId');
var tipologiaDiOrganizzazione = $('#$tipologiaDiOrganizzazione');
var sameHeadquarterElementId = $('#$sameHeadquarterElementId');

function addRequiredAsterisk(fieldName) {
    $('.field-' + fieldName).addClass('required');
}

function removeRequiredAsterisk(fieldName) {
    $('.field-' + fieldName).removeClass('required');
}

function manageEnabledFields() {
    if (profiloEntiTypeElement.val() == $typeMunicipality) {
        addRequiredAsterisk('$istatCodeElementId');
        istatCodeElement.prop("disabled", false);
        tipologiaDiOrganizzazione.prop("disabled", true);
    } else {
        removeRequiredAsterisk('$istatCodeElementId');
        istatCodeElement.prop("disabled", true);
        tipologiaDiOrganizzazione.prop("disabled", false);
    }
}

manageEnabledFields();

profiloEntiTypeElement.change(function() {
    manageEnabledFields();
});

function manageLegalHeadquarterRequiredAddress() {
    if (sameHeadquarterElementId.val() == 1) {
        removeRequiredAsterisk('$legalHeadquarterAddressElementId');
    } else {
        addRequiredAsterisk('$legalHeadquarterAddressElementId');
    }
}

manageLegalHeadquarterRequiredAddress();

sameHeadquarterElementId.change(function() {
    manageLegalHeadquarterRequiredAddress();
});
JS;
$this->registerJs($js);

?>

<?php $form = ActiveForm::begin([
    'options' => [
        'id' => 'are-profilo_' . ((isset($fid)) ? $fid : 0),
        'data-fid' => (isset($fid)) ? $fid : 0,
        'data-field' => ((isset($dataField)) ? $dataField : ''),
        'data-entity' => ((isset($dataEntity)) ? $dataEntity : ''),
        'class' => ((isset($class)) ? $class : ''),
        'enctype' => 'multipart/form-data'// important
    ]
]);

/** @var ProfiloEntiType $modelProfiloEntiType */
$modelProfiloEntiType = Module::instance()->createModel('ProfiloEntiType');

/** @var ProfiloTypesPmi $modelProfiloTypesPmi */
$modelProfiloTypesPmi = Module::instance()->createModel('ProfiloTypesPmi');

/** @var ProfiloLegalForm $modelProfiloLegalForm */
$modelProfiloLegalForm = Module::instance()->createModel('ProfiloLegalForm');

/** @var UserProfile $modelUserProfile */
$modelUserProfile = AmosAdmin::instance()->createModel('UserProfile');

?>

<div class="area-profilo-form">
    <div class="row">
        <div class="col-xs-12"><?= Html::tag('h2', Module::t('amosorganizzazioni', '#settings_general_title'), ['class' => 'subtitle-form']) ?></div>
        <div class="col-md-8 col-xs-12">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => Module::t('amosorganizzazioni', '#name_field_placeholder')])->hint(Module::t('amosorganizzazioni', '#name_field_hint')) ?>
            <div class="col-md-6 col-xs-12">
                <?= $form->field($model, 'profilo_enti_type_id')->widget(Select::classname(), [
                    'data' => ArrayHelper::map($modelProfiloEntiType::find()->all(), 'id', 'name'),
                    'language' => substr(Yii::$app->language, 0, 2),
                    'options' => [
                        'multiple' => false,
                        'placeholder' => Module::t('amosorganizzazioni', 'Seleziona') . '...',
                        'disabled' => (!$model->isNewRecord),
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ]) ?>
            </div>
            <div class="col-md-6 col-xs-12">
                <?= $form->field($model, 'istat_code')->textInput(['maxlength' => true, 'placeholder' => Module::t('amosorganizzazioni', '#istat_code_field_placeholder')]) ?>
            </div>
            <div class="col-md-6 col-xs-12">
                <?= $form->field($model, 'tipologia_di_organizzazione')->widget(Select::classname(), [
                    'data' => ArrayHelper::map($modelProfiloTypesPmi::find()->asArray()->all(), 'id', 'name'),
                    'language' => substr(Yii::$app->language, 0, 2),
                    'options' => [
                        'multiple' => false,
                        'placeholder' => Module::t('amosorganizzazioni', 'Seleziona') . '...',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]) ?>
            </div>
            <div class="col-md-6 col-xs-12">
                <?= $form->field($model, 'forma_legale')->widget(Select::classname(), [
                    'data' => ArrayHelper::map($modelProfiloLegalForm::find()->all(), 'id', 'name'),
                    'language' => substr(Yii::$app->language, 0, 2),
                    'options' => [
                        'multiple' => false,
                        'placeholder' => Module::t('amosorganizzazioni', 'Seleziona') . '...',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]) ?>
            </div>
            <!--            <div class="clearfix"></div>-->
            <div class="col-md-6 col-xs-12">
                <?php echo $form->field($model, 'partita_iva')->textInput(['maxlength' => true, 'placeholder' => Module::t('amosorganizzazioni', '#partita_iva_field_placeholder')]) ?>
            </div>
            <div class="col-md-6 col-xs-12">
                <?php echo $form->field($model, 'codice_fiscale')->textInput(['maxlength' => true, 'placeholder' => Module::t('amosorganizzazioni', '#codice_fiscale_field_placeholder')]) ?>
            </div>

            <?= $form->field($model, 'presentazione_della_organizzaz')->widget(TextEditorWidget::className(), [
                'options' => [
                    'id' => 'presentazione_della_organizzaz' . $fid,
                ],
                'clientOptions' => [
                    'placeholder' => Module::t('amosorganizzazioni', '#presentazione_field_placeholder'),
                    'lang' => substr(Yii::$app->language, 0, 2)
                ]
            ]) ?>

            <?php if ($organizzazioniModule->enableMembershipOrganizations): ?>
                <?= $form->field($model, 'parent_id')->widget(Select::className(), [
                    'data' => OrganizzazioniUtility::getMembershipOrganizationsReadyForSelect($model),
                    'options' => [
                        'lang' => substr(Yii::$app->language, 0, 2),
                        'multiple' => false,
                        'placeholder' => Module::t('amosorganizzazioni', 'Select/Choose') . '...',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ]
                ]) ?>
            <?php endif; ?>

            <?= $form->field($model, 'mainOperativeHeadquarterAddress')->widget(
                PlaceWidget::className(), [
                    'placeAlias' => 'sedeIndirizzo'
                ]
            ); ?>

            <div class="col-xs-12">
                <?= $form->field($model, 'sito_web')->textInput([
                    'maxlength' => true,
                    'placeholder' => Module::t('amosorganizzazioni', '#sito_field_placeholder')
                ]) ?>
            </div>

            <div class="col-md-6 col-xs-12">
                <?= $form->field($mainSedeOperativa, 'email')->textInput([
                    'maxlength' => true,
                    'placeholder' => Module::t('amosorganizzazioni', '#email_field_placeholder')
                ]) ?>
            </div>

            <div class="col-md-6 col-xs-12">
                <?= $form->field($mainSedeOperativa, 'pec')->textInput([
                    'maxlength' => true,
                    'placeholder' => Module::t('amosorganizzazioni', '#pec_field_placeholder')
                ]) ?>
            </div>
            <div class="col-md-6 col-xs-12">
                <?= $form->field($mainSedeOperativa, 'phone')->textInput([
                    'maxlength' => true,
                    'placeholder' => Module::t('amosorganizzazioni', '#telefono_field_placeholder')
                ]) ?>
            </div>

            <div class="col-md-6 col-xs-12">
                <?= $form->field($mainSedeOperativa, 'fax')->textInput([
                    'maxlength' => true,
                    'placeholder' => Module::t('amosorganizzazioni', '#fax_field_placeholder')
                ]) ?>
            </div>

            <?php echo $form->field($model, 'responsabile')->textInput(['maxlength' => true,
                'placeholder' => Module::t('amosorganizzazioni', '#responsabile_field_placeholder')
            ]) ?>

            <?php if ($organizzazioniModule->enableRappresentanteLegaleText): ?>
                <div class="col-md-6 col-xs-12">
                    <?= $form->field($model, 'rappresentante_legale_text') ?>
                </div>
            <?php else: ?>
                <div class="col-md-6 col-xs-12"><!-- rappresentante_legale string -->
                    <?= $form->field($model, 'rappresentante_legale')->widget(Select::className(), [
                        'initValueText' => empty($model->rappresentante_legale) ? '' : $modelUserProfile::findOne($model->rappresentante_legale)->nomeCognome,
                        'language' => substr(Yii::$app->language, 0, 2),
                        'options' => [
                            'placeholder' => Module::t('amosorganizzazioni', 'Seleziona') . '...',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'minimumInputLength' => 3,
                            'ajax' => [
                                'url' => Url::to(['/admin/user-profile-ajax/ajax-user-list']),
                                'dataType' => 'json',
                                'data' => new JsExpression('function(params) { return {q:params.term}; }')
                            ],
                        ],
                    ]); ?>
                </div>
            <?php endif; ?>
            <div class="col-md-6 col-xs-12"><!-- referente_operativo string -->
                <?= $form->field($model, 'referente_operativo')->widget(Select::className(), [
                    'initValueText' => empty($model->referente_operativo) ? '' : $modelUserProfile::findOne($model->referente_operativo)->nomeCognome,
                    'language' => substr(Yii::$app->language, 0, 2),
                    'options' => [
                        'placeholder' => Module::t('amosorganizzazioni', 'Seleziona') . '...',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'minimumInputLength' => 3,
                        'ajax' => [
                            'url' => Url::to(['/admin/user-profile-ajax/ajax-user-list']),
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                    ],
                ]); ?>
            </div>
            <div class="col-xs-12<?= ($organizzazioniModule->forceSameSede ? ' hidden' : '') ?>">
                <?= $form->field($model, 'la_sede_legale_e_la_stessa_del', [
                    'options' => [
                        'class' => 'checkLocationsForCopy',
                    ]
                ])->inline(true)->radioList([
                    1 => Yii::t('amosorganizzazioni', 'Si'),
                    0 => Yii::t('amosorganizzazioni', 'No')
                ]) ?>
            </div>
            <div class="row" id="same_sede">
                <div class="col-md-12">
                    <div class="col-xs-12">
                        <?= Html::tag('h2', Module::t('amosorganizzazioni', '#same_sede_title'), ['class' => 'subtitle-form']) ?>
                    </div>

                    <div class="col-xs-12">
                        <?= $form->field($model, 'mainLegalHeadquarterAddress')->widget(
                            PlaceWidget::className(), [
                                'placeAlias' => 'sedeLegaleIndirizzo'
                            ]
                        ); ?>
                    </div>

                    <div class="col-md-6 col-xs-12">
                        <?= $form->field($mainSedeLegale, 'email')->textInput([
                            'maxlength' => true,
                            'placeholder' => Module::t('amosorganizzazioni', '#email_field_placeholder')
                        ]) ?>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <?= $form->field($mainSedeLegale, 'pec')->textInput([
                            'maxlength' => true,
                            'placeholder' => Module::t('amosorganizzazioni', '#pec_field_placeholder')
                        ]) ?>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <?= $form->field($mainSedeLegale, 'phone')->textInput([
                            'maxlength' => true,
                            'placeholder' => Module::t('amosorganizzazioni', '#telefono_field_placeholder')
                        ]) ?>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <?= $form->field($mainSedeLegale, 'fax')->textInput([
                            'maxlength' => true,
                            'placeholder' => Module::t('amosorganizzazioni', '#fax_field_placeholder')
                        ]) ?>
                    </div>
                </div>
            </div>
            <div class="col-xs-12">
                <?= Html::tag('h2', Module::t('amosorganizzazioni', '#settings_receiver_title'), ['class' => 'subtitle-form']) ?>
                <div class="col-xs-12 receiver-section">
                    <?= DestinatariPlusTagWidget::widget([
                        'model' => $model,
                    ]); ?>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-xs-12">
            <div class="col-xs-12 nop">
                <?= $form->field($model, 'logoOrganization')->widget(CropInput::classname(), [
                    'jcropOptions' => ['aspectRatio' => '1.7']
                ])->label(Module::t('amosorganizzazioni', '#image_field'))->hint(Module::t('amosorganizzazioni', '#image_field_hint')) ?>
            </div>
            <div class="col-xs-12 attachment-section nop">
                <div class="col-xs-12">
                    <?= Html::tag('h2', Module::t('amosorganizzazioni', '#attachments_title')) ?>
                    <?= $form->field($model,
                        'allegati')->widget(AttachmentsInput::classname(), [
                        'options' => [ // Options of the Kartik's FileInput widget
                            'multiple' => true, // If you want to allow multiple upload, default to false
                        ],
                        'pluginOptions' => [ // Plugin options of the Kartik's FileInput widget
                            'maxFileCount' => 100,// Client max files
                            'showPreview' => false
                        ]
                    ])->label(Module::t('amosorganizzazioni', '#attachments_field'))->hint(Module::t('amosorganizzazioni', '#attachments_field_hint')) ?>

                    <?= AttachmentsList::widget([
                        'model' => $model,
                        'attribute' => 'allegati'
                    ]) ?>
                </div>
            </div>

            <div class="col-xs-12 social-section nop">
                <div class="col-xs-12">
                    <?= Html::tag('h2', Module::t('amosorganizzazioni', '#social_title')) ?>
                    <div class="col-xs-2 nop">
                        <?= AmosIcons::show('facebook-box'); ?>
                    </div>
                    <div class="col-xs-10 nop">
                        <?= $form->field($model, 'facebook')->textInput(['maxlength' => true])->label(false) ?>
                    </div>
                    <div class="col-xs-2 nop">
                        <?= AmosIcons::show('twitter-box'); ?>
                    </div>
                    <div class="col-xs-10 nop">
                        <?= $form->field($model, 'twitter')->textInput(['maxlength' => true])->label(false) ?>
                    </div>
                    <div class="col-xs-2 nop">
                        <?= AmosIcons::show('linkedin-box'); ?>
                    </div>
                    <div class="col-xs-10 nop">
                        <?= $form->field($model, 'linkedin')->textInput(['maxlength' => true])->label(false) ?>
                    </div>
                    <div class="col-xs-2 nop">
                        <?= AmosIcons::show('google-plus-box'); ?>
                    </div>
                    <div class="col-xs-10 nop">
                        <?= $form->field($model, 'google')->textInput(['maxlength' => true])->label(false) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <?= AccordionWidget::widget([
                'items' => [
                    [
                        'header' => Module::t('amosorganizzazioni', '#other_headquarters'),
                        'content' => $this->render('../_other_headquarters', ['model' => $model, 'isView' => false]),
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
                'options' => [
                    'class' => 'first-accordion'
                ]
            ]); ?>
        </div>
        <div class="col-xs-12"><?= RequiredFieldsTipWidget::widget() ?></div>
        <div class="col-xs-12"><?= CloseSaveButtonWidget::widget(['model' => $model]); ?></div>
    </div>
</div>
<?php ActiveForm::end(); ?>
