<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2010 Nicholas J Humfrey.  All rights reserved.
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

/**
 * Class to serialise an EasyRdf_Graph to RDF 
 * using the 'rapper' command line tool.
 *
 * Note: the built-in N-Triples serialiser is used to pass data to Rapper.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser_Rapper extends EasyRdf_Serialiser_Ntriples
{
    private $_rapperCmd = null;

    /**
     * Constructor
     *
     * @param string $rapperCmd Optional path to the rapper command to use.
     * @return object EasyRdf_Serialiser_Rapper
     */
    public function __construct($rapperCmd='rapper')
    {
        exec("which ".escapeshellarg($rapperCmd), $output, $retval);
        if ($retval == 0) {
            $this->_rapperCmd = $rapperCmd;
        } else {
            throw new EasyRdf_Exception(
                "The command '$rapperCmd' is not available on this system."
            );
        }
    }

    /**
     * Serialise an EasyRdf_Graph to the RDF format of choice.
     *
     * @param string  $graph   An EasyRdf_Graph object.
     * @param string  $format  The name of the format to convert to.
     * @return string          The RDF in the new desired format.
     */
    public function serialise($graph, $format)
    {
        parent::checkSerialiseParams($graph, $format);

        $ntriples = parent::serialise($graph, 'ntriples');

        // Open a pipe to the rapper command
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => array("pipe", "w")
        );

        // Hack to produce more concise RDF/XML
        if ($format == 'rdfxml') $format = 'rdfxml-abbrev';

        $process = proc_open(
            escapeshellcmd($this->_rapperCmd).
            " --quiet ".
            " --input ntriples ".
            " --output " . escapeshellarg($format).
            " - ". 'unknown://', # FIXME: how can this be improved?
            $descriptorspec, $pipes, '/tmp', null
        );
        if (is_resource($process)) {
            // $pipes now looks like this:
            // 0 => writeable handle connected to child stdin
            // 1 => readable handle connected to child stdout
            // 2 => readable handle connected to child stderr

            fwrite($pipes[0], $ntriples);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[2]);

            // It is important that you close any pipes before calling
            // proc_close in order to avoid a deadlock
            $returnValue = proc_close($process);
            if ($returnValue) {
                throw new EasyRdf_Exception(
                    "Failed to convert RDF: ".$error
                );
            }
        } else {
            throw new EasyRdf_Exception(
                "Failed to execute rapper command."
            );
        }

        return $output;
    }
}

// FIXME: do this automatically
EasyRdf_Format::register('dot', 'Graphviz');
EasyRdf_Format::register('json-triples', 'RDF/JSON Triples');
EasyRdf_Format::registerSerialiser('dot', 'EasyRdf_Serialiser_Rapper');
EasyRdf_Format::registerSerialiser('json-triples', 'EasyRdf_Serialiser_Rapper');
EasyRdf_Format::registerSerialiser('rdfxml', 'EasyRdf_Serialiser_Rapper');
EasyRdf_Format::registerSerialiser('turtle', 'EasyRdf_Serialiser_Rapper');
