<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\views\profilo\email
 * @category   CategoryName
 */

use lispa\amos\organizzazioni\Module;

/**
 * @var \yii\web\View $this
 * @var \lispa\amos\organizzazioni\utility\EmailUtility $util
 */

?>
<?= Module::t('amosorganizzazioni', "Invitation to") . " " . $util->model->name . " " . Module::t('amosorganizzazioni', "has been rejected by") . " " . $util->userName; ?>
