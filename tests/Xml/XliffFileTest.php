<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff\Test\Xml;

use CyberSpectrum\I18N\Xliff\Xml\XliffFile;
use DateTimeInterface;
use DOMText;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

/** @covers \CyberSpectrum\I18N\Xliff\Xml\XliffFile */
class XliffFileTest extends TestCase
{
    public function testBasicDocumentContainsValidDefaults(): void
    {
        $xliff = new XliffFile();
        self::assertSame('plaintext', $xliff->getDataType());
        self::assertSame(date(DateTimeInterface::ATOM), $xliff->getDate()->format(DateTimeInterface::ATOM));
        self::assertSame('unspecified source', $xliff->getOriginal());
        self::assertSame('en', $xliff->getSourceLanguage());
        self::assertSame('en', $xliff->getTargetLanguage());

        $rootNode = $xliff->documentElement;

        self::assertTrue($xliff->isDefaultNamespace(XliffFile::XLIFF_NS));
        self::assertSame('xliff', $rootNode->localName);
        self::assertSame(XliffFile::XLIFF_NS, $rootNode->namespaceURI);
        self::assertSame('1.2', $rootNode->getAttributeNS(XliffFile::XLIFF_NS, 'version'));
        $file = $rootNode->firstChild;
        self::assertSame('file', $file->localName);
        self::assertSame(XliffFile::XLIFF_NS, $file->namespaceURI);
        $body = $file->firstChild;
        self::assertSame('body', $body->localName);
        self::assertSame(XliffFile::XLIFF_NS, $body->namespaceURI);

        self::assertSame(XliffFile::XLIFF_NS, $rootNode->namespaceURI);
        self::assertSame(XliffFile::XLIFF_NS, $xliff->lookupNamespaceUri(null));
    }

    public function testAddingUnitContainsValidDefaults(): void
    {
        $xliff = new XliffFile();

        $xliff->createTranslationUnit('test', 'temp');

        $unit = $xliff->documentElement->firstChild->firstChild->firstChild;
        self::assertSame('trans-unit', $unit->localName);
        self::assertSame(XliffFile::XLIFF_NS, $unit->namespaceURI);
        self::assertSame('test', $unit->getAttributeNS(XliffFile::XLIFF_NS, 'id'));
        $source = $unit->firstChild;
        self::assertSame('source', $source->localName);
        self::assertSame(XliffFile::XLIFF_NS, $source->namespaceURI);
        self::assertInstanceOf(DOMText::class, $source->firstChild);
        self::assertSame('temp', $source->textContent);
    }

    public function testSearchTranslationUnit(): void
    {
        $xliff = new XliffFile();
        $xliff->createTranslationUnit('test', 'temp');
        $unit = $xliff->documentElement->firstChild->firstChild->firstChild;

        self::assertSame($unit, $xliff->searchTranslationUnit('test'));
    }

    public function testSearchTranslationUnitThrowsForEmptyId(): void
    {
        $xliff = new XliffFile();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Empty Id passed');

        $xliff->searchTranslationUnit('');
    }

    public function testSearchTranslationUnitReturnsNullForUnknown(): void
    {
        $xliff = new XliffFile();

        self::assertNull($xliff->searchTranslationUnit('test'));
    }

    public function testExtractTranslationKeys(): void
    {
        $xliff = new XliffFile();

        $xliff->createTranslationUnit('test1', 'temp');
        $xliff->createTranslationUnit('test2', 'temp');
        self::assertSame(['test1', 'test2'], iterator_to_array($xliff->extractTranslationKeys()));
    }
}
