<?php
/**
 * GlobalState
 *
 * Copyright (c) 2001-2014, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/global-state
 */

namespace SebastianBergmann\GlobalState;

use Closure;
use ReflectionClass;
use ReflectionProperty;

/**
 * A snapshot of global state.
 *
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/global-state
 */
class Snapshot
{
    /**
     * @var Blacklist
     */
    private $blacklist;

    /**
     * @var array
     */
    private $globals = array();

    /**
     * @var array
     */
    private $superGlobals = array();

    /**
     * @var array
     */
    private $staticAttributes = array();

    /**
     * @var array
     */
    private $iniSettings = array();

    /**
     * @var array
     */
    private $includedFiles = array();

    /**
     * @var array
     */
    private $constants = array();

    /**
     * @var array
     */
    private $classes = array();

    /**
     * @var array
     */
    private $interfaces = array();

    /**
     * @var array
     */
    private $functions = array();

    /**
     * @var array
     */
    private $traits = array();

    /**
     * Creates a snapshot of the current global state.
     *
     * @param Blacklist $blacklist
     */
    public function __construct(Blacklist $blacklist = null)
    {
        if ($blacklist === null) {
            $blacklist = new Blacklist;
        }

        $this->blacklist = $blacklist;

        $this->snapshotConstants();
        $this->snapshotFunctions();
        $this->snapshotClasses();
        $this->snapshotInterfaces();
        $this->snapshotGlobals();
        $this->snapshotStaticAttributes();

        $this->iniSettings   = ini_get_all(null, false);
        $this->includedFiles = get_included_files();

        if (function_exists('get_declared_traits')) {
            $this->traits = get_declared_traits();
        }
    }

    /**
     * Restores all global and super-global variables as well as
     * all static attributes in user-defined classes with the state
     * stored in this snapshot.
     */
    public function restore()
    {
        $this->restoreGlobalVariables();
        $this->restoreStaticAttributes();
    }

    /**
     * Creates a snapshot user-defined constants.
     */
    private function snapshotConstants()
    {
        $constants = get_defined_constants(true);

        if (isset($constants['user'])) {
            $this->constants = $constants['user'];
        }
    }

    /**
     * Creates a snapshot user-defined functions.
     */
    private function snapshotFunctions()
    {
        $functions = get_defined_functions();

        $this->functions = $functions['user'];
    }

    /**
     * Creates a snapshot user-defined classes.
     */
    private function snapshotClasses()
    {
        foreach (array_reverse(get_declared_classes()) as $className) {
            $class = new ReflectionClass($className);

            if (!$class->isUserDefined()) {
                break;
            }

            $this->classes[] = $className;
        }

        $this->classes = array_reverse($this->classes);
    }

    /**
     * Creates a snapshot user-defined interfaces.
     */
    private function snapshotInterfaces()
    {
        foreach (array_reverse(get_declared_interfaces()) as $interfaceName) {
            $class = new ReflectionClass($interfaceName);

            if (!$class->isUserDefined()) {
                break;
            }

            $this->interfaces[] = $interfaceName;
        }

        $this->interfaces = array_reverse($this->interfaces);
    }

    /**
     * Creates a snapshot of all global and super-global variables.
     */
    private function snapshotGlobals()
    {
        $superGlobalArrays = $this->superGlobalArrays();

        foreach ($superGlobalArrays as $superGlobalArray) {
            $this->snapshotSuperGlobalArray($superGlobalArray);
        }

        foreach (array_keys($GLOBALS) as $key) {
            if ($key != 'GLOBALS' &&
                !in_array($key, $superGlobalArrays) &&
                !$GLOBALS[$key] instanceof Closure &&
                !$this->blacklist->isGlobalVariableBlacklisted($key)) {
                $this->globals[$key] = serialize($GLOBALS[$key]);
            }
        }
    }

    /**
     * Restores all global and super-global variables from this snapshot.
     */
    private function restoreGlobalVariables()
    {
        $superGlobalArrays = $this->superGlobalArrays();

        foreach ($superGlobalArrays as $superGlobalArray) {
            $this->restoreSuperGlobalArray($superGlobalArray);
        }

        foreach (array_keys($GLOBALS) as $key) {
            if ($key != 'GLOBALS' &&
                !in_array($key, $superGlobalArrays) &&
                !$this->blacklist->isGlobalVariableBlacklisted($key)) {
                if (isset($this->globals[$key])) {
                    $GLOBALS[$key] = unserialize($this->globals[$key]);
                } else {
                    unset($GLOBALS[$key]);
                }
            }
        }
    }

    /**
     * Creates a snapshot a super-global variable array.
     *
     * @param $superGlobalArray
     */
    private function snapshotSuperGlobalArray($superGlobalArray)
    {
        $this->superGlobals[$superGlobalArray] = array();

        if (isset($GLOBALS[$superGlobalArray]) && is_array($GLOBALS[$superGlobalArray])) {
            foreach ($GLOBALS[$superGlobalArray] as $key => $value) {
                $this->superGlobals[$superGlobalArray][$key] = serialize($value);
            }
        }
    }

    /**
     * Restores a super-global variable array from this snapshot.
     *
     * @param $superGlobalArray
     */
    private function restoreSuperGlobalArray($superGlobalArray)
    {
        if (isset($GLOBALS[$superGlobalArray]) &&
            is_array($GLOBALS[$superGlobalArray]) &&
            isset($this->superGlobals[$superGlobalArray])) {
            $keys = array_keys(
                array_merge(
                    $GLOBALS[$superGlobalArray],
                    $this->superGlobals[$superGlobalArray]
                )
            );

            foreach ($keys as $key) {
                if (isset($this->superGlobals[$superGlobalArray][$key])) {
                    $GLOBALS[$superGlobalArray][$key] = unserialize(
                        $this->superGlobals[$superGlobalArray][$key]
                    );
                } else {
                    unset($GLOBALS[$superGlobalArray][$key]);
                }
            }
        }
    }

    /**
     * Creates a snapshot of all static attributes in user-defined classes.
     */
    private function snapshotStaticAttributes()
    {
        foreach ($this->classes as $className) {
            $class    = new ReflectionClass($className);
            $snapshot = array();

            foreach ($class->getProperties() as $attribute) {
                if ($attribute->isStatic()) {
                    $name = $attribute->getName();

                    if ($this->blacklist->isStaticAttributeBlacklisted($className, $name)) {
                        continue;
                    }

                    $attribute->setAccessible(true);
                    $value = $attribute->getValue();

                    if (!$value instanceof Closure) {
                        $snapshot[$name] = serialize($value);
                    }
                }
            }

            if (!empty($snapshot)) {
                $this->staticAttributes[$className] = $snapshot;
            }
        }
    }

    /**
     * Restores all static attributes in user-defined classes from this snapshot.
     */
    private function restoreStaticAttributes()
    {
        foreach ($this->staticAttributes as $className => $staticAttributes) {
            foreach ($staticAttributes as $name => $value) {
                $reflector = new ReflectionProperty($className, $name);
                $reflector->setAccessible(true);
                $reflector->setValue(unserialize($value));
            }
        }
    }

    /**
     * Returns a list of all super-global variable arrays.
     * @return array
     */
    private function superGlobalArrays()
    {
        $superGlobalArrays = array(
            '_ENV',
            '_POST',
            '_GET',
            '_COOKIE',
            '_SERVER',
            '_FILES',
            '_REQUEST'
        );

        if (ini_get('register_long_arrays') == '1') {
            $superGlobalArrays = array_merge(
                $superGlobalArrays,
                array(
                    'HTTP_ENV_VARS',
                    'HTTP_POST_VARS',
                    'HTTP_GET_VARS',
                    'HTTP_COOKIE_VARS',
                    'HTTP_SERVER_VARS',
                    'HTTP_POST_FILES'
                )
            );
        }

        return $superGlobalArrays;
    }
}
