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

use yii\helpers\ArrayHelper;

/**
 * Class ProfiloSediOperative
 * @package lispa\amos\organizzazioni\models
 */
class ProfiloSediOperative extends ProfiloSedi
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->isNewRecord) {
            $this->profilo_sedi_type_id = ProfiloSediTypes::TYPE_OPERATIVE_HEADQUARTER;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),
            [
                [[
                    'address',
                    'phone',
                    'email',
                ], 'required'],
            ]);
    }
}
