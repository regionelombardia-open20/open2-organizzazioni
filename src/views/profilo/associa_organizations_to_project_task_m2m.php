<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni
 * @category   CategoryName
 */

use lispa\amos\core\forms\editors\m2mWidget\M2MWidget;
use lispa\amos\core\user\User;
use lispa\amos\projectmanagement\Module;

/**
 * @var \lispa\amos\projectmanagement\models\Projects $model
 */
$activity = $model->projectsActivities;

$this->title = $model;
$this->params['breadcrumbs'][] = [
    'label' => Module::t('amosproject_management', 'Projects'),
    'url' => ['/project_management']
];
$this->params['breadcrumbs'][] = ['label' => strip_tags($activity->projects)];
$this->params['breadcrumbs'][] = [
    'label' => Module::t('amosproject_management', 'Project Activities'),
    'url' => ['/project_management/projects-activities/by-project', 'pid' => $activity->projects->id]
];
$this->params['breadcrumbs'][] = [
    'label' => strip_tags($model),
    'url' => ['update', 'id' => $model->id, '#' => 'tab-organizations']
];
$this->params['breadcrumbs'][] = Module::t('amosproject_management', 'Invite Organizations');

$organizationModelClass = \lispa\amos\organizzazioni\models\Profilo::className();

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
    'moduleClassName' => Module::className(),
    'postName' => 'Project Task',
    'postKey' => 'projects-tasks',
    'targetColumnsToView' => [
        'name' => [
            'attribute' => 'name',
            'label' => Module::t('amosproject_management', 'Name'),
            'headerOptions' => [
                'id' => Module::t('amosproject_management', 'Name'),
            ],
            'contentOptions' => [
                'headers' => Module::t('amosproject_management', 'Name'),
            ]
        ],
        'addressField',
//        'numero_civico',
//        'cap'
    ],
]);
?>