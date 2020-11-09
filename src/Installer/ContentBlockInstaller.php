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
use Composer\Util\Silencer;

class ContentBlockInstaller extends LibraryInstaller
{
    const BASEPATH = 'typo3conf/contentBlocks/';
    const LABEL = '<info>[ContentBlockInstaller]</info>';
    const TYPE = 'typo3-cms-contentblock';

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
        parent::__construct($io, $composer, self::TYPE);

        $this->io = $io;
        $webDir = $composer->getPackage()->getExtra()['typo3/cms']['web-dir'] ?? 'public';
        $this->cbsDir = $webDir . DIRECTORY_SEPARATOR . self::BASEPATH;
    }

    public function supports($packageType)
    {
        return $packageType === self::TYPE;
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
                '    - Target: <comment>%s</comment> ' . self::LABEL,
                $this->getInstallPath($package)
            )
        );
    }

    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::uninstall($repo, $package);

        $installPath = $this->getInstallPath($package);
        if (is_link($installPath)) {
            $this->io->writeError(
                sprintf(
                    '    - Removing symlink <comment>%s</comment> ' . self::LABEL,
                    $installPath
                )
            );
            Silencer::call('unlink', $installPath);
        } else {
            $this->io->writeError(
                sprintf(
                    '    - Removing directory <comment>%s</comment> ' . self::LABEL,
                    $installPath
                )
            );
            Silencer::call('rmdir', $installPath);
        }
    }
}
