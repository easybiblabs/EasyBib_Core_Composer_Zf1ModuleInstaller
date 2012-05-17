<?php
namespace EasyBib\Core\Composer\Test;

use EasyBib\Core\Composer\Zf1ModuleInstaller;
use Composer\Downloader\DownloadManager;
use Composer\Downloader\SvnDownloader;
use Composer\IO\NullIO;
use Composer\Package\MemoryPackage;
use Composer\Repository\ArrayRepository;
use Composer\Repository\InstalledArrayRepository;

/**
 * @author Till Klampaeckel <till@lagged.biz>
 */
class Zf1ModuleInstallerTestCase extends \PHPUnit_Framework_TestCase
{
    protected $fixtureDir;

    public function setUp()
    {
        $this->fixtureDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/fixtures';
    }

    public static function envProvider()
    {
        return array(
            array('test1', 'app/modules'),
            array('test2', 'application/modules'),
        );
    }

    /**
     * @dataProvider envProvider
     */
    public function testCorrectVendorDir($env, $assertion)
    {
        $currentCwd = getcwd();
        chdir($this->fixtureDir . '/' . $env);

        $installer = new Zf1ModuleInstaller(
            'vendor',
            'bin',
            new DownloadManager(),
            new NullIO()
        );
        $this->assertEquals($assertion, $installer->getVendorDir());

        chdir($currentCwd);
    }

    public static function moduleProvider()
    {
        return array(
            array('easybib-foo/default', 'default'),
            array('foo/Bar', 'bar'),
        );
    }

    /**
     * @dataProvider moduleProvider
     */
    public function testEnsureVendorIsStrippedFromInstallPath($dep, $module)
    {
        $currentCwd = getcwd();
        chdir($this->fixtureDir . '/test1');

        $installer = new Zf1ModuleInstaller(
            'vendor',
            'bin',
            new DownloadManager(),
            new NullIO()
        );

        $package = new MemoryPackage($dep, '1.0.0', '1.0.0-stable');

        $this->assertEquals('app/modules/' . $module, $installer->getInstallPath($package));

        chdir($currentCwd);
    }

    /**
     * The objective of this test is to confirm that the following works:
     *
     * 1) A SVN repository is used which contains a complete ZendFramework application.
     * 2) We are trying to detect the application structure (application/modules or app/modules).
     * 3) When detected, we adjust the check out URL so only the module is checked out.
     *
     * @return void
     */
    public function testInstallTransformations()
    {
        $this->markTestIncomplete("Needs some sort of mocking (works with real server and credentials).");

        $currentCwd = getcwd();
        chdir($this->fixtureDir . '/test1');

        $io = new NullIO();

        $downloadManager = new DownloadManager();
        $downloadManager->setDownloader('svn', new SvnDownloader($io));

        $installer = new Zf1ModuleInstaller(
            'vendor',
            'bin',
            $downloadManager,
            $io
        );

        /**
         * @desc Build up a package to run this test against!
         */
        $package = new MemoryPackage('company/module-name', '1.3.6', '1.3.6');
        $package->setSourceUrl('http://svn.company.tld/svn/project');
        $package->setSourceReference('tags/1.3.6');
        $package->setSourceType('svn');

        $localRepository = new InstalledArrayRepository();

        $installer->install($localRepository, $package);

        $this->assertTrue(is_dir('app/modules/module-name'));
        `rm -rf app/modules/module-name`;

        chdir($currentCwd);
    }
}