<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\models\search
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\models\search;

use lispa\amos\core\interfaces\SearchModelInterface;
use lispa\amos\organizzazioni\models\Profilo;

/**
 * Class ProfiloSearch
 * ProfiloSearch represents the model behind the search form about `lispa\amos\organizzazioni\models\Profilo`.
 * @package lispa\amos\organizzazioni\models\search
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
