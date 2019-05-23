<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\views\profilo
 * @category   CategoryName
 */

use lispa\amos\organizzazioni\widgets\UserNetworkWidget;

/**
 * @var yii\web\View $this
 * @var int $userId
 * @var bool $isUpdate
 */

?>

<?= UserNetworkWidget::widget([
    'userId' => $userId,
    'isUpdate' => $isUpdate
]); ?>
