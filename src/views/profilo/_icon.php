<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni
 * @category   CategoryName
 */

use yii\helpers\Html;
use lispa\amos\core\icons\AmosIcons;
use lispa\amos\core\forms\ContextMenuWidget;
use lispa\amos\core\forms\CreatedUpdatedWidget;
use lispa\amos\organizzazioni\Module;
use lispa\amos\organizzazioni\assets\OrganizzazioniAsset;

$moduleL = \Yii::$app->getModule('layout');
if (!empty($moduleL)) {
    OrganizzazioniAsset::register($this);
}

?>

<div class="card-container organization-card-container col-xs-12 nop">
    <div class="col-xs-12 nop icon-header">
        <?= ContextMenuWidget::widget([
            'model' => $model,
            'actionModify' => '/organizzazioni/profilo/update?id=' . $model->id,
            'disableDelete' => true
        ]) ?>
        <?php
        if (!is_null($model->logoOrganization)) {
            $url = $model->logoOrganization->getUrl('original', [
                'class' => 'img-responsive'
            ]);
            echo Html::img($url,['alt' => $model->name, 'class' => 'img-responsive']);
        }
        else{
            echo AmosIcons::show('building',[],'dash');
        }
        ?>
    </div>
    <div class="col-xs-12 nop icon-body">
        <h3 class="title">
            <?= Html::a($model->name, '/organizzazioni/profilo/view?id=' . $model->id, ['title' => $model->name]); ?>
        </h3>
        <div class="">
            <!-- COSA SONO LE ICONE NEL MOKUP? -->
        </div>
    </div>
    <div class="col-xs-12 nop icon-footer">
        <?= CreatedUpdatedWidget::widget(['model' => $model, 'isTooltip' => true]) ?> <!-- BISOGNA VISUALIZZARE LO STATO -->
        <?= Html::a(Module::t('amosorganizzazioni','#icon_card_link') . AmosIcons::show('forward'),'#',['class' => 'icon-footer-link'])?>
    </div>
</div>
