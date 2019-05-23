<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\views\profilo
 * @category   CategoryName
 */

use lispa\amos\core\helpers\Html;
use lispa\amos\organizzazioni\Module;
use lispa\amos\tag\AmosTag;
use lispa\amos\tag\widgets\TagWidget;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var lispa\amos\organizzazioni\models\search\ProfiloSearch $model
 * @var yii\widgets\ActiveForm $form
 */

/** @var AmosTag $moduleTag */
$moduleTag = Yii::$app->getModule('tag');


$enableAutoOpenSearchPanel = !isset(\Yii::$app->params['enableAutoOpenSearchPanel']) || \Yii::$app->params['enableAutoOpenSearchPanel'] === true;
?>
<div class="<?= Yii::$app->controller->id ?>-search element-to-toggle" data-toggle-element="form-search">

    <?php $form = ActiveForm::begin([
        'action' => Yii::$app->controller->action->id,
        'method' => 'get',
        'options' => [
            'class' => 'default-form'
        ]
    ]);
    ?>
    <?= Html::hiddenInput("enableSearch", $enableAutoOpenSearchPanel); ?>
    <?= Html::hiddenInput("currentView", Yii::$app->request->getQueryParam('currentView')); ?>

    <div class="col-xs-12">
        <h2 class="title">
            <?= Module::tHtml('amosorganizzazioni', 'Search by'); ?>:
        </h2>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'name')->textInput(['placeholder' => Module::t('amosorganizzazioni', '#search_placeholder_name')]) ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'partita_iva')->textInput(['placeholder' => Module::t('amosorganizzazioni', '#search_placeholder_partita_iva')]) ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'istat_code')->textInput(['placeholder' => Module::t('amosorganizzazioni', '#search_placeholder_istat_code')]) ?>
    </div>

    <?php if (isset($moduleTag) && in_array(Module::instance()->createModel('Profilo')->className(), $moduleTag->modelsEnabled) && $moduleTag->behaviors): ?>
        <div class="col-xs-12">
            <?php
            $params = \Yii::$app->request->getQueryParams();
            echo TagWidget::widget([
                'model' => $model,
                'attribute' => 'tagValues',
                'form' => $form,
                'isSearch' => true,
                'form_values' => isset($params[$model->formName()]['tagValues']) ? $params[$model->formName()]['tagValues'] : []
            ]);
            ?>
        </div>
    <?php endif; ?>

    <div class="col-xs-12">
        <div class="pull-right">
            <?= Html::resetButton(Module::tHtml('amosorganizzazioni', 'Reset'), ['class' => 'btn btn-secondary']) ?>
            <?= Html::submitButton(Module::tHtml('amosorganizzazioni', 'Search'), ['class' => 'btn btn-navigation-primary']) ?>
        </div>
    </div>

    <div class="clearfix"></div>
    <?php ActiveForm::end(); ?>
</div>
