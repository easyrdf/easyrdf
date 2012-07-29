<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2012 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'TestHelper.php';

class Examples_BasicSparqlTest extends EasyRdf_TestCase
{
    public function testDoctorWho()
    {
        $output = executeExample('basic_sparql.php');
        $this->assertContains('<title>EasyRdf Basic Sparql Example</title>', $output);
        $this->assertContains('<h1>EasyRdf Basic Sparql Example</h1>', $output);
        $this->assertContains('<h2>Doctor Who - Series 1</h2>', $output);
        $this->assertContains('<li>1. <a href="http://www.bbc.co.uk/programmes/b0074dlv#programme">Rose</a></li>', $output);
        $this->assertContains('<li>2. <a href="http://www.bbc.co.uk/programmes/b0074dmp#programme">The End of the World</a></li>', $output);
        $this->assertContains('<li>3. <a href="http://www.bbc.co.uk/programmes/b0074dng#programme">The Unquiet Dead</a></li>', $output);
        $this->assertContains('<li>4. <a href="http://www.bbc.co.uk/programmes/b0074dp9#programme">Aliens of London</a></li>', $output);
        $this->assertContains('<li>5. <a href="http://www.bbc.co.uk/programmes/b0074dpv#programme">World War Three</a></li>', $output);
        $this->assertContains('<li>6. <a href="http://www.bbc.co.uk/programmes/b0074dq8#programme">Dalek</a></li>', $output);
        $this->assertContains('<li>7. <a href="http://www.bbc.co.uk/programmes/b0074dr5#programme">The Long Game</a></li>', $output);
        $this->assertContains('<li>8. <a href="http://www.bbc.co.uk/programmes/b0074drw#programme">Father\'s Day</a></li>', $output);
        $this->assertContains('<li>9. <a href="http://www.bbc.co.uk/programmes/b0074ds9#programme">The Empty Child</a></li>', $output);
        $this->assertContains('<li>10. <a href="http://www.bbc.co.uk/programmes/b0074dsp#programme">The Doctor Dances</a></li>', $output);
        $this->assertContains('<li>11. <a href="http://www.bbc.co.uk/programmes/b0074dt5#programme">Boom Town</a></li>', $output);
        $this->assertContains('<li>12. <a href="http://www.bbc.co.uk/programmes/b0074dth#programme">Bad Wolf</a></li>', $output);
        $this->assertContains('<li>13. <a href="http://www.bbc.co.uk/programmes/b0074dv1#programme">The Parting of the Ways</a></li>', $output);
    }
}
