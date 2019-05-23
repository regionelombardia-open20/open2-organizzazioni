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
use lispa\amos\organizzazioni\models\ProfiloSediUserMm;
use lispa\amos\organizzazioni\Module;
use yii\db\Migration;

/**
 * Class m190201_160523_populate_profilo_sedi_user_mm_status_with_active
 */
class m190201_160523_populate_profilo_sedi_user_mm_status_with_active extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        try {
            /** @var ProfiloSediUserMm $profiloUserMm */
            $profiloUserMm = Module::instance()->createModel('ProfiloSediUserMm');
            $this->update($profiloUserMm::tableName(), ['status' => ProfiloSediUserMm::STATUS_ACTIVE]);
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
            /** @var ProfiloSediUserMm $profiloUserMm */
            $profiloUserMm = Module::instance()->createModel('ProfiloSediUserMm');
            $this->update($profiloUserMm::tableName(), ['status' => null]);
        } catch (\Exception $exception) {
            MigrationCommon::printConsoleMessage($exception->getMessage());
            return false;
        }
        return true;
    }
}
