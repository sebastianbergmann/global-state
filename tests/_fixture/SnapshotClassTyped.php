<?php declare(strict_types=1);
/*
 * This file is part of sebastian/global-state.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\GlobalState\TestFixture;

class SnapshotClassTyped
{
    private static bool $bool = true;

    private static string $string;

    public static function init(): void
    {
        self::$string = 'string';
    }
}
