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

/**
 * Class to parse RDF using the 'rapper' command line tool.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Parser_Rapper extends EasyRdf_Parser_Json
{
    private $_rapperCmd = null;
    private $_tempDir = '/tmp';

    const MINIMUM_RAPPER_VERSION = '1.4.17';

    /**
     * Constructor
     *
     * @param string $rapperCmd Optional path to the rapper command to use.
     * @return object EasyRdf_Parser_Rapper
     */
    public function __construct($rapperCmd='rapper')
    {
        $result = exec("$rapperCmd --version 2>/dev/null", $output, $status);
        if ($status != 0) {
            throw new EasyRdf_Exception(
                "Failed to execute the command '$rapperCmd': $result"
            );
        } else if (version_compare($result, self::MINIMUM_RAPPER_VERSION) < 0) {
            throw new EasyRdf_Exception(
                "Version ".self::MINIMUM_RAPPER_VERSION." or higher of rapper is required."
            );
        } else {
            $this->_rapperCmd = $rapperCmd;
        }

        if (function_exists('sys_get_temp_dir')) {
            $this->_tempDir = sys_get_temp_dir();
        }
    }

    /**
      * Parse an RDF document into an EasyRdf_Graph
      *
      * @param object EasyRdf_Graph $graph   the graph to load the data into
      * @param string               $data    the RDF document data
      * @param string               $format  the format of the input data
      * @param string               $baseUri the base URI of the data being parsed
      * @return integer             The number of triples added to the graph
      */
    public function parse($graph, $data, $format, $baseUri)
    {
        parent::checkParseParams($graph, $data, $format, $baseUri);

        // Open a pipe to the rapper command
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        $process = proc_open(
            escapeshellcmd($this->_rapperCmd).
            " --quiet ".
            " --input " . escapeshellarg($format).
            " --output json ".
            " --ignore-errors ".
            " --input-uri " . escapeshellarg($baseUri).
            " --output-uri -".
            " - ",
            $descriptorspec, $pipes, $this->_tempDir, NULL
        );
        if (is_resource($process)) {
            // $pipes now looks like this:
            // 0 => writeable handle connected to child stdin
            // 1 => readable handle connected to child stdout
            // 2 => readable handle connected to child stderr

            fwrite($pipes[0], $data);
            fclose($pipes[0]);

            $data = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            // It is important that you close any pipes before calling
            // proc_close in order to avoid a deadlock
            $returnValue = proc_close($process);
            if ($returnValue) {
                throw new EasyRdf_Exception(
                    "Failed to parse RDF ($returnValue): ".$error
                );
            }
        } else {
            throw new EasyRdf_Exception(
                "Failed to execute rapper command."
            );
        }

        // Parse in the JSON
        return parent::parse($graph, $data, 'json', $baseUri);
    }
}

## FIXME: do this automatically
EasyRdf_Format::registerParser('rdfxml', 'EasyRdf_Parser_Rapper');
EasyRdf_Format::registerParser('turtle', 'EasyRdf_Parser_Rapper');
EasyRdf_Format::registerParser('ntriples', 'EasyRdf_Parser_Rapper');
EasyRdf_Format::registerParser('rdfa', 'EasyRdf_Parser_Rapper');
