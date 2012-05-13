<?php
namespace EasyBib\Core\Composer;

use \Composer\Installer\LibraryInstaller;
use \Composer\Package\PackageInterface;
use \Composer\Downloader\DownloadManager;
use \Composer\IO\IOInterface;

/**
 * @author Till Klampaeckel <till@lagged.biz>
 */
class Zf1ModuleInstaller extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        static $vendorDir;
        if (null === $vendorDir) {
            $vendorDir = $this->getVendorDir();
        }

        $name = $package->getName();
        list($vendor, $module) = explode('/', $name);

        return sprintf('%s/%s', $vendorDir, $module);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return 'zf1-module' === $packageType;
    }

    /**
     * Setup vendor directory to one of these two:
     *  application/modules
     *  app/modules
     *
     * @return string
     * @throws \RuntimeException
     */
    public function getVendorDir()
    {
        $appRoot   = getcwd();
        $vendorDir = '';

        if (is_dir($appRoot . '/application')) {
            $vendorDir .= 'application/modules';
        } elseif (is_dir($appRoot . '/app')) {
            $vendorDir .= 'app/modules';
        }

        if (empty($vendorDir)) {
            throw new \RuntimeException("Could not determine the location of the 'application directory'.");
        }

        if (!is_dir($vendorDir)) {
            throw new \RuntimeException("The 'application directory' does not contain a 'modules' directory.");
        }

        return $vendorDir;
    }
}
