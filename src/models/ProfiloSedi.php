<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\models
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\models;

use lispa\amos\organizzazioni\components\OrganizationsPlacesComponents;
use lispa\amos\organizzazioni\Module;
use yii\helpers\ArrayHelper;

/**
 * Class ProfiloSedi
 * This is the model class for table "profilo_sedi".
 *
 * @property \lispa\amos\organizzazioni\models\OrganizationsPlaces $sedeIndirizzo
 * @property string $addressField
 *
 * @package lispa\amos\organizzazioni\models
 */
class ProfiloSedi extends \lispa\amos\organizzazioni\models\base\ProfiloSedi
{
    const SCENARIO_CREATE = 'scenario_create';

    /**
     * @inheritdoc
     */
    public function representingColumn()
    {
        return [
            'name'
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->on(self::EVENT_BEFORE_VALIDATE, [$this, 'organizationsBeforeValidate']);

        parent::init();

        if ($this->isNewRecord) {
            $this->active = 1;
        }
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $skipColumns = [
            'name',
            'profilo_id',
        ];
        $modelColumns = $this->attributes();
        $scenarioCreateFields = [];
        foreach ($modelColumns as $modelColumn) {
            if (!in_array($modelColumn, $skipColumns)) {
                $scenarioCreateFields[] = $modelColumn;
            }
        }
        return ArrayHelper::merge(parent::scenarios(), [
            self::SCENARIO_CREATE => $scenarioCreateFields
        ]);
    }

    public function organizationsBeforeValidate()
    {
        $place_id = $this->address;
        OrganizationsPlacesComponents::checkPlace($place_id);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $place_id = $this->address;
        OrganizationsPlacesComponents::checkPlace($place_id);

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'addressField' => Module::t('amosorganizzazioni', 'Address'),
        ]);
    }

    /**
     * Return the columns to show as default in GridViewWidget
     * @param bool $showActionColumns
     * @return array
     */
    public function getGridViewColumns($showActionColumns = true)
    {
        $columns = [
            'name',
            [
                'attribute' => 'profiloSediType.name',
                'label' => $this->getAttributeLabel('profiloSediType')
            ],
            'addressField',
            'phone',
            'fax',
            'email:email',
            [
                'attribute' => 'profilo.name',
                'label' => $this->getAttributeLabel('profilo')
            ]
        ];

        if ($showActionColumns) {
            $columns [] = [
                'class' => 'lispa\amos\core\views\grid\ActionColumn',
                'viewOptions' => [
                    'class' => 'btn btn-tools-secondary view-headquarter-btn',
                    'url' => ['/organizzazioni/profilo-sedi/view'],
                    'defaultUrlIdParam' => true
                ],
                'updateOptions' => [
                    'class' => 'btn btn-tools-secondary update-headquarter-btn',
                    'url' => ['/organizzazioni/profilo-sedi/update'],
                    'defaultUrlIdParam' => true,
                ],
                'deleteOptions' => [
                    'class' => 'btn btn-danger-inverse delete-headquarter-btn',
                    'url' => ['/organizzazioni/profilo-sedi/delete'],
                    'defaultUrlIdParam' => true,
                ]
            ];
        }

        return $columns;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSedeIndirizzo()
    {
        return $this->hasOne(OrganizationsPlaces::className(), ['place_id' => 'address']);
    }

    /**
     * @return string
     */
    public function getAddressField()
    {
        if (is_null($this->sedeIndirizzo)) {
            return '-';
        }

        return ($this->sedeIndirizzo->postal_code ? '(' . $this->sedeIndirizzo->postal_code . ')' : '') .
            ($this->sedeIndirizzo->region ? ' ' . $this->sedeIndirizzo->region : '') .
            ($this->sedeIndirizzo->city ? ' ' . $this->sedeIndirizzo->city : '') .
            ($this->sedeIndirizzo->address ? ' ' . $this->sedeIndirizzo->address : '') .
            ($this->sedeIndirizzo->street_number ? ' ' . $this->sedeIndirizzo->street_number : '');
    }

    /**
     * @return array
     */
    public function getAddressFieldAsArray()
    {
        if (!empty($this->sedeIndirizzo)) {
            return [
                'postal_code' => $this->sedeIndirizzo->postal_code,
                'region' => $this->sedeIndirizzo->region,
                'city' => $this->sedeIndirizzo->city,
                'address' => $this->sedeIndirizzo->address,
                'street_number' => $this->sedeIndirizzo->street_number
            ];
        } else {
            return null;
        }
    }
}
