<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\organizzazioni\views\profilo
 * @category   CategoryName
 */

use open20\amos\core\views\DataProviderView;
use open20\amos\organizzazioni\Module;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var open20\amos\organizzazioni\models\search\ProfiloSearch $model
 */

$this->title = Module::t('amosorganizzazioni', 'Organizzazioni');
$this->params['breadcrumbs'][] = $this->title;

/** @var \open20\amos\organizzazioni\models\Profilo $profiloModel */
$profiloModel = Module::instance()->createModel('Profilo');

?>

<div class="are-profilo-index">
    <?= $this->render('_search', ['model' => $model]); ?>
    <?= DataProviderView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $model,
        'currentView' => $currentView,
        'gridView' => [
            'columns' => $profiloModel->getGridViewColumns()
        ]
    ]); ?>
</div>
