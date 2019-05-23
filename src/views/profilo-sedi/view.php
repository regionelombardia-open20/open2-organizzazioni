<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\views\profilo-sedi
 * @category   CategoryName
 */

use lispa\amos\organizzazioni\Module;
use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View $this
 * @var lispa\amos\organizzazioni\models\ProfiloSedi $model
 */

$this->title = $model;
$this->params['breadcrumbs'][] = ['label' => Module::t('amosorganizzazioni', 'Profilo Sedi'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="profilo-sedi-view col-xs-12">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            'description:ntext',
            [
                'attribute' => 'profiloSediType.name',
                'label' => $model->getAttributeLabel('profiloSediType')
            ],
            'addressField:raw',
            'phone',
            'fax',
            'email:email',
            'pec:email',
            [
                'attribute' => 'profilo.name',
                'label' => $model->getAttributeLabel('profilo')
            ],
        ],
    ]) ?>

    <div class="btnViewContainer pull-right">
        <?= Html::a(Module::t('amoscore', 'Chiudi'), ['/organizzazioni/profilo/update', 'id' => $model->profilo_id], ['class' => 'btn btn-secondary']); ?>
    </div>
</div>
