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
}
