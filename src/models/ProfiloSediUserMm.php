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

/**
 * Class ProfiloSediUserMm
 * This is the model class for table "profilo_sedi_user_mm".
 * @package lispa\amos\organizzazioni\models
 */
class ProfiloSediUserMm extends \lispa\amos\organizzazioni\models\base\ProfiloSediUserMm
{
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_WAITING_REQUEST_CONFIRM = 'WAITING_REQUEST_CONFIRM';

    /**
     * Return all statuses of the headquarter users.
     * @return array
     */
    public static function getUserStates()
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_REJECTED,
            self::STATUS_WAITING_REQUEST_CONFIRM
        ];
    }
}
