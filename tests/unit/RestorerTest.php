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
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\GlobalState\TestFixture\ClassWithPublicStaticProperty;

#[CoversClass(Restorer::class)]
#[UsesClass(ExcludeList::class)]
#[UsesClass(Snapshot::class)]
final class RestorerTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $GLOBALS['varBool'] = false;
        $GLOBALS['varNull'] = null;
        $_GET['varGet']     = 0;
    }

    public function testRestorerGlobalVariable(): void
    {
        $snapshot = new Snapshot(null, true, false, false, false, false, false, false, false, false);
        $restorer = new Restorer;
        $restorer->restoreGlobalVariables($snapshot);

        $this->assertArrayHasKey('varBool', $GLOBALS);
        $this->assertEquals(false, $GLOBALS['varBool']);
        $this->assertArrayHasKey('varNull', $GLOBALS);
        $this->assertEquals(null, $GLOBALS['varNull']);
        $this->assertArrayHasKey('varGet', $_GET);
        $this->assertEquals(0, $_GET['varGet']);
    }

    public function testIntegrationRestorerGlobalVariables(): void
    {
        $this->assertArrayHasKey('varBool', $GLOBALS);
        $this->assertEquals(false, $GLOBALS['varBool']);
        $this->assertArrayHasKey('varNull', $GLOBALS);
        $this->assertEquals(null, $GLOBALS['varNull']);
        $this->assertArrayHasKey('varGet', $_GET);
        $this->assertEquals(0, $_GET['varGet']);
    }

    #[Depends('testIntegrationRestorerGlobalVariables')]
    public function testIntegrationRestorerGlobalVariables2(): void
    {
        $this->assertArrayHasKey('varBool', $GLOBALS);
        $this->assertEquals(false, $GLOBALS['varBool']);
        $this->assertArrayHasKey('varNull', $GLOBALS);
        $this->assertEquals(null, $GLOBALS['varNull']);
        $this->assertArrayHasKey('varGet', $_GET);
        $this->assertEquals(0, $_GET['varGet']);
    }

    public function testRestoresStaticProperties(): void
    {
        ClassWithPublicStaticProperty::$property = 'original';

        $snapshot = new Snapshot(null, false, true, false, false, false, false, false, false, false);

        ClassWithPublicStaticProperty::$property = 'changed';

        $restorer = new Restorer;
        $restorer->restoreStaticProperties($snapshot);

        $this->assertSame('original', ClassWithPublicStaticProperty::$property);
    }
}
