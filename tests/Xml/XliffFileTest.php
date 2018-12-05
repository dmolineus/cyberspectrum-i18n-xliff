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

namespace CyberSpectrum\I18N\Xliff\Test\Xml;

use CyberSpectrum\I18N\Xliff\Xml\XliffFile;
use PHPUnit\Framework\TestCase;

/**
 * This tests the xliff file.
 */
class XliffFileTest extends TestCase
{
    /**
     * Test.
     *
     * @return void
     */
    public function testBasicDocumentContainsValidDefaults(): void
    {
        $xliff = new XliffFile();
        $this->assertSame('plaintext', $xliff->getDataType());
        $this->assertSame(date(\DateTime::ATOM), $xliff->getDate()->format(\DateTime::ATOM));
        $this->assertSame('unspecified source', $xliff->getOriginal());
        $this->assertSame('en', $xliff->getSourceLanguage());
        $this->assertSame('en', $xliff->getTargetLanguage());

        $rootNode = $xliff->documentElement;

        $this->assertTrue($xliff->isDefaultNamespace(XliffFile::XLIFF_NS));
        $this->assertSame('xliff', $rootNode->localName);
        $this->assertSame(XliffFile::XLIFF_NS, $rootNode->namespaceURI);
        $this->assertSame('1.2', $rootNode->getAttributeNS(XliffFile::XLIFF_NS, 'version'));
        $file = $rootNode->firstChild;
        $this->assertSame('file', $file->localName);
        $this->assertSame(XliffFile::XLIFF_NS, $file->namespaceURI);
        $body = $file->firstChild;
        $this->assertSame('body', $body->localName);
        $this->assertSame(XliffFile::XLIFF_NS, $body->namespaceURI);

        $this->assertSame(XliffFile::XLIFF_NS, $rootNode->namespaceURI);
        $this->assertSame(XliffFile::XLIFF_NS, $xliff->lookupNamespaceUri(null));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testAddingUnitContainsValidDefaults(): void
    {
        $xliff = new XliffFile();

        $xliff->createTranslationUnit('test', 'temp');

        $unit = $xliff->documentElement->firstChild->firstChild->firstChild;
        $this->assertSame('trans-unit', $unit->localName);
        $this->assertSame(XliffFile::XLIFF_NS, $unit->namespaceURI);
        $this->assertSame('test', $unit->getAttributeNS(XliffFile::XLIFF_NS, 'id'));
        $source = $unit->firstChild;
        $this->assertSame('source', $source->localName);
        $this->assertSame(XliffFile::XLIFF_NS, $source->namespaceURI);
        $this->assertInstanceOf(\DOMText::class, $source->firstChild);
        $this->assertSame('temp', $source->textContent);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testSearchTranslationUnit(): void
    {
        $xliff = new XliffFile();
        $xliff->createTranslationUnit('test', 'temp');
        $unit = $xliff->documentElement->firstChild->firstChild->firstChild;

        $this->assertSame($unit, $xliff->searchTranslationUnit('test'));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testSearchTranslationUnitThrowsForEmptyId(): void
    {
        $xliff = new XliffFile();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty Id passed');

        $xliff->searchTranslationUnit('');
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testSearchTranslationUnitReturnsNullForUnknown(): void
    {
        $xliff = new XliffFile();

        $this->assertNull($xliff->searchTranslationUnit('test'));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testExtractTranslationKeys(): void
    {
        $xliff = new XliffFile();

        $xliff->createTranslationUnit('test1', 'temp');
        $xliff->createTranslationUnit('test2', 'temp');
        $this->assertSame(['test1', 'test2'], \iterator_to_array($xliff->extractTranslationKeys()));
    }
}
