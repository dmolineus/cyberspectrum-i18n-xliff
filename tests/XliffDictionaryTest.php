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

use CyberSpectrum\I18N\Exception\TranslationNotFoundException;
use CyberSpectrum\I18N\Xliff\XliffDictionary;
use CyberSpectrum\I18N\Xliff\XliffTranslationValue;

/**
 * This tests the simple translation value.
 *
 * @covers \CyberSpectrum\I18N\Xliff\XliffDictionary
 * @covers \CyberSpectrum\I18N\Xliff\XliffTranslationValue
 */
class XliffDictionaryTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $dictionary = new XliffDictionary($this->provide(__DIR__ . '/Fixtures/without-subdir') . '/test1.xlf');

        $this->assertSame('en', $dictionary->getSourceLanguage());
        $this->assertSame('de', $dictionary->getTargetLanguage());
        $this->assertSame(
            ['test-string-with-only-source', 'test-string-with-source-and-target'],
            \iterator_to_array($dictionary->keys())
        );
        $this->assertInstanceOf(
            XliffTranslationValue::class,
            $value = $dictionary->get('test-string-with-only-source')
        );
        $this->assertSame('test-string-with-only-source', $value->getKey());
        $this->assertSame('The source value', $value->getSource());
        $this->assertNull($value->getTarget());
        $this->assertFalse($value->isSourceEmpty());
        $this->assertTrue($value->isTargetEmpty());

        $this->assertInstanceOf(
            XliffTranslationValue::class,
            $value = $dictionary->get('test-string-with-source-and-target')
        );
        $this->assertSame('test-string-with-source-and-target', $value->getKey());
        $this->assertSame('The source value', $value->getSource());
        $this->assertSame('The target value', $value->getTarget());
        $this->assertFalse($value->isSourceEmpty());
        $this->assertFalse($value->isTargetEmpty());
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testThrowsForUnknownKey(): void
    {
        $dictionary = new XliffDictionary($this->provide(__DIR__ . '/Fixtures/without-subdir') . '/test1.xlf');

        $this->expectException(TranslationNotFoundException::class);
        $this->expectExceptionMessage('Key "unknown-key" not found');

        $dictionary->get('unknown-key');
    }
}
