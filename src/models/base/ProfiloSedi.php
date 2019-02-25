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

use lispa\amos\core\record\Record;
use lispa\amos\organizzazioni\Module;

/**
 * Class ProfiloSedi
 *
 * This is the base-model class for table "profilo_sedi".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property string $address
 * @property integer $is_main
 * @property integer $active
 * @property string $website
 * @property string $phone
 * @property string $fax
 * @property string $email
 * @property string $pec
 * @property integer $profilo_id
 * @property integer $profilo_sedi_type_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 *
 * @property \lispa\amos\organizzazioni\models\Profilo $profilo
 * @property \lispa\amos\organizzazioni\models\ProfiloSediTypes $profiloSediType
 *
 * @package lispa\amos\organizzazioni\models\base
 */
abstract class ProfiloSedi extends Record
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'profilo_sedi';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'name',
                'profilo_id',
                'profilo_sedi_type_id',
            ], 'required'],
            [['description'], 'string'],
            [[
                'is_main',
                'active',
                'profilo_id',
                'profilo_sedi_type_id',
                'created_by',
                'updated_by',
                'deleted_by'
            ], 'integer'],
            [[
                'created_at',
                'updated_at',
                'deleted_at'
            ], 'safe'],
            [['name'], 'string', 'max' => 100],
            [['phone', 'fax'], 'string', 'max' => 50],
            [['address', 'website', 'email', 'pec'], 'string', 'max' => 255],
            [['email', 'pec'], 'email'],
            [['profilo_id'], 'exist', 'skipOnError' => true, 'targetClass' => \lispa\amos\organizzazioni\models\Profilo::className(), 'targetAttribute' => ['profilo_id' => 'id']],
            [['profilo_sedi_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => \lispa\amos\organizzazioni\models\ProfiloSediTypes::className(), 'targetAttribute' => ['profilo_sedi_type_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Module::t('amosorganizzazioni', 'ID'),
            'name' => Module::t('amosorganizzazioni', 'Name'),
            'description' => Module::t('amosorganizzazioni', 'Description'),
            'address' => Module::t('amosorganizzazioni', 'Address'),
            'is_main' => Module::t('amosorganizzazioni', 'Is Main'),
            'active' => Module::t('amosorganizzazioni', 'Active'),
            'website' => Module::t('amosorganizzazioni', 'Web Site'),
            'phone' => Module::t('amosorganizzazioni', 'Phone'),
            'fax' => Module::t('amosorganizzazioni', 'Fax'),
            'email' => Module::t('amosorganizzazioni', 'Email'),
            'pec' => Module::t('amosorganizzazioni', 'PEC'),
            'profilo_id' => Module::t('amosorganizzazioni', 'Profilo ID'),
            'profilo_sedi_type_id' => Module::t('amosorganizzazioni', 'Profilo Sedi Type ID'),
            'created_at' => Module::t('amosorganizzazioni', 'Created at'),
            'updated_at' => Module::t('amosorganizzazioni', 'Updated at'),
            'deleted_at' => Module::t('amosorganizzazioni', 'Deleted at'),
            'created_by' => Module::t('amosorganizzazioni', 'Created by'),
            'updated_by' => Module::t('amosorganizzazioni', 'Updated by'),
            'deleted_by' => Module::t('amosorganizzazioni', 'Deleted by'),

            'profilo' => Module::t('amosorganizzazioni', '#organization'),
            'profiloSediType' => Module::t('amosorganizzazioni', 'Headquarter type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfilo()
    {
        return $this->hasOne(\lispa\amos\organizzazioni\models\Profilo::className(), ['id' => 'profilo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfiloSediType()
    {
        return $this->hasOne(\lispa\amos\organizzazioni\models\ProfiloSediTypes::className(), ['id' => 'profilo_sedi_type_id']);
    }
}
