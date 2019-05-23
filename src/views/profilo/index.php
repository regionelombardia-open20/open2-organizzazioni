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
$this->params['breadcrumbs'][] = $this->title;

/** @var \lispa\amos\organizzazioni\models\Profilo $profiloModel */
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
