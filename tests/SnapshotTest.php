<?php
/*
 * This file is part of the GlobalState package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\GlobalState;

use ArrayObject;
use PHPUnit_Framework_TestCase;
use SebastianBergmann\GlobalState\TestFixture\SnapshotClass;

/**
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/global-state
 */
class SnapshotTest extends PHPUnit_Framework_TestCase
{
    public function testStaticAttributes()
    {
        $blacklist = $this->getBlacklist();
        $blacklist->method('isStaticAttributeBlacklisted')->willReturnCallback(function ($class) {
            return $class !== 'SebastianBergmann\GlobalState\TestFixture\SnapshotClass';
        });

        SnapshotClass::init();

        $snapshot = new Snapshot($blacklist, false, true, false, false, false, false, false, false, false);
        $expected = array('SebastianBergmann\GlobalState\TestFixture\SnapshotClass' => array(
            'string' => 'snapshot',
            'arrayObject' => new ArrayObject(array(1, 2, 3)),
        ));

        $this->assertEquals($expected, $snapshot->staticAttributes());
    }

    public function testConstants()
    {
        $snapshot = new Snapshot($this->getBlacklist(), false, false, true, false, false, false, false, false, false);
        $this->assertArrayHasKey('GLOBALSTATE_TESTSUITE', $snapshot->constants());
    }

    public function testFunctions()
    {
        require_once __DIR__.'/_fixture/SnapshotFunctions.php';

        $snapshot = new Snapshot($this->getBlacklist(), false, false, false, true, false, false, false, false, false);
        $functions = $snapshot->functions();

        $this->assertContains('sebastianbergmann\globalstate\testfixture\snapshotfunction', $functions);
        $this->assertNotContains('assert', $functions);
    }

    public function testClasses()
    {
        $snapshot = new Snapshot($this->getBlacklist(), false, false, false, false, true, false, false, false, false);
        $classes = $snapshot->classes();

        $this->assertContains('PHPUnit_Framework_TestCase', $classes);
        $this->assertNotContains('Exception', $classes);
    }

    public function testInterfaces()
    {
        $snapshot = new Snapshot($this->getBlacklist(), false, false, false, false, false, true, false, false, false);
        $interfaces = $snapshot->interfaces();

        $this->assertContains('PHPUnit_Framework_Test', $interfaces);
        $this->assertNotContains('Countable', $interfaces);
    }

    /**
     * @requires PHP 5.4
     */
    public function testTraits()
    {
        spl_autoload_call('SebastianBergmann\GlobalState\TestFixture\SnapshotTrait');

        $snapshot = new Snapshot($this->getBlacklist(), false, false, false, false, false, false, true, false, false);
        $this->assertContains('SebastianBergmann\GlobalState\TestFixture\SnapshotTrait', $snapshot->traits());
    }

    public function testIniSettings()
    {
        $snapshot = new Snapshot($this->getBlacklist(), false, false, false, false, false, false, false, true, false);
        $iniSettings = $snapshot->iniSettings();

        $this->assertArrayHasKey('serialize_precision', $iniSettings);
        $this->assertEquals('14', $iniSettings['serialize_precision']);
    }

    public function testIncludedFiles()
    {
        $snapshot = new Snapshot($this->getBlacklist(), false, false, false, false, false, false, false, false, true);
        $this->assertContains(__FILE__, $snapshot->includedFiles());
    }

    /**
     * @return \SebastianBergmann\GlobalState\Blacklist
     */
    private function getBlacklist()
    {
        return $this->getMockBuilder('SebastianBergmann\GlobalState\Blacklist')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
