<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff\Test;

use CyberSpectrum\I18N\Exception\TranslationAlreadyContainedException;
use CyberSpectrum\I18N\Exception\TranslationNotFoundException;
use CyberSpectrum\I18N\Xliff\WritableXliffDictionary;
use CyberSpectrum\I18N\Xliff\WritableXliffTranslationValue;
use CyberSpectrum\I18N\Xliff\XliffTranslationValue;

use function iterator_to_array;

/**
 * @covers \CyberSpectrum\I18N\Xliff\WritableXliffDictionary
 * @covers \CyberSpectrum\I18N\Xliff\XliffTranslationValue
 * @covers \CyberSpectrum\I18N\Xliff\WritableXliffTranslationValue
 */
class WritableXliffDictionaryTest extends TestCase
{
    public function testInstantiation(): void
    {
        $dictionary = new WritableXliffDictionary($this->provide(__DIR__ . '/Fixtures/without-subdir') . '/test1.xlf');

        self::assertSame('en', $dictionary->getSourceLanguage());
        self::assertSame('de', $dictionary->getTargetLanguage());
        self::assertSame(
            ['test-string-with-only-source', 'test-string-with-source-and-target'],
            iterator_to_array($dictionary->keys())
        );
        self::assertInstanceOf(
            XliffTranslationValue::class,
            $value = $dictionary->get('test-string-with-only-source')
        );
        self::assertSame('test-string-with-only-source', $value->getKey());
        self::assertSame('The source value', $value->getSource());
        self::assertNull($value->getTarget());
        self::assertFalse($value->isSourceEmpty());
        self::assertTrue($value->isTargetEmpty());

        self::assertInstanceOf(
            XliffTranslationValue::class,
            $value = $dictionary->get('test-string-with-source-and-target')
        );
        self::assertSame('test-string-with-source-and-target', $value->getKey());
        self::assertSame('The source value', $value->getSource());
        self::assertSame('The target value', $value->getTarget());
        self::assertFalse($value->isSourceEmpty());
        self::assertFalse($value->isTargetEmpty());
    }

    public function testThrowsForUnknownKey(): void
    {
        $dictionary = new WritableXliffDictionary($this->provide(__DIR__ . '/Fixtures/without-subdir') . '/test1.xlf');

        $this->expectException(TranslationNotFoundException::class);
        $this->expectExceptionMessage('Key "unknown-key" not found');

        $dictionary->getWritable('unknown-key');
    }

    public function testAddingValuesWorks(): void
    {
        $dictionary = new WritableXliffDictionary($fileName = $this->getTempFile());

        self::assertInstanceOf(WritableXliffTranslationValue::class, $value = $dictionary->add('test-key-add'));
        self::assertSame(['test-key-add'], iterator_to_array($dictionary->keys()));
        self::assertSame('test-key-add', $value->getKey());
        self::assertNull($value->getSource());
        self::assertNull($value->getTarget());
        self::assertTrue($value->isSourceEmpty());
        self::assertTrue($value->isTargetEmpty());
        self::assertFileExists($fileName);
    }

    public function testAddingExistingValuesThrows(): void
    {
        $dictionary = new WritableXliffDictionary($this->getTempFile());
        $dictionary->add('test-key');

        $this->expectException(TranslationAlreadyContainedException::class);
        $this->expectExceptionMessage('Key "test-key" already contained');

        $dictionary->add('test-key');
    }

    public function testRemovalOfValuesWorks(): void
    {
        $dictionary = new WritableXliffDictionary($fileName = $this->getTempFile());
        $dictionary->add('test-key');

        $dictionary->remove('test-key');

        self::assertSame([], iterator_to_array($dictionary->keys()));
        self::assertFalse($dictionary->has('test-key'));
        self::assertFileExists($fileName);
    }

    public function testRemovalOfNonExistentValueThrows(): void
    {
        $dictionary = new WritableXliffDictionary($this->getTempFile());

        $this->expectException(TranslationNotFoundException::class);
        $this->expectExceptionMessage('Key "unknown-key" not found');

        $dictionary->remove('unknown-key');
    }

    public function testWritingValuesWorks(): void
    {
        $dictionary = new WritableXliffDictionary($fileName = $this->getTempFile());
        $dictionary->add('test-key');

        self::assertInstanceOf(WritableXliffTranslationValue::class, $value = $dictionary->getWritable('test-key'));

        $value->setSource('source value');
        $value->setTarget('target value');
        unset($value);

        $test = $dictionary->get('test-key');
        self::assertSame('source value', $test->getSource());
        self::assertSame('target value', $test->getTarget());
        self::assertFileExists($fileName);
    }

    public function testWritingThrowsForUnknown(): void
    {
        $dictionary = new WritableXliffDictionary($this->getTempFile());

        $this->expectException(TranslationNotFoundException::class);
        $this->expectExceptionMessage('Key "unknown-key" not found');

        $dictionary->getWritable('unknown-key');
    }
}
