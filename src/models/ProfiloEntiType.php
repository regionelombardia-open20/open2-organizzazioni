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

use lispa\amos\organizzazioni\Module;

/**
 * Class ProfiloEntiType
 * This is the model class for table "profilo_enti_type".
 *
 * @property \lispa\amos\organizzazioni\models\Profilo[] $profili
 *
 * @package lispa\amos\organizzazioni\models
 */
class ProfiloEntiType extends \lispa\amos\organizzazioni\models\base\ProfiloEntiType
{
    const TYPE_MUNICIPALITY = 1;
    const TYPE_OTHER_ENTITY = 2;

    /**
     * @inheritdoc
     */
    public function representingColumn()
    {
        return [
            'name'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMunicipalities()
    {
        $model = Module::instance()->createModel('ProfiloEntiType');
        return $this->getProfili()->andWhere([$model::tableName() . '.id' => ProfiloEntiType::TYPE_MUNICIPALITY]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOtherEntities()
    {
        $model = Module::instance()->createModel('ProfiloEntiType');
        return $this->getProfili()->andWhere([$model::tableName() . '.id' => ProfiloEntiType::TYPE_OTHER_ENTITY]);
    }
}
