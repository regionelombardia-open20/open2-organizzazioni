<?php

namespace lispa\amos\organizzazioni\i18n\grammar;

use lispa\amos\core\interfaces\ModelGrammarInterface;
use lispa\amos\news\AmosNews;
use lispa\amos\organizzazioni\Module;

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    piattaforma-openinnovation
 * @category   CategoryName
 */

class OrganizzazioniGrammar implements ModelGrammarInterface
{

    /**
     * @return string
     */
    public function getModelSingularLabel()
    {
        return Module::t('amosorganizzazioni', '#organizzazioni_singular');
    }

    /**
     * @inheritdoc
     */
    public function getModelLabel()
    {
        return Module::t('amosorganizzazioni', '#organizzazioni_plural');
    }

    /**
     * @return mixed
     */
    public function getArticleSingular()
    {
        return Module::t('amosorganizzazioni', '#article_singular');
    }

    /**
     * @return mixed
     */
    public function getArticlePlural()
    {
        return Module::t('amosorganizzazioni', '#article_plural');
    }

    /**
     * @return string
     */
    public function getIndefiniteArticle()
    {
        return Module::t('amosorganizzazioni', '#article_indefinite');
    }
}