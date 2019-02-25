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
 * @var lispa\amos\organizzazioni\models\ProfiloSediLegal $mainSedeLegale
 * @var lispa\amos\organizzazioni\models\ProfiloSediOperative $mainSedeOperativa
 */

$this->title = Module::t('amosorganizzazioni', 'Update organization');
$this->params['breadcrumbs'][] = ['label' => 'Organizzazioni', 'url' => ['/organizzazioni']];
$this->params['breadcrumbs'][] = ['label' => 'Organizzazioni', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => strip_tags($model), 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Aggiorna';

?>

<div class="are-profilo-update">
    <?php echo $this->render('_form', [
        'model' => $model,
        'mainSedeLegale' => $mainSedeLegale,
        'mainSedeOperativa' => $mainSedeOperativa,
        'fid' => NULL,
        'dataField' => NULL,
        'dataEntity' => NULL,
    ]) ?>
</div>
