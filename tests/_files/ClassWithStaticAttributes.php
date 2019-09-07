<?php declare(strict_types=1);
/*
 * This file is part of sebastian/global-state.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ClassWithStaticAttributes
{
    public const STATIC_PUBLIC = 'foo';

    public const STATIC_PROTECTED = 'bar';

    public const STATIC_PRIVATE = 'baz';

    public static $publicStaticAttribute = self::STATIC_PUBLIC;

    protected static $protectedStaticAttribute = self::STATIC_PROTECTED;

    protected static $privateStaticAttribute = self::STATIC_PRIVATE;

    public static function setProtectedStaticAttribute(string $protectedStaticAttribute): void
    {
        self::$protectedStaticAttribute = $protectedStaticAttribute;
    }

    public static function setPublicStaticAttribute(string $publicStaticAttribute): void
    {
        self::$publicStaticAttribute = $publicStaticAttribute;
    }

    public static function setPrivateStaticAttribute(string $privateStaticAttribute): void
    {
        self::$privateStaticAttribute = $privateStaticAttribute;
    }

    public static function getPublicStaticAttribute(): string
    {
        return self::$publicStaticAttribute;
    }

    public static function getProtectedStaticAttribute(): string
    {
        return self::$protectedStaticAttribute;
    }

    public static function getPrivateStaticAttribute(): string
    {
        return self::$privateStaticAttribute;
    }
}
