<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff\Test;

use CyberSpectrum\I18N\Xliff\WritableXliffDictionary;
use CyberSpectrum\I18N\Xliff\WritableXliffTranslationValue;
use CyberSpectrum\I18N\Xliff\Xml\XliffFile;
use CyberSpectrum\I18N\Xliff\Xml\XmlElement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** @covers \CyberSpectrum\I18N\Xliff\WritableXliffTranslationValue */
class WritableXliffTranslationValueTest extends TestCase
{
    public function testEmptyValue(): void
    {
        $value = new WritableXliffTranslationValue($this->mockDictionary(), $this->mockElement('test-key'));

        self::assertSame('test-key', $value->getKey());
        self::assertNull($value->getSource());
        self::assertNull($value->getTarget());
        self::assertTrue($value->isSourceEmpty());
        self::assertTrue($value->isTargetEmpty());
    }

    public function testCreatingWithValuesWorks(): void
    {
        $element = $this->mockElement('test-key', 'Source value', 'Target value');
        $value   = new WritableXliffTranslationValue($this->mockDictionary(), $element);

        self::assertSame('Source value', $value->getSource());
        self::assertSame('Target value', $value->getTarget());
        self::assertFalse($value->isSourceEmpty());
        self::assertFalse($value->isTargetEmpty());
    }

    public function testSettingValuesWorks(): void
    {
        $element = $this->mockElement('test-key');
        $value   = new WritableXliffTranslationValue($this->mockDictionary(2), $element);

        $value->setSource('Source value');
        $value->setTarget('Target value');

        self::assertSame('Source value', $value->getSource());
        self::assertSame('Target value', $value->getTarget());
        self::assertFalse($value->isSourceEmpty());
        self::assertFalse($value->isTargetEmpty());
    }

    public function testClearingValuesWorks(): void
    {
        $element = $this->mockElement('test-key', 'Source value', 'Target value');
        $value   = new WritableXliffTranslationValue($this->mockDictionary(), $element);

        $value->clearSource();
        $value->clearTarget();

        self::assertNull($value->getSource());
        self::assertNull($value->getTarget());
        self::assertTrue($value->isSourceEmpty());
        self::assertTrue($value->isTargetEmpty());
    }

    /**
     * Mock a dictionary.
     *
     * @param int $expectedChangeCount Expected count how often "markChanged" should be triggered.
     *
     * @return MockObject|WritableXliffDictionary
     */
    private function mockDictionary(int $expectedChangeCount = 0): WritableXliffDictionary
    {
        $mock = $this
            ->getMockBuilder(WritableXliffDictionary::class)
            ->onlyMethods(['markChanged'])
            ->disableOriginalConstructor()
            ->getMock();
        if ($expectedChangeCount > 0) {
            $mock->expects($this->exactly($expectedChangeCount))->method('markChanged');
        } else {
            $mock->expects($this->never())->method('markChanged');
        }

        return $mock;
    }

    /**
     * Mock an element.
     *
     * @param string      $key         The translation key.
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
