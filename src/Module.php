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

use lispa\amos\core\interfaces\OrganizationsModuleInterface;
use lispa\amos\core\interfaces\SearchModuleInterface;
use lispa\amos\core\module\AmosModule;
use lispa\amos\organizzazioni\i18n\grammar\OrganizzazioniGrammar;
use lispa\amos\organizzazioni\models\Profilo;
use lispa\amos\organizzazioni\models\ProfiloUserMm;
use lispa\amos\organizzazioni\widgets\JoinedOrganizationsWidget;
use lispa\amos\organizzazioni\widgets\JoinedOrgParticipantsTasksWidget;
use lispa\amos\organizzazioni\widgets\ProfiloCardWidget;
use Yii;

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
     * @var bool $enableAddOtherLegalHeadquarters If true it's possible to add other legal headquarters. The headquarter type is visible in create headquarted select.
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
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        \Yii::setAlias('@lispa/amos/' . static::getModuleName() . '/controllers/', __DIR__ . '/controllers/');
        // custom initialization code goes here
        \Yii::configure($this, require(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php'));
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultModels()
    {
        return [
            'Profilo' => __NAMESPACE__ . '\\' . 'models\Profilo',
            'ProfiloSearch' => __NAMESPACE__ . '\\' . 'models\search\ProfiloSearch',
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
        return 'linentita';
    }

    /**
     * @inheritdoc
     */
    public function getWidgetGraphics()
    {
        return NULL;
    }

    /**
     * @inheritdoc
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
     * @return string
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
     * @param $user_id
     * @param $organization_id
     */
    public function saveOrganizationUserMm($user_id, $organization_id)
    {
        try {
            $org = ProfiloUserMm::findOne(['profilo_id' => $organization_id, 'user_id' => $user_id]);
            if (empty($org)) {
                $org = new ProfiloUserMm();
                $org->profilo_id = $organization_id;
                $org->user_id = $user_id;
                $org->save();
            }
        } catch (\Exception $ex) {
            Yii::getLogger()->log($ex->getMessage(), \yii\log\Logger::LEVEL_ERROR);
        }
    }

    /**
     * @param int $id
     * @return null|Profilo
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
     * @return OrganizzazioniGrammar
     */
    public function getGrammar()
    {
        return new OrganizzazioniGrammar();
    }
}
