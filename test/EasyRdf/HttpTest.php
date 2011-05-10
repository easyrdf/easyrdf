<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2011 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class Mock_Http_Test_Client extends EasyRdf_Http_Client
{
    public function request($method = null)
    {
        return null;
    }
}

class EasyRdf_HttpTest extends EasyRdf_TestCase
{
// FIXME: this test needs to run before the first call to setDefaultHttpClient()
//     public function testGetDefaultHttpClient()
//     {
//         $this->assertEquals(
//             'EasyRdf_Http_Client',
//             get_class(EasyRdf_Http::getDefaultHttpClient())
//         );
//     }

    public function testSetDefaultHttpClient()
    {
        EasyRdf_Http::setDefaultHttpClient(new Mock_Http_Test_Client());
        $this->assertEquals(
            'Mock_Http_Test_Client',
            get_class(EasyRdf_Http::getDefaultHttpClient())
        );
    }

    public function testSetDefaultHttpClientNull()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Http::setDefaultHttpClient(null);
    }

    public function testSetDefaultHttpClientString()
    {
        $this->setExpectedException('InvalidArgumentException');
        EasyRdf_Http::setDefaultHttpClient('foobar');
    }
}
