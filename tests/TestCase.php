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

use Symfony\Component\Filesystem\Filesystem;

/**
 * This provides copying of fixtures.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Temp dir name.
     *
     * @var string
     */
    private $dirName;

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
        if (null !== $this->dirName && is_dir($this->dirName)) {
            $fileSystem = new Filesystem();
            $fileSystem->remove($this->dirName);
        }
    }

    /**
     * Provide the passed fixtures directory.
     *
     * @param string|null $dirName The fixtures directory.
     *
     * @return string
     *
     * @throws \LogicException When a temp dir has already been created.
     */
    protected function provide(string $dirName = null): string
    {
        if (null !== $this->dirName) {
            if (null !== $dirName) {
                throw new \LogicException('Temp dir has already been created, can not provision fixtures');
            }
            return $this->dirName;
        }

        $this->dirName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('translation-test-', false);
        $fileSystem    = new Filesystem();

        if (null === $dirName) {
            $fileSystem->mkdir($this->dirName);

            return $this->dirName;
        }

        $fileSystem->mirror($dirName, $this->dirName);

        return $this->dirName;
    }

    /**
     * Obtain the path to a temp file.
     *
     * @return string
     */
    protected function getTempFile(): string
    {
        return $this->provide() . DIRECTORY_SEPARATOR . uniqid('translation-test-', false) . '.xlf';
    }
}
