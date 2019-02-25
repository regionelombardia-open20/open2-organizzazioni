<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\widgets
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\widgets;

use lispa\amos\admin\AmosAdmin;
use lispa\amos\admin\models\UserProfile;
use lispa\amos\admin\models\UserProfileArea;
use lispa\amos\admin\models\UserProfileRole;
use lispa\amos\core\forms\editors\m2mWidget\M2MWidget;
use lispa\amos\core\forms\editors\Select;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\user\User;
use lispa\amos\core\utilities\JsUtility;
use lispa\amos\organizzazioni\models\Profilo;
use lispa\amos\organizzazioni\models\ProfiloUserMm;
use lispa\amos\organizzazioni\Module;
use Yii;
use yii\base\Widget;
use yii\bootstrap\Modal;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\web\View;
use yii\widgets\PjaxAsset;

/**
 * Class UserNetworkWidget
 * @package lispa\amos\organizzazioni\widgets
 */
class UserNetworkWidget extends Widget
{
    /**
     * @var int $userId
     */
    public $userId = null;

    /**
     * @var bool|false true if we are in edit mode, false if in view mode or otherwise
     */
    public $isUpdate = false;

    /**
     * @var string $gridId
     */
    public $gridId = 'user-organizzazioni-grid';

    /**
     * @var AmosAdmin $adminModule
     */
    private $adminModule;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->adminModule = Yii::$app->getModule('admin');

        if (is_null($this->userId)) {
            throw new \Exception(Module::t('amosorganizzazioni', '#Missing_user_id'));
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $searchPostName = 'searchProfiloName';
        $gridId = $this->gridId;
        $url = \Yii::$app->urlManager->createUrl([
            '/' . Module::getModuleName() . '/profilo/user-network',
            'userId' => $this->userId,
            'isUpdate' => $this->isUpdate
        ]);

        $js = JsUtility::getSearchM2mFirstGridJs($gridId, $url, $searchPostName);
        PjaxAsset::register($this->getView());
        $this->getView()->registerJs($js, View::POS_LOAD);

        $query = $this->getModelDataQuery($searchPostName);

        /** @var UserProfile $model */
        $model = User::findOne($this->userId)->getProfile();
        $loggedUserId = Yii::$app->getUser()->id;
        $this->isUpdate = $this->isUpdate && ($loggedUserId == $model->user_id);

        $itemsMittente = [
            'profilo.profilo_enti_type_id' => [
                'attribute' => 'profilo.profilo_enti_type_id',
                'value' => 'profilo.profiloEntiType.name'
            ],
            'logo_id' => [
                'headerOptions' => [
                    'id' => Module::t('amosorganizzazioni', '#logo'),
                ],
                'contentOptions' => [
                    'headers' => Module::t('amosorganizzazioni', '#logo'),
                ],
                'label' => Module::t('amosorganizzazioni', '#logo'),
                'format' => 'raw',
                'value' => function ($model) {
                    /** @var ProfiloUserMm $model */
                    return ProfiloCardWidget::widget(['model' => $model->profilo]);
                }
            ],
            'profilo.name',
            [
                'attribute' => 'profilo.createdUserProfile.created_by',
                'value' => 'profilo.createdUserProfile.nomeCognome'
            ],
        ];

        $actionColumnsButtons = [
            'deleteRelation' => function ($url, $model) {
                /** @var ProfiloUserMm $model */
                $url = '/' . Module::getModuleName() . '/profilo/elimina-m2m';
                $organizationId = $model->profilo_id;
                $targetId = $this->userId;
                $urlDelete = Yii::$app->urlManager->createUrl([
                    $url,
                    'id' => $organizationId,
                    'targetId' => $targetId
                ]);
                $loggedUser = Yii::$app->getUser();
                if ($loggedUser->id == $this->userId && ($model->profilo->created_by != $loggedUser->id || $loggedUser->can('ADMIN'))) {
                    $btnDelete = Html::a(AmosIcons::show('close', ['class' => 'btn-delete-relation']),
                        $urlDelete,
                        [
                            'title' => Module::t('amosorganizzazioni', '#delete'),
                            'data-confirm' => Module::t('amosorganizzazioni', '#are_you_sure_cancel'),
                        ]
                    );
                } else {
                    $btnDelete = '';
                }
                return $btnDelete;
            }
        ];

        if ($this->adminModule->roleAndAreaOnOrganizations) {
            $itemsMittente[] = [
                'label' => Module::t('amosorganizzazioni', '#user_profile_role_label'),
                'value' => function ($model) {
                    /** @var ProfiloUserMm $model */
                    $str = '-';
                    if (!is_null($model->userProfileRole)) {
                        $str = $model->userProfileRole->name;
                    }
                    return $str;
                }
            ];
            $itemsMittente[] = [
                'label' => Module::t('amosorganizzazioni', '#user_profile_area_label'),
                'value' => function ($model) {
                    /** @var ProfiloUserMm $model */
                    $str = '-';
                    if (!is_null($model->userProfileArea)) {
                        $str = $model->userProfileArea->name;
                    }
                    return $str;
                }
            ];

            $actionColumnsButtons['relationAttributeManage'] = function ($url, $model) {
                /** @var ProfiloUserMm $model */
                $url = Yii::$app->urlManager->createUrl($createUrlParamsRole = [
                    '/' . Module::getModuleName() . '/profilo/change-user-role-area',
                    'profiloId' => $model->profilo_id,
                    'userId' => $this->userId
                ]);

                $modalId = 'change-organizzazioni-user-role-area-modal-' . $model->profilo_id;
                $selectRoleId = 'profilo_user_mm-role-' . $model->profilo_id;
                $selectAreaId = 'profilo_user_mm-area-' . $model->profilo_id;

                Modal::begin([
                    'header' => Module::t('amosorganizzazioni', 'Manage role and area'),
                    'id' => $modalId,
                ]);

                echo Html::tag('div', Html::label('Ruolo', 'user_profile_role_id') . Select::widget([
                        'auto_fill' => true,
                        'hideSearch' => true,
                        'theme' => 'bootstrap',
                        'data' => ArrayHelper::map($this->getUserProfileRoles($model), 'id', 'name'),
                        'model' => $model,
                        'attribute' => 'user_profile_role_id',
                        'options' => [
                            'prompt' => Module::t('amosorganizzazioni', 'Select/Choose') . '...',
                            'disabled' => false,
                            'id' => $selectRoleId
                        ],
                        'pluginOptions' => [
                            'allowClear' => false,
                        ]
                    ]), ['class' => 'm-15-0']);

                echo Html::tag('div', Html::label('Area', 'user_profile_area_id') . Select::widget([
                        'auto_fill' => true,
                        'hideSearch' => true,
                        'theme' => 'bootstrap',
                        'data' => ArrayHelper::map($this->getUserProfileAreas($model), 'id', 'name'),
                        'model' => $model,
                        'attribute' => 'user_profile_area_id',
                        'options' => [
                            'prompt' => Module::t('amosorganizzazioni', 'Select/Choose') . '...',
                            'disabled' => false,
                            'id' => $selectAreaId
                        ],
                        'pluginOptions' => [
                            'allowClear' => false,
                        ]
                    ]), ['class' => 'm-15-0']);

                echo Html::tag('div',
                    Html::a(Module::t('amosorganizzazioni', '#cancel'),
                        null,
                        ['class' => 'btn btn-secondary', 'data-dismiss' => 'modal'])
                    . Html::a(Module::t('amosorganizzazioni', 'Save'),
                        null,
                        [
                            'class' => 'btn btn-primary',
                            'onclick' => "
                                $.ajax({
                                    url : '$url', 
                                    type: 'POST',
                                    async: true,
                                    data: {
                                        user_profile_role: $('#$selectRoleId').val(),
                                        user_profile_area: $('#$selectAreaId').val()
                                    },
                                    success: function(response) {
                                       $('#$modalId').modal('hide');
                                       $('#reset-search-btn-$this->gridId').click();
                                   }
                                });
                            return false;"
                        ]),
                    ['class' => 'pull-right m-15-0']
                );
                Modal::end();

                $btn = Html::a(
                    Module::t('amosorganizzazioni', '#change_role_area'),
                    null, [
                    'class' => 'btn btn-tools-secondary btn-tools-secondary-text',
                    'title' => Module::t('amosorganizzazioni', '#change_role_area'),
                    'data-toggle' => 'modal',
                    'data-target' => '#' . $modalId,
                    'onclick' => 'checkSelect2Init("' . $modalId . '", "' . $selectRoleId . '");checkSelect2Init("' . $modalId . '", "' . $selectAreaId . '");'
                ]);

                return $btn;
            };
        }

        $widget = M2MWidget::widget([
            'model' => $model,
            'modelId' => $model->id,
            'modelData' => $query,
            'overrideModelDataArr' => true,
            'forceListRender' => true,
            'targetUrlParams' => [
                'viewM2MWidgetGenericSearch' => true
            ],
            'gridId' => $gridId,
            'firstGridSearch' => true,
            'itemsSenderPageSize' => 10,
            'pageParam' => 'page-organizations',
            'disableCreateButton' => true,
            'createAssociaButtonsEnabled' => $this->isUpdate,
            'btnAssociaLabel' => Module::t('amosorganizzazioni', '#add_new_organization'),
            'actionColumnsTemplate' => $this->isUpdate ? '{relationAttributeManage}{deleteRelation}' : '',
            'deleteRelationTargetIdField' => 'user_id',
            'targetUrl' => '/' . Module::getModuleName() . '/profilo/associate-organization-m2m',
            'createNewTargetUrl' => '/admin/user-profile/create',
            'moduleClassName' => Module::className(),
            'targetUrlController' => 'organizzazioni',
            'postName' => 'User',
            'postKey' => 'user',
            'permissions' => [
                'add' => 'USERPROFILE_UPDATE',
                'manageAttributes' => 'USERPROFILE_UPDATE'
            ],
            'actionColumnsButtons' => $actionColumnsButtons,
            'itemsMittente' => $itemsMittente,
        ]);

        return "<div id='" . $gridId . "' data-pjax-container='" . $gridId . "-pjax' data-pjax-timeout=\"1000\">"
            . "<h3>" . Module::tHtml('amosorganizzazioni', '#organization') . "</h3>"
            . $widget . "</div>";
    }

    /**
     * @param string $searchPostName
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    private function getModelDataQuery($searchPostName)
    {
        /** @var ActiveQuery $query */
        $query = ProfiloUserMm::find();
        $query->innerJoinWith('profilo');
        $query->andWhere([ProfiloUserMm::tableName() . '.user_id' => $this->userId]);

        $searchName = Yii::$app->request->post($searchPostName);
        if (!is_null($searchName) && !empty($searchName)) {
            $query->andFilterWhere(['like', Profilo::tableName() . '.name', $searchName]);
        }
        return $query;
    }

    /**
     * @param ProfiloUserMm $profiloUserMm
     * @return UserProfileArea[]
     * @throws \yii\base\InvalidConfigException
     */
    private function getUserProfileAreas($profiloUserMm)
    {
        /** @var ActiveQuery $query */
        $query = UserProfileArea::find();
        if ($this->adminModule->roleAndAreaOnOrganizations && $this->adminModule->roleAndAreaFromOrganizationsWithTypeCat) {
            $query->andWhere(['type_cat' => [UserProfileArea::TYPE_CAT_GENERIC, $profiloUserMm->profilo->profilo_enti_type_id]]);
        }
        $query->orderBy(['order' => SORT_ASC]);
        $areas = $query->all();
        return $areas;
    }

    /**
     * @param ProfiloUserMm $profiloUserMm
     * @return UserProfileArea[]
     * @throws \yii\base\InvalidConfigException
     */
    private function getUserProfileRoles($profiloUserMm)
    {
        /** @var ActiveQuery $query */
        $query = UserProfileRole::find();
        if ($this->adminModule->roleAndAreaOnOrganizations && $this->adminModule->roleAndAreaFromOrganizationsWithTypeCat) {
            $query->andWhere(['type_cat' => [UserProfileRole::TYPE_CAT_GENERIC, $profiloUserMm->profilo->profilo_enti_type_id]]);
        }
        $query->orderBy(['order' => SORT_ASC]);
        $areas = $query->all();
        return $areas;
    }
}
