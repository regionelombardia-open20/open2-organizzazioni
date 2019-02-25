<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\views\profilo-sedi
 * @category   CategoryName
 */

use lispa\amos\organizzazioni\Module;

/**
 * @var yii\web\View $this
 * @var lispa\amos\organizzazioni\models\ProfiloSedi $model
 */

$this->title = Module::t('amosorganizzazioni', 'Create headquarters');
$this->params['breadcrumbs'][] = ['label' => Module::t('amosorganizzazioni', 'Profilo Sedi'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="profilo-sedi-create">
    <?= $this->render('_form', [
        'model' => $model
    ]) ?>
</div>
