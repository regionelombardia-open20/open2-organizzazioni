<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\organizzazioni\i18n\grammar
 * @category   CategoryName
 */

namespace lispa\amos\organizzazioni\i18n\grammar;

use lispa\amos\core\interfaces\ModelGrammarInterface;
use lispa\amos\organizzazioni\Module;

/**
 * Class ProfiloSediGrammar
 * @package lispa\amos\organizzazioni\i18n\grammar
 */
class ProfiloSediGrammar implements ModelGrammarInterface
{
    /**
     * @return string
     */
    public function getModelSingularLabel()
    {
        return Module::t('amosorganizzazioni', '#sedi_singular');
    }

    /**
     * @inheritdoc
     */
    public function getModelLabel()
    {
        return Module::t('amosorganizzazioni', '#sedi_plural');
    }

    /**
     * @inheritdoc
     */
    public function getArticleSingular()
    {
        return Module::t('amosorganizzazioni', '#sedi_article_singular');
    }

    /**
     * @inheritdoc
     */
    public function getArticlePlural()
    {
        return Module::t('amosorganizzazioni', '#sedi_article_plural');
    }

    /**
     * @inheritdoc
     */
    public function getIndefiniteArticle()
    {
        return Module::t('amosorganizzazioni', '#sedi_article_indefinite');
    }
}
