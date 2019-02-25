<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\assets;

use yii\web\AssetBundle;

class OrganizzazioniAsset extends AssetBundle
{
    public $sourcePath = '@vendor/lispa/amos-organizzazioni/src/assets/web';

    public $js = [
    ];
    public $css = [
        'less/organizzazioni.less',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\jui\JuiAsset'
    ];

}