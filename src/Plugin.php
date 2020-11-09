<?php

declare(strict_types=1);

/*
 * This file is part of the package typo3-contentblocks/composer-plugin.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Typo3Contentblocks\ComposerPlugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Typo3Contentblocks\ComposerPlugin\Configuration\Constants;
use Typo3Contentblocks\ComposerPlugin\Installer\ContentBlockInstaller;

class Plugin implements PluginInterface
{
    const LABEL = '<info>[ContentBlocksPlugin]</info>';

    public function activate(Composer $composer, IOInterface $io)
    {
        $io->debug('<comment>Plugin activated</comment> ' . self::LABEL);

        $composer
            ->getInstallationManager()
            ->addInstaller(
                new ContentBlockInstaller($io, $composer)
            );
    }

    public function deactivate(Composer $composer, IOInterface $io)
    {
        $io->debug('<comment>Plugin deactivated</comment> ' . self::LABEL);

        $composer
            ->getInstallationManager()
            ->removeInstaller(
                new ContentBlockInstaller($io, $composer)
            );
    }

    public function uninstall(Composer $composer, IOInterface $io)
    {
        $io->debug('<comment>Plugin uninstalling...</comment> ' . self::LABEL);
    }
}
