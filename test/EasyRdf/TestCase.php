<?php
namespace EasyRdf;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2020 Nicholas J Humfrey.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 3. The name of the author 'Nicholas J Humfrey" may be used to endorse or
 *    promote products derived from this software without specific prior
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function assertStringEquals($str1, $str2, $message = null)
    {
        self::assertSame(strval($str1), strval($str2), (string) $message);
    }

    // Note: this differs from assertInstanceOf because it disallows subclasses
    public static function assertClass($class, $object)
    {
        self::assertSame($class, get_class($object));
    }

    /**
     * Forward compatibility layer for PHPUnit 6/7
     *
     * @todo Outdated. Remove it and use appropriate PHPUnit functions.
     */
    public function setExpectedException($exceptionName, $exceptionMessage = '', $exceptionCode = null)
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException($exceptionName);
            if ($exceptionMessage) {
                $this->expectExceptionMessage($exceptionMessage);
            }
        } else {
            parent::setExpectedException($exceptionName, $exceptionMessage);
        }
    }

    /**
     * Compatibility layer for PHPUnit 7+.
     *
     * As long as we have to support PHPUnit 7 this function is required, because its replacement in PHPUnit 9
     * is defined as a static function, but assertRegExp was non-static.
     *
     * @todo remove if PHPUnit 7 support is no longer required.
     */
    public static function assertMatchesRegularExpression(string $pattern, string $string, string $message = ''): void
    {
        $case = new TestCase();
        if (method_exists($case, 'assertRegExp')) {
            $case->assertRegExp($pattern, $string, $message);
        } else {
            parent::assertMatchesRegularExpression($pattern, $string, $message);
        }
    }
}
