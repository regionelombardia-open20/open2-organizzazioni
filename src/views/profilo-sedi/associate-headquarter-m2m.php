<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\views\profilo
 * @category   CategoryName
 */

use lispa\amos\core\forms\editors\m2mWidget\M2MWidget;
use lispa\amos\core\helpers\Html;
use lispa\amos\organizzazioni\models\ProfiloSedi;
use lispa\amos\organizzazioni\Module;
use lispa\amos\organizzazioni\widgets\JoinProfiloSediWidget;
use yii\db\ActiveQuery;

/**
 * @var yii\web\View $this
 * @var \lispa\amos\organizzazioni\models\ProfiloSedi $model
 */

$this->title = Module::t('amosorganizzazioni', '#add_headquarter');
$this->params['breadcrumbs'][] = $this->title;

$userId = Yii::$app->request->get("id");

/** @var ProfiloSedi $headquarter */
$headquarter = Module::instance()->createModel('ProfiloSedi');
/** @var ActiveQuery $query */
$query = $headquarter->getAssociateHeadquarterQuery($userId);

$post = Yii::$app->request->post();
$modelProfiloSedi = Module::instance()->createModel('ProfiloSedi');
if (isset($post['genericSearch'])) {
    /** @var ProfiloSedi $modelProfiloSedi */
    $query->andFilterWhere(['like', $modelProfiloSedi::tableName() . '.name', $post['genericSearch']]);
}

?>
<?= M2MWidget::widget([
    'model' => $model,
    'modelId' => $model->id,
    'modelData' => $query,
    'modelDataArrFromTo' => [
        'from' => 'id',
        'to' => 'id'
    ],
    'modelTargetSearch' => [
        'class' => Module::instance()->createModel('ProfiloSedi')->className(),
        'query' => $query,
    ],
    'targetFooterButtons' => Html::a(Module::t('amosorganizzazioni', '#close'), Yii::$app->urlManager->createUrl([
        '/organizzazioni/profilo-sedi/annulla-m2m',
        'id' => $userId
    ]), ['class' => 'btn btn-secondary', 'AmosOrganizzazioni' => Module::t('amosorganizzazioni', '#close')]),
    'renderTargetCheckbox' => false,
    'viewSearch' => (isset($viewM2MWidgetGenericSearch) ? $viewM2MWidgetGenericSearch : false),
//    'relationAttributesArray' => ['status', 'role'],
    'targetUrlController' => 'profilo',
    'targetActionColumnsTemplate' => '{joinOrganization}',
    'moduleClassName' => Module::className(),
    'postName' => 'Organization',
    'postKey' => 'organization',
    'targetColumnsToView' => [
        'profilo_sedi_type_id' => [
            'attribute' => 'profilo_sedi_type_id',
            'value' => 'profiloSediType.name'
        ],
        'name',
        [
            'attribute' => 'addressField',
            'format' => 'raw',
            'label' => $modelProfiloSedi->getAttributeLabel('addressField')
        ],
        [
            'label' => $modelProfiloSedi->getAttributeLabel('profilo'),
            'value' => 'profilo.name'
        ],
        [
            'class' => 'lispa\amos\core\views\grid\ActionColumn',
            'template' => '{info}{view}{joinOrganization}',
            'buttons' => [
                'joinOrganization' => function ($url, $model) {
                    $btn = JoinProfiloSediWidget::widget(['model' => $model, 'isGridView' => true]);
                    return $btn;
                }
            ]
        ]
    ]
]);
?>
