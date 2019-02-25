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

echo UserNetworkWidget::widget([
    'userId' => $userId,
    'isUpdate' => $isUpdate
]);

?>
