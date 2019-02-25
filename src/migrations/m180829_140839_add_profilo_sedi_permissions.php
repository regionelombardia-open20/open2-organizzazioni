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
use yii\helpers\ArrayHelper;
use yii\rbac\Permission;


/**
 * Class m180829_140839_add_profilo_sedi_permissions
 */
class m180829_140839_add_profilo_sedi_permissions extends AmosMigrationPermissions
{
    /**
     * @inheritdoc
     */
    protected function setRBACConfigurations()
    {
        return ArrayHelper::merge(
            $this->setPluginRoles(),
            $this->setModelPermissions()
        );
    }

    private function setPluginRoles()
    {
        return [
            [
                'name' => 'AMMINISTRATORE_ORGANIZZAZIONI',
                'type' => Permission::TYPE_ROLE,
                'description' => 'Administrator role for the Organizzazioni plugin',
                'parent' => ['ADMIN'],
                'children' => [
                    'PROFILO_CREATE',
                    'PROFILO_READ',
                    'PROFILO_UPDATE',
                    'PROFILO_DELETE'
                ]
            ]
        ];
    }

    private function setModelPermissions()
    {
        return [
            [
                'name' => 'PROFILOSEDI_CREATE',
                'type' => Permission::TYPE_PERMISSION,
                'description' => 'Permesso di CREATE sul model ProfiloSedi',
                'parent' => ['AMMINISTRATORE_ORGANIZZAZIONI']
            ],
            [
                'name' => 'PROFILOSEDI_READ',
                'type' => Permission::TYPE_PERMISSION,
                'description' => 'Permesso di READ sul model ProfiloSedi',
                'parent' => ['AMMINISTRATORE_ORGANIZZAZIONI']
            ],
            [
                'name' => 'PROFILOSEDI_UPDATE',
                'type' => Permission::TYPE_PERMISSION,
                'description' => 'Permesso di UPDATE sul model ProfiloSedi',
                'parent' => ['AMMINISTRATORE_ORGANIZZAZIONI']
            ],
            [
                'name' => 'PROFILOSEDI_DELETE',
                'type' => Permission::TYPE_PERMISSION,
                'description' => 'Permesso di DELETE sul model ProfiloSedi',
                'parent' => ['AMMINISTRATORE_ORGANIZZAZIONI']
            ],

        ];
    }
}
