<?php
declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/composer-plugin.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ComposerPlugin\Installer;

use Composer\Composer;
use Composer\Installer\LibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use TYPO3\CMS\Composer\Plugin\Config;
use Typo3Contentblocks\ComposerPlugin\Configuration\Constants;

class ContentBlockInstaller extends LibraryInstaller
{
    const LABEL = '<info>[ContentBlockInstaller]</info>';

    /**
     * @var string
     */
    protected $cbsDir;

    public function __construct(
        IOInterface $io,
        Composer    $composer,
        Config      $pluginConfig = null
    )
    {
        parent::__construct($io, $composer, Constants::TYPE);

        $rootPackageName = $this->composer->getPackage()->getName();

        // TYPO3 in core-dev (git) mode
        $defaultWebDir = $rootPackageName === 'typo3/cms'
            ? '.'
            : 'public';

        $webDir = $composer->getPackage()->getExtra()['typo3/cms']['web-dir'] ?? $defaultWebDir;
        $this->cbsDir = $webDir . DIRECTORY_SEPARATOR . Constants::BASEPATH;

        // make cbsDir absolute
        if ($rootPackageName !== 'typo3/cms') {
            $pluginConfig = $pluginConfig ?: Config::load($composer);
            $this->cbsDir = $this->filesystem->normalizePath(
                $pluginConfig->get('root-dir') . DIRECTORY_SEPARATOR
                . $webDir . DIRECTORY_SEPARATOR
                . Constants::BASEPATH
            );

            // remove potential double occurrence of webDir
            $this->cbsDir = str_replace($webDir . DIRECTORY_SEPARATOR . $webDir, $webDir, $this->cbsDir);
        }
    }

    public function supports($packageType)
    {
        return $packageType === Constants::TYPE;
    }

    public function getInstallPath(PackageInterface $package)
    {
        [$vendor, $packageName] = explode('/', $package->getPrettyName());
        return $this->cbsDir . $packageName;
    }

    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);

        $this->io->writeError(
            sprintf(
                '    - Target: <comment>%s</comment> %s',
                $this->getInstallPath($package),
                self::LABEL
            )
        );
    }

    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::uninstall($repo, $package);

        $this->unregisterCb($package);
    }

    /**
     * Unlink or remove installation target. We call it "unregistering".
     *
     * @param PackageInterface $package
     */
    public function unregisterCb(PackageInterface $package)
    {
        $installPath = $this->getInstallPath($package);
        $fs = new Filesystem();

        if (!$fs->exists($installPath)) {
            $this->io->writeError(
                sprintf(
                    '    - <comment>%s</comment> is clean %s',
                    $installPath,
                    self::LABEL
                )
            );
            return;
        }

        $this->io->writeError(
            sprintf(
                '    - Removing <comment>%s</comment> %s',
                $installPath,
                self::LABEL
            )
        );

        try {
            $fs->remove($installPath);
        } catch (IOException $e) {
            $this->io->writeError(
                sprintf(
                    '%s <error>%s</error>',
                    self::LABEL,
                    $e->getMessage()
                )
            );
        }
    }
}
