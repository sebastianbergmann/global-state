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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\GlobalState\TestFixture\ExcludedChildClass;
use SebastianBergmann\GlobalState\TestFixture\ExcludedClass;
use SebastianBergmann\GlobalState\TestFixture\ExcludedImplementor;
use SebastianBergmann\GlobalState\TestFixture\ExcludedInterface;

#[CoversClass(ExcludeList::class)]
final class ExcludeListTest extends TestCase
{
    private ExcludeList $excludeList;

    protected function setUp(): void
    {
        $this->excludeList = new ExcludeList;
    }

    public function testGlobalVariableThatIsNotExcludedIsNotTreatedAsExcluded(): void
    {
        $this->assertFalse($this->excludeList->isGlobalVariableExcluded('variable'));
    }

    public function testGlobalVariableCanBeExcluded(): void
    {
        $this->excludeList->addGlobalVariable('variable');

        $this->assertTrue($this->excludeList->isGlobalVariableExcluded('variable'));
    }

    public function testStaticPropertyThatIsNotExcludedIsNotTreatedAsExcluded(): void
    {
        $this->assertFalse(
            $this->excludeList->isStaticPropertyExcluded(
                ExcludedClass::class,
                'property',
            ),
        );
    }

    public function testClassCanBeExcluded(): void
    {
        $this->excludeList->addClass(ExcludedClass::class);

        $this->assertTrue(
            $this->excludeList->isStaticPropertyExcluded(
                ExcludedClass::class,
                'property',
            ),
        );
    }

    public function testSubclassesCanBeExcluded(): void
    {
        $this->excludeList->addSubclassesOf(ExcludedClass::class);

        $this->assertTrue(
            $this->excludeList->isStaticPropertyExcluded(
                ExcludedChildClass::class,
                'property',
            ),
        );
    }

    public function testImplementorsCanBeExcluded(): void
    {
        $this->excludeList->addImplementorsOf(ExcludedInterface::class);

        $this->assertTrue(
            $this->excludeList->isStaticPropertyExcluded(
                ExcludedImplementor::class,
                'property',
            ),
        );
    }

    public function testClassNamePrefixesCanBeExcluded(): void
    {
        $this->excludeList->addClassNamePrefix('SebastianBergmann\GlobalState');

        $this->assertTrue(
            $this->excludeList->isStaticPropertyExcluded(
                ExcludedClass::class,
                'property',
            ),
        );
    }

    public function testStaticPropertyCanBeExcluded(): void
    {
        $this->excludeList->addStaticProperty(
            ExcludedClass::class,
            'property',
        );

        $this->assertTrue(
            $this->excludeList->isStaticPropertyExcluded(
                ExcludedClass::class,
                'property',
            ),
        );
    }
}
