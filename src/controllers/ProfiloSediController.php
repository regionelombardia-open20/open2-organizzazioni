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
use lispa\amos\core\forms\editors\m2mWidget\controllers\M2MWidgetControllerTrait;
use lispa\amos\core\forms\editors\m2mWidget\M2MEventsEnum;
use lispa\amos\core\user\User;
use lispa\amos\organizzazioni\models\ProfiloSedi;
use lispa\amos\organizzazioni\models\ProfiloSediUserMm;
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
 * Class ProfiloSediController
 * This is the class for controller "ProfiloSediController".
 * @package lispa\amos\organizzazioni\controllers
 */
class ProfiloSediController extends \lispa\amos\organizzazioni\controllers\base\ProfiloSediController
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

        $this->setMmTableName(Module::instance()->createModel('ProfiloSediUserMm')->className());
        $this->setStartObjClassName(Module::instance()->createModel('ProfiloSedi')->className());
        $this->setMmStartKey('profilo_sedi_id');
        $this->setTargetObjClassName(AmosAdmin::instance()->createModel('UserProfile')->className());
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
        $id = \Yii::$app->request->get('id');

        if (strstr($urlPrevious, 'associate-headquarter-m2m')) {
            $this->setRedirectArray('/admin/user-profile/update?id=' . $id);
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
                            'user-network'
                        ],
                        'roles' => ['PROFILOSEDI_READ']
                    ],
                    [
                        'allow' => true,
                        'actions' => [
                            'elimina-m2m',
                            'annulla-m2m',
                            'associate-headquarter-m2m',
                            'join-headquarter'
                        ],
                        'roles' => ['ASSOCIATE_PROFILO_SEDI_TO_USER_PERMISSION']
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
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionAssociateHeadquarterM2m()
    {
        $userId = \Yii::$app->request->get('id');
        Url::remember();

        $this->setMmTableName(Module::instance()->createModel('ProfiloSediUserMm')->className());
        $this->setStartObjClassName(User::className());
        $this->setMmStartKey('user_id');
        $this->setTargetObjClassName(Module::instance()->createModel('ProfiloSedi')->className());
        $this->setMmTargetKey('profilo_sedi_id');
        $this->setRedirectAction('update');
        $this->setTargetUrl('associate-headquarter-m2m');
        $this->setCustomQuery(true);
        $userProfileId = User::findOne($userId)->getProfile()->id;
        $this->setRedirectArray('/admin/user-profile/update?id=' . $userProfileId . '#tab-network');
        return $this->actionAssociaM2m($userId);

    }

    /**
     * @param $headquarterId
     * @param bool $accept
     * @param null $redirectAction
     * @return \yii\web\Response
     * @throws \yii\base\InvalidConfigException
     */
    public function actionJoinHeadquarter($headquarterId, $accept = false, $redirectAction = null)
    {
        $defaultAction = 'index';

        if (empty($redirectAction)) {
            $urlPrevious = Url::previous();
            $redirectAction = $urlPrevious;
        }
        if (!$headquarterId) {
            \Yii::$app->getSession()->addFlash('danger', Module::tHtml('amosorganizzazioni', "It is not possible to subscribe the user. Missing parameter headquarter."));
            return $this->redirect($defaultAction);
        }

        $nomeCognome = ' ';
        $organizationName = '';
        $headquarterName = '';
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $userId = $user->id;
        $userProfile = $user->userProfile;
        if (!is_null($userProfile)) {
            $nomeCognome = " '" . $userProfile->nomeCognome . "' ";
        }

        /** @var ProfiloSedi $profiloSediModel */
        $profiloSediModel = Module::instance()->createModel('ProfiloSedi');
        $headquarter = $profiloSediModel::findOne($headquarterId);
        if (!is_null($headquarter)) {
            $headquarterName = " '" . $headquarter->name . "'";
            $organizationName = "'" . $headquarter->profilo->name . "'";
        }
        /** @var ProfiloSediUserMm $profiloSediUserMm */
        $profiloSediUserMm = Module::instance()->createModel('ProfiloSediUserMm');
        $userHeadquarter = $profiloSediUserMm::findOne(['profilo_sedi_id' => $headquarterId, 'user_id' => $userId]);

        // Verify if the user is already in the headquarter user relation table
        if (is_null($userHeadquarter)) {
            $organizationRefereesIds = OrganizzazioniUtility::getOrganizationReferees($headquarter->profilo_id, true);
            if (in_array($userId, $organizationRefereesIds)) {
                // The user is a legal representative or a operative referee for the organization, then cannot be a member now.
                // In future modify this code if you want to enable the roles in MM table like communities (and remove this comment).
                Yii::$app->getSession()->addFlash('danger', Module::tHtml('amosorganizzazioni', '#join_headquarter_already_referee', [
                    'nomeCognome' => $nomeCognome,
                    'organizationName' => $organizationName,
                    'headquarterName' => $headquarterName
                ]));
                $action = (isset($redirectAction) ? $redirectAction : $defaultAction);
                return $this->redirect($action);
            } else {
                // Iscrivo l'utente alla sede
                /** @var ProfiloSediUserMm $userHeadquarter */
                $userHeadquarter = Module::instance()->createModel('ProfiloSediUserMm');
                $userHeadquarter->profilo_sedi_id = $headquarterId;
                $userHeadquarter->user_id = $userId;
                if (!$this->organizzazioniModule->enableConfirmUsersJoinRequests) {
                    // If the confirm of an user that request to join an organization headquarter is disabled set directly the active status and do anything else.
                    $userHeadquarter->status = ProfiloSediUserMm::STATUS_ACTIVE;
                    $message = Module::tHtml('amosorganizzazioni', "You are now linked to the headquarter ") . ' ' . $headquarterName;
                } else {
                    // If the confirm of an user that request to join an organization headquarter is enabled set the request confirm status and send an email to the legal representative.
                    $userHeadquarter->status = ProfiloSediUserMm::STATUS_WAITING_REQUEST_CONFIRM;
                    $message = Module::tHtml('amosorganizzazioni', '#join_headquarter_request_forwarded_to_referees', [
                        'organizationName' => $organizationName,
                        'headquarterName' => $headquarterName
                    ]);
                    $emailType = EmailUtility::REGISTRATION_REQUEST;
                    $emailUtil = new EmailUtility($emailType, $userHeadquarter->role, $headquarter, $userProfile->nomeCognome, '', null, $userProfile->user_id);
                    $organizationRefereesEmails = $emailUtil->getOrganizationRefereesMailList($userHeadquarter->profiloSedi->profilo_id);
                    $subject = $emailUtil->getSubject();
                    $text = $emailUtil->getText();
                    foreach ($organizationRefereesEmails as $to) {
                        $emailUtil->sendMail(null, $to, $subject, $text, [], []);
                    }
                }
                $ok = $userHeadquarter->save(false);
            }
            if ($ok) {
                Yii::$app->getSession()->addFlash('success', $message);
                $action = (isset($redirectAction) ? $redirectAction : $defaultAction);
                return $this->redirect($action);
            } else {
                Yii::$app->getSession()->addFlash('danger', Module::tHtml('amosorganizzazioni', '#join_headquarter_error', [
                    'nomeCognome' => $nomeCognome,
                    'headquarterName' => $headquarterName
                ]));
                return $this->redirect($defaultAction);
            }
        } else {
            if ($userHeadquarter->status == ProfiloSediUserMm::STATUS_WAITING_REQUEST_CONFIRM) {
                $messagePlaceholder = '#join_headquarter_user_waiting_request_confirm';
            } elseif ($userHeadquarter->status == ProfiloSediUserMm::STATUS_ACTIVE) {
                $messagePlaceholder = '#join_headquarter_user_already_joined';
            } elseif ($userHeadquarter->status == ProfiloSediUserMm::STATUS_REJECTED) {
                $messagePlaceholder = '#join_headquarter_user_rejected';
            } else {
                $messagePlaceholder = '#join_headquarter_user_already_joined';
            }
            $this->addFlash('info', Module::tHtml('amosorganizzazioni', $messagePlaceholder, [
                'nomeCognome' => $nomeCognome,
                'organizationName' => $organizationName,
                'headquarterName' => $headquarterName
            ]));
            return $this->redirect($defaultAction);
        }
    }

    /**
     * Organization referees accepts the user membership request to an organization headquarter
     *
     * @param int $profiloSediId
     * @param $userId
     * @return \yii\web\Response
     */
    public function actionAcceptUser($profiloSediId, $userId)
    {
        return $this->redirect($this->acceptOrRejectUser($profiloSediId, $userId, true));
    }

    /**
     * Organization referees rejects the user membership request to an organization headquarter
     *
     * @param int $profiloSediId
     * @param int $userId
     * @return \yii\web\Response
     */
    public function actionRejectUser($profiloSediId, $userId)
    {
        return $this->redirect($this->acceptOrRejectUser($profiloSediId, $userId, false));
    }

    /**
     * @param int $profiloSediId
     * @param int $userId
     * @param bool $acccept - true if User membership request has been accepted by organization referees, false if rejected
     * @return string
     */
    private function acceptOrRejectUser($profiloSediId, $userId, $acccept)
    {
        /** @var ProfiloSediUserMm $profiloSediUserMm */
        $profiloSediUserMm = Module::instance()->createModel('ProfiloSediUserMm');
        $userHeadquarter = $profiloSediUserMm::findOne(['profilo_sedi_id' => $profiloSediId, 'user_id' => $userId]);
        $redirectUrl = '';

        if (!is_null($userHeadquarter)) {
            $refereeName = '';
            $nomeCognome = " ";
            $headquarterName = '';
            $userHeadquarterRole = $userHeadquarter->role;
            $redirectUrl = Url::previous();

            $user = User::findOne($userId);
            $userProfile = $user->userProfile;
            if (!is_null($userProfile)) {
                $nomeCognome = "'" . $userProfile->nomeCognome . "'";
            }

            /** @var ProfiloSedi $profiloSediModel */
            $profiloSediModel = Module::instance()->createModel('ProfiloSedi');
            $headquarter = $profiloSediModel::findOne($profiloSediId);
            if (!is_null($headquarter)) {
                $headquarterName = "'" . $headquarter->name . "'";
            }

            if ($acccept) {
                $emailType = EmailUtility::WELCOME;
                $userHeadquarter->status = $profiloSediUserMm::STATUS_ACTIVE;
                $userHeadquarter->save(false);
                $messagePlaceholder = '#join_headquarter_user_accepted';
            } else {
                $emailType = EmailUtility::REGISTRATION_REJECTED;
                $userHeadquarter->status = $profiloSediUserMm::STATUS_REJECTED;
                $userHeadquarter->save(false);
                $userHeadquarter->delete();
                /** @var User $loggedUser */
                $loggedUser = Yii::$app->user->identity;
                $loggedUserProfile = $loggedUser->userProfile;
                $refereeName = $loggedUserProfile->getNomeCognome();
                $messagePlaceholder = '#join_headquarter_user_rejected_successfully';
            }

            $emailUtil = new EmailUtility($emailType, $userHeadquarterRole, $headquarter, $userProfile->nomeCognome, $refereeName, null, $userProfile->user_id);
            $subject = $emailUtil->getSubject();
            $text = $emailUtil->getText();
            $emailUtil->sendMail(null, $user->email, $subject, $text, [], []);

            $message = Module::tHtml('amosorganizzazioni', $messagePlaceholder, [
                'nomeCognome' => $nomeCognome,
                'headquarterName' => $headquarterName
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
}
