<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\migrations
 * @category   CategoryName
 */

use lispa\amos\core\migration\AmosMigrationPermissions;
use lispa\amos\organizzazioni\rules\ConfirmUserRequestRule;
use yii\rbac\Permission;

/**
 * Class m190211_163217_fix_organizzazioni_confirm_user_permission
 */
class m190211_163217_fix_organizzazioni_confirm_user_permission extends AmosMigrationPermissions
{
    /**
     * @inheritdoc
     */
    protected function setRBACConfigurations()
    {
        return [
            [
                'name' => 'CONFIRM_ORGANIZZAZIONI_OR_SEDI_USER_REQUEST',
                'type' => Permission::TYPE_PERMISSION,
                'description' => 'Permesso per associare un utente a una sede di una organizzazione nel profilo utente',
                'ruleName' => ConfirmUserRequestRule::className(),
                'parent' => ['VALIDATED_BASIC_USER']
            ]
        ];
    }
}
