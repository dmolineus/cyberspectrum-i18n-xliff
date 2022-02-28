<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff\Test;

use CyberSpectrum\I18N\Exception\TranslationNotFoundException;
use CyberSpectrum\I18N\Xliff\XliffDictionary;
use CyberSpectrum\I18N\Xliff\XliffTranslationValue;

/**
 * @covers \CyberSpectrum\I18N\Xliff\XliffDictionary
 * @covers \CyberSpectrum\I18N\Xliff\XliffTranslationValue
 */
class XliffDictionaryTest extends TestCase
{
    public function testInstantiation(): void
    {
        $dictionary = new XliffDictionary($this->provide(__DIR__ . '/Fixtures/without-subdir') . '/test1.xlf');

        self::assertSame('en', $dictionary->getSourceLanguage());
        self::assertSame('de', $dictionary->getTargetLanguage());
        self::assertSame(
            ['test-string-with-only-source', 'test-string-with-source-and-target'],
            \iterator_to_array($dictionary->keys())
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
        $dictionary = new XliffDictionary($this->provide(__DIR__ . '/Fixtures/without-subdir') . '/test1.xlf');

        $this->expectException(TranslationNotFoundException::class);
        $this->expectExceptionMessage('Key "unknown-key" not found');

        $dictionary->get('unknown-key');
    }
}
