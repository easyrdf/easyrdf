<?php
namespace EasyRdf\Parser;

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
use EasyRdf\Graph;
use EasyRdf\Utils;

/**
 * Class to parse RDF using the 'rapper' command line tool.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2020 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class Rapper extends Json
{
    private $rapperCmd = null;

    const MINIMUM_RAPPER_VERSION = '1.4.17';

    /**
     * Constructor
     *
     * @param string $rapperCmd Optional path to the rapper command to use.
     *
     * @throws \EasyRdf\Exception
     */
    public function __construct($rapperCmd = 'rapper')
    {
        exec("$rapperCmd --version 2>/dev/null", $output, $status);
        if ($status != 0) {
            throw new \EasyRdf\Exception(
                "Failed to execute the command '$rapperCmd': " . join("\n", $output)
            );
        } elseif (version_compare($output[0], self::MINIMUM_RAPPER_VERSION) < 0) {
            throw new \EasyRdf\Exception(
                "Version ".self::MINIMUM_RAPPER_VERSION." or higher of rapper is required."
            );
        } else {
            $this->rapperCmd = $rapperCmd;
        }
    }

    /**
      * Parse an RDF document into an EasyRdf\Graph
      *
      * @param Graph  $graph   the graph to load the data into
      * @param string $data    the RDF document data
      * @param string $format  the format of the input data
      * @param string $baseUri the base URI of the data being parsed
      *
      * @return integer             The number of triples added to the graph
      */
    public function parse($graph, $data, $format, $baseUri)
    {
        parent::checkParseParams($graph, $data, $format, $baseUri);

        $json = Utils::execCommandPipe(
            $this->rapperCmd,
            array(
                '--quiet',
                '--input', $format,
                '--output', 'json',
                '--ignore-errors',
                '--input-uri', $baseUri,
                '--output-uri', '-', '-'
            ),
            $data
        );

        // Parse in the JSON
        return parent::parse($graph, $json, 'json', $baseUri);
    }
}
