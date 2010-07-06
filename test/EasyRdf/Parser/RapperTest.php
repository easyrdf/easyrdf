<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'TestCase.php';
require_once 'EasyRdf/Parser/Rapper.php';

class EasyRdf_Parser_RapperTest extends EasyRdf_Parser_TestCase
{
    public function setUp()
    {
        // FIXME: suppress stderr
        // FIXME: check for rapper version 1.4.17
        exec('which rapper', $output, $retval);
        if ($retval == 0) {
            $this->_parser = new EasyRdf_Parser_Rapper();
        } else {
            $this->markTestSkipped(
                "The rapper command is not available on this system."
            );
        }
    }

    function testRapperNotFound()
    {
        $this->setExpectedException('EasyRdf_Exception');
        new EasyRdf_Parser_Rapper('random_command_that_doesnt_exist');
    }

    function testRapperExecError()
    {
        # FIXME: how can we cause proc_open() to fail?
        $this->markTestIncomplete();
    }


    function testParseJson()
    {
        $this->markTestSkipped(
            "EasyRdf_Parser_Rapper() does not support JSON."
        );
    }
}
