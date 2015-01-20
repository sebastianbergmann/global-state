<?php
/*
 * This file is part of the GlobalState package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SebastianBergmann\GlobalState\TestFixture;

use DomDocument;
use ArrayObject;

/**
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/global-state
 */
class SnapshotClass
{
    private static $string = 'snapshot';
    private static $dom;
    private static $closure;
    private static $arrayObject;
    private static $snapshotDomDocument;
    private static $resource;

    public static function init()
    {
        self::$dom = new DomDocument();
        self::$closure = function () {};
        self::$arrayObject = new ArrayObject(array(1, 2, 3));
        self::$snapshotDomDocument = new SnapshotDomDocument();
        self::$resource = fopen('php://memory', 'r');
    }
}
