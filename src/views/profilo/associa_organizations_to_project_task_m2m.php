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

/**
 * @var yii\web\View $this
 * @var \lispa\amos\projectmanagement\models\Projects $model
 */

$activity = $model->projectsActivities;

$this->title = $model;
$this->params['breadcrumbs'][] = [
    'label' => \lispa\amos\projectmanagement\Module::t('amosproject_management', 'Projects'),
    'url' => ['/project_management']
];
$this->params['breadcrumbs'][] = ['label' => strip_tags($activity->projects)];
$this->params['breadcrumbs'][] = [
    'label' => \lispa\amos\projectmanagement\Module::t('amosproject_management', 'Project Activities'),
    'url' => ['/project_management/projects-activities/by-project', 'pid' => $activity->projects->id]
];
$this->params['breadcrumbs'][] = [
    'label' => strip_tags($model),
    'url' => ['update', 'id' => $model->id, '#' => 'tab-organizations']
];
$this->params['breadcrumbs'][] = \lispa\amos\projectmanagement\Module::t('amosproject_management', 'Invite Organizations');

$organizationModelClass = \lispa\amos\organizzazioni\Module::instance()->createModel('Profilo')->className();

?>
<?= M2MWidget::widget([
    'model' => $model,
    'modelId' => $model->id,
    'modelData' => $model->getJoinedOrganizations(),
    'modelDataArrFromTo' => [
        'from' => 'id',
        'to' => 'id'
    ],
    'modelTargetSearch' => [
        'class' => $organizationModelClass::className(),
        'query' => $model->projectsActivities->projects->getParticipantsOrganizations()
            ->andFilterWhere([
                'not in',
                'id',
                $model->getProjectsTasksJoinedOrganizationsMms()->select('organization_id')
            ]),//$query,
    ],

    'targetUrlController' => 'projects-tasks',
    'moduleClassName' => \lispa\amos\projectmanagement\Module::className(),
    'postName' => 'Project Task',
    'postKey' => 'projects-tasks',
    'targetColumnsToView' => [
        'name' => [
            'attribute' => 'name',
            'label' => \lispa\amos\projectmanagement\Module::t('amosproject_management', 'Name'),
            'headerOptions' => [
                'id' => \lispa\amos\projectmanagement\Module::t('amosproject_management', 'Name'),
            ],
            'contentOptions' => [
                'headers' => \lispa\amos\projectmanagement\Module::t('amosproject_management', 'Name'),
            ]
        ],
        'addressField:raw',
//        'numero_civico',
//        'cap'
    ],
]);
?>
