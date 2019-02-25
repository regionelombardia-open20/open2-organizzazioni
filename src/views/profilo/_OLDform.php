<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni
 * @category   CategoryName
 */

use lispa\amos\core\forms\ActiveForm;
use lispa\amos\organizzazioni\models\ProfiloTypesPmi;
use lispa\amos\organizzazioni\models\ProfiloLegalForm;
use lispa\amos\core\forms\Tabs;
use lispa\amos\core\forms\CloseSaveButtonWidget;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use lispa\amos\organizzazioni\widgets\maps\PlaceWidget;
use lispa\amos\admin\models\UserProfile;
use lispa\amos\organizzazioni\Module;

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
/**
 *
 * @var yii\web\View $this
 * @var lispa\amos\organizzazioni\models\Profilo $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="are-profilo-form col-xs-12 nop">

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
    ?>
    <?php // $form->errorSummary($model, ['class' => 'alert-danger alert fade in']); ?>
    <?php $this->beginBlock('generale'); ?>
    <div class="row">
        <div class="col-lg-6 col-sm-6"><!-- name string -->
            <?php echo $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-3 col-sm-3"><!-- partita_iva string -->
            <?php echo $form->field($model, 'partita_iva')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-3 col-sm-3"><!-- codice_fiscale string -->
            <?php echo $form->field($model, 'codice_fiscale')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-sm-12"><!-- presentazione_della_organizzaz text -->
            <?php echo $form->field($model, 'presentazione_della_organizzaz')->widget(yii\redactor\widgets\Redactor::className(), [
                'options' => [
                    'id' => 'presentazione_della_organizzaz' . $fid,
                ],
                'clientOptions' => [
                    'lang' => 'it',
                    'plugins' => ['clips', 'fontcolor', 'imagemanager'],
                    'buttons' => ['format', 'bold', 'italic', 'deleted', 'lists', 'image', 'file', 'link', 'horizontalrule'],
                ],
            ]);
            ?></div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-sm-6"><!-- tipologia_di_organizzazione string -->
            <?=
            $form->field($model, 'tipologia_di_organizzazione')->widget(Select2::classname(), [
                'data' => ArrayHelper::map(ProfiloTypesPmi::find()->asArray()->all(),
                    'id', 'name'),
                'language' => 'it',
                'options' => [
                    'multiple' => false,
                    'placeholder' => Module::t('amosorganizzazioni', 'Seleziona') . '...',
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])
            ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- forma_legale string -->
            <?=
            $form->field($model, 'forma_legale')->widget(Select2::classname(), [
                'data' => ArrayHelper::map(ProfiloLegalForm::find()->asArray()->all(),
                    'id', 'name'),
                'language' => 'it',
                'options' => [
                    'multiple' => false,
                    'placeholder' => Module::t('amosorganizzazioni', 'Seleziona') . '...',
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-sm-6"><!-- sito_web string -->
            <?php echo $form->field($model, 'sito_web')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- facebook string -->
            <?php echo $form->field($model, 'facebook')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-sm-6"><!-- twitter string -->
            <?php echo $form->field($model, 'twitter')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- linkedin string -->
            <?php echo $form->field($model, 'linkedin')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-sm-6"><!-- google string -->
            <?php echo $form->field($model, 'google')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- logo string -->
            <?= $form->field($model,
                'logoOrganization')->widget(\lispa\amos\attachments\components\AttachmentsInput::classname(), [
                'options' => [ // Options of the Kartik's FileInput widget
                    'multiple' => false, // If you want to allow multiple upload, default to false
                    'accept' => "image/*"
                ],
                'pluginOptions' => [ // Plugin options of the Kartik's FileInput widget
                    'maxFileCount' => 1,
                    'showRemove' => false,// Client max files,
                    'indicatorNew' => false,
                    'allowedPreviewTypes' => ['image'],
                    'previewFileIconSettings' => false,
                    'overwriteInitial' => false,
                    'layoutTemplates' => false
                ]
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 col-sm-6">
            <?=
            $form->field($model, 'indirizzo')->widget(
                PlaceWidget::className(), [
                    'placeAlias' => 'sedeIndirizzo'
                ]
            );
            ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- telefono decimal -->
            <?php echo $form->field($model, 'telefono')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- fax decimal -->
            <?php echo $form->field($model, 'fax')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- email string -->
            <?php echo $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- pec string -->
            <?php echo $form->field($model, 'pec')->textInput(['maxlength' => true]) ?>
        </div>
        
    </div>
    <hr>
    <div class="row">
        <div class="col-lg-12 col-sm-12"><!-- la_sede_legale_e_la_stessa_del string -->
            <?= $form->field($model, 'la_sede_legale_e_la_stessa_del', [
                'options' => [
                    'class' => 'checkLocationsForCopy',
                ]
            ])
                ->inline(true)
                ->radioList([
                    1 => Yii::t('amosorganizzazioni', 'Si'),
                    0 => Yii::t('amosorganizzazioni', 'No')
                ]) ?>
        </div>
        <div id="same_sede">
        <div class="col-lg-6 col-sm-6"><!-- sede_legale_indirizzo string -->
            <?=
            $form->field($model, 'sede_legale_indirizzo')->widget(
                PlaceWidget::className(), [
                    'placeAlias' => 'sedeLegaleIndirizzo'
                ]
            );
            ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- sede_legale_telefono decimal -->
            <?php echo $form->field($model, 'sede_legale_telefono')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- sede_legale_fax decimal -->
            <?php echo $form->field($model, 'sede_legale_fax')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- sede_legale_email string -->
            <?php echo $form->field($model, 'sede_legale_email')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- sede_legale_pec string -->
            <?php echo $form->field($model, 'sede_legale_pec')->textInput(['maxlength' => true]) ?>
        </div>
        </div>
    </div>   
    <div class="row">
        <div class="col-lg-6 col-sm-6"><!-- responsabile string -->
            <?php echo $form->field($model, 'responsabile')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-lg-3 col-sm-3"><!-- rappresentante_legale string -->
            <?=
            $form->field($model, 'rappresentante_legale')->widget(Select2::classname(), [
                'data' => ArrayHelper::map(UserProfile::find()->asArray()->all(),
                    'id', 'nome'),
                'language' => 'it',
                'options' => [
                    'multiple' => false,
                    'placeholder' => Module::t('amosorganizzazioni', 'Seleziona') . '...',
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])
            ?>
        </div>
        <div class="col-lg-3 col-sm-3"><!-- referente_operativo string -->
            <?=
            $form->field($model, 'referente_operativo')->widget(Select2::classname(), [
                'data' => ArrayHelper::map(UserProfile::find()->asArray()->all(),
                    'id', 'nome'),
                'language' => 'it',
                'options' => [
                    'multiple' => false,
                    'placeholder' => Module::t('amosorganizzazioni', 'Seleziona') . '...',
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ])
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-sm-6"><!-- responsabile string -->
            <?php /*echo $form->field($model, 'rappresentante_legale')->widget(Select2::className(), [
                    'data' => ArrayHelper::map(UserProfile::find()->orderBy('nome')->asArray()->all(), 'id', 'nome')
                ]
            );*/ ?>
        </div>
        <div class="col-lg-6 col-sm-6"><!-- rappresentante_legale string -->
            <?php /*echo $form->field($model, 'rappresentante_legale')->textInput(['maxlength' => true])*/ ?>
        </div>
    </div>
    <div class="clearfix"></div><?php $this->endBlock(); ?>

    <?php $itemsTab[] = [
        'label' => Yii::t('cruds', 'Generale'),
        'content' => $this->blocks['generale'],
    ];
    ?>

    <?php $this->beginBlock('allegati'); ?>
    <?= $form->field($model,
        'allegati')->widget(\lispa\amos\attachments\components\AttachmentsInput::classname(), [
        'options' => [ // Options of the Kartik's FileInput widget
            'multiple' => true, // If you want to allow multiple upload, default to false
        ],
        'pluginOptions' => [ // Plugin options of the Kartik's FileInput widget
            'maxFileCount' => 100,// Client max files,
            'showPreview' => false
        ]
    ])->label(Module::t('amosorganizzazioni', 'Allegati')) ?>
    <?= \lispa\amos\attachments\components\AttachmentsTableWithPreview::widget([
        'model' => $model,
        'attribute' => 'allegati'
    ]) ?>

    <div class="clearfix"></div>
    <?php
    $this->endBlock('allegati');
    $itemsTab[] = [
        'label' => Module::t('amosorganizzazioni', 'Allegati '),
        'content' => $this->blocks['allegati'],
        'options' => ['id' => 'allegati'],
    ];
    ?>

    <?php echo Tabs::widget(
        [
            'encodeLabels' => false,
            'items' => $itemsTab
        ]
    );
    ?>
    <?php echo CloseSaveButtonWidget::widget(['model' => $model]); ?>
    <?php ActiveForm::end(); ?>
</div>
