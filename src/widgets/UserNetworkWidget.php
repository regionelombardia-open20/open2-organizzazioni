<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\organizzazioni\widgets
 * @category   CategoryName
 */

namespace open20\amos\organizzazioni\widgets;

use open20\amos\organizzazioni\Module;
use yii\base\Widget;
use Yii;
/**
 * Class UserNetworkWidget
 * @package open20\amos\organizzazioni\widgets
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
        /** @var Module $organizationsModule */
        $organizationsModule = Module::instance();
        if (!is_null($organizationsModule->enabled_widget_sedi)) {
            $sedi_enabled = $organizationsModule->enabled_widget_sedi;
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
