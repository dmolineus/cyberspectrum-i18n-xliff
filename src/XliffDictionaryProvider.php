<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff;

use CyberSpectrum\I18N\Dictionary\DictionaryInformation;
use CyberSpectrum\I18N\Dictionary\DictionaryInterface;
use CyberSpectrum\I18N\Dictionary\DictionaryProviderInterface;
use CyberSpectrum\I18N\Dictionary\WritableDictionaryInterface;
use CyberSpectrum\I18N\Dictionary\WritableDictionaryProviderInterface;
use CyberSpectrum\I18N\Exception\DictionaryNotFoundException;
use CyberSpectrum\I18N\Xliff\Exception\LanguageMismatchException;
use CyberSpectrum\I18N\Xliff\Xml\XliffFile;
use InvalidArgumentException;
use Iterator;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Traversable;

use function dirname;
use function file_exists;
use function is_dir;
use function is_readable;
use function is_writable;
use function mkdir;
use function realpath;

/**
 * This provides access to the xliff translations in the store.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class XliffDictionaryProvider implements
    DictionaryProviderInterface,
    WritableDictionaryProviderInterface,
    LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** The root directory. */
    private string $rootDir;

    /**
     * The directory mask to use when working with subdirectories.
     *
     * If not empty, translations will get stored in sub directories naming the source an target language.
     * If empty, they will get stored in the root directory.
     */
    private string $subDirectoryMask;

    /**
     * Create a new instance.
     *
     * @param string $rootDir          The root directory.
     * @param string $subDirectoryMask The sub directory mask to apply. Allowed place holders: "{source}" "{target}".
     *
     * @throws InvalidArgumentException When the root dir is invalid.
     */
    public function __construct(
        string $rootDir,
        string $subDirectoryMask = '{source}-{target}'
    ) {
        $rootDir = Path::canonicalize($rootDir);
        if (false === realpath($rootDir) || !is_dir($rootDir)) {
            throw new InvalidArgumentException('Root directory does not exist or is not a directory.');
        }
        $this->rootDir          = realpath($rootDir);
        $this->subDirectoryMask = $subDirectoryMask;
        $this->setLogger(new NullLogger());
    }

    public function getAvailableDictionaries(): Traversable
    {
        foreach ($this->getFinder() as $fileInfo) {
            if ($fileInfo->isReadable()) {
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
     */
    private function getFileNameFor(string $name, string $sourceLanguage, string $targetLanguage): string
    {
        if ('' !== $this->subDirectoryMask) {
            return $this->rootDir . DIRECTORY_SEPARATOR
                . strtr($this->subDirectoryMask, ['{source}' => $sourceLanguage, '{target}' => $targetLanguage])
                . DIRECTORY_SEPARATOR . $name . '.xlf';
        }

        return $this->rootDir . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR . $name . '.xlf';
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
        if ($this->logger) {
            $this->logger->debug('Xliff: opening dictionary ' . $name);
        }
        if (!is_readable($fileName = $this->getFileNameFor($name, $sourceLanguage, $targetLanguage))) {
            throw new DictionaryNotFoundException($name, $sourceLanguage, $targetLanguage);
        }
        $dictionary = new XliffDictionary($fileName);
        $this->guardLanguages($sourceLanguage, $targetLanguage, $dictionary);

        return $dictionary;
    }

    public function getAvailableWritableDictionaries(): Traversable
    {
        foreach ($this->getFinder() as $fileInfo) {
            if ($fileInfo->isWritable()) {
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
        if ($this->logger) {
            $this->logger->debug('Xliff: opening writable dictionary ' . $name);
        }
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
     * @throws InvalidArgumentException When the dictionary already exists.
     * @throws RuntimeException When the dictionary file can not be created.
     */
    public function createDictionary(
        string $name,
        string $sourceLanguage,
        string $targetLanguage,
        array $customData = []
    ): WritableDictionaryInterface {
        if ($this->logger) {
            $this->logger->debug('Xliff: creating new dictionary ' . $name);
        }

        if (file_exists($fileName = $this->getFileNameFor($name, $sourceLanguage, $targetLanguage))) {
            throw new InvalidArgumentException('Dictionary ' . $name . ' already exists.');
        }

        if (!is_writable($this->rootDir)) {
            throw new RuntimeException('Dictionary root directory is not writable.');
        }

        if (!is_dir($dir = dirname($fileName)) && !mkdir($dir) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" could not be created', $dir));
        }

        return new WritableXliffDictionary($fileName, $sourceLanguage, $targetLanguage);
    }

    /**
     * Create an information container for the passed xlf file.
     *
     * @param SplFileInfo $fileInfo The file info of the xlf file.
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
     * @return Iterator<string, SplFileInfo>
     */
    private function getFinder(): Iterator
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
     * @throws LanguageMismatchException When the languages do not match the required language.
     */
    private function guardLanguages(
        string $sourceLanguage,
        string $targetLanguage,
        DictionaryInterface $dictionary
    ): void {
        if (
            $dictionary->getSourceLanguage() !== $sourceLanguage
            || $dictionary->getTargetLanguage() !== $targetLanguage
        ) {
            throw new LanguageMismatchException(
                $sourceLanguage,
                $dictionary->getSourceLanguage(),
                $targetLanguage,
                $dictionary->getTargetLanguage()
            );
        }
    }
}
