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

use lispa\amos\admin\AmosAdmin;
use lispa\amos\admin\models\UserProfile;
use lispa\amos\core\forms\editors\m2mWidget\controllers\M2MWidgetControllerTrait;
use lispa\amos\core\forms\editors\m2mWidget\M2MEventsEnum;
use lispa\amos\core\module\BaseAmosModule;
use lispa\amos\core\user\User;
use lispa\amos\organizzazioni\models\Profilo;
use lispa\amos\organizzazioni\models\ProfiloUserMm;
use lispa\amos\organizzazioni\Module;
use lispa\amos\organizzazioni\utility\EmailUtility;
use lispa\amos\organizzazioni\utility\OrganizzazioniUtility;
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

        $this->setMmTableName($this->organizzazioniModule->createModel('ProfiloUserMm')->className());
        $this->setStartObjClassName($this->organizzazioniModule->createModel('Profilo')->className());
        $this->setMmStartKey('profilo_id');
        $this->setTargetObjClassName(AmosAdmin::instance()->createModel('UserProfile')->className());
        $this->setMmTargetKey('user_id');
        $this->setRedirectAction('update');
        $this->setModuleClassName(Module::className());
        $this->setCustomQuery(true);
        $this->on(M2MEventsEnum::EVENT_BEFORE_DELETE_M2M, [$this, 'beforeDeleteM2m']);
        $this->on(M2MEventsEnum::EVENT_AFTER_DELETE_M2M, [$this, 'afterDeleteM2m']);
        $this->on(M2MEventsEnum::EVENT_BEFORE_CANCEL_ASSOCIATE_M2M, [$this, 'beforeCancelAssociateM2m']);
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
                            'user-network'
                        ],
                        'roles' => ['PROFILO_READ']
                    ],
                    [
                        'allow' => true,
                        'actions' => [
                            'elimina-m2m',
                            'annulla-m2m',
                            'associate-organization-m2m',
                            'join-organization'
                        ],
                        'roles' => ['ASSOCIATE_PROFILO_TO_USER_PERMISSION']
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
                    [
                        'allow' => true,
                        'actions' => [
                            'accept-user',
                            'reject-user',
                        ],
                        'roles' => ['AMMINISTRATORE_ORGANIZZAZIONI', 'CONFIRM_ORGANIZZAZIONI_OR_SEDI_USER_REQUEST']
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
     * @param Event $event
     * @throws \yii\base\InvalidConfigException
     */
    public function beforeDeleteM2m($event)
    {
        $profiloId = Yii::$app->request->get('id');
        $userId = Yii::$app->request->get('targetId');

        /** @var Profilo $profiloModel */
        $profiloModel = $this->organizzazioniModule->createModel('Profilo');
        /** @var ProfiloUserMm $profiloUserMmModel */
        $profiloUserMmModel = $this->organizzazioniModule->createModel('ProfiloUserMm');

        /** @var Profilo $profilo */
        $profilo = $profiloModel::findOne($profiloId);

        /** @var ProfiloUserMm $profiloUserMmRow */
        $profiloUserMmRow = $profiloUserMmModel::findOne(['profilo_id' => $profiloId, 'user_id' => $userId]);

        // Remove all cwh permissions for domain = community
        $profilo->setCwhAuthAssignments($profiloUserMmRow, true);
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
            $this->setTargetObjClassName($this->organizzazioniModule->createModel('Profilo')->className());
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
            $this->setTargetObjClassName($this->organizzazioniModule->createModel('Profilo')->className());
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

        $this->setMmTableName($this->organizzazioniModule->createModel('ProfiloUserMm')->className());
        $this->setStartObjClassName(User::className());
        $this->setMmStartKey('user_id');
        $this->setTargetObjClassName($this->organizzazioniModule->createModel('Profilo')->className());
        $this->setMmTargetKey('profilo_id');
        $this->setRedirectAction('update');
        $this->setTargetUrl('associate-organization-m2m');
        $this->setCustomQuery(true);
        $userProfileId = UserProfile::findOne(['user_id' => $userId])->id;
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

        $nomeCognome = ' ';
        $organizationName = '';
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $userId = $user->id;
        $userProfile = $user->userProfile;
        if (!is_null($userProfile)) {
            $nomeCognome = "'" . $userProfile->nomeCognome . "'";
        }

        /** @var Profilo $profiloModel */
        $profiloModel = $this->organizzazioniModule->createModel('Profilo');
        $organization = $profiloModel::findOne($organizationId);
        if (!is_null($organization)) {
            $organizationName = "'" . $organization->name . "'";
        }
        /** @var ProfiloUserMm $profiloUserMm */
        $profiloUserMm = $this->organizzazioniModule->createModel('ProfiloUserMm');
        $userOrganization = $profiloUserMm::findOne(['profilo_id' => $organizationId, 'user_id' => $userId]);

        // Verify if the user is already in the organization user relation table
        if (is_null($userOrganization)) {
            $organizationRefereesIds = OrganizzazioniUtility::getOrganizationReferees($organizationId, true);
            if (in_array($userId, $organizationRefereesIds)) {
                // The user is a legal representative or a operative referee for the organization, then cannot be a member now.
                // In future modify this code if you want to enable the roles in MM table like communities (and remove this comment).
                Yii::$app->getSession()->addFlash('danger', Module::tHtml('amosorganizzazioni', '#join_organization_already_referee', [
                    'nomeCognome' => $nomeCognome,
                    'organizationName' => $organizationName
                ]));
                $action = (isset($redirectAction) ? $redirectAction : $defaultAction);
                return $this->redirect($action);
            } else {
                // Iscrivo l'utente all'organizzazione
                /** @var ProfiloUserMm $userOrganization */
                $userOrganization = $this->organizzazioniModule->createModel('ProfiloUserMm');
                $userOrganization->profilo_id = $organizationId;
                $userOrganization->user_id = $userId;
                if (!$this->organizzazioniModule->enableConfirmUsersJoinRequests) {
                    // If the confirm of an user that request to join an organization is disabled set directly the active status and do anything else.
                    $userOrganization->status = ProfiloUserMm::STATUS_ACTIVE;
                    $message = Module::tHtml('amosorganizzazioni', "You are now linked to the organization") . ' ' . $organizationName;
                } else {
                    // If the confirm of an user that request to join an organization is enabled set the request confirm status and send an email to the legal representative.
                    $userOrganization->status = ProfiloUserMm::STATUS_WAITING_REQUEST_CONFIRM;
                    $message = Module::tHtml('amosorganizzazioni', '#join_organization_request_forwarded_to_referees', [
                        'organizationName' => $organizationName
                    ]);
                    $emailType = EmailUtility::REGISTRATION_REQUEST;
                    $emailUtil = new EmailUtility($emailType, $userOrganization->role, $organization, $userProfile->nomeCognome, '', null, $userProfile->user_id);
                    $organizationRefereesEmails = $emailUtil->getOrganizationRefereesMailList($organizationId);
                    $subject = $emailUtil->getSubject();
                    $text = $emailUtil->getText();
                    foreach ($organizationRefereesEmails as $to) {
                        $emailUtil->sendMail(null, $to, $subject, $text, [], []);
                    }
                }
                $ok = $userOrganization->save(false);

                if ($ok) {
                    $organization->setCwhAuthAssignments($userOrganization);
                    Yii::$app->getSession()->addFlash('success', $message);
                    $action = (isset($redirectAction) ? $redirectAction : $defaultAction);
                    return $this->redirect($action);
                } else {
                    Yii::$app->getSession()->addFlash('danger', Module::tHtml('amosorganizzazioni', '#join_organization_error', [
                        'nomeCognome' => $nomeCognome,
                        'organizationName' => $organizationName
                    ]));
                    return $this->redirect($defaultAction);
                }
            }
        } else {
            if ($userOrganization->status == ProfiloUserMm::STATUS_WAITING_REQUEST_CONFIRM) {
                $messagePlaceholder = '#join_organization_user_waiting_request_confirm';
            } elseif ($userOrganization->status == ProfiloUserMm::STATUS_ACTIVE) {
                $messagePlaceholder = '#join_organization_user_already_joined';
            } elseif ($userOrganization->status == ProfiloUserMm::STATUS_REJECTED) {
                $messagePlaceholder = '#join_organization_user_rejected';
            } else {
                $messagePlaceholder = '#join_organization_user_already_joined';
            }
            $this->addFlash('info', Module::tHtml('amosorganizzazioni', $messagePlaceholder, [
                'nomeCognome' => $nomeCognome,
                'organizationName' => $organizationName
            ]));
            return $this->redirect($defaultAction);
        }
    }

    /**
     * Organization referees accepts the user membership request to an organization
     *
     * @param $profiloId
     * @param $userId
     * @return \yii\web\Response
     */
    public function actionAcceptUser($profiloId, $userId)
    {
        return $this->redirect($this->acceptOrRejectUser($profiloId, $userId, true));
    }

    /**
     * Organization referees rejects the user membership request to an organization
     *
     * @param int $profiloId
     * @param int $userId
     * @return \yii\web\Response
     */
    public function actionRejectUser($profiloId, $userId)
    {
        return $this->redirect($this->acceptOrRejectUser($profiloId, $userId, false));
    }

    /**
     * @param int $profiloId
     * @param int $userId
     * @param bool $acccept - true if User membership request has been accepted by organization referees, false if rejected
     * @return string
     */
    private function acceptOrRejectUser($profiloId, $userId, $acccept)
    {
        /** @var ProfiloUserMm $profiloUserMm */
        $profiloUserMm = $this->organizzazioniModule->createModel('ProfiloUserMm');
        $userOrganization = $profiloUserMm::findOne(['profilo_id' => $profiloId, 'user_id' => $userId]);
        $redirectUrl = '';

        if (!is_null($userOrganization)) {
            $refereeName = '';
            $nomeCognome = " ";
            $organizationName = '';
            $userOrganizationRole = $userOrganization->role;
            $redirectUrl = Url::previous();

            $user = User::findOne($userId);
            $userProfile = $user->userProfile;
            if (!is_null($userProfile)) {
                $nomeCognome = "'" . $userProfile->nomeCognome . "'";
            }

            /** @var Profilo $profiloModel */
            $profiloModel = $this->organizzazioniModule->createModel('Profilo');
            $organization = $profiloModel::findOne($profiloId);
            if (!is_null($organization)) {
                $organizationName = "'" . $organization->name . "'";
            }

            if ($acccept) {
                $emailType = EmailUtility::WELCOME;
                $userOrganization->status = $profiloUserMm::STATUS_ACTIVE;
                $userOrganization->save(false);
                $organization->setCwhAuthAssignments($userOrganization);
                $messagePlaceholder = '#join_organization_user_accepted';
            } else {
                $emailType = EmailUtility::REGISTRATION_REJECTED;
                $userOrganization->status = $profiloUserMm::STATUS_REJECTED;
                $userOrganization->save(false);
                $userOrganization->delete();
                /** @var User $loggedUser */
                $loggedUser = Yii::$app->user->identity;
                $loggedUserProfile = $loggedUser->userProfile;
                $refereeName = $loggedUserProfile->getNomeCognome();
                $messagePlaceholder = '#join_organization_user_rejected_successfully';
            }

            $emailUtil = new EmailUtility($emailType, $userOrganizationRole, $organization, $userProfile->nomeCognome, $refereeName, null, $userProfile->user_id);
            $subject = $emailUtil->getSubject();
            $text = $emailUtil->getText();
            $emailUtil->sendMail(null, $user->email, $subject, $text, [], []);

            $message = Module::tHtml('amosorganizzazioni', $messagePlaceholder, [
                'nomeCognome' => $nomeCognome,
                'organizationName' => $organizationName
            ]);
            $this->addFlash('success', $message);
        }
        return $redirectUrl;
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
        /** @var ProfiloUserMm $profiloUserMm */
        $profiloUserMm = $this->organizzazioniModule->createModel('ProfiloUserMm');
        $profiloUserMm = $profiloUserMm::findOne(['profilo_id' => $profiloId, 'user_id' => $userId]);
        /** @var UserProfile $userProfileModel */
        $userProfileModel = AmosAdmin::instance()->createModel('UserProfile');
        /** @var UserProfile $userProfile */
        $userProfile = $userProfileModel::findOne(['user_id' => $userId]);
        $this->model = $this->findModel($profiloId);

        if (Yii::$app->user->can('USERPROFILE_UPDATE', ['model' => $userProfile]) || Yii::$app->user->can('ADMIN') || Yii::$app->user->can('AMMINISTRATORE_ORGANIZZAZIONI')) {
            if (Yii::$app->getRequest()->isAjax && Yii::$app->request->isPost) {
                $post = Yii::$app->request->post();
                if (!is_null($profiloUserMm) && isset($post['user_profile_role']) && isset($post['user_profile_area'])) {
                    $profiloUserMm->user_profile_role_id = $post['user_profile_role'];
                    $profiloUserMm->user_profile_area_id = $post['user_profile_area'];
                    $ok = $profiloUserMm->save(false);
                    if ($ok) {
                        $profiloName = '';
                        if (!is_null($this->model)) {
                            $profiloName = $this->model->name;
                        }
                        $message = "'" . $userProfile->nomeCognome . "' " . Module::tHtml('amosorganizzazioni', 'is now') .
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
