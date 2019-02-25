<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni
 * @category   CategoryName
 */

use yii\db\Migration;
use lispa\amos\dashboard\models\AmosWidgets;

class m171208_123616_create_widgets extends \lispa\amos\core\migration\AmosMigrationWidgets
{
    const MODULE_NAME = 'organizzazioni';

    /**
     * @inheritdoc
     */
    protected function initWidgetsConfs()
    {
        $this->widgets = [
            [
                'classname' => \lispa\amos\organizzazioni\widgets\icons\WidgetIconProfilo::className(),
                'type' => AmosWidgets::TYPE_ICON,
                'module' => self::MODULE_NAME,
                'status' => AmosWidgets::STATUS_ENABLED
            ]
        ];
    }
}
