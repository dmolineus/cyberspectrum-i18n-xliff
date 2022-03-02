<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff\Test;

use CyberSpectrum\I18N\Dictionary\DictionaryInformation;
use CyberSpectrum\I18N\Exception\DictionaryNotFoundException;
use CyberSpectrum\I18N\Xliff\WritableXliffDictionary;
use CyberSpectrum\I18N\Xliff\XliffDictionary;
use CyberSpectrum\I18N\Xliff\XliffDictionaryProvider;
use InvalidArgumentException;
use RuntimeException;

use function iterator_to_array;

/**
 * @covers \CyberSpectrum\I18N\Xliff\XliffDictionaryProvider
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class XliffDictionaryProviderTest extends TestCase
{
    /**
     * Data provider for dictionary providers
     *
     * @return array
     */
    public function dictionaryProviderProvider(): array
    {
        return [
            'without sub dir'    => [__DIR__ . '/Fixtures/without-subdir', ''],
            'with sub dir' => [__DIR__ . '/Fixtures/with-subdir', '{source}-{target}'],
        ];
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param string $subDirs  The sub directory mask.
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testGetAvailableDictionariesFromFixturesDirectory(string $fixtures, string $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        /** @var DictionaryInformation[] $descriptions */
        self::assertCount(1, $descriptions = iterator_to_array($provider->getAvailableDictionaries()));
        self::assertSame('test1', $descriptions[0]->getName());
        self::assertSame('en', $descriptions[0]->getSourceLanguage());
        self::assertSame('de', $descriptions[0]->getTargetLanguage());
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param string $subDirs  The sub directory mask.
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testGetFromFixturesDirectory(string $fixtures, string $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        self::assertInstanceOf(XliffDictionary::class, $provider->getDictionary('test1', 'en', 'de'));
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param string $subDirs  The sub directory mask.
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testThrowsForUnknownDictionary(string $fixtures, string $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        $this->expectException(DictionaryNotFoundException::class);
        $this->expectExceptionMessage(
            'Dictionary unknown not found (requested source language: "en", requested target language: "de").'
        );

        $provider->getDictionary('unknown', 'en', 'de');
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param string $subDirs  The sub directory mask.
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testGetAvailableWritableDictionariesFromFixturesDirectory(string $fixtures, string $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        /** @var DictionaryInformation[] $descriptions */
        self::assertCount(1, $descriptions = iterator_to_array($provider->getAvailableWritableDictionaries()));
        self::assertSame('test1', $descriptions[0]->getName());
        self::assertSame('en', $descriptions[0]->getSourceLanguage());
        self::assertSame('de', $descriptions[0]->getTargetLanguage());
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param string $subDirs  The sub directory mask.
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testGetWritable(string $fixtures, string $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        self::assertInstanceOf(WritableXliffDictionary::class, $provider->getDictionaryForWrite('test1', 'en', 'de'));
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param string $subDirs  The sub directory mask.
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testThrowsForUnknownDictionaryForWrite(string $fixtures, string $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        $this->expectException(DictionaryNotFoundException::class);
        $this->expectExceptionMessage(
            'Dictionary unknown not found (requested source language: "en", requested target language: "de").'
        );

        $provider->getDictionaryForWrite('unknown', 'en', 'de');
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param string $subDirs  The sub directory mask.
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testCreateDictionary(string $fixtures, string $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        self::assertInstanceOf(WritableXliffDictionary::class, $provider->createDictionary('create-new', 'en', 'de'));
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param string $subDirs  The sub directory mask.
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testThrowsForExistingDictionary(string $fixtures, string $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Dictionary test1 already exists.');

        $provider->createDictionary('test1', 'en', 'de');
    }

    public function testThrowsForUnwritableRootDir(): void
    {
        $provider = new XliffDictionaryProvider('/');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Dictionary root directory is not writable.');

        $provider->createDictionary('test1', 'en', 'de');
    }

    public function testThrowsForNonExistingRootDir(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Root directory does not exist or is not a directory.');

        new XliffDictionaryProvider($this->provide() . DIRECTORY_SEPARATOR . 'does-not-exist');
    }
}
