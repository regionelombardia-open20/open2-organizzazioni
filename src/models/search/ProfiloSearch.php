<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\organizzazioni\models\search
 * @category   CategoryName
 */

namespace open20\amos\organizzazioni\models\search;

use open20\amos\core\interfaces\SearchModelInterface;
use open20\amos\organizzazioni\models\Profilo;

/**
 * Class ProfiloSearch
 * ProfiloSearch represents the model behind the search form about `open20\amos\organizzazioni\models\Profilo`.
 * @package open20\amos\organizzazioni\models\search
 */
class ProfiloSearch extends Profilo implements SearchModelInterface
{
    public $isSearch = true;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'name',
                'partita_iva',
                'istat_code',
            ], 'safe'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function baseSearch($params)
    {
        //init the default search values
        $this->initOrderVars();
        
        //check params to get orders value
        $this->setOrderVars($params);
        
        /** @var Profilo $className */
        $className = $this->organizzazioniModule->model('Profilo');
        
        return $className::find()->distinct();
    }
    
    /**
     * @inheritdoc
     */
    public function searchFieldsLike()
    {
        return [
            'name',
            'partita_iva',
            'istat_code',
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function searchFieldsGlobalSearch()
    {
        return [
            'name',
            'partita_iva',
            'istat_code',
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function filterValidated($query)
    {
        $query->andWhere([static::tableName() . '.status' => Profilo::PROFILO_WORKFLOW_STATUS_VALIDATED]);
    }
    
    /**
     * @param array $params
     * @param null $limit
     * @return \yii\data\ActiveDataProvider|\yii\data\BaseDataProvider
     * @throws \yii\base\InvalidConfigException
     */
    public function searchToValidateProfilo($params, $limit = null)
    {
        return $this->search($params, 'to-validate', $limit);
    }
}
