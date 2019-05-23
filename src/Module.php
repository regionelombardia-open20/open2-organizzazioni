<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni;

use lispa\amos\core\exceptions\AmosException;
use lispa\amos\core\interfaces\OrganizationsModuleInterface;
use lispa\amos\core\interfaces\SearchModuleInterface;
use lispa\amos\core\module\AmosModule;
use lispa\amos\core\widget\WidgetAbstract;
use lispa\amos\organizzazioni\i18n\grammar\ProfiloGrammar;
use lispa\amos\organizzazioni\models\Profilo;
use lispa\amos\organizzazioni\models\ProfiloUserMm;
use lispa\amos\organizzazioni\utility\OrganizzazioniUtility;
use lispa\amos\organizzazioni\widgets\JoinedOrganizationsWidget;
use lispa\amos\organizzazioni\widgets\JoinedOrgParticipantsTasksWidget;
use lispa\amos\organizzazioni\widgets\ProfiloCardWidget;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class Module
 * @package lispa\amos\organizzazioni
 */
class Module extends AmosModule implements OrganizationsModuleInterface, SearchModuleInterface
{
    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'lispa\amos\organizzazioni\controllers';
    public $newFileMode = 0666;
    public $name = 'organizzazioni';

    /**
     * @var bool $enableMembershipOrganizations If true enable the membership organizations. You can set a parent organization in form.
     */
    public $enableMembershipOrganizations = false;

    /**
     * @var array $defaultListViews This set the default order for the views in lists
     */
    public $defaultListViews = ['grid'/*, 'icon'*/];

    /**
     * @var bool $enableAddOtherLegalHeadquarters If true it's possible to add other legal headquarters. The headquarter type is visible in create headquarter select.
     */
    public $enableAddOtherLegalHeadquarters = false;

    /**
     * @inheritdoc
     */
    public $db_fields_translation = [
        [
            'namespace' => 'lispa\amos\organizzazioni\models\ProfiloEntiType',
            'attributes' => ['name'],
            'category' => 'amosorganizzazioni',
        ],
    ];

    /**
     * @var bool $enableSocial
     */
    public $enableSocial = true;

    /**
     * @var bool $oldStyleAddressEnabled
     */
    public $oldStyleAddressEnabled = false;

    /**
     * @var bool $enableSediRequired
     */
    public $enableSediRequired = true;

    /**
     * @var bool $enableRappresentanteLegaleText
     */
    public $enableRappresentanteLegaleText = false;

    /**
     * @var bool $forceSameSede
     */
    public $forceSameSede = false;

    /**
     * If true this configuration enable the user to request to join an organization and a validator confirm the request.
     * The confirm can be made by legal representative, operative referee and the validator.
     * @var bool $enableConfirmUsersJoinRequests
     */
    public $enableConfirmUsersJoinRequests = false;

    /**
     * @var array $htmlMailSubject
     */
    public $htmlMailSubject = [];

    /**
     * @var array $htmlMailContent
     */
    public $htmlMailContent = [];

    /**
     * @var bool $enabled_widget_sedi
     */
    public $enabled_widget_sedi = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        \Yii::setAlias('@lispa/amos/' . static::getModuleName() . '/controllers/', __DIR__ . '/controllers/');
        // custom initialization code goes here
        $config = require(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
        \Yii::configure($this, ArrayHelper::merge($config, ["params" => $this->params]));
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultModels()
    {
        return [
            'OrganizationsPlaces' => __NAMESPACE__ . '\\' . 'models\OrganizationsPlaces',
            'Profilo' => __NAMESPACE__ . '\\' . 'models\Profilo',
            'ProfiloEntiType' => __NAMESPACE__ . '\\' . 'models\ProfiloEntiType',
            'ProfiloLegalForm' => __NAMESPACE__ . '\\' . 'models\ProfiloLegalForm',
            'ProfiloSedi' => __NAMESPACE__ . '\\' . 'models\ProfiloSedi',
            'ProfiloSediLegal' => __NAMESPACE__ . '\\' . 'models\ProfiloSediLegal',
            'ProfiloSediOperative' => __NAMESPACE__ . '\\' . 'models\ProfiloSediOperative',
            'ProfiloSediTypes' => __NAMESPACE__ . '\\' . 'models\ProfiloSediTypes',
            'ProfiloSediUserMm' => __NAMESPACE__ . '\\' . 'models\ProfiloSediUserMm',
            'ProfiloTypesPmi' => __NAMESPACE__ . '\\' . 'models\ProfiloTypesPmi',
            'ProfiloUserMm' => __NAMESPACE__ . '\\' . 'models\ProfiloUserMm',
            'ProfiloSearch' => __NAMESPACE__ . '\\' . 'models\search\ProfiloSearch',
            'ProfiloSediSearch' => __NAMESPACE__ . '\\' . 'models\search\ProfiloSediSearch',
        ];
    }

    /**
     * @return string
     */
    public static function getModuleName()
    {
        return 'organizzazioni';
    }

    /**
     * @inheritdoc
     */
    public static function getModelSearchClassName()
    {
        return __NAMESPACE__ . '\models\search\ProfiloSearch';
    }

    /**
     * @inheritdoc
     */
    public static function getModuleIconName()
    {
        if (!empty(\Yii::$app->params['dashboardEngine']) && \Yii::$app->params['dashboardEngine'] == WidgetAbstract::ENGINE_ROWS) {
            return 'organizzazioni';
        } else {
            return 'building-o';
        }
    }

    /**
     * @return |null
     */
    public function getWidgetGraphics()
    {
        return NULL;
    }

    /**
     * @return array
     */
    public function getWidgetIcons()
    {
        return [
            \lispa\amos\organizzazioni\widgets\icons\WidgetIconProfilo::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getOrganizationModelClass()
    {
        return $this->model('Profilo');
    }

    /**
     * @inheritdoc
     */
    public function getOrganizationCardWidgetClass()
    {
        return ProfiloCardWidget::className();
    }

    /**
     * @inheritdoc
     */
    public function getAssociateOrgsToProjectWidgetClass()
    {
        return JoinedOrganizationsWidget::className();
    }

    /**
     * @inheritdoc
     */
    public function getAssociateOrgsToProjectTaskWidgetClass()
    {
        return JoinedOrgParticipantsTasksWidget::className();
    }

    /**
     * @inheritdoc
     */
    public function getOrganizationsListQuery()
    {
        $query = Profilo::find();
        $query->orderBy(['name' => SORT_ASC]);

        return $query;
    }

    /**
     * @inheritdoc
     */
    public function saveOrganizationUserMm($user_id, $organization_id, $user_profile_role_id = null, $user_profile_area_id = null)
    {
        try {
            $org = ProfiloUserMm::findOne(['profilo_id' => $organization_id, 'user_id' => $user_id]);
            if (empty($org)) {
                $org = new ProfiloUserMm();
                $org->profilo_id = $organization_id;
                $org->user_id = $user_id;
                if (!empty($user_profile_role_id)) {
                    $org->user_profile_role_id = $user_profile_role_id;
                }
                if (!empty($user_profile_area_id)) {
                    $org->user_profile_area_id = $user_profile_area_id;
                }
                return $org->save(false);
            }
            return true;
        } catch (\Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), \yii\log\Logger::LEVEL_ERROR);
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getOrganization($id)
    {
        $model = null;
        try {
            $model = Profilo::findOne(['id' => $id]);
        } catch (\Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), \yii\log\Logger::LEVEL_ERROR);
        }
        return $model;
    }

    /**
     * @inheritdoc
     */
    public function getUserOrganizations($userId)
    {
        return OrganizzazioniUtility::getUserOrganizations($userId);
    }

    /**
     * @inheritdoc
     */
    public function getUserHeadquarters($userId)
    {
        return OrganizzazioniUtility::getUserHeadquarters($userId);
    }

    /**
     * @return ProfiloGrammar
     */
    public function getGrammar()
    {
        return new ProfiloGrammar();
    }

    /**
     * @param int $userId
     * @param bool $onlyIds
     * @param bool $returnQuery
     * @return array|\yii\db\ActiveQuery|\yii\db\ActiveRecord[]
     * @throws AmosException
     */
    public function getOrganizationsRepresentedOrReferredByUserId($userId, $onlyIds = false, $returnQuery = false)
    {
        return OrganizzazioniUtility::getOrganizationsRepresentedOrReferredByUserId($userId, $onlyIds, $returnQuery);
    }

}
