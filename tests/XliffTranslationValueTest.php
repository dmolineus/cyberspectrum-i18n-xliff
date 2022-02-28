<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff\Test;

use CyberSpectrum\I18N\Xliff\XliffDictionary;
use CyberSpectrum\I18N\Xliff\XliffTranslationValue;
use CyberSpectrum\I18N\Xliff\Xml\XliffFile;
use CyberSpectrum\I18N\Xliff\Xml\XmlElement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** @covers \CyberSpectrum\I18N\Xliff\XliffTranslationValue */
class XliffTranslationValueTest extends TestCase
{
    public function testEmptyValue(): void
    {
        $value = new XliffTranslationValue($this->mockDictionary(), $this->mockElement('test-key'));

        self::assertSame('test-key', $value->getKey());
        self::assertNull($value->getSource());
        self::assertNull($value->getTarget());
        self::assertTrue($value->isSourceEmpty());
        self::assertTrue($value->isTargetEmpty());
    }

    public function testCreatingWithValuesWorks(): void
    {
        $element = $this->mockElement('test-key', 'Source value', 'Target value');
        $value   = new XliffTranslationValue($this->mockDictionary(), $element);

        self::assertSame('Source value', $value->getSource());
        self::assertSame('Target value', $value->getTarget());
        self::assertFalse($value->isSourceEmpty());
        self::assertFalse($value->isTargetEmpty());
    }

    /**
     * Mock a dictionary.
     *
     * @return MockObject|XliffDictionary
     */
    private function mockDictionary(): XliffDictionary
    {
        $mock = $this
            ->getMockBuilder(XliffDictionary::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }

    /**
     * Mock an element
     *
     * @param string      $key         The key value.
     * @param string|null $sourceValue The source value.
     * @param string|null $targetValue The target value.
     *
     * @return XmlElement
     */
    private function mockElement(string $key, string $sourceValue = null, string $targetValue = null): XmlElement
    {
        $file = new XliffFile();

        $unit = $file->createTranslationUnit($key, $sourceValue);

        if (null !== $targetValue) {
            $unit->appendChild(
                $unit->ownerDocument->createElementNS(XliffFile::XLIFF_NS, 'target')
            )->appendChild($unit->ownerDocument->createTextNode($targetValue));
        }

        return $unit;
    }
}
