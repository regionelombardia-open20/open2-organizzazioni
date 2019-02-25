<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\utility
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\utility;

use lispa\amos\organizzazioni\models\Profilo;
use lispa\amos\organizzazioni\models\ProfiloSediTypes;
use lispa\amos\organizzazioni\Module;
use yii\base\Object;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Class OrganizzazioniUtility
 * @package lispa\amos\organizzazioni\utility
 */
class OrganizzazioniUtility extends Object
{
    /**
     * This method returns all platform organizations ready for select.
     * @param Profilo $model
     * @return array
     */
    public static function getMembershipOrganizationsReadyForSelect($model)
    {
        /** @var ActiveQuery $query */
        $query = Profilo::find();
        if ($model->id) {
            $query->andWhere(['<>', 'id', $model->id]);
        }
        $organizations = $query->all();
        $readyForSelect = ArrayHelper::map($organizations, 'id', 'name');
        return $readyForSelect;
    }

    /**
     * This method returns all profilo sedi types ready for select.
     * @return array
     */
    public static function getProfiloSediTypesReadyForSelect()
    {
        /** @var Module $organizzazioniModule */
        $organizzazioniModule = \Yii::$app->getModule(Module::getModuleName());
        /** @var ActiveQuery $query */
        $query = ProfiloSediTypes::find();
        if (!$organizzazioniModule->enableAddOtherLegalHeadquarters) {
            $query->andWhere(['<>', 'id', ProfiloSediTypes::TYPE_LEGAL_HEADQUARTER]);
        }
        $query->andWhere(['active' => 1]);
        $query->orderBy(['order' => SORT_ASC]);
        $readyForSelect = ArrayHelper::map($query->all(), 'id', 'name');
        return $readyForSelect;
    }
}
