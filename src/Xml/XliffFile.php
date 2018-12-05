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

namespace CyberSpectrum\I18N\Xliff\Xml;

/**
 * This is an xliff implementation.
 *
 * @internal Only to be used within this abstraction.
 */
final class XliffFile extends \DOMDocument
{
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
        $this->registerNodeClass('DOMElement', XmlElement::class);

        $this->formatOutput       = true;
        $this->preserveWhiteSpace = false;

        $root = $this->createElementNS(static::XLIFF_NS, 'xliff');
        $this->appendChild($root);

        $this->documentElement->setAttributeNS(static::XLIFF_NS, 'version', '1.2');
        $this->documentElement->appendChild($file = $this->createElementNS(static::XLIFF_NS, 'file'));
        $file->appendChild($this->createElementNS(static::XLIFF_NS, 'body'));

        // Set some basic information.
        $this->setDataType('plaintext');
        $this->setDate(new \DateTime());
        $this->setOriginal('unspecified source');
        $this->setSourceLanguage('en');
        $this->setTargetLanguage('en');
    }

    /**
     * Obtain the file element.
     *
     * @return \DOMElement
     */
    public function getFileElement(): \DOMElement
    {
        return $this->getXPathFirstItem('/xlf:xliff/xlf:file', $this->documentElement);
    }

    /**
     * Set the datatype in this file.
     *
     * See http://docs.oasis-open.org/xliff/v1.2/os/xliff-core.html#datatype
     *
     * You may use a custom datatype here but have to prefix it with "x-".
     *
     * @param string $datatype The data type.
     *
     * @return void
     */
    public function setDataType($datatype): void
    {
        $this->getFileElement()->setAttributeNS(static::XLIFF_NS, 'datatype', $datatype);
    }

    /**
     * Get the datatype for this file.
     *
     * See http://docs.oasis-open.org/xliff/v1.2/os/xliff-core.html#datatype
     *
     * @return string
     */
    public function getDataType(): string
    {
        return $this->getFileElement()->getAttributeNS(static::XLIFF_NS, 'datatype');
    }

    /**
     * Sets the last modification time in this file.
     *
     * @param \DateTime $date The date.
     *
     * @return void
     */
    public function setDate(\DateTime $date): void
    {
        $this->getFileElement()->setAttributeNS(static::XLIFF_NS, 'date', $date->format(\DateTime::ATOM));
    }

    /**
     * Return the last modification time from this file.
     *
     * @return \DateTime
     */
    public function getDate(): \DateTime
    {
        return new \DateTime($this->getFileElement()->getAttributeNS(static::XLIFF_NS, 'date'));
    }

    /**
     * Set the "original" data source id value in the file.
     *
     * You will most likely the file name of the original resource or something like this here.
     *
     * @param string $original The name of the original data source.
     *
     * @return void
     */
    public function setOriginal($original): void
    {
        $this->getFileElement()->setAttributeNS(static::XLIFF_NS, 'original', $original);
    }

    /**
     * Get the original resource name from this file.
     *
     * @return string
     */
    public function getOriginal(): string
    {
        return $this->getFileElement()->getAttributeNS(static::XLIFF_NS, 'original');
    }

    /**
     * Set the source language for this file.
     *
     * @param string $sourceLanguage The language code from ISO 639-1.
     *
     * @return void
     *
     * @throws \InvalidArgumentException When the language string is invalid.
     */
    public function setSourceLanguage($sourceLanguage): void
    {
        if (!preg_match('#[a-zA-Z]{1,8}(-[a-zA-Z0-9]{1,8})*#', $sourceLanguage)) {
            throw new \InvalidArgumentException('Invalid language string: "' . $sourceLanguage . '"');
        }
        $this->getFileElement()->setAttributeNS(static::XLIFF_NS, 'source-language', $sourceLanguage);
    }

    /**
     * Get the current source language for this file.
     *
     * @return string The language code from ISO 639-1
     */
    public function getSourceLanguage(): string
    {
        return $this->getFileElement()->getAttributeNS(static::XLIFF_NS, 'source-language');
    }

    /**
     * Set the target language for this file.
     *
     * @param string $targetLanguage The language code from ISO 639-1.
     *
     * @return void
     *
     * @throws \InvalidArgumentException When the language string is invalid.
     */
    public function setTargetLanguage($targetLanguage): void
    {
        if (!preg_match('#[a-zA-Z]{1,8}(-[a-zA-Z0-9]{1,8})*#', $targetLanguage)) {
            throw new \InvalidArgumentException('Invalid language string: "' . $targetLanguage . '"');
        }
        $this->getFileElement()->setAttributeNS(static::XLIFF_NS, 'target-language', $targetLanguage);
    }

    /**
     * Get the current target language for this file.
     *
     * @return string The language code from ISO 639-1.
     */
    public function getTargetLanguage(): string
    {
        return $this->getFileElement()->getAttributeNS(static::XLIFF_NS, 'target-language');
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
     * @throws \InvalidArgumentException When the id is empty.
     */
    public function searchTranslationUnit(string $identifier): ?XmlElement
    {
        if ('' === $identifier) {
            throw new \InvalidArgumentException('Empty Id passed.', 0);
        }

        if ($this->documentElement
            && $this->documentElement->isDefaultNamespace(self::XLIFF_NS)
            && $transUnit = $this->getXPathFirstItem(
                '/xlf:xliff/xlf:file/xlf:body/xlf:trans-unit[@id=\'' . $identifier . '\']'
            )) {
            /** @var XmlElement $transUnit */
            return $transUnit;
        }

        if ($transUnit = $this->getXPathFirstItem(
            '/xlf:xliff/xlf:file/xlf:body/xlf:trans-unit[@xlf:id=\'' . $identifier . '\']'
        )) {
            /** @var XmlElement $transUnit */
            return $transUnit;
        }

        return null;
    }

    /**
     * Append a translation unit.
     *
     * @param string $identifier  The identifier to set.
     * @param string $sourceValue The content for the source value to set.
     *
     * @return XmlElement
     *
     * @throws \InvalidArgumentException When the body element can not be found.
     */
    public function createTranslationUnit(string $identifier, string $sourceValue = null): XmlElement
    {
        if (null === $body = $this->getXPathFirstItem('/xlf:xliff/xlf:file/xlf:body')) {
            throw new \InvalidArgumentException('Could not find the xliff body element');
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
     * @return \Generator
     *
     * @throws \RuntimeException When the id is empty.
     */
    public function extractTranslationKeys(): \Generator
    {
        /** @var \DOMNodeList $tmp */
        $transUnits = $this->getXPath()->query('/xlf:xliff/xlf:file/xlf:body/xlf:trans-unit');

        if ($transUnits->length > 0) {
            /** @var \DOMElement $element */
            foreach ($transUnits as $element) {
                if ('' === $key = $element->getAttributeNS(self::XLIFF_NS, 'id')) {
                    throw new \RuntimeException('Empty Id: ' . var_export($element, true));
                }
                yield $key;
            }
        }
    }

    /**
     * Creates a new XPath object for the doc with the namespace xliff registered.
     *
     * @return \DOMXPath
     */
    private function getXPath(): \DOMXPath
    {
        $xpath = new \DOMXPath($this);
        $xpath->registerNamespace('xlf', self::XLIFF_NS);

        return $xpath;
    }

    /**
     * Perform a Xpath search with the given query and return the first match if found.
     *
     * @param string $query       The query to use.
     * @param null   $contextNode The context node to apply.
     *
     * @return \DOMElement|\DOMNode|null
     */
    private function getXPathFirstItem($query, $contextNode = null)
    {
        /** @var \DOMNodeList $tmp */
        $tmp = $this->getXPath()->query($query, $contextNode);

        return $tmp->length ? $tmp->item(0) : null;
    }
}
