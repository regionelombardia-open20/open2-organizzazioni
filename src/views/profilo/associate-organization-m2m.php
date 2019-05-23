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
use lispa\amos\organizzazioni\models\Profilo;
use lispa\amos\organizzazioni\Module;
use lispa\amos\organizzazioni\widgets\JoinProfiloWidget;
use lispa\amos\organizzazioni\widgets\ProfiloCardWidget;

/**
 * @var yii\web\View $this
 * @var \lispa\amos\organizzazioni\models\Profilo $model
 */

$this->title = Module::t('amosorganizzazioni', '#add_organization');
$this->params['breadcrumbs'][] = $this->title;

$userId = Yii::$app->request->get("id");

/** @var Profilo $organization */
$organization = Module::instance()->createModel('Profilo');
$query = $organization->getUserNetworkAssociationQuery($userId);

$post = Yii::$app->request->post();
if (isset($post['genericSearch'])) {
    /** @var Profilo $modelProfilo */
    $modelProfilo = Module::instance()->createModel('Profilo');
    $query->andFilterWhere(['like', $modelProfilo::tableName() . '.name', $post['genericSearch']]);
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
        'class' => Module::instance()->createModel('Profilo')->className(),
        'query' => $query,
    ],
    'targetFooterButtons' => Html::a(Module::t('amosorganizzazioni', '#close'), Yii::$app->urlManager->createUrl([
        '/organizzazioni/profilo/annulla-m2m',
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
        'logo' => [
            'headerOptions' => [
                'id' => Module::t('amosorganizzazioni', '#logo'),
            ],
            'contentOptions' => [
                'headers' => Module::t('amosorganizzazioni', '#logo'),
            ],
            'label' => Module::t('amosorganizzazioni', '#logo'),
            'format' => 'raw',//'html',
            'value' => function ($model) {
                return ProfiloCardWidget::widget(['model' => $model]);
            }
        ],
        'name',
        'created_by' => [
            'attribute' => 'created_by',
            'format' => 'html',
            'value' => function ($model) {
                /** @var Profilo $model */
                $name = '-';
                if (!is_null($model->createdUserProfile)) {
                    return $model->createdUserProfile->getNomeCognome();
                }
                return $name;
            }
        ],
        [
            'class' => 'lispa\amos\core\views\grid\ActionColumn',
            'template' => '{info}{view}{joinOrganization}',
            'buttons' => [
                'joinOrganization' => function ($url, $model) {
                    $btn = JoinProfiloWidget::widget(['model' => $model, 'isGridView' => true]);
                    return $btn;
                }
            ]
        ]
    ]
]);
?>
