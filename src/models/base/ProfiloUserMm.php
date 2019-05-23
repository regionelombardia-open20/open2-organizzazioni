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
use yii\helpers\ArrayHelper;

/**
 * Class ProfiloUserMm
 *
 * This is the base-model class for table "profilo_user_mm".
 *
 * @property integer $id
 * @property integer $profilo_id
 * @property integer $user_id
 * @property string $status
 * @property string $role
 * @property integer $user_profile_area_id
 * @property integer $user_profile_role_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $deleted_by
 *
 * @property \lispa\amos\organizzazioni\models\Profilo $profilo
 * @property \lispa\amos\core\user\User $user
 * @property \lispa\amos\admin\models\UserProfileArea $userProfileArea
 * @property \lispa\amos\admin\models\UserProfileRole $userProfileRole
 *
 * @package lispa\amos\organizzazioni\models\base
 */
abstract class ProfiloUserMm extends Record
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'profilo_user_mm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['profilo_id', 'user_id', 'user_profile_area_id', 'user_profile_role_id', 'created_by', 'updated_by', 'deleted_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['status', 'role'], 'string', 'max' => 255],
            [['profilo_id'], 'exist', 'skipOnError' => true, 'targetClass' => Module::instance()->createModel('Profilo')->className(), 'targetAttribute' => ['profilo_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \lispa\amos\core\user\User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Module::t('amosorganizzazioni', 'ID'),
            'profilo_id' => Module::t('amosorganizzazioni', 'Organizzazione'),
            'user_id' => Module::t('amosorganizzazioni', 'Utente'),
            'status' => Module::t('amosorganizzazioni', 'Stato'),
            'role' => Module::t('amosorganizzazioni', 'Ruolo'),
            'user_profile_area_id' => Module::t('amosorganizzazioni', 'Area'),
            'user_profile_role_id' => Module::t('amosorganizzazioni', 'Ruolo'),
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
    public function getProfilo()
    {
        return $this->hasOne(Module::instance()->createModel('Profilo')->className(), ['id' => 'profilo_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\lispa\amos\core\user\User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfileArea()
    {
        return $this->hasOne(\lispa\amos\admin\models\UserProfileArea::className(), ['id' => 'user_profile_area_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfileRole()
    {
        return $this->hasOne(\lispa\amos\admin\models\UserProfileRole::className(), ['id' => 'user_profile_role_id']);
    }
}
