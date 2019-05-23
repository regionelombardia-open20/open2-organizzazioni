<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\views\profilo
 * @category   CategoryName
 */

use lispa\amos\organizzazioni\Module;

/**
 * @var yii\web\View $this
 * @var lispa\amos\organizzazioni\models\Profilo $model
 * @var lispa\amos\organizzazioni\models\ProfiloSediLegal $mainLegalHeadquarter
 * @var lispa\amos\organizzazioni\models\ProfiloSediOperative $mainOperativeHeadquarter
 */

$this->title = Module::t('amosorganizzazioni', 'Update organization');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="are-profilo-update">
    <?= $this->render('_form', [
        'model' => $model,
        'mainLegalHeadquarter' => $mainLegalHeadquarter,
        'mainOperativeHeadquarter' => $mainOperativeHeadquarter,
        'fid' => NULL,
        'dataField' => NULL,
        'dataEntity' => NULL,
    ]) ?>
</div>
