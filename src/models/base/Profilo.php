<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\models\base
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\models\base;

use lispa\amos\admin\AmosAdmin;
use lispa\amos\core\helpers\Html;
use lispa\amos\core\record\NetworkModel;
use lispa\amos\organizzazioni\Module;
use yii\helpers\ArrayHelper;

/**
 * Class Profilo
 *
 * This is the base-model class for table "profilo".
 *
 * @property integer $id
 * @property string $name
 * @property string $partita_iva
 * @property string $codice_fiscale
 * @property string $istat_code
 * @property string $presentazione_della_organizzaz
 * @property string $principali_ambiti_di_attivita_
 * @property string $ambiti_tecnologici_su_cui_siet
 * @property string $tipologia_di_organizzazione
 * @property string $forma_legale
 * @property string $sito_web
 * @property string $facebook
 * @property string $twitter
 * @property string $linkedin
 * @property string $google
 * @property string $indirizzo
 * @property string $telefono
 * @property string $fax
 * @property string $email
 * @property string $pec
 * @property string $la_sede_legale_e_la_stessa_del
 * @property string $sede_legale_indirizzo
 * @property string $sede_legale_telefono
 * @property string $sede_legale_fax
 * @property string $sede_legale_email
 * @property string $sede_legale_pec
 * @property string $responsabile
 * @property string $rappresentante_legale
 * @property string $rappresentante_legale_text
 * @property string $referente_operativo
 * @property string $parent_id
 * @property string $profilo_enti_type_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 *
 * @property \lispa\amos\admin\models\UserProfile $rappresentanteLegale
 * @property \lispa\amos\admin\models\UserProfile $referenteOperativo
 * @property \lispa\amos\organizzazioni\models\ProfiloTypesPmi $tipologiaDiOrganizzazione
 * @property \lispa\amos\organizzazioni\models\ProfiloLegalForm $formaLegale
 * @property \lispa\amos\organizzazioni\models\Profilo $parent
 * @property \lispa\amos\organizzazioni\models\Profilo $children
 * @property \lispa\amos\organizzazioni\models\ProfiloSedi[] $profiloSedi
 * @property \lispa\amos\organizzazioni\models\ProfiloSedi[] $otherHeadquarters
 * @property \lispa\amos\organizzazioni\models\ProfiloSedi[] $otherActiveHeadquarters
 * @property \lispa\amos\organizzazioni\models\ProfiloEntiType $profiloEntiType
 * @property \lispa\amos\organizzazioni\models\ProfiloUserMm[] $profiloUserMms
 * @property \lispa\amos\core\user\User[] $profiloUsers
 *
 * @package lispa\amos\organizzazioni\models\base
 */
abstract class Profilo extends NetworkModel
{
    /**
     * @var Module $organizzazioniModule
     */
    protected $organizzazioniModule;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'profilo';
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->organizzazioniModule = Module::instance();
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $organizzazioniModule = Module::instance();
        /** @var \lispa\amos\organizzazioni\models\ProfiloEntiType $profiloEntiTypeModel */
        $profiloEntiTypeModel = $organizzazioniModule->model('ProfiloEntiType');
        $typeMunicipalityId = $profiloEntiTypeModel::TYPE_MUNICIPALITY;

        return [
            [[
                'name',
                'profilo_enti_type_id'
            ], 'required'],
            [['istat_code'], 'required', 'when' => function ($model) use ($typeMunicipalityId) {
                /** @var \lispa\amos\organizzazioni\models\Profilo $model */
                return ($model->profilo_enti_type_id == $typeMunicipalityId);
            }, 'whenClient' => "function (attribute, value) {
                return $('#" . Html::getInputId($this, 'profilo_enti_type_id') . "').val() == " . $typeMunicipalityId . ";
            }"],
            [['created_at', 'updated_at', 'deleted_at', 'rappresentante_legale'], 'safe'],
            [[
                'created_by',
                'updated_by',
                'deleted_by',
                'parent_id',
                'profilo_enti_type_id',
                'referente_operativo',
                'rappresentante_legale',
            ], 'integer'],
            [[
                'presentazione_della_organizzaz',
                'telefono',
                'fax',
                'sede_legale_telefono',
                'sede_legale_fax'
            ], 'string'],
            [[
                'name',
                'partita_iva',
                'codice_fiscale',
                'tipologia_di_organizzazione',
                'forma_legale',
                'sito_web',
                'facebook',
                'twitter',
                'linkedin',
                'google',
                'indirizzo',
                'email',
                'pec',
                'la_sede_legale_e_la_stessa_del',
                'sede_legale_indirizzo',
                'sede_legale_email',
                'sede_legale_pec',
                'responsabile',
                'rappresentante_legale_text'
            ], 'string', 'max' => 255],
            [['istat_code'], 'string', 'max' => 10],
            [['logoOrganization'], 'file', 'extensions' => 'jpeg, jpg, png, gif', 'maxFiles' => 1],
            [['allegati'], 'file', 'maxFiles' => 0]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Module::t('amosorganizzazioni', 'ID'),
            'name' => Module::t('amosorganizzazioni', 'Denominazione'),
            'partita_iva' => Module::t('amosorganizzazioni', 'Partita Iva'),
            'codice_fiscale' => Module::t('amosorganizzazioni', 'Codice Fiscale'),
            'istat_code' => Module::t('amosorganizzazioni', 'Istat Code'),
            'presentazione_della_organizzaz' => Module::t('amosorganizzazioni', 'Presentazione'),
            'tipologia_di_organizzazione' => Module::t('amosorganizzazioni', 'Tipologia di organizzazione'),
            'forma_legale' => Module::t('amosorganizzazioni', 'Forma legale'),
            'sito_web' => Module::t('amosorganizzazioni', 'Sito web'),
            'facebook' => Module::t('amosorganizzazioni', 'Facebook'),
            'twitter' => Module::t('amosorganizzazioni', 'Twitter'),
            'linkedin' => Module::t('amosorganizzazioni', 'Linkedin'),
            'google' => Module::t('amosorganizzazioni', 'Google+'),
            'indirizzo' => Module::t('amosorganizzazioni', 'Indirizzo'),
            'addressField' => Module::t('amosorganizzazioni', 'Indirizzo'),
            'telefono' => Module::t('amosorganizzazioni', 'Telefono'),
            'fax' => Module::t('amosorganizzazioni', 'Fax'),
            'email' => Module::t('amosorganizzazioni', 'Email'),
            'pec' => Module::t('amosorganizzazioni', 'PEC'),
            'la_sede_legale_e_la_stessa_del' => Module::t('amosorganizzazioni', 'La sede legale è la stessa della sede operativa'),
            'sede_legale_indirizzo' => Module::t('amosorganizzazioni', 'Sede legale indirizzo'),
            'sede_legale_telefono' => Module::t('amosorganizzazioni', 'Sede legale telefono'),
            'sede_legale_fax' => Module::t('amosorganizzazioni', 'Sede legale fax'),
            'sede_legale_email' => Module::t('amosorganizzazioni', 'Sede legale email'),
            'sede_legale_pec' => Module::t('amosorganizzazioni', 'Sede legale PEC'),
            'responsabile' => Module::t('amosorganizzazioni', 'Responsabile'),
            'rappresentante_legale' => Module::t('amosorganizzazioni', 'Rappresentante legale'),
            'rappresentante_legale_text' => Module::t('amosorganizzazioni', 'Rappresentante legale'),
            'referente_operativo' => Module::t('amosorganizzazioni', 'Referente operativo'),
            'parent_id' => Module::t('amosorganizzazioni', 'Membership organization'),
            'profilo_enti_type_id' => Module::t('amosorganizzazioni', 'Tipologia di ente'),
            'created_at' => Module::t('amosorganizzazioni', 'Creato il'),
            'updated_at' => Module::t('amosorganizzazioni', 'Aggiornato il'),
            'deleted_at' => Module::t('amosorganizzazioni', 'Cancellato il'),
            'created_by' => Module::t('amosorganizzazioni', 'Creato da'),
            'updated_by' => Module::t('amosorganizzazioni', 'Aggiornato da'),
            'deleted_by' => Module::t('amosorganizzazioni', 'Cancellato da'),
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRappresentanteLegale()
    {
        return $this->hasOne(AmosAdmin::instance()->createModel('UserProfile')->className(), ['user_id' => 'rappresentante_legale']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getReferenteOperativo()
    {
        return $this->hasOne(AmosAdmin::instance()->createModel('UserProfile')->className(), ['user_id' => 'referente_operativo']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTipologiaDiOrganizzazione()
    {
        return $this->hasOne(Module::instance()->createModel('ProfiloTypesPmi')->className(), ['id' => 'tipologia_di_organizzazione']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFormaLegale()
    {
        return $this->hasOne(Module::instance()->createModel('ProfiloLegalForm')->className(), ['id' => 'forma_legale']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Module::instance()->createModel('Profilo')->className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(Module::instance()->createModel('Profilo')->className(), ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfiloSedi()
    {
        return $this->hasMany(Module::instance()->createModel('ProfiloSedi')->className(), ['profilo_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOtherHeadquarters()
    {
        return $this->getProfiloSedi()->andWhere(['is_main' => 0]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOtherActiveHeadquarters()
    {
        return $this->getOtherHeadquarters()->andWhere(['active' => 1]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfiloEntiType()
    {
        return $this->hasOne(Module::instance()->createModel('ProfiloEntiType')->className(), ['id' => 'profilo_enti_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfiloUserMms()
    {
        return $this->hasMany(Module::instance()->createModel('ProfiloUserMm')->className(), ['profilo_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfiloUsers()
    {
        return $this->hasMany(\lispa\amos\core\user\User::className(), ['id' => 'user_id'])->via('profiloUserMms');
    }
}
