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

use CyberSpectrum\I18N\Xliff\WritableXliffDictionary;
use CyberSpectrum\I18N\Xliff\WritableXliffTranslationValue;
use CyberSpectrum\I18N\Xliff\Xml\XliffFile;
use CyberSpectrum\I18N\Xliff\Xml\XmlElement;
use PHPUnit\Framework\TestCase;

/**
 * This tests the simple translation value.
 *
 * @covers \CyberSpectrum\I18N\Xliff\WritableXliffTranslationValue
 */
class WritableXliffTranslationValueTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testEmptyValue(): void
    {
        $value = new WritableXliffTranslationValue($this->mockDictionary(), $this->mockElement('test-key'));

        $this->assertSame('test-key', $value->getKey());
        $this->assertNull($value->getSource());
        $this->assertNull($value->getTarget());
        $this->assertTrue($value->isSourceEmpty());
        $this->assertTrue($value->isTargetEmpty());
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testCreatingWithValuesWorks(): void
    {
        $element = $this->mockElement('test-key', 'Source value', 'Target value');
        $value   = new WritableXliffTranslationValue($this->mockDictionary(), $element);

        $this->assertSame('Source value', $value->getSource());
        $this->assertSame('Target value', $value->getTarget());
        $this->assertFalse($value->isSourceEmpty());
        $this->assertFalse($value->isTargetEmpty());
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testSettingValuesWorks(): void
    {
        $element = $this->mockElement('test-key');
        $value   = new WritableXliffTranslationValue($this->mockDictionary(2), $element);

        $this->assertSame($value, $value->setSource('Source value'));
        $this->assertSame($value, $value->setTarget('Target value'));

        $this->assertSame('Source value', $value->getSource());
        $this->assertSame('Target value', $value->getTarget());
        $this->assertFalse($value->isSourceEmpty());
        $this->assertFalse($value->isTargetEmpty());
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testClearingValuesWorks(): void
    {
        $element = $this->mockElement('test-key', 'Source value', 'Target value');
        $value   = new WritableXliffTranslationValue($this->mockDictionary(), $element);

        $this->assertSame($value, $value->clearSource());
        $this->assertSame($value, $value->clearTarget());

        $this->assertNull($value->getSource());
        $this->assertNull($value->getTarget());
        $this->assertTrue($value->isSourceEmpty());
        $this->assertTrue($value->isTargetEmpty());
    }

    /**
     * Mock a dictionary.
     *
     * @param int $expectedChangeCount Expected count how often "markChanged" should be triggered.
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|WritableXliffDictionary
     */
    private function mockDictionary($expectedChangeCount = 0): WritableXliffDictionary
    {
        $mock = $this
            ->getMockBuilder(WritableXliffDictionary::class)
            ->setMethods(['markChanged'])
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
