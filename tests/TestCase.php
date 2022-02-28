<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff\Test;

use Symfony\Component\Filesystem\Filesystem;

/**
 * This provides copying of fixtures.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /** Temp dir name. */
    private ?string $dirName = null;

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
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
