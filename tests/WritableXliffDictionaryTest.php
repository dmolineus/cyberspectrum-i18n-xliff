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

use CyberSpectrum\I18N\Exception\TranslationAlreadyContainedException;
use CyberSpectrum\I18N\Exception\TranslationNotFoundException;
use CyberSpectrum\I18N\Xliff\WritableXliffDictionary;
use CyberSpectrum\I18N\Xliff\WritableXliffTranslationValue;
use CyberSpectrum\I18N\Xliff\XliffTranslationValue;

/**
 * This tests the writable dictionary.
 *
 * @covers \CyberSpectrum\I18N\Xliff\WritableXliffDictionary
 * @covers \CyberSpectrum\I18N\Xliff\XliffTranslationValue
 * @covers \CyberSpectrum\I18N\Xliff\WritableXliffTranslationValue
 */
class WritableXliffDictionaryTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $dictionary = new WritableXliffDictionary($this->provide(__DIR__ . '/Fixtures/without-subdir') . '/test1.xlf');

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
        $dictionary = new WritableXliffDictionary($this->provide(__DIR__ . '/Fixtures/without-subdir') . '/test1.xlf');

        $this->expectException(TranslationNotFoundException::class);
        $this->expectExceptionMessage('Key "unknown-key" not found');

        $dictionary->getWritable('unknown-key');
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testAddingValuesWorks(): void
    {
        $dictionary = new WritableXliffDictionary($fileName = $this->getTempFile());

        $this->assertInstanceOf(WritableXliffTranslationValue::class, $value = $dictionary->add('test-key-add'));
        $this->assertSame(['test-key-add'], \iterator_to_array($dictionary->keys()));
        $this->assertSame('test-key-add', $value->getKey());
        $this->assertNull($value->getSource());
        $this->assertNull($value->getTarget());
        $this->assertTrue($value->isSourceEmpty());
        $this->assertTrue($value->isTargetEmpty());
        $this->assertFileExists($fileName);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testAddingExistingValuesThrows(): void
    {
        $dictionary = new WritableXliffDictionary($this->getTempFile());
        $dictionary->add('test-key');

        $this->expectException(TranslationAlreadyContainedException::class);
        $this->expectExceptionMessage('Key "test-key" already contained');

        $dictionary->add('test-key');
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testRemovalOfValuesWorks(): void
    {
        $dictionary = new WritableXliffDictionary($fileName = $this->getTempFile());
        $dictionary->add('test-key');

        $dictionary->remove('test-key');

        $this->assertSame([], \iterator_to_array($dictionary->keys()));
        $this->assertFalse($dictionary->has('test-key'));
        $this->assertFileExists($fileName);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testRemovalOfNonExistentValueThrows(): void
    {
        $dictionary = new WritableXliffDictionary($this->getTempFile());

        $this->expectException(TranslationNotFoundException::class);
        $this->expectExceptionMessage('Key "unknown-key" not found');

        $dictionary->remove('unknown-key');
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testWritingValuesWorks(): void
    {
        $dictionary = new WritableXliffDictionary($fileName = $this->getTempFile());
        $dictionary->add('test-key');

        $this->assertInstanceOf(WritableXliffTranslationValue::class, $value = $dictionary->getWritable('test-key'));

        $value->setSource('source value');
        $value->setTarget('target value');
        unset($value);

        $test = $dictionary->get('test-key');
        $this->assertSame('source value', $test->getSource());
        $this->assertSame('target value', $test->getTarget());
        $this->assertFileExists($fileName);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testWritingThrowsForUnknown(): void
    {
        $dictionary = new WritableXliffDictionary($this->getTempFile());

        $this->expectException(TranslationNotFoundException::class);
        $this->expectExceptionMessage('Key "unknown-key" not found');

        $dictionary->getWritable('unknown-key');
    }
}
