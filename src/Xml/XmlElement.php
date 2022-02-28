<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff\Xml;

use DOMDocument;
use DOMElement;

use function array_slice;
use function call_user_func_array;
use function func_get_args;

/**
 * This is an xliff element implementation.
 *
 * @internal Only to be used within this abstraction.
 *
 * @property DOMDocument $ownerDocument
 */
final class XmlElement extends DOMElement
{
    /**
     * Adds new attribute.
     *
     * Work around method for the fact that DOMDocument adds some mysterious namespache "xmlns:default"
     * when the root NS is the requested XMLNS and setAttributeNS() is used.
     *
     * @param string $namespace     The namespace URI.
     * @param string $qualifiedName The qualified name of the attribute, as prefix:tagname.
     * @param string $value         The value of the attribute.
     *
     * @return void
     *
     * @link https://php.net/manual/en/domelement.setattributens.php
     */
    public function setAttributeNS($namespace, $qualifiedName, $value): void
    {
        if (
            $namespace === XliffFile::XLIFF_NS
            && $this->ownerDocument->isDefaultNamespace(XliffFile::XLIFF_NS)
        ) {
            call_user_func_array(['parent', 'setAttribute'], array_slice(func_get_args(), 1));
            return;
        }

        call_user_func_array(['parent', 'setAttributeNS'], func_get_args());
    }

    /**
     * Returns value of attribute
     *
     * Work around method for the fact that DOMDocument adds some mysterious namespache "xmlns:default"
     * when the root NS is the requested XMLNS and setAttributeNS() is used.
     *
     * @param string $namespace The namespace URI.
     * @param string $localName The local name.
     *
     * @return string The value of the attribute, or an empty string if no attribute with the given localName and
     *                namespaceURI is found.
     *
     * @link https://php.net/manual/en/domelement.getattributens.php
     *
     * @psalm-suppress MixedInferredReturnType
     */
    public function getAttributeNS($namespace, $localName): string
    {
        if (
            $namespace === XliffFile::XLIFF_NS
            && $this->ownerDocument->isDefaultNamespace(XliffFile::XLIFF_NS)
        ) {
            /** @psalm-suppress MixedReturnStatement */
            return call_user_func_array(['parent', 'getAttribute'], array_slice(func_get_args(), 1));
        }

        /** @psalm-suppress MixedReturnStatement */
        return call_user_func_array(['parent', 'getAttributeNS'], func_get_args());
    }
}
