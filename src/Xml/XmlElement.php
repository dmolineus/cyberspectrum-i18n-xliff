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
 * This is an xliff element implementation.
 *
 * @internal Only to be used within this abstraction.
 */
final class XmlElement extends \DOMElement
{
    /**
     * Adds new attribute.
     *
     * Work around method for the fact that DOMDocument adds some mysterious namespache "xmlns:default"
     * when the root NS is the requested XMLNS and setAttributeNS() is used.
     *
     * @param string $namespaceURI  The namespace URI.
     * @param string $qualifiedName The qualified name of the attribute, as prefix:tagname.
     * @param string $value         The value of the attribute.
     *
     * @return void
     *
     * @link https://php.net/manual/en/domelement.setattributens.php
     */
    public function setAttributeNS($namespaceURI, $qualifiedName, $value): void
    {
        if ($this->ownerDocument
            && $namespaceURI === XliffFile::XLIFF_NS
            && $this->ownerDocument->isDefaultNamespace(XliffFile::XLIFF_NS)) {
            \call_user_func_array(['parent', 'setAttribute'], \array_slice(\func_get_args(), 1));
            return;
        }

        \call_user_func_array(['parent', 'setAttributeNS'], \func_get_args());
    }

    /**
     * Returns value of attribute
     *
     * Work around method for the fact that DOMDocument adds some mysterious namespache "xmlns:default"
     * when the root NS is the requested XMLNS and setAttributeNS() is used.
     *
     * @param string $namespaceURI The namespace URI.
     * @param string $localName    The local name.
     *
     * @return string The value of the attribute, or an empty string if no attribute with the given localName and
     *                namespaceURI is found.
     *
     * @link https://php.net/manual/en/domelement.getattributens.php
     */
    public function getAttributeNS($namespaceURI, $localName): ?string
    {
        if ($this->ownerDocument
            && $namespaceURI === XliffFile::XLIFF_NS
            && $this->ownerDocument->isDefaultNamespace(XliffFile::XLIFF_NS)) {
            return \call_user_func_array(['parent', 'getAttribute'], \array_slice(\func_get_args(), 1));
        }

        return \call_user_func_array(['parent', 'getAttributeNS'], \func_get_args());
    }
}
