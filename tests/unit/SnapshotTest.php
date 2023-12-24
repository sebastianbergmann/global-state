<?php declare(strict_types=1);
/*
 * This file is part of sebastian/global-state.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\GlobalState;

use function get_declared_classes;
use function spl_autoload_call;
use Countable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\GlobalState\TestFixture\ExcludedClass;
use SebastianBergmann\GlobalState\TestFixture\ExcludedInterface;
use SebastianBergmann\GlobalState\TestFixture\SnapshotClass;
use SebastianBergmann\GlobalState\TestFixture\SnapshotClassTyped;
use SebastianBergmann\GlobalState\TestFixture\SnapshotTrait;
use stdClass;

#[CoversClass(Snapshot::class)]
#[UsesClass(ExcludeList::class)]
final class SnapshotTest extends TestCase
{
    private ExcludeList $excludeList;

    protected function setUp(): void
    {
        $this->excludeList = new ExcludeList;
    }

    public function testStaticAttributes(): void
    {
        SnapshotClass::init();

        $this->excludeAllLoadedClassesExceptClass(SnapshotClass::class);

        $snapshot = new Snapshot($this->excludeList, false, true, false, false, false, false, false, false, false);

        $expected = [
            SnapshotClass::class => [
                'string'  => 'string',
                'objects' => [new stdClass],
            ],
        ];

        $this->assertEquals($expected, $snapshot->staticProperties());
    }

    public function testStaticNotInitialisedAttributes(): void
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        new SnapshotClassTyped;

        $this->excludeAllLoadedClassesExceptClass(SnapshotClassTyped::class);

        $snapshot = new Snapshot($this->excludeList, false, true, false, false, false, false, false, false, false);

        $expected = [
            SnapshotClassTyped::class => [
                'bool' => true,
            ],
        ];

        $this->assertEquals($expected, $snapshot->staticProperties());
    }

    public function testStaticInitialisedAttributes(): void
    {
        SnapshotClassTyped::init();

        $this->excludeAllLoadedClassesExceptClass(SnapshotClassTyped::class);

        $snapshot = new Snapshot($this->excludeList, false, true, false, false, false, false, false, false, false);

        $expected = [
            SnapshotClassTyped::class => [
                'bool'   => true,
                'string' => 'string',
            ],
        ];

        $this->assertEquals($expected, $snapshot->staticProperties());
    }

    public function testConstructorExcludesAspectsWhenTheyShouldNotBeIncluded(): void
    {
        $snapshot = new Snapshot(
            $this->excludeList,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
            false,
        );

        $this->assertEmpty($snapshot->constants());
        $this->assertEmpty($snapshot->functions());
        $this->assertEmpty($snapshot->globalVariables());
        $this->assertEmpty($snapshot->includedFiles());
        $this->assertEmpty($snapshot->iniSettings());
        $this->assertEmpty($snapshot->interfaces());
        $this->assertEmpty($snapshot->staticProperties());
        $this->assertEmpty($snapshot->superGlobalArrays());
        $this->assertEmpty($snapshot->superGlobalVariables());
        $this->assertEmpty($snapshot->traits());
    }

    public function testExcludeListCanBeAccessed(): void
    {
        $snapshot = new Snapshot(
            $this->excludeList,
            false,
            false,
            true,
            false,
            false,
            false,
            false,
            false,
            false,
        );

        $this->assertSame($this->excludeList, $snapshot->excludeList());
    }

    public function testConstants(): void
    {
        $snapshot = new Snapshot($this->excludeList, false, false, true, false, false, false, false, false, false);

        $this->assertArrayHasKey('GLOBALSTATE_TESTSUITE', $snapshot->constants());
    }

    public function testFunctions(): void
    {
        $snapshot  = new Snapshot($this->excludeList, false, false, false, true, false, false, false, false, false);
        $functions = $snapshot->functions();

        $this->assertContains('sebastianbergmann\globalstate\testfixture\snapshotfunction', $functions);
        $this->assertNotContains('assert', $functions);
    }

    public function testClasses(): void
    {
        $snapshot = new Snapshot($this->excludeList, false, false, false, false, true, false, false, false, false);
        $classes  = $snapshot->classes();

        $this->assertContains(TestCase::class, $classes);
        $this->assertNotContains(Exception::class, $classes);
    }

    public function testInterfaces(): void
    {
        /* @noinspection PhpExpressionResultUnusedInspection */
        new ExcludedClass;

        $snapshot   = new Snapshot($this->excludeList, false, false, false, false, false, true, false, false, false);
        $interfaces = $snapshot->interfaces();

        $this->assertContains(ExcludedInterface::class, $interfaces);
        $this->assertNotContains(Countable::class, $interfaces);
    }

    public function testTraits(): void
    {
        spl_autoload_call('SebastianBergmann\GlobalState\TestFixture\SnapshotTrait');

        $snapshot = new Snapshot($this->excludeList, false, false, false, false, false, false, true, false, false);

        $this->assertContains(SnapshotTrait::class, $snapshot->traits());
    }

    public function testIniSettings(): void
    {
        $snapshot    = new Snapshot($this->excludeList, false, false, false, false, false, false, false, true, false);
        $iniSettings = $snapshot->iniSettings();

        $this->assertArrayHasKey('date.timezone', $iniSettings);
        $this->assertEquals('Etc/UTC', $iniSettings['date.timezone']);
    }

    public function testIncludedFiles(): void
    {
        $snapshot = new Snapshot($this->excludeList, false, false, false, false, false, false, false, false, true);
        $this->assertContains(__FILE__, $snapshot->includedFiles());
    }

    private function excludeAllLoadedClassesExceptClass(string $excludedClass): void
    {
        foreach (get_declared_classes() as $class) {
            if ($class === $excludedClass) {
                continue;
            }

            $this->excludeList->addClass($class);
        }
    }
}
