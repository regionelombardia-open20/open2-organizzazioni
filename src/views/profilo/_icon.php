<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\organizzazioni
 * @category   CategoryName
 */

use open20\amos\core\forms\ContextMenuWidget;
use open20\amos\core\forms\CreatedUpdatedWidget;
use open20\amos\core\icons\AmosIcons;
use open20\amos\organizzazioni\assets\OrganizzazioniAsset;
use open20\amos\organizzazioni\Module;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var open20\amos\organizzazioni\models\Profilo $model
 */

$moduleL = \Yii::$app->getModule('layout');
if (!empty($moduleL)) {
    OrganizzazioniAsset::register($this);
}

?>

<div class="card-container organization-card-container col-xs-12 nop">
    <div class="col-xs-12 nop icon-header">
        <?= ContextMenuWidget::widget([
            'model' => $model,
            'actionModify' => $model->getFullUpdateUrl(),
            'actionDelete' => $model->getFullDeleteUrl()
        ]) ?>
        <?php
        if (!is_null($model->logoOrganization)) {
            $url = $model->logoOrganization->getUrl('original', [
                'class' => 'img-responsive'
            ]);
            echo Html::img($url, ['alt' => $model->name, 'class' => 'img-responsive']);
        } else {
            echo AmosIcons::show('building', [], 'dash');
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
        <?= Html::a(Module::t('amosorganizzazioni', '#icon_card_link') . AmosIcons::show('forward'), '#', ['class' => 'icon-footer-link']) ?>
    </div>
</div>
