<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff;

use CyberSpectrum\I18N\TranslationValue\WritableTranslationValueInterface;
use CyberSpectrum\I18N\Xliff\Xml\XliffFile;
use CyberSpectrum\I18N\Xliff\Xml\XmlElement;
use DOMDocument;
use LogicException;

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
    public function __construct(WritableXliffDictionary $dictionary, XmlElement $node)
    {
        parent::__construct($dictionary, $node);
    }

    public function setSource(string $value): void
    {
        if (null === ($element = $this->getSourceElement())) {
            $element = $this->node->appendChild(
                $this->getDocument()->createElementNS(XliffFile::XLIFF_NS, 'source')
            );
        }
        $parent = $element->parentNode;
        if (!$parent instanceof XmlElement) {
            throw new LogicException('Unparented node encountered.');
        }

        if (null === $textNode = $element->firstChild) {
            $element->appendChild($this->getDocument()->createTextNode($value));
            $parent->setAttributeNS(XliffFile::XLIFF_NS, 'state', 'new');
            $this->dictionary->markChanged();

            return;
        }

        $textNode->nodeValue = $value;
        $parent->setAttributeNS(XliffFile::XLIFF_NS, 'state', 'needs-translation');
        $this->dictionary->markChanged();
    }

    public function setTarget(string $value): void
    {
        if (null === ($element = $this->getTargetElement())) {
            $element = $this->node->appendChild(
                $this->getDocument()->createElementNS(XliffFile::XLIFF_NS, 'target')
            );
        }

        if (null === $textNode = $element->firstChild) {
            $element->appendChild($this->getDocument()->createTextNode($value));
            $this->dictionary->markChanged();

            return;
        }

        $textNode->nodeValue = $value;
        $this->dictionary->markChanged();
    }

    public function clearSource(): void
    {
        if (($element = $this->getSourceElement()) && $element->firstChild) {
            $element->removeChild($element->firstChild);
        }
    }

    public function clearTarget(): void
    {
        if (($element = $this->getTargetElement()) && $element->firstChild) {
            $element->removeChild($element->firstChild);
        }
    }

    private function getDocument(): DOMDocument
    {
        $document = $this->node->ownerDocument;
        if (!$document instanceof DOMDocument) {
            throw new LogicException('Failed to obtain owner document');
        }
        return $document;
    }
}
