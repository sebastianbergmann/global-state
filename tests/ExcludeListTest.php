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

use PHPUnit\Framework\TestCase;
use SebastianBergmann\GlobalState\TestFixture\ExcludedChildClass;
use SebastianBergmann\GlobalState\TestFixture\ExcludedClass;
use SebastianBergmann\GlobalState\TestFixture\ExcludedImplementor;
use SebastianBergmann\GlobalState\TestFixture\ExcludedInterface;

/**
 * @covers \SebastianBergmann\GlobalState\ExcludeList
 */
final class ExcludeListTest extends TestCase
{
    /**
     * @var \SebastianBergmann\GlobalState\ExcludeList
     */
    private $excludeList;

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

    public function testStaticAttributeThatIsNotExcludedIsNotTreatedAsExcluded(): void
    {
        $this->assertFalse(
            $this->excludeList->isStaticAttributeExcluded(
                ExcludedClass::class,
                'attribute'
            )
        );
    }

    public function testClassCanBeExcluded(): void
    {
        $this->excludeList->addClass(ExcludedClass::class);

        $this->assertTrue(
            $this->excludeList->isStaticAttributeExcluded(
                ExcludedClass::class,
                'attribute'
            )
        );
    }

    public function testSubclassesCanBeExcluded(): void
    {
        $this->excludeList->addSubclassesOf(ExcludedClass::class);

        $this->assertTrue(
            $this->excludeList->isStaticAttributeExcluded(
                ExcludedChildClass::class,
                'attribute'
            )
        );
    }

    public function testImplementorsCanBeExcluded(): void
    {
        $this->excludeList->addImplementorsOf(ExcludedInterface::class);

        $this->assertTrue(
            $this->excludeList->isStaticAttributeExcluded(
                ExcludedImplementor::class,
                'attribute'
            )
        );
    }

    public function testClassNamePrefixesCanBeExcluded(): void
    {
        $this->excludeList->addClassNamePrefix('SebastianBergmann\GlobalState');

        $this->assertTrue(
            $this->excludeList->isStaticAttributeExcluded(
                ExcludedClass::class,
                'attribute'
            )
        );
    }

    public function testStaticAttributeCanBeExcluded(): void
    {
        $this->excludeList->addStaticAttribute(
            ExcludedClass::class,
            'attribute'
        );

        $this->assertTrue(
            $this->excludeList->isStaticAttributeExcluded(
                ExcludedClass::class,
                'attribute'
            )
        );
    }
}
