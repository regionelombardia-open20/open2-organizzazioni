<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\widgets
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\widgets;

use lispa\amos\organizzazioni\Module;
use yii\base\Widget;
use Yii;
/**
 * Class UserNetworkWidget
 * @package lispa\amos\organizzazioni\widgets
 */
class UserNetworkWidget extends Widget
{
    /**
     * @var int $userId
     */
    public $userId = null;

    /**
     * @var bool|false true if we are in edit mode, false if in view mode or otherwise
     */
    public $isUpdate = false;

    /**
     * @var string $gridId
     */
    public $gridId = 'user-organizzazioni-grid';

    /**
     * @var string $gridSediId
     */
    public $gridSediId = 'user-sedi-grid';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (is_null($this->userId)) {
            throw new \Exception(Module::t('amosorganizzazioni', '#Missing_user_id'));
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $post = \Yii::$app->request->post();
        $organizzazioniPostName = UserNetworkWidgetOrganizzazioni::getSearchPostName();
        $sediPostName = UserNetworkWidgetSedi::getSearchPostName();
        $organizzazioniWidget = '';
        $sediWidget = '';
        $sedi_enabled = true;
        if (!$post || ($post && isset($post[$organizzazioniPostName]))) {
            $organizzazioniWidget = UserNetworkWidgetOrganizzazioni::widget([
                'userId' => $this->userId,
                'isUpdate' => $this->isUpdate,
                'gridId' => $this->gridId,
            ]);
        }
        if (isset(Yii::$app->getModule('organizzazioni')->enabled_widget_sedi)) {
            $sedi_enabled = Yii::$app->getModule('organizzazioni')->enabled_widget_sedi;
        }

        if ($sedi_enabled && (!$post || ($post && isset($post[$sediPostName])))) {
            $sediWidget = UserNetworkWidgetSedi::widget([
                'userId' => $this->userId,
                'isUpdate' => $this->isUpdate,
                'gridId' => $this->gridSediId,
            ]);
        }
        return $organizzazioniWidget . $sediWidget;
    }
}
