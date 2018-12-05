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

use CyberSpectrum\I18N\TranslationValue\WritableTranslationValueInterface;
use CyberSpectrum\I18N\Xliff\Xml\XliffFile;
use CyberSpectrum\I18N\Xliff\Xml\XmlElement;

/**
 * This provides access to a xliff translation value.
 *
 * @property WritableXliffDictionary $dictionary The writable dictionary.
 */
class WritableXliffTranslationValue extends XliffTranslationValue implements WritableTranslationValueInterface
{
    /**
     * Create a new instance.
     *
     * @param WritableXliffDictionary $dictionary The dictionary.
     * @param XmlElement              $node       The XML node.
     */
    // @codingStandardsIgnoreStart This is no useless constructor overriding, we change the parameter type.
    public function __construct(WritableXliffDictionary $dictionary, XmlElement $node)
    {
        parent::__construct($dictionary, $node);
    }
    // @codingStandardsIgnoreEnd

    /**
     * {@inheritDoc}
     */
    public function setSource(string $value)
    {
        if (null === ($element = $this->getSourceElement())) {
            $element = $this->node->appendChild(
                $this->node->ownerDocument->createElementNS(XliffFile::XLIFF_NS, 'source')
            );
        }

        if (null === $textNode = $element->firstChild) {
            $element->appendChild($this->node->ownerDocument->createTextNode($value));
            $element->parentNode->setAttributeNS(XliffFile::XLIFF_NS, 'state', 'new');
            $this->dictionary->markChanged();

            return $this;
        }

        $textNode->nodeValue = $value;
        $element->parentNode->setAttributeNS(XliffFile::XLIFF_NS, 'state', 'needs-translation');
        $this->dictionary->markChanged();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setTarget(string $value)
    {
        if (null === ($element = $this->getTargetElement())) {
            $element = $this->node->appendChild(
                $this->node->ownerDocument->createElementNS(XliffFile::XLIFF_NS, 'target')
            );
        }

        if (null === $textNode = $element->firstChild) {
            $element->appendChild($this->node->ownerDocument->createTextNode($value));
            $this->dictionary->markChanged();

            return $this;
        }

        $textNode->nodeValue = $value;
        $this->dictionary->markChanged();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearSource()
    {
        if (($element = $this->getSourceElement()) && $element->firstChild) {
            $element->removeChild($element->firstChild);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function clearTarget()
    {
        if (($element = $this->getTargetElement()) && $element->firstChild) {
            $element->removeChild($element->firstChild);
        }

        return $this;
    }
}
