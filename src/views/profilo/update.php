<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\organizzazioni\views\profilo
 * @category   CategoryName
 */

use open20\amos\organizzazioni\Module;

/**
 * @var yii\web\View $this
 * @var open20\amos\organizzazioni\models\Profilo $model
 * @var open20\amos\organizzazioni\models\ProfiloSediLegal $mainLegalHeadquarter
 * @var open20\amos\organizzazioni\models\ProfiloSediOperative $mainOperativeHeadquarter
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
        'moduleCwh' => $moduleCwh,
        'scope' => $scope
    ]) ?>
</div>
