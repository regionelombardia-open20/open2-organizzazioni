<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\views\profilo-sedi\email
 * @category   CategoryName
 */

use lispa\amos\core\helpers\Html;
use lispa\amos\organizzazioni\Module;
use lispa\amos\organizzazioni\widgets\ProfiloCardWidget;

/**
 * @var \yii\web\View $this
 * @var \lispa\amos\organizzazioni\utility\EmailUtility $util
 */

if (!empty($profile)) {
    $this->params['profile'] = $profile;
}
?>

<div>
    <div style="box-sizing:border-box;">
        <div style="padding:5px 10px;background-color: #F2F2F2;">
            <h1 style="color:#297A38;text-align:center;font-size:1.5em;margin:0;"><?= Module::t('amosorganizzazioni', '#change_role_mail_title') ?></h1>
        </div>
        <div style="border:1px solid #cccccc;padding:10px;margin-bottom: 10px;background-color: #ffffff; margin-top: 20px;">
            <h2 style="color:#000000;font-size:2em;line-height: 1;"><?= Module::t('amosorganizzazioni', '#change_role_mail_text_1') . $util->contextLabel ?></h2>
            <div style="color:#000000;display: flex; padding: 10px;">
                <div style="width: 50px; height: 50px; -webkit-border-radius: 50%; -moz-border-radius: 50%; border-radius: 50%;float: left;">
                    <?= ProfiloCardWidget::widget([
                        'model' => $util->model->profilo,
                        'onlyLogo' => true,
                        'absoluteUrl' => true,
                        'inEmail' => true
                    ]) ?>
                </div>
                <?php
                echo Html::tag('div', '<p style="font-weight: 900">' . $util->model->name . '</p>
                <p>' . $util->model->getDescription(true) . '</p>', ['style' => 'margin: 0 0 0 20px;'])
                ?>
            </div>
            <?php if (!empty($util->role)): ?>
                <div style="color:#000000;width:100%;margin-top:30px">
                    <p>
                        <?= Module::t('amosorganizzazioni', "You are now:") . " " ?>
                        <span style="font-weight: 900"><?= $util->role . "." ?></span>
                    </p>
                </div>
            <?php endif; ?>
            <p style="color:#000000;"><?= Html::a(Module::t('amosorganizzazioni', 'Sign into the platform'), $util->url, ['style' => 'color: green;']) . ' ' .
                Module::t('amosorganizzazioni', "to start working within") . " " . $util->model->name
                ?>
            </p>
            <p style="color:#000000;">
                <?= Module::t('amosorganizzazioni', "#mail_network_headquarter_1") ?>
                <?= Html::a(' ' . Module::t('amosorganizzazioni', '#mail_network_organization_2'),
                    Yii::$app->urlManager->createAbsoluteUrl('dashboard'),
                    ['style' => 'color: green;']
                ) ?>
                <?= ' ' . Module::t('amosorganizzazioni', '#mail_network_organization_3') . ' ' ?>
                <span style="color:#000000;font-weight: 900; font-style: italic;"><?= Module::t('amosorganizzazioni', '#mail_network_organization_4') ?> </span>
                <?= Module::t('amosorganizzazioni', '#mail_network_organization_5') ?>
            </p>
        </div>
    </div>
</div>
