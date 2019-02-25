<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use lispa\amos\dashboard\controllers\base\DashboardController;

class DefaultController extends DashboardController
{
   /**
     * @var string $layout Layout per la dashboard interna.
     */
    public $layout = "@vendor/lispa/amos-core/views/layouts/dashboard_interna";

    /**
     * Lists all organizzazioni models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect('/organizzazioni/profilo');
    }
}

