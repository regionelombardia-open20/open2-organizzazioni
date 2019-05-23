<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\models
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\models;

use lispa\amos\admin\AmosAdmin;
use lispa\amos\attachments\behaviors\FileBehavior;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\interfaces\OrganizationsModelInterface;
use lispa\amos\core\validators\CfPivaValidator;
use lispa\amos\core\validators\PIVAValidator;
use lispa\amos\cwh\AmosCwh;
use lispa\amos\cwh\models\CwhAuthAssignment;
use lispa\amos\cwh\models\CwhConfig;
use lispa\amos\organizzazioni\components\OrganizationsPlacesComponents;
use lispa\amos\organizzazioni\i18n\grammar\ProfiloGrammar;
use lispa\amos\organizzazioni\Module;
use lispa\amos\organizzazioni\widgets\icons\WidgetIconProfilo;
use lispa\amos\organizzazioni\widgets\ProfiloCardWidget;
use lispa\amos\organizzazioni\widgets\UserNetworkWidget;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Class Profilo
 * This is the model class for table "profilo".
 *
 * @property \lispa\amos\organizzazioni\models\ProfiloSediOperative $operativeHeadquarter
 * @property \lispa\amos\organizzazioni\models\ProfiloSediLegal $legalHeadquarter
 * @property \lispa\amos\organizzazioni\models\OrganizationsPlaces $sedeIndirizzo
 * @property \lispa\amos\organizzazioni\models\OrganizationsPlaces $sedeLegaleIndirizzo
 *
 * @package lispa\amos\organizzazioni\models
 */
class Profilo extends \lispa\amos\organizzazioni\models\base\Profilo implements OrganizationsModelInterface
{
    private $allegati;

    /**
     * @var array $places_fields
     */
    protected $places_fields = [
        'mainLegalHeadquarterAddress',
        'mainOperativeHeadquarterAddress'
    ];

    /**
     * @var string $mainOperativeHeadquarterAddress
     */
    public $mainOperativeHeadquarterAddress;

    /**
     * @var string $mainLegalHeadquarterAddress
     */
    public $mainLegalHeadquarterAddress;

    /**
     * @inheritdoc
     */
    public function representingColumn()
    {
        return [
            //inserire il campo o i campi rappresentativi del modulo
            'name',
//            'la_sede_legale_e_la_stessa_del',
//            'rappresentante_legale',
//            'referente_operativo',
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        /* return [
          'denominazione' => Module::t('amosorganizzazioni', ''),
          'partita_iva' => Module::t('amosorganizzazioni', 'Deve essere obbligatorio ma alternativi a cf'),
          'codice_fiscale' => Module::t('amosorganizzazioni', 'Deve essere obbligatorio ma alternativi a p.iva'),
          'presentazione_della_organizzaz' => Module::t('amosorganizzazioni', ''),
          'principali_ambiti_di_attivita_organizzazione' => Module::t('amosorganizzazioni', 'deve essere di tipo albero identico a quello del plugin della POI'),
          'ambiti_tecnologici_su_cui_siet' => Module::t('amosorganizzazioni', 'deve essere di tipo albero identico a quello del plugin della POI'),
          'tipologia_di_organizzazione' => Module::t('amosorganizzazioni', 'deve essere una tendina le cui opzioni mi verranno confermate ma tendenzialmente uguali a POI'),
          'forma_legale' => Module::t('amosorganizzazioni', 'deve essere una tendina le cui opzioni mi verranno confermate ma tendenzialmente uguali a POI'),
          'sito_web' => Module::t('amosorganizzazioni', ''),
          'facebook' => Module::t('amosorganizzazioni', ''),
          'twitter' => Module::t('amosorganizzazioni', ''),
          'linkedin' => Module::t('amosorganizzazioni', ''),
          'google' => Module::t('amosorganizzazioni', ''),
          'logo' => Module::t('amosorganizzazioni', 'upload di immagine'),
          'allegati' => Module::t('amosorganizzazioni', 'upload di file'),
          'indirizzo' => Module::t('amosorganizzazioni', 'deve essere come quello di POI cioè con la mappa'),
          'telefono' => Module::t('amosorganizzazioni', ''),
          'fax' => Module::t('amosorganizzazioni', ''),
          'email' => Module::t('amosorganizzazioni', ''),
          'pec' => Module::t('amosorganizzazioni', ''),
          'la_sede_legale_e_la_stessa_del' => Module::t('amosorganizzazioni', 'si/no'),
          'sede_legale_indirizzo' => Module::t('amosorganizzazioni', 'deve essere come quello di POI cioè con la mappa e deve comparire solo se alla domanda precedente è stato risposto no'),
          'sede_legale_telefono' => Module::t('amosorganizzazioni', ' deve comparire solo se alla domanda precedente è stato risposto no'),
          'sede_legale_fax' => Module::t('amosorganizzazioni', ' deve comparire solo se alla domanda precedente è stato risposto no'),
          'sede_legale_email' => Module::t('amosorganizzazioni', ' deve comparire solo se alla domanda precedente è stato risposto no'),
          'sede_legale_pec' => Module::t('amosorganizzazioni', ' deve comparire solo se alla domanda precedente è stato risposto no'),
          'responsabile' => Module::t('amosorganizzazioni', 'da selezionare tra gli utenti registrati'),
          'rappresentante_legale' => Module::t('amosorganizzazioni', 'da selezionare tra gli utenti registrati'),
          'referente_operativo' => Module::t('amosorganizzazioni', 'da selezionare tra gli utenti registrati'),
          ]; */
        return [];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(),
            [
                'fileBehavior' => [
                    'class' => FileBehavior::className()
                ],
            ]);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!$this->organizzazioniModule->oldStyleAddressEnabled) {
            $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'organizationsBeforeValidate']);
        }

        if (!$this->isNewRecord && !$this->organizzazioniModule->oldStyleAddressEnabled) {
            $mainOperativeHeadquarter = $this->operativeHeadquarter;
            if (!is_null($mainOperativeHeadquarter)) {
                $this->mainOperativeHeadquarterAddress = $mainOperativeHeadquarter->address;
            }
            $mainLegalHeadquarter = $this->legalHeadquarter;
            if (!is_null($mainLegalHeadquarter)) {
                $this->mainLegalHeadquarterAddress = $mainLegalHeadquarter->address;
            }
        }

        /** @var Module $organizzazioniModule */
        if ($this->organizzazioniModule->forceSameSede) {
            // Not check if is new record because it must be always the same.
            $this->la_sede_legale_e_la_stessa_del = 1;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = ArrayHelper::merge(parent::rules(),
            [
                [['partita_iva'], PIVAValidator::className()],
//                [['partita_iva'], 'string', 'length' => 11],
                [['codice_fiscale'], CfPivaValidator::className()],
//                [['codice_fiscale'], 'string', 'length' => 11],
                [['email'], 'email'],
                [['pec'], 'email'],
                [['sede_legale_email'], 'email'],
                [['sede_legale_pec'], 'email'],
                [[
                    'mainLegalHeadquarterAddress',
                    'mainOperativeHeadquarterAddress'
                ], 'string'],

            ]);

        if ($this->organizzazioniModule->enableSediRequired && !$this->organizzazioniModule->oldStyleAddressEnabled) {
            $rules = ArrayHelper::merge($rules, [
                [['mainOperativeHeadquarterAddress', 'la_sede_legale_e_la_stessa_del'], 'required'],
                [['mainLegalHeadquarterAddress'], 'required', 'when' => function ($model) {
                    /** @var Profilo $model */
                    return ($model->la_sede_legale_e_la_stessa_del == 0);
                }, 'whenClient' => "function (attribute, value) {
                    return $('#" . Html::getInputId($this, 'la_sede_legale_e_la_stessa_del') . "').val() == 0;
                }"],
            ]);
        }
        return $rules;
    }

    public function organizationsBeforeValidate()
    {
        if (!$this->organizzazioniModule->oldStyleAddressEnabled) {
            foreach ($this->places_fields as $place_field) {
                $place_id = $this->{$place_field};
                OrganizationsPlacesComponents::checkPlace($place_id);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (!$this->organizzazioniModule->oldStyleAddressEnabled) {
            foreach ($this->places_fields as $place_field) {
                $place_id = $this->{$place_field};
                OrganizationsPlacesComponents::checkPlace($place_id);
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Returns the address in string format
     * @return string
     */
    public function getAddressString($field_name)
    {
        if ($this->organizzazioniModule->oldStyleAddressEnabled) {
            return false;
        }

        //check if the input $field_name is in the class array containg the address's fields
        if (!isset($this->{$field_name}) && !in_array($field_name, $this->places_fields)) {
            return false;
        }
        //gets the record by the input field
        $placeObj = OrganizationsPlacesComponents::getPlace($this->{$field_name});
        //return the address's string
        return OrganizationsPlacesComponents::getGeocodeString($placeObj);
    }

    /**
     * Returns the text hint for the specified attribute.
     * @param string $attribute the attribute name
     * @return string the attribute hint
     */
    public function getAttributeHint($attribute)
    {
        $hints = $this->attributeHints();
        return isset($hints[$attribute]) ? $hints[$attribute] : null;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'mainOperativeHeadquarterAddress' => Module::t('amosorganizzazioni', 'Address'),
            'mainLegalHeadquarterAddress' => Module::t('amosorganizzazioni', 'Address'),
        ]);
    }

    /**
     * Verifica la validità del codice fiscale (numerico) o della partita iva
     * @param string $attribute
     * @param array $params
     */
    public function checkPartitaIva($attribute, $params)
    {
        $partitaIva = $this->{$attribute};
        $isValid = false;
        if (!$partitaIva) {
            $isValid = true;
        } else if (strlen($partitaIva) != 11) {
            $isValid = false;
        } else if (strlen($partitaIva) == 11) {
            //la p.iva deve avere solo cifre
            if (!preg_match("/^[0-9]+$/i", $partitaIva)) {
                $isValid = false;
            } else {
                $primo = 0;
                for ($i = 0; $i <= 9; $i += 2) {
                    $primo += ord($partitaIva[$i]) - ord('0');
                }

                for ($i = 1; $i <= 9; $i += 2) {
                    $secondo = 2 * (ord($partitaIva[$i]) - ord('0'));

                    if ($secondo > 9) $secondo = $secondo - 9;
                    $primo += $secondo;
                }
                if ((10 - $primo % 10) % 10 != ord($partitaIva[10]) - ord('0')) {
                    $isValid = false;
                } else {
                    $isValid = true;
                }
            }
        }
        if (!$isValid) {
            $this->addError($attribute, Module::t('amoscore', 'The TAX code/VAT code is not in a permitted format'));
        }
    }

    /**
     * @return array
     */
    public static function getEditFields()
    {
        $labels = self::attributeLabels();

        return [
            [
                'slug' => 'name',
                'label' => $labels['name'],
                'type' => 'string'
            ],
            [
                'slug' => 'partita_iva',
                'label' => $labels['partita_iva'],
                'type' => 'string'
            ],
            [
                'slug' => 'codice_fiscale',
                'label' => $labels['codice_fiscale'],
                'type' => 'string'
            ],
            [
                'slug' => 'presentazione_della_organizzaz',
                'label' => $labels['presentazione_della_organizzaz'],
                'type' => 'text'
            ],
            [
                'slug' => 'tipologia_di_organizzazione',
                'label' => $labels['tipologia_di_organizzazione'],
                'type' => 'string'
            ],
            [
                'slug' => 'forma_legale',
                'label' => $labels['forma_legale'],
                'type' => 'string'
            ],
            [
                'slug' => 'sito_web',
                'label' => $labels['sito_web'],
                'type' => 'string'
            ],
            [
                'slug' => 'facebook',
                'label' => $labels['facebook'],
                'type' => 'string'
            ],
            [
                'slug' => 'twitter',
                'label' => $labels['twitter'],
                'type' => 'string'
            ],
            [
                'slug' => 'linkedin',
                'label' => $labels['linkedin'],
                'type' => 'string'
            ],
            [
                'slug' => 'google',
                'label' => $labels['google'],
                'type' => 'string'
            ],
            [
                'slug' => 'indirizzo',
                'label' => $labels['indirizzo'],
                'type' => 'string'
            ],
            [
                'slug' => 'telefono',
                'label' => $labels['telefono'],
                'type' => 'decimal'
            ],
            [
                'slug' => 'fax',
                'label' => $labels['fax'],
                'type' => 'decimal'
            ],
            [
                'slug' => 'email',
                'label' => $labels['email'],
                'type' => 'string'
            ],
            [
                'slug' => 'pec',
                'label' => $labels['pec'],
                'type' => 'string'
            ],
            [
                'slug' => 'la_sede_legale_e_la_stessa_del',
                'label' => $labels['la_sede_legale_e_la_stessa_del'],
                'type' => 'string'
            ],
            [
                'slug' => 'sede_legale_indirizzo',
                'label' => $labels['sede_legale_indirizzo'],
                'type' => 'string'
            ],
            [
                'slug' => 'sede_legale_telefono',
                'label' => $labels['sede_legale_telefono'],
                'type' => 'decimal'
            ],
            [
                'slug' => 'sede_legale_fax',
                'label' => $labels['sede_legale_fax'],
                'type' => 'decimal'
            ],
            [
                'slug' => 'sede_legale_email',
                'label' => $labels['sede_legale_email'],
                'type' => 'string'
            ],
            [
                'slug' => 'sede_legale_pec',
                'label' => $labels['sede_legale_pec'],
                'type' => 'string'
            ],
            [
                'slug' => 'responsabile',
                'label' => $labels['responsabile'],
                'type' => 'string'
            ],
            [
                'slug' => 'rappresentante_legale',
                'label' => $labels['rappresentante_legale'],
                'type' => 'string'
            ],
            [
                'slug' => 'referente_operativo',
                'label' => $labels['referente_operativo'],
                'type' => 'string'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getModelImage()
    {
        return $this->modelImage = $this->hasOneFile('logoOrganization')->one();
    }

    public function getAllegati()
    {
        if (empty($this->allegati)) {
            $this->allegati = $this->hasMultipleFiles('allegati')->one();
        }
        return $this->allegati;
    }

    /**
     * @param $allegati
     */
    public function setAllegati($allegati)
    {
        $this->allegati = $allegati;
    }

    /**
     * @return string The model title field value
     */
    public function getTitle()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getShortDescription()
    {
        return $this->presentazione_della_organizzaz;
    }

    /**
     * @return string The model description field value
     */
    public function getDescription($truncate)
    {
        $ret = $this->name;

        if ($truncate) {
            $ret = $this->__shortText($this->name, 200);
        }
        return $ret;
    }

    /**
     * @return array The columns ti show as default in GridViewWidget
     */
    public function getGridViewColumns()
    {
        return [
            'profilo_enti_type_id' => [
                'attribute' => 'profilo_enti_type_id',
                'value' => 'profiloEntiType.name'
            ],
            'name',
            'formaLegale.name',
            'addressField:raw',
            'operativeHeadquarter.email',
            [
                'class' => 'lispa\amos\core\views\grid\ActionColumn',
            ]
        ];
    }

    /**
     * @return array
     */
    public function getUserNetworkWidgetColumns()
    {
        return [
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
    }

    /**
     * @return \yii\db\ActiveQuery category of content
     */
    public function getCategory()
    {
        return null;
    }

    /**
     * @return string The classname of the generic dashboard widget to access the plugin
     */
    public function getPluginWidgetClassname()
    {
        return WidgetIconProfilo::className();
    }

    /**
     * @return ProfiloGrammar
     */
    public function getGrammar()
    {
        return new ProfiloGrammar();
    }

    /**
     * @return string The name that correspond to 'to validate' status for the content model
     */
    public function getToValidateStatus()
    {
        return null;
    }

    /**
     * @return string The name that correspond to 'published' status for the content model
     */
    public function getValidatedStatus()
    {
        return null;
    }

    /**
     * @return string The name that correspond to 'draft' status for the content model
     */
    public function getDraftStatus()
    {
        return null;
    }

    /**
     * @return string The name of model validator role
     */
    public function getValidatorRole()
    {
        return null;
    }

    /**
     * Get the user id used in network-users association table
     * @return int
     */
    public function getUserId()
    {
        return Yii::$app->getUser()->id;
    }

    /**
     * Get the name of the table storing network-users associations
     * @return string
     */
    public function getMmTableName()
    {
        return ProfiloUserMm::tableName();
    }

    /**
     * Get the name of field that contains user id in network-users association table
     * @return string
     */
    public function getMmNetworkIdFieldName()
    {
        return 'profilo_id';
    }

    /**
     * Get the name of field that contains network id in network-users association table
     * @return string
     */
    public function getMmUserIdFieldName()
    {
        return 'user_id';
    }

    /**
     * Return true if the user with id $userId belong to the network with id $networkId; if $userId is null the logged User id is considered
     * @param int $networkId
     * @param int $userId
     * @return bool
     */
    public function isNetworkUser($networkId, $userId = null)
    {
        if (!isset($userId)) {
            $userId = $this->getUserId();
        }
        $mmRow = ProfiloUserMm::findOne([
            $this->getMmNetworkIdFieldName() => $networkId,
            $this->getMmUserIdFieldName() => $userId,
        ]);
        if (!is_null($mmRow)) {
            return true;
        }
        return false;
    }

    /**
     * Return true if the network is validated or no validation process is implemented for the network.
     * if $networkId is null, current network (this) is condidered
     * @param int $networkId
     * @return bool
     */
    public function isValidated($networkId = null)
    {
        return true;
    }

    /**
     * Return classname of the MM table connecting user and network
     * @return string
     */
    public function getMmClassName()
    {
        return ProfiloUserMm::className();
    }

    /**
     * @param null $userId
     * @param bool $isUpdate
     * @return mixed
     */
    public function getUserNetworkWidget($userId = null, $isUpdate = false)
    {
        /** @var AmosAdmin $adminModule */
        $adminModule = AmosAdmin::instance();
        $organizationsModuleName = $adminModule->getOrganizationModuleName();
        if (is_null(Yii::$app->getModule($organizationsModuleName))) {
            return '';
        }
        return UserNetworkWidget::widget(['userId' => $userId, 'isUpdate' => $isUpdate]);
    }

    /**
     * @return array list of statuses that for cwh is validated
     */
    public function getCwhValidationStatuses()
    {
        return [];
    }

//    Uses the common method in NetworkModel
//    /**
//     * @param null $userId
//     * @return mixed
//     */
//    public function getUserNetworkAssociationQuery($userId = null)
//    {
//        if (empty($userId)) {
//            $userId = Yii::$app->user->id;
//        }
//        $query = self::find()->distinct();
//        $queryJoined = Profilo::find()->distinct();
//        $queryJoined->innerJoin(\lispa\amos\organizzazioni\models\ProfiloUserMm::tableName(),
//            Profilo::tableName() . '.id = ' . ProfiloUserMm::tableName() . '.profilo_id'
//            . ' AND ' . ProfiloUserMm::tableName() . '.user_id = ' . $userId)
//            ->andWhere(ProfiloUserMm::tableName() . '.deleted_at is null');
//        $queryJoined->select(Profilo::tableName() . '.id');
//        $query->andWhere(['not in', Profilo::tableName() . '.id', $queryJoined]);
//        $query->andWhere(Profilo::tableName() . '.deleted_at is null');
//        return $query;
//    }

    /**
     * Get Id of configuration record for network model Profilo
     * @return int $cwhConfigId
     */
    public static function getCwhConfigId()
    {
        // Default network configuration id = 7 for organizzazioni
        $cwhConfigId = 7;
        $cwhConfig = CwhConfig::findOne(['tablename' => self::tableName()]);
        if (!is_null($cwhConfig)) {
            $cwhConfigId = $cwhConfig->id;
        }
        return $cwhConfigId;
    }

    /**
     * Add CWH permissions based on the role for which a permissions array has been specified,
     * Remove CWH permissions on profilo domain in case of role degradation
     * or delete all permission in case of user-profilo association deletion
     *
     * @param ProfiloUserMm $profiloUserMmRow
     * @param bool|false $delete - if true remove all permission (case deletion user-community association)
     */
    public function setCwhAuthAssignments($profiloUserMmRow, $delete = false)
    {
        /** @var AmosCwh $cwhModule */
        $cwhModule = Yii::$app->getModule("cwh");
        $cwhNodeId = self::tableName() . '-' . $this->id;
        $userId = $profiloUserMmRow->user_id;
        $cwhConfigId = self::getCwhConfigId();

        $cwhPermissions = CwhAuthAssignment::find()->andWhere([
            'user_id' => $userId,
            'cwh_config_id' => $cwhConfigId,
            'cwh_network_id' => $this->id
        ])->all();

        if ($delete) {
            if (!empty($cwhPermissions)) {
                /** @var CwhAuthAssignment $cwhPermission */
                foreach ($cwhPermissions as $cwhPermission) {
                    $cwhPermission->delete();
                }
            } else {
                $existingPermissions = [];
                foreach ($cwhPermissions as $item) {
                    $existingPermissions[$item->item_name] = $item;
                }

                /** @var Profilo $callingModel */
                $callingModel = $this->organizzazioniModule->createModel('Profilo');
                /** @var array $rolePermissions */
                $rolePermissions = $callingModel->getRolePermissions($profiloUserMmRow->role);
                $permissionsToAdd = [];
                if (!is_null($rolePermissions) && count($rolePermissions)) {
                    // For each enabled Content model in CWH...
                    foreach ($cwhModule->modelsEnabled as $modelClassname) {
                        // ...and each role permission...
                        foreach ($rolePermissions as $permission) {
                            // ...
                            $cwhAuthAssignment = new CwhAuthAssignment();
                            $cwhAuthAssignment->user_id = $userId;
                            $cwhAuthAssignment->item_name = $permission . '_' . $modelClassname;
                            $cwhAuthAssignment->cwh_nodi_id = $cwhNodeId;
                            $cwhAuthAssignment->cwh_config_id = $cwhConfigId;
                            $cwhAuthAssignment->cwh_network_id = $this->id;
                            $permissionsToAdd[$cwhAuthAssignment->item_name] = $cwhAuthAssignment;
                        }
                    }
                }
                if (!empty($permissionsToAdd)) {
                    /** @var CwhAuthAssignment $permissionToAdd */
                    foreach ($permissionsToAdd as $key => $permissionToAdd) {
                        //if user has not already the permission for the community , add it to cwh auth assignment
                        if (!array_key_exists($key, $existingPermissions)) {
                            $permissionToAdd->save(false);
                        }
                    }
                }
                // check if there are permissions to remove
                if (!empty($existingPermissions)) {
                    /** @var CwhAuthAssignment $cwhPermission */
                    foreach ($existingPermissions as $key => $cwhPermission) {
                        if (!array_key_exists($key, $permissionsToAdd)) {
                            $cwhPermission->delete();
                        }
                    }
                }
            }
        }
    }

    /**
     * Array containing user permission for a given role
     * @param string $role
     * @return array
     */
    public function getRolePermissions($role)
    {
        return ['CWH_PERMISSION_CREATE', 'CWH_PERMISSION_VALIDATE'];
    }


    public function getNameField()
    {
        return $this->name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOperativeHeadquarter()
    {
        return $this->hasOne($this->organizzazioniModule->createModel('ProfiloSediOperative')->className(), ['profilo_id' => 'id'])
            ->andWhere(['profilo_sedi_type_id' => ProfiloSediTypes::TYPE_OPERATIVE_HEADQUARTER])
            ->andWhere(['active' => 1])
            ->andWhere(['is_main' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSedeIndirizzo()
    {
        if ($this->organizzazioniModule->oldStyleAddressEnabled) {
            return null;
        }
        return $this->hasOne($this->organizzazioniModule->createModel('OrganizationsPlaces')->className(), ['place_id' => 'address'])->via('operativeHeadquarter');
    }

    /**
     * @return string
     */
    public function getAddressField()
    {
        if (!$this->organizzazioniModule->oldStyleAddressEnabled) {
            if (is_null($this->sedeIndirizzo)) {
                return '-';
            }

            return ($this->sedeIndirizzo->postal_code ? '(' . $this->sedeIndirizzo->postal_code . ')' : '') .
                ($this->sedeIndirizzo->region ? ' ' . $this->sedeIndirizzo->region : '') .
                ($this->sedeIndirizzo->city ? ' ' . $this->sedeIndirizzo->city : '') .
                ($this->sedeIndirizzo->address ? ' ' . $this->sedeIndirizzo->address : '') .
                ($this->sedeIndirizzo->street_number ? ' ' . $this->sedeIndirizzo->street_number : '');
        } else {
            $operativeHeadquarter = $this->operativeHeadquarter;
            return (!is_null($operativeHeadquarter) ? $operativeHeadquarter->getOldStyleAddress() : '-');
        }
    }

    /**
     * @return array
     */
    public function getAddressFieldAsArray()
    {
        if ($this->organizzazioniModule->oldStyleAddressEnabled) {
            $operativeHeadquarter = $this->operativeHeadquarter;
            if (!is_null($operativeHeadquarter)) {
                return [
                    'postal_code' => ($operativeHeadquarter->cap_text ? $operativeHeadquarter->cap_text : ''),
                    'region' => (!is_null($operativeHeadquarter->province) && !is_null($operativeHeadquarter->province->istatRegioni) ? $operativeHeadquarter->province->istatRegioni->nome : ''),
                    'city' => (!is_null($operativeHeadquarter->city) ? $operativeHeadquarter->city->nome : ''),
                    'address' => ($operativeHeadquarter->address_text ? $operativeHeadquarter->address_text : ''),
                    'street_number' => '',
                ];
            } else {
                return null;
            }
        } elseif (!empty($this->sedeIndirizzo)) {
            return [
                'postal_code' => $this->sedeIndirizzo->postal_code,
                'region' => $this->sedeIndirizzo->region,
                'city' => $this->sedeIndirizzo->city,
                'address' => $this->sedeIndirizzo->address,
                'street_number' => $this->sedeIndirizzo->street_number
            ];
        } else {
            return null;
        }
    }

    /**
     * @param array $addressArray
     * @return string
     */
    private function getAddressForView($addressArray)
    {
        $headquarterAddress = "-";
        $addressFieldAsArray = $addressArray;
        if (!empty($addressFieldAsArray) && is_array($addressFieldAsArray)) {
            $headquarterAddress = $addressFieldAsArray['address'] . ', ' . $addressFieldAsArray['street_number'] . '<br />' .
                $addressFieldAsArray['postal_code'] . ' ' . $addressFieldAsArray['city'] . '<br />' .
                $addressFieldAsArray['region'];
        }
        return $headquarterAddress;
    }

    /**
     * @return string
     */
    public function getAddressFieldForView()
    {
        if ($this->organizzazioniModule->oldStyleAddressEnabled) {
            return (!is_null($this->operativeHeadquarter) ? $this->operativeHeadquarter->getOldStyleAddress() : '-');
        }
        return $this->getAddressForView($this->getAddressFieldAsArray());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLegalHeadquarter()
    {
        return $this->hasOne($this->organizzazioniModule->createModel('ProfiloSediLegal')->className(), ['profilo_id' => 'id'])
            ->andWhere(['profilo_sedi_type_id' => ProfiloSediTypes::TYPE_LEGAL_HEADQUARTER])
            ->andWhere(['active' => 1])
            ->andWhere(['is_main' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSedeLegaleIndirizzo()
    {
        if ($this->organizzazioniModule->oldStyleAddressEnabled) {
            return null;
        }
        return $this->hasOne($this->organizzazioniModule->createModel('OrganizationsPlaces')->className(), ['place_id' => 'address'])->via('legalHeadquarter');
    }

    /**
     * @return string
     */
    public function getAddressFieldSedeLegale()
    {
        if (!$this->organizzazioniModule->oldStyleAddressEnabled) {
            if (is_null($this->sedeLegaleIndirizzo)) {
                return '-';
            }

            return ($this->sedeLegaleIndirizzo->postal_code ? '(' . $this->sedeLegaleIndirizzo->postal_code . ')' : '') .
                ($this->sedeLegaleIndirizzo->region ? ' ' . $this->sedeLegaleIndirizzo->region : '') .
                ($this->sedeLegaleIndirizzo->city ? ' ' . $this->sedeLegaleIndirizzo->city : '') .
                ($this->sedeLegaleIndirizzo->address ? ' ' . $this->sedeLegaleIndirizzo->address : '') .
                ($this->sedeLegaleIndirizzo->street_number ? ' ' . $this->sedeLegaleIndirizzo->street_number : '');
        } else {
            return (!is_null($this->legalHeadquarter) ? $this->legalHeadquarter->getOldStyleAddress() : '-');
        }
    }

    /**
     * @return array
     */
    public function getAddressFieldSedeLegaleAsArray()
    {
        if ($this->organizzazioniModule->oldStyleAddressEnabled) {
            $legalHeadquarter = $this->legalHeadquarter;
            if (!is_null($legalHeadquarter)) {
                return [
                    'postal_code' => ($legalHeadquarter->cap_text ? $legalHeadquarter->cap_text : ''),
                    'region' => (!is_null($legalHeadquarter->province) && !is_null($legalHeadquarter->province->istatRegioni) ? $legalHeadquarter->province->istatRegioni->nome : ''),
                    'city' => (!is_null($legalHeadquarter->city) ? $legalHeadquarter->city->nome : ''),
                    'address' => ($legalHeadquarter->address_text ? $legalHeadquarter->address_text : ''),
                    'street_number' => '',
                ];
            } else {
                return null;
            }
        } elseif (!empty($this->sedeLegaleIndirizzo)) {
            return [
                'postal_code' => $this->sedeLegaleIndirizzo->postal_code,
                'region' => $this->sedeLegaleIndirizzo->region,
                'city' => $this->sedeLegaleIndirizzo->city,
                'address' => $this->sedeLegaleIndirizzo->address,
                'street_number' => $this->sedeLegaleIndirizzo->street_number
            ];
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getAddressFieldSedeLegaleForView()
    {
        if ($this->organizzazioniModule->oldStyleAddressEnabled) {
            return (!is_null($this->legalHeadquarter) ? $this->legalHeadquarter->getOldStyleAddress() : '-');
        }
        return $this->getAddressForView($this->getAddressFieldSedeLegaleAsArray());
    }

    /**
     * @return bool
     */
    public function isMunicipality()
    {
        return ($this->profilo_enti_type_id == ProfiloEntiType::TYPE_MUNICIPALITY);
    }

    /**
     * @return bool
     */
    public function isOtherEntity()
    {
        return ($this->profilo_enti_type_id == ProfiloEntiType::TYPE_OTHER_ENTITY);
    }

    /**
     * Return the recordset of all recipients relative to the networks
     * associated to the relative user (they may be different? check it!)
     * @param array $networkIds
     * @param array $usersId
     * @return array
     */
    public function getListOfRecipients($networkIds = [], $usersId = [])
    {
        $query = new Query();
        /** @var Profilo $modelProfilo */
        $modelProfilo = $this->organizzazioniModule->createModel('Profilo');
        /** @var ProfiloUserMm $modelProfiloUserMm */
        $modelProfiloUserMm = $this->organizzazioniModule->createModel('ProfiloUserMm');
        $profiloTable = $modelProfilo::tableName();
        $profiloUserMmTable = $modelProfiloUserMm::tableName();
        $query->select([
            "CONCAT('" . $profiloTable . "', '-', " . $profiloUserMmTable . ".profilo_id) AS objID",
            $profiloTable . '.id', $profiloTable . '.name', $profiloTable . '.deleted_at',
            $profiloUserMmTable . '.profilo_id', $profiloUserMmTable . '.profilo_id AS reference',
            $profiloUserMmTable . '.deleted_at'
        ])
            ->from(static::tableName())
            ->leftJoin($profiloUserMmTable, $profiloUserMmTable . '.profilo_id = ' . $profiloTable . '.id
        AND ' . $profiloUserMmTable . '.deleted_at IS NULL')
            ->where([$profiloTable . '.id' => $networkIds])
            ->andWhere([
                $profiloUserMmTable . '.user_id' => $usersId,
                $profiloTable . '.deleted_at' => null,
            ]);
        return $query->all();
    }
}
