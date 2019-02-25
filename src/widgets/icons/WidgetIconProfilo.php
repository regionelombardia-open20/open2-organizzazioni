<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\widgets\icons
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\widgets\icons;

use lispa\amos\core\widget\WidgetIcon;
use lispa\amos\organizzazioni\Module;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class WidgetIconProfilo
 * @package lispa\amos\organizzazioni\widgets\icons
 */
class WidgetIconProfilo extends WidgetIcon
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->setLabel(Module::t('amosorganizzazioni', '#widget_icon_profilo_label'));
        $this->setDescription(Module::t('amosorganizzazioni', '#widget_icon_profilo_description'));

        $this->setIcon('linentita');

        if (!Yii::$app->user->isGuest) {
            $this->setUrl(['/organizzazioni/profilo/index']);
        }

        $this->setCode('PROFILO');
        $this->setModuleName('organizzazioni');
        $this->setNamespace(__CLASS__);

        $this->setClassSpan(ArrayHelper::merge($this->getClassSpan(), [
            'bk-backgroundIcon',
            'color-lightBase'
        ]));
    }
}
