<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff\Xml;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Generator;
use InvalidArgumentException;
use RuntimeException;

/**
 * This is an xliff implementation.
 *
 * @internal Only to be used within this abstraction.
 */
final class XliffFile extends DOMDocument
{
    public const XLIFF_LANGUAGE_PATTERN = '#^[a-zA-Z]{1,8}(-[a-zA-Z\d]{1,8})*$#';

    /**
     * The xliff Namespace.
     */
    public const XLIFF_NS = 'urn:oasis:names:tc:xliff:document:1.2';

    /**
     * The xliff Namespace.
     */
    public const NS_XMLNS = 'http://www.w3.org/2000/xmlns/';

    /**
     * Creates a new document object.
     *
     * @param string $encoding The encoding of the document as part of the XML declaration.
     */
    public function __construct(string $encoding = 'UTF-8')
    {
        parent::__construct('1.0', $encoding);
        $this->registerNodeClass(DOMElement::class, XmlElement::class);

        $this->formatOutput       = true;
        $this->preserveWhiteSpace = false;

        $root = $this->createElementNS(self::XLIFF_NS, 'xliff');
        $this->appendChild($root);

        /** @psalm-suppress UninitializedProperty - it is initialized in the paren constructor */
        assert($this->documentElement instanceof XmlElement);
        $this->documentElement->setAttributeNS(self::XLIFF_NS, 'version', '1.2');
        $this->documentElement->appendChild($file = $this->createElementNS(self::XLIFF_NS, 'file'));
        $file->appendChild($this->createElementNS(self::XLIFF_NS, 'body'));

        // Set some basic information.
        $this->setDataType('plaintext');
        $this->setDate(new DateTime());
        $this->setOriginal('unspecified source');
        $this->setSourceLanguage('en');
        $this->setTargetLanguage('en');
    }

    /** Obtain the file element. */
    public function getFileElement(): XmlElement
    {
        if (null === $element = $this->getXPathFirstItem('/xlf:xliff/xlf:file', $this->documentElement)) {
            throw new RuntimeException('Failed to find file element');
        }
        return $element;
    }

    /**
     * Set the datatype in this file.
     *
     * See http://docs.oasis-open.org/xliff/v1.2/os/xliff-core.html#datatype
     *
     * You may use a custom datatype here but have to prefix it with "x-".
     *
     * @param string $datatype The data type.
     */
    public function setDataType(string $datatype): void
    {
        $this->getFileElement()->setAttributeNS(self::XLIFF_NS, 'datatype', $datatype);
    }

    /**
     * Get the datatype for this file.
     *
     * See http://docs.oasis-open.org/xliff/v1.2/os/xliff-core.html#datatype
     */
    public function getDataType(): string
    {
        return $this->getFileElement()->getAttributeNS(self::XLIFF_NS, 'datatype');
    }

    /**
     * Sets the last modification time in this file.
     *
     * @param DateTimeInterface $date The date.
     */
    public function setDate(DateTimeInterface $date): void
    {
        $this->getFileElement()->setAttributeNS(self::XLIFF_NS, 'date', $date->format(DateTimeInterface::ATOM));
    }

    /** Return the last modification time from this file. */
    public function getDate(): DateTimeInterface
    {
        return new DateTimeImmutable($this->getFileElement()->getAttributeNS(self::XLIFF_NS, 'date'));
    }

    /**
     * Set the "original" data source id value in the file.
     *
     * You will most likely the file name of the original resource or something like this here.
     *
     * @param string $original The name of the original data source.
     */
    public function setOriginal(string $original): void
    {
        $this->getFileElement()->setAttributeNS(self::XLIFF_NS, 'original', $original);
    }

    /** Get the original resource name from this file. */
    public function getOriginal(): string
    {
        return $this->getFileElement()->getAttributeNS(self::XLIFF_NS, 'original');
    }

    /**
     * Set the source language for this file.
     *
     * @param string $sourceLanguage The language code from ISO 639-1.
     *
     * @throws InvalidArgumentException When the language string is invalid.
     */
    public function setSourceLanguage(string $sourceLanguage): void
    {
        if (!preg_match(self::XLIFF_LANGUAGE_PATTERN, $sourceLanguage)) {
            throw new InvalidArgumentException('Invalid language string: "' . $sourceLanguage . '"');
        }
        $this->getFileElement()->setAttributeNS(self::XLIFF_NS, 'source-language', $sourceLanguage);
    }

    /**
     * Get the current source language for this file.
     *
     * @return string The language code from ISO 639-1
     */
    public function getSourceLanguage(): string
    {
        return $this->getFileElement()->getAttributeNS(self::XLIFF_NS, 'source-language');
    }

    /**
     * Set the target language for this file.
     *
     * @param string $targetLanguage The language code from ISO 639-1.
     *
     * @throws InvalidArgumentException When the language string is invalid.
     */
    public function setTargetLanguage(string $targetLanguage): void
    {
        if (!preg_match(self::XLIFF_LANGUAGE_PATTERN, $targetLanguage)) {
            throw new InvalidArgumentException('Invalid language string: "' . $targetLanguage . '"');
        }
        $this->getFileElement()->setAttributeNS(self::XLIFF_NS, 'target-language', $targetLanguage);
    }

    /**
     * Get the current target language for this file.
     *
     * @return string The language code from ISO 639-1.
     */
    public function getTargetLanguage(): string
    {
        return $this->getFileElement()->getAttributeNS(self::XLIFF_NS, 'target-language');
    }

    /**
     * Searches for the XMLNode that contains the given id.
     *
     * Optionally, the node can be created if not found.
     *
     * @param string $identifier The id string to search for.
     *
     * @return XmlElement|null
     *
     * @throws InvalidArgumentException When the id is empty.
     */
    public function searchTranslationUnit(string $identifier): ?XmlElement
    {
        if ('' === $identifier) {
            throw new InvalidArgumentException('Empty Id passed.', 0);
        }

        if (
            $this->documentElement->isDefaultNamespace(self::XLIFF_NS)
            && $transUnit = $this->getXPathFirstItem(
                '/xlf:xliff/xlf:file/xlf:body/xlf:trans-unit[@id=\'' . $identifier . '\']'
            )
        ) {
            return $transUnit;
        }

        if (
            $transUnit = $this->getXPathFirstItem(
                '/xlf:xliff/xlf:file/xlf:body/xlf:trans-unit[@xlf:id=\'' . $identifier . '\']'
            )
        ) {
            return $transUnit;
        }

        return null;
    }

    /**
     * Append a translation unit.
     *
     * @param string      $identifier  The identifier to set.
     * @param string|null $sourceValue The content for the source value to set.
     *
     * @return XmlElement
     *
     * @throws InvalidArgumentException When the body element can not be found.
     */
    public function createTranslationUnit(string $identifier, string $sourceValue = null): XmlElement
    {
        if (null === $body = $this->getXPathFirstItem('/xlf:xliff/xlf:file/xlf:body')) {
            throw new InvalidArgumentException('Could not find the xliff body element');
        }

        /** @var XmlElement $transUnit */
        $transUnit = $this->createElementNS(self::XLIFF_NS, 'trans-unit');

        $body->appendChild($transUnit);

        $transUnit->setAttributeNS(self::XLIFF_NS, 'id', $identifier);
        $source = $transUnit->appendChild($this->createElementNS(self::XLIFF_NS, 'source'));
        if (null !== $sourceValue) {
            $source->appendChild($this->createTextNode($sourceValue));
        }

        return $transUnit;
    }

    /**
     * Obtain all keys within the dictionary.
     *
     * @return Generator
     *
     * @throws RuntimeException When the id is empty.
     */
    public function extractTranslationKeys(): Generator
    {
        /** @var DOMNodeList $tmp */
        $transUnits = $this->getXPath()->query('/xlf:xliff/xlf:file/xlf:body/xlf:trans-unit');

        if ($transUnits->length > 0) {
            /** @var DOMElement $element */
            foreach ($transUnits as $element) {
                if ('' === $key = $element->getAttributeNS(self::XLIFF_NS, 'id')) {
                    throw new RuntimeException('Empty Id: ' . var_export($element, true));
                }
                yield $key;
            }
        }
    }

    /** Creates a new XPath object for the doc with the namespace xliff registered. */
    private function getXPath(): DOMXPath
    {
        $xpath = new DOMXPath($this);
        $xpath->registerNamespace('xlf', self::XLIFF_NS);

        return $xpath;
    }

    /**
     * Perform a Xpath search with the given query and return the first match if found.
     *
     * @param string       $query       The query to use.
     * @param DOMNode|null $contextNode The context node to apply.
     */
    private function getXPathFirstItem(string $query, ?DOMNode $contextNode = null): ?XmlElement
    {
        /** @var DOMNodeList $tmp */
        $tmp = $this->getXPath()->query($query, $contextNode);

        $item = $tmp->item(0);
        assert(null === $item || $item instanceof XmlElement);

        return $item;
    }
}
