<?php

declare(strict_types=1);

namespace MediaCloud\Vendor\Doctrine\Inflector\Rules\Turkish;
use MediaCloud\Vendor\Doctrine\Inflector\GenericLanguageInflectorFactory;
use MediaCloud\Vendor\Doctrine\Inflector\Rules\Ruleset;

final class InflectorFactory extends GenericLanguageInflectorFactory
{
    protected function getSingularRuleset(): Ruleset
    {
        return Rules::getSingularRuleset();
    }

    protected function getPluralRuleset(): Ruleset
    {
        return Rules::getPluralRuleset();
    }
}
