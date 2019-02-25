<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni
 * @category   CategoryName
 */

use lispa\amos\core\icons\AmosIcons;
/*
 * Personalizzare a piacimento la vista
 * $model è il model legato alla tabella del db
 * $buttons sono i tasti del template standard {view}{update}{delete}
 */



?>

<div class="row list-horizontal-element">

    <div class="list-element-left">
        <div class="grow-pict">
            <img class="img-responsive img-round" src="<?php echo $model->getAvatarUrl('medium') ?>" alt="<?php echo $model ?>" />
        </div>
    </div>

    <div class="list-element-right">
        <div class="container-btn">
            <button class="btn btn-navigation-primary">
                <?php echo AmosIcons::show('link'); ?>
                <span class="hidden-xs">LINK</span>
            </button>
        </div>
        <div class ="list-element-body">
            <h3><a href="#"><?php echo $model ?></a></h3>
            <?php if ($model->getTelefonoDefault()): ?>
            <p>
                <?php echo AmosIcons::show('phone'); ?>
                <span><?php echo $model->getTelefonoDefault() ?></span>
            </p>
            <?php
endif;
if ($model->getTestoIcon()):
?>
            <p>
                <?php echo AmosIcons::show('home'); ?>
                <?php echo $model->getTestoItem() ?>
            </p>
            <?php
endif;
if ($model->getEmailDefault()):
?>
            <p>
                <?php echo AmosIcons::show('email'); ?>
                <span><?php echo $model->getEmailDefault() ?></span>
            </p>
            <?php endif; ?>
        </div>
        <div class="foot-bar row">
            <?php if ($model->getContattiComune()): ?>
            <div class="col-sm-7 col-xs-12 foot-bar-left">
                <p><?php echo $model->getContattiComune() ?></p>
            </div>
            <div class="col-sm-5 col-xs-12 foot-bar-right">
                <p>
                    <?php echo $buttons ?>
                </p>
            </div>
            <?php else: ?>
            <div class="col-xs-12 foot-bar-right">
                <p>
                    <?php echo $buttons ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
