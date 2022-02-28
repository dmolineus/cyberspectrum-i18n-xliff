<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff;

use CyberSpectrum\I18N\Dictionary\DictionaryInterface;
use CyberSpectrum\I18N\Exception\TranslationNotFoundException;
use CyberSpectrum\I18N\TranslationValue\TranslationValueInterface;
use CyberSpectrum\I18N\Xliff\Xml\XliffFile;
use Traversable;

use function is_readable;

/**
 * This represents a dictionary that can read and write xliff files.
 */
class XliffDictionary implements DictionaryInterface
{
    /** The XLIFF document. */
    protected XliffFile $xliff;

    /**
     * Create a new instance.
     *
     * @param string|null $filename The filename to use or null when none should be loaded.
     */
    public function __construct(?string $filename = null)
    {
        $this->xliff    = new XliffFile();
        if ($filename && is_readable($filename)) {
            $this->xliff->load($filename);
        }
    }

    public function keys(): Traversable
    {
        return $this->xliff->extractTranslationKeys();
    }

    /**
     * {@inheritDoc}
     *
     * @throws TranslationNotFoundException When the translation unit can not be found in the XLIFF file.
     */
    public function get(string $key): TranslationValueInterface
    {
        if (null === $unit = $this->xliff->searchTranslationUnit($key)) {
            throw new TranslationNotFoundException($key, $this);
        }

        return new XliffTranslationValue($this, $unit);
    }

    public function has(string $key): bool
    {
        return null !== $this->xliff->searchTranslationUnit($key);
    }

    public function getSourceLanguage(): string
    {
        return $this->xliff->getSourceLanguage();
    }

    public function getTargetLanguage(): string
    {
        return $this->xliff->getTargetLanguage();
    }
}
