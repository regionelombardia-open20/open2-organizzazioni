<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\views\profilo
 * @category   CategoryName
 */

use lispa\amos\core\views\DataProviderView;
use lispa\amos\organizzazioni\Module;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var lispa\amos\organizzazioni\models\search\ProfiloSearch $model
 */

$this->title = Module::t('amosorganizzazioni', 'Organizzazioni');
$this->params['breadcrumbs'][] = ['label' => Module::t('amosorganizzazioni', 'Organizzazioni'), 'url' => ['/organizzazioni']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="are-profilo-index">
    <?= $this->render('_search', ['model' => $model]); ?>
    <?php echo DataProviderView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $model,
        'currentView' => $currentView,
        'gridView' => [
            'columns' => [
                'profilo_enti_type_id' => [
                    'attribute' => 'profilo_enti_type_id',
                    'value' => 'profiloEntiType.name'
                ],
                'name',
                'formaLegale.name',
                'operativeHeadquarter.website',
                //'facebook',
                'addressField',
                'operativeHeadquarter.phone',
                //'operativeHeadquarter.fax',
                'operativeHeadquarter.email',
                [
                    'class' => 'lispa\amos\core\views\grid\ActionColumn',
                ],
            ],
        ],
    ]); ?>
</div>
