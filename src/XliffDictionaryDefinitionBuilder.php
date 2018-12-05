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

use CyberSpectrum\I18N\Configuration\Configuration;
use CyberSpectrum\I18N\Configuration\Definition\Definition;
use CyberSpectrum\I18N\Configuration\Definition\DictionaryDefinition;
use CyberSpectrum\I18N\Configuration\DefinitionBuilder\DefinitionBuilderInterface;

/**
 * Builds xliff dictionary definitions.
 */
class XliffDictionaryDefinitionBuilder implements DefinitionBuilderInterface
{
    /**
     * Build a definition from the passed values.
     *
     * @param Configuration $configuration The configuration.
     * @param array         $data          The configuration values.
     *
     * @return Definition|DictionaryDefinition
     *
     * @@SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(Configuration $configuration, array $data): Definition
    {
        $name = $data['name'];
        unset($data['name']);
        $data['type'] = 'xliff';

        return new DictionaryDefinition($name, $data);
    }
}
