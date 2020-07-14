<?php
namespace EasyRdf\Examples;

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
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class VillagesTest extends \EasyRdf\TestCase
{
    public function testIndex()
    {
        $output = executeExample('wikidata_villages.php');
        $this->assertContains('<title>EasyRdf Village Info Example</title>', $output);
        $this->assertContains('<h1>EasyRdf Village Info Example</h1>', $output);
        $this->assertContains('?id=Q33980">Ceres</a></li>', $output);
        $this->assertContains('?id=Q1011990">Strathkinness</a></li>', $output);
    }

    public function testCeres()
    {
        $output = executeExample(
            'wikidata_villages.php',
            array('id' => 'Q33980')
        );
        $this->assertContains('<h2>Ceres</h2>', $output);
        $this->assertContains('<p>village in Fife, Scotland', $output);
        $this->assertContains(
            '<img src="http://commons.wikimedia.org/wiki/Special:FilePath/Ceres%20in%20Fife.JPG"',
            $output
        );
        $this->assertContains(
            "src='http://www.openlinkmap.org/small.php?lat=56.29205&lon=-2.971445",
            $output
        );
        $this->assertContains('<a href="https://en.wikipedia.org/wiki/Ceres,_Fife">', $output);
    }
}
