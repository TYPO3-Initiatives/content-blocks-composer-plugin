<?php declare(strict_types=1);

/*
 * This file is part of the package sci/content-blocks-composer-plugin.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Sci\ContentBlocksComposerPlugin\Installer;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;

class ContentBlockInstaller extends LibraryInstaller
{
    const BASEPATH = 'typo3conf/contentBlocks/';

    /**
     * @var string
     */
    protected $cbsDir;

    /**
     * @var IOInterface
     */
    protected $io;

    public function __construct(
        IOInterface $io,
        Composer $composer
    ) {
        parent::__construct($io, $composer, 'typo3-cms-contentblock');

        $this->io = $io;

        $reflectionClass = new \ReflectionClass($composer->getConfig());
        $reflectionProperty = $reflectionClass->getProperty('baseDir');
        $reflectionProperty->setAccessible(true);

        $baseDir = $reflectionProperty->getValue($composer->getConfig());
        $rootPackage = $composer->getPackage();

        $rootPackageExtraConfig = $rootPackage->getExtra() ?: [];
        $typo3Config = $rootPackageExtraConfig['typo3/cms'] ?? [];

        $webDir = $typo3Config['web-dir'] ?? 'public';

        $this->cbsDir = $baseDir . DIRECTORY_SEPARATOR . $webDir . DIRECTORY_SEPARATOR . self::BASEPATH;
    }

    public function supports($packageType)
    {
        return $packageType === 'typo3-cms-contentblock';
    }

    public function getInstallPath(PackageInterface $package)
    {
        [$vendor, $packageName] = explode('/', $package->getPrettyName());

        $cbDir = $this->cbsDir . $packageName;
        $this->io->info(sprintf('Installing ‹%s› into ‹%s›', $package->getPrettyName(), $cbDir));

        return $cbDir;
    }
}
