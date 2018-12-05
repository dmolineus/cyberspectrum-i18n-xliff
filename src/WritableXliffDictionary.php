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

use CyberSpectrum\I18N\Dictionary\BufferedWritableDictionaryInterface;
use CyberSpectrum\I18N\Dictionary\WritableDictionaryInterface;
use CyberSpectrum\I18N\Exception\NotSupportedException;
use CyberSpectrum\I18N\Exception\TranslationAlreadyContainedException;
use CyberSpectrum\I18N\Exception\TranslationNotFoundException;
use CyberSpectrum\I18N\TranslationValue\WritableTranslationValueInterface;

/**
 * This represents a dictionary that can read and write xliff files.
 */
class WritableXliffDictionary extends XliffDictionary implements
    WritableDictionaryInterface,
    BufferedWritableDictionaryInterface
{
    /**
     * Flag if the contents have been changed.
     *
     * @var bool
     */
    private $changed = false;

    /**
     * Flag if the dictionary is already buffering.
     *
     * @var bool
     */
    private $buffering = false;

    /**
     * Create a new instance.
     *
     * @param string $filename       The filename to use or null when none should be loaded.
     * @param string $sourceLanguage The source language.
     * @param string $targetLanguage The destination language.
     *
     * @throws NotSupportedException When the file is not writable.
     */
    public function __construct($filename, string $sourceLanguage = null, string $targetLanguage = null)
    {
        if (!is_writable($filename) && !(!file_exists($filename) && is_writable(\dirname($filename)))) {
            throw new NotSupportedException($this, 'File is not writable: ' . $filename);
        }

        parent::__construct($filename);

        if (!file_exists($filename)) {
            if ($sourceLanguage) {
                $this->setSourceLanguage($sourceLanguage);
            }
            if ($targetLanguage) {
                $this->setTargetLanguage($targetLanguage);
            }
            $this->markChanged();
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws TranslationAlreadyContainedException When the translation is already contained.
     */
    public function add(string $key): WritableTranslationValueInterface
    {
        if ($this->xliff->searchTranslationUnit($key)) {
            throw new TranslationAlreadyContainedException($key, $this);
        }

        $this->markChanged();

        return new WritableXliffTranslationValue($this, $this->xliff->createTranslationUnit($key));
    }

    /**
     * {@inheritDoc}
     *
     * @throws TranslationNotFoundException When the translation unit can not be found.
     */
    public function remove(string $key): void
    {
        if (null === $unit = $this->xliff->searchTranslationUnit($key)) {
            throw new TranslationNotFoundException($key, $this);
        }
        $unit->parentNode->removeChild($unit);

        $this->markChanged();
    }

    /**
     * {@inheritDoc}
     *
     * @throws TranslationNotFoundException When the translation unit can not be found.
     */
    public function getWritable($key): WritableTranslationValueInterface
    {
        if (null === $unit = $this->xliff->searchTranslationUnit($key)) {
            throw new TranslationNotFoundException($key, $this);
        }

        return new WritableXliffTranslationValue($this, $unit);
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException When already buffering.
     */
    public function beginBuffering(): void
    {
        if ($this->buffering) {
            throw new \RuntimeException('Already buffering.');
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException When the dictionary is not currently buffering.
     */
    public function commitBuffer(): void
    {
        if (!$this->buffering) {
            throw new \RuntimeException('Not buffering.');
        }
        $this->buffering = false;
        if ($this->changed) {
            $this->xliff->save($this->filename);
            $this->changed = false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isBuffering(): bool
    {
        return $this->buffering;
    }

    /**
     * Mark the file as changed.
     *
     * @return void
     *
     * @internal Should only be called from Xliff dictionary classes.
     */
    public function markChanged(): void
    {
        $this->changed = true;
        $this->xliff->setDate(new \DateTime());
        if (!$this->buffering) {
            $this->xliff->save($this->filename);
        }
    }

    /**
     * Set the source language.
     *
     * @param string $language The language.
     *
     * @return void
     */
    public function setSourceLanguage(string $language): void
    {
        $this->xliff->setSourceLanguage($language);
        $this->markChanged();
    }

    /**
     * Set the source language.
     *
     * @param string $language The language.
     *
     * @return void
     */
    public function setTargetLanguage(string $language): void
    {
        $this->xliff->setTargetLanguage($language);
        $this->markChanged();
    }

    /**
     * Set the original.
     *
     * @param string $source The original.
     *
     * @return void
     */
    public function setOriginal(string $source): void
    {
        $this->xliff->setOriginal($source);
        $this->markChanged();
    }
}
