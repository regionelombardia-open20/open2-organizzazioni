<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\migrations
 * @category   CategoryName
 */

use lispa\amos\core\migration\libs\common\MigrationCommon;
use lispa\amos\organizzazioni\models\ProfiloUserMm;
use lispa\amos\organizzazioni\Module;
use yii\db\Migration;

/**
 * Class m190123_114146_populate_profilo_user_mm_status_with_active
 */
class m190123_114146_populate_profilo_user_mm_status_with_active extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        try {
            /** @var ProfiloUserMm $profiloUserMm */
            $profiloUserMm = Module::instance()->createModel('ProfiloUserMm');
            $this->update($profiloUserMm::tableName(), ['status' => ProfiloUserMm::STATUS_ACTIVE]);
        } catch (\Exception $exception) {
            MigrationCommon::printConsoleMessage($exception->getMessage());
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        try {
            /** @var ProfiloUserMm $profiloUserMm */
            $profiloUserMm = Module::instance()->createModel('ProfiloUserMm');
            $this->update($profiloUserMm::tableName(), ['status' => null]);
        } catch (\Exception $exception) {
            MigrationCommon::printConsoleMessage($exception->getMessage());
            return false;
        }
        return true;
    }
}
