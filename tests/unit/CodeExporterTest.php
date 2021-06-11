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

use function define;
use function ini_get;
use function ini_set;
use PHPUnit\Framework\TestCase;

/**
 * @covers \SebastianBergmann\GlobalState\CodeExporter
 */
final class CodeExporterTest extends TestCase
{
    public function testCanExportGlobalVariablesToCode(): void
    {
        $expected = <<<'EOT'
call_user_func(
    function ()
    {
        foreach (array_keys($GLOBALS) as $key) {
            unset($GLOBALS[$key]);
        }
    }
);

$GLOBALS['foo'] = 'bar';

EOT;

        $this->cleanGlobals();
        $GLOBALS['foo'] = 'bar';

        $snapshot = new Snapshot(null, true, false, false, false, false, false, false, false, false);
        $exporter = new CodeExporter;

        $this->assertSame($expected, $exporter->globalVariables($snapshot));
    }

    public function testCanExportIniSettingsToCode(): void
    {
        $iniSettingName = 'display_errors';
        ini_set($iniSettingName, '1');
        $iniValue = ini_get($iniSettingName);

        $snapshot = new Snapshot(null, false, false, false, false, false, false, false, true, false);

        $export = (new CodeExporter)->iniSettings($snapshot);

        $pattern = "/@ini_set\(\'{$iniSettingName}\', \'{$iniValue}\'\);/";

        $this->assertMatchesRegularExpression(
            $pattern,
            $export
        );
    }

    public function testCanExportConstantsToCode(): void
    {
        define('FOO', 'BAR');

        $snapshot = new Snapshot(null, false, false, true, false, false, false, false, false, false);

        $exporter = new CodeExporter;

        $this->assertStringContainsString(
            "if (!defined('FOO')) define('FOO', 'BAR');",
            $exporter->constants($snapshot)
        );
    }

    /**
     * @see https://github.com/sebastianbergmann/global-state/issues/31
     * @see https://wiki.php.net/rfc/restrict_globals_usage
     */
    private function cleanGlobals(): void
    {
        foreach (array_keys($GLOBALS) as $key) {
            if ($key === 'GLOBALS') {
                continue;
            }

            unset($GLOBALS[$key]);
        }
    }
}
