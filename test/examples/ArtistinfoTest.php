<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2013 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class Examples_ArtistinfoTest extends EasyRdf_TestCase
{
    public function testNoParams()
    {
        $output = executeExample('artistinfo.php');
        $this->assertContains('<title>EasyRdf Artist Info Example</title>', $output);
        $this->assertContains('<h1>EasyRdf Artist Info Example</h1>', $output);
    }

    public function testBruce()
    {
        $this->markTestSkipped('BBC music removed this resource');
        $output = executeExample(
            'artistinfo.php',
            array(
                'uri' => 'http://www.bbc.co.uk/music/artists/70248960-cb53-4ea4-943a-edb18f7d336f.rdf',
            )
        );

        $this->assertContains('<title>EasyRdf Artist Info Example</title>', $output);
        $this->assertContains('<h1>EasyRdf Artist Info Example</h1>', $output);
        $this->assertContains('<dt>Artist Name:</dt><dd>Bruce Springsteen</dd>', $output);
        $this->assertContains("<dt>Type:</dt><dd>mo:MusicArtist, mo:SoloMusicArtist</dd>", $output);
        $this->assertContains(
            '<dt>Homepage:</dt><dd><a href="http://www.brucespringsteen.net/">'.
            'http://www.brucespringsteen.net/</a></dd>',
            $output
        );
        $this->assertContains(
            '<dt>Wikipedia page:</dt><dd><a href="http://en.wikipedia.org/wiki/Bruce_Springsteen">'.
            'http://en.wikipedia.org/wiki/Bruce_Springsteen</a></dd>',
            $output
        );
        $this->assertContains("<dt>Age:</dt>  <dd>".(date('Y') - 1949)."</dd>", $output);
    }
}
