<?php

/**
 * This file is part of cyberspectrum/i18n-xliff.
 *
 * (c) 2018 CyberSpectrum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    cyberspectrum/i18n-xliff
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2018 CyberSpectrum.
 * @license    https://github.com/cyberspectrum/i18n-xliff/blob/master/LICENSE MIT
 * @filesource
 */

declare(strict_types = 1);

namespace CyberSpectrum\I18N\Xliff\Test;

use CyberSpectrum\I18N\Configuration\Configuration;
use CyberSpectrum\I18N\Configuration\Definition\DictionaryDefinition;
use CyberSpectrum\I18N\Xliff\XliffDictionaryDefinitionBuilder;
use PHPUnit\Framework\TestCase;

/**
 * This tests the copy job builder.
 *
 * @covers \CyberSpectrum\I18N\Xliff\XliffDictionaryDefinitionBuilder
 */
class XliffDictionaryDefinitionBuilderTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
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

        $this->assertInstanceOf(DictionaryDefinition::class, $dictionary);
        $this->assertSame('test', $dictionary->getName());
    }
}
