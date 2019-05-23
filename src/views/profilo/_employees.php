<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\views\profilo
 * @category   CategoryName
 */

use lispa\amos\admin\AmosAdmin;
use lispa\amos\admin\models\UserProfile;
use lispa\amos\admin\widgets\UserCardWidget;
use lispa\amos\core\views\AmosGridView;
use yii\data\ActiveDataProvider;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var lispa\amos\organizzazioni\models\Profilo $model
 * @var bool $isView
 */

/** @var UserProfile $emptyUserProfile */
$emptyUserProfile = AmosAdmin::instance()->createModel('UserProfile');

?>

<div class="col-xs-12">
    <?= AmosGridView::widget([
        'dataProvider' => new ActiveDataProvider([
            'query' => $model->getProfiloUsers()
        ]),
        'columns' => [
            [
                'label' => $emptyUserProfile->getAttributeLabel('userProfileImage'),
                'format' => 'raw',
                'value' => function ($model) {
                    /** @var \lispa\amos\core\user\User $model */
                    return UserCardWidget::widget(['model' => $model->userProfile]);
                }
            ],
            'userProfile.nomeCognome',
            'email',
        ]
    ]); ?>
</div>
