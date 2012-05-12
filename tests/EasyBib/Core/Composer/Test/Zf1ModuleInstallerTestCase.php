<?php
namespace EasyBib\Core\Composer\Test;

use EasyBib\Core\Composer\Zf1ModuleInstaller;
use Composer\Downloader\DownloadManager;
use Composer\IO\NullIO;
use Composer\Package\MemoryPackage;

/**
 * @author Till Klampaeckel <till@lagged.biz>
 */
class Zf1ModuleInstallerTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testRuntimeException()
    {
        new Zf1ModuleInstaller(
            'vendor',
            'bin',
            new DownloadManager(),
            new NullIO()
        );
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
        $fixtureDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/fixtures';

        $currentCwd = getcwd();
        chdir($fixtureDir . '/' . $env);

        $installer = new Zf1ModuleInstaller(
            'vendor',
            'bin',
            new DownloadManager(),
            new NullIO()
        );
        $this->assertEquals($assertion, $installer->getVendorDir());

        chdir($currentCwd);
    }

    public function testEnsureVendorIsStrippedFromInstallPath()
    {
        $fixtureDir = dirname(dirname(dirname(dirname(__DIR__)))) . '/fixtures';

        $currentCwd = getcwd();
        chdir($fixtureDir . '/test1');

        $installer = new Zf1ModuleInstaller(
            'vendor',
            'bin',
            new DownloadManager(),
            new NullIO()
        );

        $package = new MemoryPackage('easybib-foo/default', '1.0.0', '1.0.0-stable');

        $this->assertEquals('app/modules/default', $installer->getInstallPath($package));

        chdir($currentCwd);
    }
}