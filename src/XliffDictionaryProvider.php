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

use CyberSpectrum\I18N\Dictionary\DictionaryInformation;
use CyberSpectrum\I18N\Dictionary\DictionaryInterface;
use CyberSpectrum\I18N\Dictionary\DictionaryProviderInterface;
use CyberSpectrum\I18N\Dictionary\WritableDictionaryInterface;
use CyberSpectrum\I18N\Dictionary\WritableDictionaryProviderInterface;
use CyberSpectrum\I18N\Exception\DictionaryNotFoundException;
use CyberSpectrum\I18N\Xliff\Exception\LanguageMismatchException;
use CyberSpectrum\I18N\Xliff\Xml\XliffFile;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\PathUtil\Path;

/**
 * This provides access to the xliff translations in the store.
 */
class XliffDictionaryProvider implements
    DictionaryProviderInterface,
    WritableDictionaryProviderInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * The root directory.
     *
     * @var string
     */
    private $rootDir;

    /**
     * Flag if translations shall be stored in sub directories.
     *
     * If true, translations will get stored in sub directories naming the source an target language, if false they will
     * all get stored in the root.
     *
     * @var bool
     */
    private $subDirectories;

    /**
     * Create a new instance.
     *
     * @param string $rootDir        The root directory.
     * @param bool   $subDirectories Flag if sub directories per language shall be created.
     *
     * @throws \InvalidArgumentException When the root dir is invalid.
     */
    public function __construct(string $rootDir, bool $subDirectories = true)
    {
        $rootDir = Path::canonicalize($rootDir);
        if (false === realpath($rootDir) || !is_dir($rootDir)) {
            throw new \InvalidArgumentException('Root directory does not exist or is not a directory.');
        }
        $this->rootDir        = realpath($rootDir);
        $this->subDirectories = $subDirectories;
        $this->setLogger(new NullLogger());
    }

    /**
     * {@inheritDoc}
     *
     * @return \Traversable|DictionaryInformation[]
     */
    public function getAvailableDictionaries(): \Traversable
    {
        foreach ($this->getFinder() as $fileInfo) {
            /** @var SplFileInfo $fileInfo */
            if (is_readable($fileInfo->getPathname())) {
                yield $this->createInformation($fileInfo);
            }
        }
    }

    /**
     * Generate the file name for a dictionary.
     *
     * @param string $name           The name of the dictionary.
     * @param string $sourceLanguage The source language.
     * @param string $targetLanguage The target language.
     *
     * @return string
     */
    private function getFileNameFor(string $name, string $sourceLanguage, string $targetLanguage): string
    {
        return $this->rootDir . DIRECTORY_SEPARATOR . ($this->subDirectories
            ? $sourceLanguage . '-' . $targetLanguage . DIRECTORY_SEPARATOR
            : DIRECTORY_SEPARATOR) . $name . '.xlf';
    }

    /**
     * {@inheritDoc}
     *
     * @throws DictionaryNotFoundException When the file can not be found.
     */
    public function getDictionary(
        string $name,
        string $sourceLanguage,
        string $targetLanguage,
        array $customData = []
    ): DictionaryInterface {
        $this->logger->debug('Xliff: opening dictionary ' . $name);
        if (!is_readable($fileName = $this->getFileNameFor($name, $sourceLanguage, $targetLanguage))) {
            throw new DictionaryNotFoundException($name, $sourceLanguage, $targetLanguage);
        }
        $dictionary = new XliffDictionary($fileName);
        $this->guardLanguages($sourceLanguage, $targetLanguage, $dictionary);

        return $dictionary;
    }

    /**
     * {@inheritDoc}
     */
    public function getAvailableWritableDictionaries(): \Traversable
    {
        foreach ($this->getFinder() as $fileInfo) {
            if (is_writable($fileInfo->getPathname())) {
                yield $this->createInformation($fileInfo);
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws DictionaryNotFoundException When the file can not be found.
     */
    public function getDictionaryForWrite(
        string $name,
        string $sourceLanguage,
        string $targetLanguage,
        array $customData = []
    ): WritableDictionaryInterface {
        $this->logger->debug('Xliff: opening writable dictionary ' . $name);
        if (!file_exists($fileName = $this->getFileNameFor($name, $sourceLanguage, $targetLanguage))) {
            throw new DictionaryNotFoundException($name, $sourceLanguage, $targetLanguage);
        }
        $dictionary = new WritableXliffDictionary($fileName);
        $dictionary->setOriginal($name);
        $this->guardLanguages($sourceLanguage, $targetLanguage, $dictionary);

        return $dictionary;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \InvalidArgumentException When the dictionary already exists.
     * @throws \RuntimeException When the dictionary file can not be created.
     */
    public function createDictionary(
        string $name,
        string $sourceLanguage,
        string $targetLanguage,
        array $customData = []
    ): WritableDictionaryInterface {
        $this->logger->debug('Xliff: creating new dictionary ' . $name);
        if (file_exists($fileName = $this->getFileNameFor($name, $sourceLanguage, $targetLanguage))) {
            throw new \InvalidArgumentException('Dictionary ' . $name . ' already exists.');
        }

        if (!is_writable($this->rootDir)) {
            throw new \RuntimeException('Dictionary root directory is not writable.');
        }

        if (!is_dir($dir = \dirname($fileName)) && !mkdir($dir) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" could not be created', $dir));
        }

        return new WritableXliffDictionary($fileName, $sourceLanguage, $targetLanguage);
    }

    /**
     * Create an information container for the passed xlf file.
     *
     * @param SplFileInfo $fileInfo The file info of the xlf file.
     *
     * @return DictionaryInformation
     */
    private function createInformation(SplFileInfo $fileInfo): DictionaryInformation
    {
        $tmpFile = new XliffFile();
        $tmpFile->load($fileInfo->getPathname());

        return new DictionaryInformation(
            substr($fileInfo->getFilename(), 0, -4),
            $tmpFile->getSourceLanguage(),
            $tmpFile->getTargetLanguage()
        );
    }

    /**
     * Create a finder instance.
     *
     * @return \Iterator
     */
    private function getFinder(): \Iterator
    {
        return Finder::create()
            ->in($this->rootDir)
            ->ignoreUnreadableDirs()
            ->files()
            ->name('*.xlf')
            ->getIterator();
    }

    /**
     * Guard the language.
     *
     * @param string              $sourceLanguage The source language.
     * @param string              $targetLanguage The target language.
     * @param DictionaryInterface $dictionary     The dictionary.
     *
     * @return void
     *
     * @throws LanguageMismatchException When the languages do not match the required language.
     */
    private function guardLanguages(
        string $sourceLanguage,
        string $targetLanguage,
        DictionaryInterface $dictionary
    ): void {
        if ($dictionary->getSourceLanguage() !== $sourceLanguage
            || $dictionary->getTargetLanguage() !== $targetLanguage) {
            throw new LanguageMismatchException(
                $sourceLanguage,
                $dictionary->getSourceLanguage(),
                $targetLanguage,
                $dictionary->getTargetLanguage()
            );
        }
    }
}
