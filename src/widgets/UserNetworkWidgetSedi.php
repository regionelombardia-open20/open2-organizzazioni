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
use lispa\amos\core\forms\editors\m2mWidget\M2MWidget;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\user\User;
use lispa\amos\core\utilities\JsUtility;
use lispa\amos\organizzazioni\models\ProfiloSedi;
use lispa\amos\organizzazioni\models\ProfiloSediUserMm;
use lispa\amos\organizzazioni\models\ProfiloUserMm;
use lispa\amos\organizzazioni\Module;
use Yii;
use yii\base\Widget;
use yii\db\ActiveQuery;
use yii\web\View;
use yii\widgets\PjaxAsset;

/**
 * Class UserNetworkWidgetSedi
 * @package lispa\amos\organizzazioni\widgets
 */
class UserNetworkWidgetSedi extends Widget
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
    public $gridId = 'user-sedi-grid';

    /**
     * @var AmosAdmin $adminModule
     */
    private $adminModule;

    /**
     * @var Module $organizationsModule
     */
    private $organizationsModule;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->adminModule = Yii::$app->getModule('admin');
        $this->organizationsModule = Module::instance();

        if (is_null($this->userId)) {
            throw new \Exception(Module::t('amosorganizzazioni', '#Missing_user_id'));
        }
    }

    /**
     * @return string
     */
    public static function getSearchPostName()
    {
        return 'searchProfiloSediName';
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $gridId = $this->gridId;
        $url = \Yii::$app->urlManager->createUrl([
            '/' . Module::getModuleName() . '/profilo-sedi/user-network',
            'userId' => $this->userId,
            'isUpdate' => $this->isUpdate
        ]);

        $js = JsUtility::getSearchM2mFirstGridJs($gridId, $url, static::getSearchPostName());
        PjaxAsset::register($this->getView());
        $this->getView()->registerJs($js, View::POS_LOAD);

        $query = $this->getModelDataQuery(static::getSearchPostName());

        /** @var UserProfile $model */
        $model = User::findOne($this->userId)->getProfile();
        $loggedUserId = Yii::$app->getUser()->id;
        $this->isUpdate = $this->isUpdate && ($loggedUserId == $model->user_id);

        /** @var ProfiloSedi $profiloSedi */
        $profiloSedi = Module::instance()->createModel('ProfiloSedi');
        $itemsMittente = $profiloSedi->getUserNetworkWidgetColumns();

        if ($this->organizationsModule->enableConfirmUsersJoinRequests) {
            $itemsMittente[] = [
                'label' => Module::t('amosorganizzazioni', '#profilo_sedi_user_mm_status_label'),
                'value' => function ($model) {
                    /** @var ProfiloSediUserMm $model */
                    $str = Module::t('amosorganizzazioni', $model->status);
                    return $str;
                }
            ];
        }

        $actionColumnsButtons = [
            'deleteRelation' => function ($url, $model) {
                /** @var ProfiloSediUserMm $model */
                $url = '/' . Module::getModuleName() . '/profilo-sedi/elimina-m2m';
                $headquarterId = $model->profilo_sedi_id;
                $targetId = $this->userId;
                $urlDelete = Yii::$app->urlManager->createUrl([
                    $url,
                    'id' => $headquarterId,
                    'targetId' => $targetId
                ]);
                $loggedUser = Yii::$app->getUser();
                $btnDelete = '';
                if (($loggedUser->id == $this->userId) && (($model->profiloSedi->created_by != $loggedUser->id) || $loggedUser->can('ADMIN'))) {
                    $btnDelete = Html::a(AmosIcons::show('close', ['class' => 'btn-delete-relation']),
                        $urlDelete,
                        [
                            'title' => Module::t('amosorganizzazioni', '#delete'),
                            'data-confirm' => Module::t('amosorganizzazioni', '#are_you_sure_cancel_headquarter'),
                        ]
                    );
                }
                return $btnDelete;
            }
        ];

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
            'btnAssociaLabel' => Module::t('amosorganizzazioni', '#add_new_organization_headquarter'),
            'actionColumnsTemplate' => ($this->isUpdate ? '{deleteRelation}' : ''),
            'deleteRelationTargetIdField' => 'user_id',
            'targetUrl' => '/' . Module::getModuleName() . '/profilo-sedi/associate-headquarter-m2m',
            'createNewTargetUrl' => '/admin/user-profile/create',
            'moduleClassName' => Module::className(),
            'targetUrlController' => 'organizzazioni',
            'postName' => 'User',
            'postKey' => 'user',
            'permissions' => [
                'add' => 'ASSOCIATE_ORGANIZZAZIONI_SEDI_TO_USER',
                'manageAttributes' => 'ASSOCIATE_ORGANIZZAZIONI_SEDI_TO_USER'
            ],
            'actionColumnsButtons' => $actionColumnsButtons,
            'itemsMittente' => $itemsMittente,
        ]);

        return "<div id='" . $gridId . "' data-pjax-container='" . $gridId . "-pjax' data-pjax-timeout=\"1000\">"
            . "<h3>" . Module::tHtml('amosorganizzazioni', '#headquarter') . "</h3>"
            . $widget . "</div>";
    }

    /**
     * @param string $searchPostName
     * @return ActiveQuery
     * @throws \yii\base\InvalidConfigException
     */
    private function getModelDataQuery($searchPostName)
    {
        /** @var ProfiloSedi $profiloSedi */
        $profiloSedi = Module::instance()->createModel('ProfiloSedi');
        /** @var ProfiloSediUserMm $profiloSediUserMm */
        $profiloSediUserMm = Module::instance()->createModel('ProfiloSediUserMm');
        /** @var ActiveQuery $query */
        $query = $profiloSediUserMm::find();
        $query->innerJoinWith('profiloSedi');
        $query->andWhere([$profiloSediUserMm::tableName() . '.user_id' => $this->userId]);

        $searchName = Yii::$app->request->post($searchPostName);
        if (!is_null($searchName) && !empty($searchName)) {
            $query->andFilterWhere(['like', $profiloSedi::tableName() . '.name', $searchName]);
        }
        return $query;
    }
}
