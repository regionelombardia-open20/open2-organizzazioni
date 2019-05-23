<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\admin\rules
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\rules;

use lispa\amos\core\record\Record;
use lispa\amos\core\rules\BasicContentRule;
use lispa\amos\organizzazioni\models\Profilo;
use lispa\amos\organizzazioni\models\ProfiloSedi;
use lispa\amos\organizzazioni\models\ProfiloSediUserMm;
use lispa\amos\organizzazioni\models\ProfiloUserMm;
use lispa\amos\organizzazioni\utility\OrganizzazioniUtility;

/**
 * Class ConfirmUserRequestRule
 * @package lispa\amos\organizzazioni\rules
 */
class ConfirmUserRequestRule extends BasicContentRule
{
    public $name = 'confirmUserRequest';

    /**
     * @inheritdoc
     */
    public function execute($user, $item, $params)
    {
        if (isset($params['model'])) {
            /** @var Record $model */
            $model = $params['model'];
            if (!$model->id) {
                $post = \Yii::$app->getRequest()->post();
                $get = \Yii::$app->getRequest()->get();
                if (isset($get['profiloId'])) {
                    $model = $this->instanceModel($model, $get['profiloId']);
                } elseif (isset($post['profiloId'])) {
                    $model = $this->instanceModel($model, $post['profiloId']);
                } elseif (isset($get['profiloSediId'])) {
                    $model = $this->instanceModel($model, $get['profiloSediId']);
                } elseif (isset($post['profiloSediId'])) {
                    $model = $this->instanceModel($model, $post['profiloSediId']);
                } elseif (isset($get['id'])) {
                    $model = $this->instanceModel($model, $get['id']);
                } elseif (isset($post['id'])) {
                    $model = $this->instanceModel($model, $post['id']);
                }
            }
            return $this->ruleLogic($user, $item, $params, $model);
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function ruleLogic($user, $item, $params, $model)
    {
        if (
            is_null($model) ||
            (
                (!($model instanceof Profilo)) &&
                (!($model instanceof ProfiloSedi)) &&
                (!($model instanceof ProfiloUserMm)) &&
                (!($model instanceof ProfiloSediUserMm))
            )
        ) {
            return false;
        }
        if (!($model instanceof Profilo)) {
            if (($model instanceof ProfiloSedi) || ($model instanceof ProfiloUserMm)) {
                $model = $model->profilo;
            } elseif ($model instanceof ProfiloSediUserMm) {
                $model = $model->profiloSedi->profilo;
            }
        }
        /** @var Profilo $model */
        $organizationReferees = OrganizzazioniUtility::getOrganizationReferees($model->id, true);
        $ok = false;
        if (!is_null($model->rappresentanteLegale) && in_array($model->rappresentanteLegale->user_id, $organizationReferees)) {
            $ok = true;
        } elseif (!is_null($model->referenteOperativo) && in_array($model->referenteOperativo->user_id, $organizationReferees)) {
            $ok = true;
        }
        return $ok;
    }
}
