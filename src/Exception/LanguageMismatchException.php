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

namespace CyberSpectrum\I18N\Xliff\Exception;

/**
 * This is thrown when the languages in a file do not match the expectations.
 */
class LanguageMismatchException extends \RuntimeException
{
    /**
     * The expected source.
     *
     * @var string
     */
    private $expectedSource;

    /**
     * The expected target.
     *
     * @var string
     */
    private $expectedTarget;

    /**
     * The real source.
     *
     * @var string
     */
    private $realSource;

    /**
     * The real target.
     *
     * @var string
     */
    private $realTarget;

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

    /**
     * Retrieve expectedSource.
     *
     * @return string
     */
    public function getExpectedSource(): string
    {
        return $this->expectedSource;
    }

    /**
     * Retrieve expectedTarget.
     *
     * @return mixed
     */
    public function getExpectedTarget(): string
    {
        return $this->expectedTarget;
    }

    /**
     * Retrieve realSource.
     *
     * @return mixed
     */
    public function getRealSource(): string
    {
        return $this->realSource;
    }

    /**
     * Retrieve realTarget.
     *
     * @return mixed
     */
    public function getRealTarget(): string
    {
        return $this->realTarget;
    }
}
