<?php
namespace EasyBib\Core\Composer;

use \Composer\Installer\LibraryInstaller;
use \Composer\Package\PackageInterface;
use \Composer\Downloader\DownloadManager;
use \Composer\IO\IOInterface;
use \Composer\Repository\InstalledRepositoryInterface;
use \Composer\Util\Svn as SvnUtil;

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
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        if ($package->getSourceType() !== 'svn') {
            return parent::install($repo, $package);
        }

        /**
         * Check if the SVN repo contains the entire application structure and try to extract
         * only the module from it!
         *
         * This is very ugly, please cover your eyes!
         */
        $sourceUrl = $package->getSourceUrl();
        $sourceRef = $package->getSourceReference();

		/**
		 * dev- is a branch, likely a weird name with foo@rev. Let's parse out 'rev'. [fix later]
		 */
		if (0 === strpos($package->getPrettyVersion(), 'dev-')) {
            if (false !== strpos($sourceRef, '@')) {
                list($trunk, $rev) = explode('@', $sourceRef);
                if (empty($trunk)) {
                    throw new \DomainException(sprintf(
                        "Could not parse sourceRef '%s' for package '%'",
                        $sourceRef, $package->getName()));
                }
                $sourceRef = $trunk;
            }
        }

        if (substr($sourceRef, 0, 1) == '/') {
            $sourceRef = substr($sourceRef, 1);
        }
        $baseUrl = sprintf('%s/%s', $sourceUrl, $sourceRef);

        $svnUtil = new SvnUtil(
            $baseUrl,
            $this->io
        );

        /**
         * @desc Whatever could be the structure - in most cases application/modules.
         */
        static $appDirs   = array('application', 'app');
        static $moduleDir = 'modules';

        foreach ($appDirs as $appDir) {
            try {
                $output = $svnUtil->execute(
                    'svn ls',
                    sprintf('%s/%s', $baseUrl, $appDir),
                    null,
                    null,
                    $this->io->isVerbose()
                );
                if (false === strstr($output, sprintf('%s/', $moduleDir))) {
                    continue;
                }

                // success! - adjust the checkout url

                list($vendor, $packageName) = explode('/', $package->getName());
                if (true === $this->io->isVerbose()) {
                    $this->io->write(sprintf("Transforming checkout to retrieve module '%s' only.", $packageName));
                }

                // potential 'Problem?', check if it's always MemoryPackage
                $package->setSourceReference(sprintf('%s/%s/%s/%s', $sourceRef, $appDir, $moduleDir, $packageName));
                break;

            } catch (\RuntimeException $e) {
                if (false === strpos($e->getMessage(), 'non-existent')) {
                    throw $e;
                }
                // 404 ends up here
                continue;
            }
        }

        return parent::install($repo, $package);
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
