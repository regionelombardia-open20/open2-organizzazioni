<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\controllers
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\controllers;

use lispa\amos\admin\models\UserProfile;
use lispa\amos\core\forms\editors\m2mWidget\controllers\M2MWidgetControllerTrait;
use lispa\amos\core\forms\editors\m2mWidget\M2MEventsEnum;
use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\core\user\User;
use lispa\amos\organizzazioni\models\Profilo;
use lispa\amos\organizzazioni\models\ProfiloUserMm;
use lispa\amos\organizzazioni\Module;
use Yii;
use yii\base\Event;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * Class ProfiloController
 * This is the class for controller "ProfiloController".
 * @package lispa\amos\organizzazioni\controllers
 */
class ProfiloController extends base\ProfiloController
{
    /**
     * M2MWidgetControllerTrait
     */
    use M2MWidgetControllerTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->setMmTableName(ProfiloUserMm::className());
        $this->setStartObjClassName(Profilo::className());
        $this->setMmStartKey('profilo_id');
        $this->setTargetObjClassName(UserProfile::className());
        $this->setMmTargetKey('user_id');
        $this->setRedirectAction('update');
        $this->setModuleClassName(Module::className());
        $this->setCustomQuery(true);
        $this->on(M2MEventsEnum::EVENT_AFTER_DELETE_M2M, [$this, 'afterDeleteM2m']);
        $this->on(M2MEventsEnum::EVENT_BEFORE_CANCEL_ASSOCIATE_M2M, [$this, 'beforeCancelAssociateM2m']);
    }

    /**
     * @param Event $event
     */
    public function afterDeleteM2m($event)
    {
        $this->setRedirectArray([Url::previous()]);
    }

    /**
     * @param Event $event
     */
    public function beforeCancelAssociateM2m($event)
    {
        $urlPrevious = Url::previous();
        $id = Yii::$app->request->get('id');

        if (strstr($urlPrevious, 'associate-organization-m2m')) {
            $this->setRedirectArray('/admin/user-profile/update?id=' . $id);
        }
        if (strstr($urlPrevious, 'associate-project-m2m')) {
            $this->setRedirectArray('/project_management/projects/update?id=' . $id . '#tab-organizations');
        }
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [
                            'associate-organization-m2m',
                            'elimina-m2m',
                            'annulla-m2m',
                            'user-network',
                            'join-organization'
                        ],
                        'roles' => ['PROFILO_READ']
                    ],
                    [
                        'allow' => true,
                        'actions' => [
                            'associate-organizations-to-project-m2m',
                            'associate-organizations-to-project-task-m2m',
                        ],
                        'roles' => ['@']
                    ],
                    [
                        'allow' => true,
                        'actions' => [
                            'change-user-role-area',
                        ],
                        'roles' => ['AMMINISTRATORE_ORGANIZZAZIONI', 'BASIC_USER']
                    ],
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post', 'get']
                ]
            ]
        ]);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function actionAssociateOrganizationsToProjectM2m()
    {
        if (!empty(\Yii::$app->getModule('project_management'))) {
            $projectId = Yii::$app->request->get('id');
            Url::remember();

            $this->setMmTableName(\lispa\amos\projectmanagement\models\ProjectsJoinedOrganizationsMm::className());
            $this->setStartObjClassName(\lispa\amos\projectmanagement\models\Projects::className());
            $this->setMmStartKey('projects_id');
            $this->setTargetObjClassName(Profilo::className());
            $this->setMmTargetKey('organization_id');
            $this->setRedirectAction('/project_management/projects/update');
            $this->setOptions(['#' => 'tab-organizations']);
            $this->setTargetUrl('associa_organizations_to_project_m2m');
            $this->setCustomQuery(true);
            $this->setModuleClassName(Module::className());
            $this->setRedirectArray('/project_management/projects/update?id=' . $projectId . '#tab-organizations');
            return $this->actionAssociaM2m($projectId);
        } else {
            throw new \Exception(Module::t('organizations', 'The module project is not enabled'));
        }
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function actionAssociateOrganizationsToProjectTaskM2m()
    {
        if (!empty(\Yii::$app->getModule('project_management'))) {
            $projectTaskId = Yii::$app->request->get('id');
            Url::remember();

            $this->setMmTableName(\lispa\amos\projectmanagement\models\ProjectsTasksJoinedOrganizationsMm::className());
            $this->setStartObjClassName(\lispa\amos\projectmanagement\models\ProjectsTasks::className());
            $this->setMmStartKey('projects_tasks_id');
            $this->setTargetObjClassName(Profilo::className());
            $this->setMmTargetKey('organization_id');
            $this->setRedirectAction('/project_management/projects-tasks/update');
            $this->setOptions(['#' => 'tab-organizations']);
            $this->setTargetUrl('associa_organizations_to_project_task_m2m');
            $this->setCustomQuery(true);
            $this->setModuleClassName(Module::className());
            $this->setRedirectArray('/project_management/projects-tasks/update?id=' . $projectTaskId . '#tab-organizations');
            return $this->actionAssociaM2m($projectTaskId);
        } else {
            throw new \Exception(Module::t('organizations', 'The module project is not enabled'));
        }
    }

    /**
     * @return mixed
     */
    public function actionAssociateOrganizationM2m()
    {
        $userId = Yii::$app->request->get('id');
        Url::remember();

        $this->setMmTableName(ProfiloUserMm::className());
        $this->setStartObjClassName(User::className());
        $this->setMmStartKey('user_id');
        $this->setTargetObjClassName(Profilo::className());
        $this->setMmTargetKey('profilo_id');
        $this->setRedirectAction('update');
        $this->setTargetUrl('associate-organization-m2m');
        $this->setCustomQuery(true);
        $userProfileId = User::findOne($userId)->getProfile()->id;
        $this->setRedirectArray('/admin/user-profile/update?id=' . $userProfileId . '#tab-network');
        return $this->actionAssociaM2m($userId);

    }

    /**
     * @param $organizationId
     * @param bool $accept
     * @param null $redirectAction
     * @return \yii\web\Response
     */
    public function actionJoinOrganization($organizationId, $accept = false, $redirectAction = null)
    {
        $defaultAction = 'index';

        if (empty($redirectAction)) {
            $urlPrevious = Url::previous();
            $redirectAction = $urlPrevious;
        }
        if (!$organizationId) {
            Yii::$app->getSession()->addFlash('danger', Module::tHtml('amosorganizzazioni', "It is not possible to subscribe the user. Missing parameter organization."));
            return $this->redirect($defaultAction);
        }

        $ok = false;
        $message = '';
        $nomeCognome = '';
        $organizationName = '';
        $userId = Yii::$app->getUser()->getId();
        /** @var User $user */
        $user = User::findOne($userId);
        /** @var UserProfile $userProfile */
        $userProfile = $user->getProfile();
        if (!is_null($userProfile)) {
            $nomeCognome = " '" . $userProfile->nomeCognome . "' ";
        }

        $organization = Profilo::findOne($organizationId);
        if (!is_null($organization)) {
            $organizationName = " '" . $organization->name . "'";
        }
        $userorganization = ProfiloUserMm::findOne(['profilo_id' => $organizationId, 'user_id' => $userId]);
        // Verify if user already in organization user relation table
        if (is_null($userorganization)) {
            // Iscrivo l'utente alla organization
            $userorganization = new ProfiloUserMm();
            $userorganization->profilo_id = $organizationId;
            $userorganization->user_id = $userId;
            $ok = $userorganization->save(false);
            $message = Module::tHtml('amosorganizzazioni', "You are now linked to the organization") . $organizationName;
        }

        if ($ok) {
            Yii::$app->getSession()->addFlash('success', $message);
            if (isset($redirectAction)) {
                return $this->redirect($redirectAction);
            } else {
                return $this->redirect($defaultAction);
            }
        } else {
            Yii::$app->getSession()->addFlash('danger', Module::tHtml('amosorganizzazioni',
                    "Error occured while subscribing the user") . $nomeCognome . Module::tHtml('amosorganizzazioni',
                    "to organization") . $organizationName);
            return $this->redirect($defaultAction);
        }
    }

    /**
     * @param int $userId
     * @param bool $isUpdate
     * @return string
     */
    public function actionUserNetwork($userId, $isUpdate = false)
    {
        if (\Yii::$app->request->isAjax) {
            $this->setUpLayout(false);

            return $this->render('user-network', [
                'userId' => $userId,
                'isUpdate' => $isUpdate
            ]);
        }
        return '';
    }

    /**
     * @param int $profiloId
     * @param int $userId
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionChangeUserRoleArea($profiloId, $userId)
    {
        $profiloUserMm = ProfiloUserMm::findOne(['profilo_id' => $profiloId, 'user_id' => $userId]);
        $userProfile = UserProfile::findOne(['user_id' => $userId]);
        $this->model = $this->findModel($profiloId);

        if (Yii::$app->user->can('USERPROFILE_UPDATE', ['model' => $userProfile]) || Yii::$app->user->can('ADMIN')) {
            if (Yii::$app->getRequest()->isAjax && Yii::$app->request->isPost) {
                $post = Yii::$app->request->post();
                if (!is_null($profiloUserMm) && isset($post['user_profile_role']) && isset($post['user_profile_area'])) {
                    $profiloUserMm->user_profile_role_id = $post['user_profile_role'];
                    $profiloUserMm->user_profile_area_id = $post['user_profile_area'];
                    $ok = $profiloUserMm->save(false);
                    if ($ok) {
                        /** @var UserProfile $userProfile */
                        $nomeCognome = " '" . $userProfile->nomeCognome . "' ";
                        if (!is_null($this->model)) {
                            $profiloName = " '" . $this->model->name . "'";
                        }
                        $message = $nomeCognome . " " . Module::tHtml('amosorganizzazioni', 'is now') .
                            " " . $profiloUserMm->userProfileRole->name . " " .
                            Module::tHtml('amosorganizzazioni', 'of') . " '" . $profiloName . "'";
                        $this->addFlash('success', $message);
                    }
                }
            }
        } else {
            $this->addFlash('danger', BaseAmosModule::t('amoscore', '#unauthorized_flash_message'));
        }
    }
}
