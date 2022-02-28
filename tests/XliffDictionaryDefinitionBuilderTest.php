<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff\Test;

use CyberSpectrum\I18N\Configuration\Configuration;
use CyberSpectrum\I18N\Configuration\Definition\DictionaryDefinition;
use CyberSpectrum\I18N\Xliff\XliffDictionaryDefinitionBuilder;
use PHPUnit\Framework\TestCase;

/** @covers \CyberSpectrum\I18N\Xliff\XliffDictionaryDefinitionBuilder */
class XliffDictionaryDefinitionBuilderTest extends TestCase
{
    public function testBuilding(): void
    {
        $configuration = new Configuration();

        $configuration->setDictionary(new DictionaryDefinition('base-dict1'));
        $configuration->setDictionary(new DictionaryDefinition('base-dict2'));

        $builder = new XliffDictionaryDefinitionBuilder();

        $dictionary = $builder->build($configuration, [
            'type'   => 'xliff',
            'name'   => 'test',
        ]);

        self::assertInstanceOf(DictionaryDefinition::class, $dictionary);
        self::assertSame('test', $dictionary->getName());
    }
}
