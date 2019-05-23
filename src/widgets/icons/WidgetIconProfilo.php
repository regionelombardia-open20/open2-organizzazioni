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

use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\widget\WidgetAbstract;
use lispa\amos\core\widget\WidgetIcon;
use lispa\amos\organizzazioni\Module;
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

        $paramsClassSpan = [
            'bk-backgroundIcon',
            'color-lightBase'
        ];

        $this->setLabel(Module::tHtml('amosorganizzazioni', '#widget_icon_profilo_label'));
        $this->setDescription(Module::t('amosorganizzazioni', '#widget_icon_profilo_description'));

        if (!empty(\Yii::$app->params['dashboardEngine']) && \Yii::$app->params['dashboardEngine'] == WidgetAbstract::ENGINE_ROWS) {
            $this->setIconFramework(AmosIcons::IC);
            $this->setIcon('organizzazioni');
            $paramsClassSpan = [];
        } else {
            $this->setIcon('building-o');
        }

        if (!\Yii::$app->user->isGuest) {
            $this->setUrl(['/organizzazioni/profilo/index']);
        }

        $this->setCode('PROFILO');
        $this->setModuleName('organizzazioni');
        $this->setNamespace(__CLASS__);

        $this->setClassSpan(
            ArrayHelper::merge(
                $this->getClassSpan(),
                $paramsClassSpan
            )
        );
    }
}
