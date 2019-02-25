<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\controllers\api;

/**
 * This is the class for REST controller "ProfiloController".
 */
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

class ProfiloController extends \yii\rest\ActiveController
{
	public $modelClass = 'lispa\amos\organizzazioni\models\Profilo';
}
