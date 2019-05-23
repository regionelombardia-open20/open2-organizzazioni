<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\controllers
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\controllers;

use lispa\amos\dashboard\controllers\base\DashboardController;

/**
 * Class DefaultController
 * @package lispa\amos\organizzazioni\controllers
 */
class DefaultController extends DashboardController
{
    /**
     * @var string $layout Layout per la dashboard interna.
     */
    public $layout = "dashboard_interna";

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->setUpLayout();
    }

    /**
     * Lists all organizzazioni models.
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->redirect('/organizzazioni/profilo/index');
    }
}
