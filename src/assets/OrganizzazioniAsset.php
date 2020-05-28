<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\organizzazioni
 * @category   CategoryName
 */

namespace open20\amos\organizzazioni\assets;

use yii\web\AssetBundle;
use open20\amos\core\widget\WidgetAbstract;


class OrganizzazioniAsset extends AssetBundle
{
    public $sourcePath = '@vendor/open20/amos-organizzazioni/src/assets/web';

    public $js = [
    ];
    public $css = [
        'less/organizzazioni.less',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset'
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if(!empty(\Yii::$app->params['dashboardEngine']) && \Yii::$app->params['dashboardEngine'] == WidgetAbstract::ENGINE_ROWS){
            $this->css = ['less/organizzazioniFullsize.less'];
        }
        parent::init();
    }

}