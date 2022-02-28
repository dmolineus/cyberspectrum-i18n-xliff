<?php

declare(strict_types=1);

namespace CyberSpectrum\I18N\Xliff\Exception;

/**
 * This is thrown when the languages in a file do not match the expectations.
 */
class LanguageMismatchException extends \RuntimeException
{
    /** The expected source. */
    private string $expectedSource;

    /** The expected target. */
    private string $expectedTarget;

    /** The real source. */
    private string $realSource;

    /** The real target. */
    private string $realTarget;

    /**
     * Create a new instance.
     *
     * @param string $expectedSource The expected source.
     * @param string $expectedTarget The expected target.
     * @param string $realSource     The real source.
     * @param string $realTarget     The real target.
     */
    public function __construct(string $expectedSource, string $expectedTarget, string $realSource, string $realTarget)
    {
        $this->expectedSource = $expectedSource;
        $this->expectedTarget = $expectedTarget;
        $this->realSource     = $realSource;
        $this->realTarget     = $realTarget;
        parent::__construct(sprintf(
            'Languages do not match - source should be "%s" and is "%s", destination should be "%s" and is "%s"',
            $expectedSource,
            $realSource,
            $expectedTarget,
            $realTarget
        ));
    }

    /** Retrieve expected source. */
    public function getExpectedSource(): string
    {
        return $this->expectedSource;
    }

    /** Retrieve expected target. */
    public function getExpectedTarget(): string
    {
        return $this->expectedTarget;
    }

    /** Retrieve real source. */
    public function getRealSource(): string
    {
        return $this->realSource;
    }

    /** Retrieve real target. */
    public function getRealTarget(): string
    {
        return $this->realTarget;
    }
}
