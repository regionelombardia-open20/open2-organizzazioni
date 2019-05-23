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
 * Class ProfiloUserMm
 * This is the model class for table "profilo_user_mm".
 * @package lispa\amos\organizzazioni\models
 */
class ProfiloUserMm extends \lispa\amos\organizzazioni\models\base\ProfiloUserMm
{
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_REJECTED = 'REJECTED';
    const STATUS_WAITING_REQUEST_CONFIRM = 'WAITING_REQUEST_CONFIRM';

    /**
     * Return all statuses of the organization users.
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
