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

namespace CyberSpectrum\I18N\Xliff;

use CyberSpectrum\I18N\TranslationValue\TranslationValueInterface;
use CyberSpectrum\I18N\Xliff\Xml\XliffFile;
use CyberSpectrum\I18N\Xliff\Xml\XmlElement;

/**
 * This provides access to a xliff translation value.
 */
class XliffTranslationValue implements TranslationValueInterface
{
    /**
     * The dictionary.
     *
     * @var XliffDictionary
     */
    protected $dictionary;

    /**
     * The XML element of this translation value.
     *
     * @var XmlElement
     */
    protected $node;

    /**
     * Create a new instance.
     *
     * @param XliffDictionary $dictionary The dictionary.
     * @param XmlElement     $node       The XML node.
     */
    public function __construct(XliffDictionary $dictionary, XmlElement $node)
    {
        $this->dictionary = $dictionary;
        $this->node       = $node;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return $this->node->getAttributeNS(XliffFile::XLIFF_NS, 'id');
    }

    /**
     * {@inheritDoc}
     */
    public function getSource(): ?string
    {
        if (($element = $this->getSourceElement()) && $element->firstChild) {
            return $element->firstChild->nodeValue;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getTarget(): ?string
    {
        if (($element = $this->getTargetElement()) && $element->firstChild) {
            return $element->firstChild->nodeValue;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isSourceEmpty(): bool
    {
        return (null === ($element = $this->getSourceElement()) || null === $element->firstChild);
    }

    /**
     * {@inheritDoc}
     */
    public function isTargetEmpty(): bool
    {
        return (null === ($element = $this->getTargetElement()) || null === $element->firstChild);
    }

    /**
     * Fetch the target element.
     *
     * @return XmlElement|null
     */
    protected function getSourceElement(): ?XmlElement
    {
        $list = $this->node->getElementsByTagNameNS(XliffFile::XLIFF_NS, 'source');
        if ($list->length && $element = $list->item(0)) {
            /** @var XmlElement $element */
            return $element;
        }

        return null;
    }

    /**
     * Fetch the target element.
     *
     * @return XmlElement|null
     */
    protected function getTargetElement(): ?XmlElement
    {
        $list = $this->node->getElementsByTagNameNS(XliffFile::XLIFF_NS, 'target');
        if ($list->length && $element = $list->item(0)) {
            /** @var XmlElement $element */
            return $element;
        }

        return null;
    }
}
