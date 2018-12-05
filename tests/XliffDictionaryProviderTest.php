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

namespace CyberSpectrum\I18N\Xliff\Test;

use CyberSpectrum\I18N\Dictionary\DictionaryInformation;
use CyberSpectrum\I18N\Exception\DictionaryNotFoundException;
use CyberSpectrum\I18N\Xliff\WritableXliffDictionary;
use CyberSpectrum\I18N\Xliff\XliffDictionary;
use CyberSpectrum\I18N\Xliff\XliffDictionaryProvider;

/**
 * This tests the xliff provider.
 *
 * @covers \CyberSpectrum\I18N\Xliff\XliffDictionaryProvider
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
            'without sub dir'    => [__DIR__ . '/Fixtures/without-subdir', false],
            'with sub dir' => [__DIR__ . '/Fixtures/with-subdir', true],
        ];
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param bool   $subDirs  Flag if sub dirs shall be created.
     *
     * @return void
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testGetAvailableDictionariesFromFixturesDirectory(string $fixtures, bool $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        /** @var DictionaryInformation[] $descriptions */
        $this->assertCount(1, $descriptions = \iterator_to_array($provider->getAvailableDictionaries()));
        $this->assertSame('test1', $descriptions[0]->getName());
        $this->assertSame('en', $descriptions[0]->getSourceLanguage());
        $this->assertSame('de', $descriptions[0]->getTargetLanguage());
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param bool   $subDirs  Flag if sub dirs shall be created.
     *
     * @return void
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testGetFromFixturesDirectory(string $fixtures, bool $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        $this->assertInstanceOf(XliffDictionary::class, $provider->getDictionary('test1', 'en', 'de'));
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param bool   $subDirs  Flag if sub dirs shall be created.
     *
     * @return void
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testThrowsForUnknownDictionary(string $fixtures, bool $subDirs): void
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
     * @param bool   $subDirs  Flag if sub dirs shall be created.
     *
     * @return void
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testGetAvailableWritableDictionariesFromFixturesDirectory(string $fixtures, bool $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        /** @var DictionaryInformation[] $descriptions */
        $this->assertCount(1, $descriptions = \iterator_to_array($provider->getAvailableWritableDictionaries()));
        $this->assertSame('test1', $descriptions[0]->getName());
        $this->assertSame('en', $descriptions[0]->getSourceLanguage());
        $this->assertSame('de', $descriptions[0]->getTargetLanguage());
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param bool   $subDirs  Flag if sub dirs shall be created.
     *
     * @return void
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testGetWritable(string $fixtures, bool $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        $this->assertInstanceOf(WritableXliffDictionary::class, $provider->getDictionaryForWrite('test1', 'en', 'de'));
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param bool   $subDirs  Flag if sub dirs shall be created.
     *
     * @return void
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testThrowsForUnknownDictionaryForWrite(string $fixtures, bool $subDirs): void
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
     * @param bool   $subDirs  Flag if sub dirs shall be created.
     *
     * @return void
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testCreateDictionary(string $fixtures, bool $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        $this->assertInstanceOf(WritableXliffDictionary::class, $provider->createDictionary('create-new', 'en', 'de'));
    }

    /**
     * Test.
     *
     * @param string $fixtures The fixtures directory.
     * @param bool   $subDirs  Flag if sub dirs shall be created.
     *
     * @return void
     *
     * @dataProvider dictionaryProviderProvider
     */
    public function testThrowsForExistingDictionary(string $fixtures, bool $subDirs): void
    {
        $provider = new XliffDictionaryProvider($this->provide($fixtures), $subDirs);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Dictionary test1 already exists.');

        $provider->createDictionary('test1', 'en', 'de');
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testThrowsForUnwritableRootDir(): void
    {
        $provider = new XliffDictionaryProvider('/');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Dictionary root directory is not writable.');

        $provider->createDictionary('test1', 'en', 'de');
    }
}
