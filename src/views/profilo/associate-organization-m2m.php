<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni
 * @category   CategoryName
 */

use lispa\amos\organizzazioni\Module;
use lispa\amos\core\forms\editors\m2mWidget\M2MWidget;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\user\User;
use lispa\amos\organizzazioni\widgets\JoinProfiloWidget;
use lispa\amos\organizzazioni\widgets\ProfiloCardWidget;
use lispa\amos\organizzazioni\models\Profilo;
use yii\bootstrap\Modal;

/**
 * @var Profilo $model
 */

$this->title = Module::t('amosorganizzazioni', '#add_organization');
$this->params['breadcrumbs'][] = Module::t('amosorganizzazioni', '#add_organization');

$userId = Yii::$app->request->get("id");

$organization = new Profilo();
$query = $organization->getUserNetworkAssociationQuery($userId);

$post = Yii::$app->request->post();
if (isset($post['genericSearch'])) {
    $query->andFilterWhere(['like', Profilo::tableName().'.name', $post['genericSearch']]);
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
        'class' => Profilo::className(),
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
            'value' => function($model){
                /** @var Profilo $model */
                $name = '-';
                if(!is_null($model->created_by)) {
                    $creator = User::findOne($model->created_by);
                    if(!empty($creator)) {
                        return $creator->getProfile()->getNomeCognome();
                    }
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
                },
            ]
        ]
    ],
]);
?>
