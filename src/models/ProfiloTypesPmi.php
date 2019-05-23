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

/**
 * Class ProfiloTypesPmi
 * This is the model class for table "organizations_types_pmi".
 * @package lispa\amos\organizzazioni\models
 */
class ProfiloTypesPmi extends \lispa\amos\organizzazioni\models\base\ProfiloTypesPmi
{
    const TYPE_CAT_GENERIC = 0;
    const TYPE_CAT_ENTE_AZIENDA_PUBBLICA = 1;
    const TYPE_CAT_PRIVATO = 3;
    const TYPE_CAT_ALTRO_ENTE = 2;
    const TYPE_CAT_GENERIC_PUBBLICO = 4;
}
