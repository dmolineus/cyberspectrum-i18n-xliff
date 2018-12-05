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

use CyberSpectrum\I18N\Dictionary\DictionaryInterface;
use CyberSpectrum\I18N\Exception\TranslationNotFoundException;
use CyberSpectrum\I18N\TranslationValue\TranslationValueInterface;
use CyberSpectrum\I18N\Xliff\Xml\XliffFile;

/**
 * This represents a dictionary that can read and write xliff files.
 */
class XliffDictionary implements DictionaryInterface
{
    /**
     * The filename to work on.
     *
     * @var string|null
     */
    protected $filename;

    /**
     * The XLIFF document.
     *
     * @var XliffFile
     */
    protected $xliff;

    /**
     * Create a new instance.
     *
     * @param string|null $filename The filename to use or null when none should be loaded.
     */
    public function __construct($filename = null)
    {
        $this->xliff    = new XliffFile();
        $this->filename = $filename;
        if ($this->filename && is_readable($this->filename)) {
            $this->xliff->load($this->filename);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function keys(): \Traversable
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

    /**
     * {@inheritDoc}
     */
    public function has(string $key): bool
    {
        return null !== $this->xliff->searchTranslationUnit($key);
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceLanguage(): string
    {
        return $this->xliff->getSourceLanguage();
    }

    /**
     * {@inheritDoc}
     */
    public function getTargetLanguage(): string
    {
        return $this->xliff->getTargetLanguage();
    }
}
