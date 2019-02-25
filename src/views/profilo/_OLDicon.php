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
use backend\modules\admin\Module as AdminModule;

/*
 * Personalizzare a piacimento la vista
 * $model è il model legato alla tabella del db
 * $buttons sono i tasti del template standard {view}{update}{delete}
 */
?>

<div class="card-container">
    <div class="icon-header grow-pict">
        <a href="#"><img class="img-responsive img-round"src="<?php echo $model->getAvatarUrl('medium') ?>"  alt="<?php echo $model ?>"></a>
    </div>
    <div class="icon-body">
        <div class="container-title"><h3><a href="#"><?php echo $model ?></a></h3></div>
        <?php if ($model->getTestoIcon()): ?>
            <p><?php echo $model->getTestoIcon() ?></p>
        <?php endif; ?>
        <?php if ($model->getTelefonoDefault()): ?>
            <p>
                <?php echo AmosIcons::show('phone'); ?>
                <span title="<?php echo $model->getTelefonoDefault() ?>"><?php echo $model->getTelefonoDefault() ?></span>
            </p>
            <?php
endif;
if ($model->getEmailDefault()):
?>
            <?php echo AmosIcons::show('email', ['class' => 'ellipsis-text', 'title' => $model->getEmailDefault()], \Yii::$app->params['icon-framework'], true, 'p', $model->getEmailDefault()); ?>
        <?php endif; ?>
    </div>
    <div class="icon-footer">
        <div class="foot-bar">
            <?php if ($model->getContattiComune()): ?>
            <div class="col-sm-7 col-xs-12 ">
                <?php echo $model->getContattiComune() ?>
            </div>
            <div class="col-sm-5 col-xs-12 container-action">
                <?php echo $buttons ?>
            </div>
            <?php else: ?>
            <div class="col-xs-12 container-action">
                <?php echo $buttons ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="foot-button">
            <button class="btn btn-navigation-primary">
                <?php echo AmosIcons::show('link'); ?>
                <span class="hidden-xs">LINK</span>
            </button>
        </div>
    </div>

</div>
