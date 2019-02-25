<?php

namespace lispa\amos\organizzazioni\i18n\grammar;

use lispa\amos\core\interfaces\ModelGrammarInterface;
use lispa\amos\organizzazioni\Module;

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    piattaforma-openinnovation
 * @category   CategoryName
 */

class OrganizationGrammar implements ModelGrammarInterface
{

    /**
     * @return string
     */
    public function getModelSingularLabel()
    {
        return Module::t('organizations', 'organization');
    }

    /**
     * @inheritdoc
     */
    public function getModelLabel()
    {
        return Module::t('organizations', 'Organizzazioni');
    }

    /**
     * @return mixed
     */
    public function getArticleSingular()
    {
        return Module::t('organizations', '#article_singular');
    }

    /**
     * @return mixed
     */
    public function getArticlePlural()
    {
        return Module::t('organizations', '#article_plural');
    }

    /**
     * @return string
     */
    public function getIndefiniteArticle()
    {
        return Module::t('organizations', '#article_indefinite');
    }
}