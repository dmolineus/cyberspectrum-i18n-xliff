<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff;

use CyberSpectrum\I18N\Configuration\Configuration;
use CyberSpectrum\I18N\Configuration\Definition\Definition;
use CyberSpectrum\I18N\Configuration\Definition\DictionaryDefinition;
use CyberSpectrum\I18N\Configuration\DefinitionBuilder\DefinitionBuilderInterface;
use InvalidArgumentException;

/**
 * Builds xliff dictionary definitions.
 *
 * @psalm-type TXliffDictionaryConfigurationArray=array{
 *   name: string,
 * }
 */
class XliffDictionaryDefinitionBuilder implements DefinitionBuilderInterface
{
    /**
     * {@inheritDoc}
     */
    public function build(Configuration $configuration, array $data): Definition
    {
        $this->checkConfiguration($data);
        $name = $data['name'];
        unset($data['name']);
        $data['type'] = 'xliff';

        return new DictionaryDefinition($name, $data);
    }

    /** @psalm-assert TXliffDictionaryConfigurationArray $data */
    private function checkConfiguration(array $data): void
    {
        if (!array_key_exists('name', $data)) {
            throw new InvalidArgumentException('Missing key \'name\'');
        }
        if (!is_string($data['name'])) {
            throw new InvalidArgumentException('\'name\' must be a string');
        }
    }
}
