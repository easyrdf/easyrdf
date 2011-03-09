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
 * Class to serialise an EasyRdf_Graph to RDF/XML
 * with no external dependancies.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser_RdfXml extends EasyRdf_Serialiser
{
    private $_prefixes = array();

    /** A constant for the RDF Type property URI */
    const RDF_XML_LITERAL = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral';

    /**
     * @ignore
     */
    protected function addPrefix($qname)
    {
        list ($prefix) = explode(':', $qname);
        $this->_prefixes[$prefix] = true;
    }

    /**
     * Protected method to serialise an object node into an XML partial
     * @ignore
     */
    protected function rdfxmlResource($res, $type='rdf:Description')
    {
        if ($res->isBNode()) {
            return "<$type rdf:nodeID=\"".
                   htmlspecialchars($res->getNodeId())."\">";
        } else {
            return "<$type rdf:about=\"".
                   htmlspecialchars($res->getUri())."\">";
        }
    }

    /**
     * Protected method to serialise an object node into an XML object
     * @ignore
     */
    protected function rdfxmlObject($property, $obj)
    {
        if (is_object($obj) and $obj instanceof EasyRdf_Resource) {
            if ($obj->isBNode()) {
                return "    <".$property.
                       " rdf:nodeID=\"".htmlspecialchars($obj->getNodeId()).
                       "\"/>\n";
            } else {
                return "    <".$property.
                       " rdf:resource=\"".htmlspecialchars($obj->getURI()).
                       "\"/>\n";
            }
        } else if (is_object($obj) and $obj instanceof EasyRdf_Literal) {
            $atrributes = "";
            $datatype = $obj->getDatatypeUri();
            if ($datatype) {
                if ($datatype == self::RDF_XML_LITERAL) {
                    $atrributes .= " rdf:parseType=\"Literal\"";
                    $value = $obj->getValue();
                } else {
                    $datatype = htmlspecialchars($datatype);
                    $atrributes .= " rdf:datatype=\"$datatype\"";
                }
            } elseif ($obj->getLang()) {
                $atrributes .= ' xml:lang="'.
                               htmlspecialchars($obj->getLang()).'"';
            }

            // Escape value
            if (!isset($value)) {
                $value = htmlspecialchars($obj->getValue());
            }

            return "    <$property$atrributes>$value</$property>\n";
        } else {
            throw new EasyRdf_Exception(
                "Unable to serialise object to xml: ".getType($obj)
            );
        }
    }

    /**
     * Method to serialise an EasyRdf_Graph to RDF/XML
     *
     * @param string  $graph   An EasyRdf_Graph object.
     * @param string  $format  The name of the format to convert to.
     * @return string          The RDF in the new desired format.
     */
    public function serialise($graph, $format)
    {
        parent::checkSerialiseParams($graph, $format);

        if ($format != 'rdfxml') {
            throw new EasyRdf_Exception(
                "EasyRdf_Serialiser_RdfXml does not support: $format"
            );
        }

        // store of namespaces to be appended to the rdf:RDF tag
        $this->_prefixes = array('rdf' => true);

        $xml = '';
        foreach ($graph->resources() as $resource) {
            $properties = $resource->propertyUris();
            if (count($properties) == 0)
                continue;

            $type = $resource->type();
            if (!$type)
                $type = 'rdf:Description';

            $xml .= "\n  ".$this->rdfxmlResource($resource, $type)."\n";
            foreach ($properties as $property) {
                $short = EasyRdf_Namespace::shorten($property, true);
                if ($short) {
                    $this->addPrefix($short);
                    $objects = $resource->all($property);
                    if ($short == 'rdf:type')
                        array_shift($objects);
                    foreach ($objects as $object) {
                        $xml .= $this->rdfxmlObject($short, $object);
                    }
                } else {
                    throw new EasyRdf_Exception(
                        "It is not possible to serialse the property ".
                        "'$property' to RDF/XML."
                    );
                }
            }
            $xml .= "  </$type>\n";
        }

        // iterate through namepsaces array prefix and output a string.
        $namespaceStr = '';
        foreach ($this->_prefixes as $prefix => $count) {
            $url = EasyRdf_Namespace::get($prefix);
            if (strlen($namespaceStr)) {
                $namespaceStr .= "\n        ";
            }
            $namespaceStr .= ' xmlns:'.$prefix.'="'.htmlspecialchars($url).'"';
        }

        return "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n".
               "<rdf:RDF". $namespaceStr . ">\n" . $xml . "\n</rdf:RDF>\n";
    }

}

EasyRdf_Format::registerSerialiser('rdfxml', 'EasyRdf_Serialiser_RdfXml');
