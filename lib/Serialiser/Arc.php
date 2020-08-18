<?php
namespace EasyRdf\Serialiser;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2016 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2016 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
use EasyRdf\Exception;
use EasyRdf\Format;
use EasyRdf\Graph;

/**
 * Class to serialise RDF using the ARC2 library.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2016 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class Arc extends RdfPhp
{
    private static $supportedTypes = array(
        'rdfxml' => 'RDFXML',
        'turtle' => 'Turtle',
        'ntriples' => 'NTriples',
        'posh' => 'POSHRDF'
    );

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!class_exists('ARC2')) {
            throw new Exception('ARC2 dependency is not installed');
        }
    }


    /**
     * Serialise an EasyRdf\Graph into RDF format of choice.
     *
     * @param Graph  $graph  An EasyRdf\Graph object.
     * @param string $format The name of the format to convert to.
     * @param array  $options
     *
     * @return string The RDF in the new desired format.
     * @throws Exception
     */
    public function serialise(Graph $graph, $format, array $options = array())
    {
        parent::checkSerialiseParams($format);

        if (array_key_exists($format, self::$supportedTypes)) {
            $className = self::$supportedTypes[$format];
        } else {
            throw new Exception(
                "EasyRdf\\Serialiser\\Arc does not support: {$format}"
            );
        }

        /** @var \ARC2_RDFSerializer $serialiser */
        $serialiser = \ARC2::getSer($className);
        if ($serialiser) {
            return $serialiser->getSerializedIndex(
                parent::serialise($graph, 'php')
            );
        } else {
            throw new Exception(
                "ARC2 failed to get a $className serialiser."
            );
        }
    }
}

Format::register('posh', 'poshRDF');
