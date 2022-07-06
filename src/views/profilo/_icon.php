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
use open20\amos\core\icons\AmosIcons;
use open20\amos\organizzazioni\Module;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var open20\amos\organizzazioni\models\Profilo $model
 * @var string $altText
 */

/** @var Module $organizzazioniModule */
$organizzazioniModule = Yii::$app->getModule(Module::getModuleName());

$imgTitle = $altText . ' ' . $model->name;

?>

<div class="organizzazioni-wrapper mb-0 ">
    <div class="organizzazioni-container row flex-sm-nowrap h-100">
        <div class="organizzazioni-containter-img col-xs-12 col-sm-4 nop">
            <?= ContextMenuWidget::widget([
                'model' => $model,
                'actionModify' => $model->getFullUpdateUrl(),
                'actionDelete' => $model->getFullDeleteUrl()
            ]); ?>
            <?php
            if (!is_null($model->logoOrganization)) {
                $url = $model->logoOrganization->getUrl('original', [
                    'class' => 'img-responsive'
                ]);
                $contentImage = Html::img($url, ['alt' => $imgTitle, 'class' => 'img-responsive']);
            } else {
                $contentImage = AmosIcons::show('building', ['class' => 'icona-organizzazioni h-100'], 'dash');
            }
            ?>
            <?= Html::a(
                $contentImage,
                (!empty($model->community_id) && $organizzazioniModule->directAccessToCommunityOrganization) ? ['/community/join/open-join', 'id' => $model->community_id] : $model->getFullViewUrl(),
                [
                    'class' => 'img-link-organizzazioni h-100 d-block',
                    'title' => $imgTitle
                ]
            )
            ?>
        </div>
        <div class="col-xs-12 col-sm-8 nop">
            <div class="info-organizzazioni ml-0 ml-sm-2 mt-2 mt-sm-0 d-flex flex-column align-items-start h-100">
                <?php
                $communityUrl = $model->getFullViewUrl();
                if (!empty($model->community_id) && $organizzazioniModule->directAccessToCommunityOrganization) {
                    $communityUrl = ['/community/join/open-join', 'id' => $model->community_id];
                }
                $goToPageOfTitle = Module::t('amosorganizzazioni', '#go_to_page_of') . ' ';
                ?>
                <?= Html::a(Html::tag('h5', $model->name, ['class' => 'bold mb-0 mb-sm-3 w-100']), $communityUrl, ['class' => 'title-one-line link-list-title', 'title' => $goToPageOfTitle . $model->name]) ?>
                <?= Html::a(Module::t('amosorganizzazioni', '#explore'), $communityUrl, ['class' => 'cta-organizzazioni btn btn-xs btn-primary py-1 mt-auto', 'title' => $goToPageOfTitle . $model->name]) ?>
            </div>
        </div>
    </div>
</div>
