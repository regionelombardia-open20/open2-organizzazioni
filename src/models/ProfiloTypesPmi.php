<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\organizzazioni\models
 * @category   CategoryName
 */

namespace open20\amos\organizzazioni\models;

/**
 * Class ProfiloTypesPmi
 * This is the model class for table "organizations_types_pmi".
 * @package open20\amos\organizzazioni\models
 */
class ProfiloTypesPmi extends \open20\amos\organizzazioni\models\base\ProfiloTypesPmi
{
    const TYPE_CAT_GENERIC = 0;
    const TYPE_CAT_ENTE_AZIENDA_PUBBLICA = 1;
    const TYPE_CAT_PRIVATO = 3;
    const TYPE_CAT_ALTRO_ENTE = 2;
    const TYPE_CAT_GENERIC_PUBBLICO = 4;
}
