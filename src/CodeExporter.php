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

/**
 * Exports parts of a Snapshot as PHP code.
 *
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2001-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.github.com/sebastianbergmann/global-state
 */
class CodeExporter
{
    /**
     * @param  Snapshot $snapshot
     * @return string
     */
    public function constants(Snapshot $snapshot)
    {
        $result = '';

        foreach ($snapshot->constants() as $name => $value) {
            $result .= sprintf(
                'if (!defined(\'%s\')) define(\'%s\', %s);' . "\n",
                $name,
                $name,
                $this->exportVariable($value)
            );
        }

        return $result;
    }

    /**
     * @param  Snapshot $snapshot
     * @return string
     */
    public function iniSettings(Snapshot $snapshot)
    {
        $result = '';

        foreach ($snapshot->iniSettings() as $key => $value) {
            $result .= sprintf(
                '@ini_set(%s, %s);' . "\n",
                $this->exportVariable($key),
                $this->exportVariable($value)
            );
        }

        return $result;
    }

    /**
     * @param  mixed $variable
     * @return string
     */
    private function exportVariable($variable)
    {
        if (is_scalar($variable) || is_null($variable) ||
            (is_array($variable) && $this->arrayOnlyContainsScalars($variable))) {
            return var_export($variable, true);
        }

        return 'unserialize(' . var_export(serialize($variable), true) . ')';
    }

    /**
     * @param  array $array
     * @return boolean
     */
    private function arrayOnlyContainsScalars(array $array)
    {
        $result = true;

        foreach ($array as $element) {
            if (is_array($element)) {
                $result = self::arrayOnlyContainsScalars($element);
            } elseif (!is_scalar($element) && !is_null($element)) {
                $result = false;
            }

            if ($result === false) {
                break;
            }
        }

        return $result;
    }
}
