<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\organizzazioni\models
 * @category   CategoryName
 */

namespace open20\amos\organizzazioni\models;

/**
 * Class ProfiloSediOperative
 * @package open20\amos\organizzazioni\models
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
}
