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
 * Class to serialise an EasyRdf_Graph into RDF
 * with no external dependancies.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser_RdfXml extends EasyRdf_Serialiser
{
    protected $_prefixes = array();

    protected function addPrefix($qname)
    {
        list ($prefix) = explode(':', $qname);
        $this->_prefixes[$prefix] = true;
    }
    
    /**
     * Protected method to serialise an object node into an XML partial
     */    
    protected function rdfxmlResource($res)
    {
        if (is_object($res)) {
            if ($res->isBNode()) {
                return $res->getURI();
            } else {
                return $res->getURI();
            }
        } else {
            $uri = EasyRdf_Namespace::expand($res);
            if ($uri) {
                return "$uri";
            } else {
                return "$res";
            }
        }       
    }
    
    /**
     * Protected method to serialise an object node into an XML object
     */
    protected function rdfxmlObject($obj)
    {
        if (is_object($obj) and $obj instanceof EasyRdf_Literal) {
            $obj = $obj->getValue();        
        }
        if (is_object($obj) and $obj instanceof EasyRdf_Resource) {
            return $this->rdfxmlResource($obj);
        } else if (is_scalar($obj)) {
            // FIXME: peform encoding of Unicode characters as described here:
            // http://www.w3.org/TR/rdf-testcases/#ntrip_strings
            $literal = str_replace('\\', '\\\\', $obj);
            $literal = str_replace('"', '', $literal);
            $literal = str_replace('\n', '', $literal);
            $literal = str_replace('\r', '', $literal);
            $literal = str_replace('\t', '', $literal);
            return $literal;
        } else {
            throw new EasyRdf_Exception(
                "Unable to serialise object to xml: $obj. Object is of type ".getType($obj)
            );
        }
    }
    
    /**
     * Method to serialise an EasyRdf_Graph into RDF/XML
     *
     * http://n2.talis.com/wiki/RDF_JSON_Specification
     * 
     * @param string $graph An EasyRdf_Graph object.
     * @param string $format The name of the format to convert to (rdfxml).
     * @return string The xml formatted RDF.
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
        $namespaces = EasyRdf_Namespace::namespaces();
        $xml = '';
        foreach ($graph->resources() as $resource) {
            $xml .= '<rdf:Description rdf:about="'.$this->rdfxmlResource($resource).'">'."\n";
            foreach ($resource->properties() as $property) {
                $objects = $resource->all($property);
                
                foreach ($objects as $object) {
                    $tagName = EasyRdf_NameSpace::shorten($this->rdfxmlResource($property));
                    //@TODO: This should be a getPrefix function in Namespace.php
                    $prefix = explode(':', $tagName);
                    $prefix = $prefix[0];                   
                    $namespaces[$prefix] = EasyRdf_NameSpace::get($prefix);
                    if ($object instanceof EasyRdf_Resource) {
                        $value = $this->rdfxmlObject($object);
                        $xml .= "\t<".$tagName;
                        $xml .= " rdf:resource='".$value."'/>\n";
                    } else {
                        $dataType = "";
                        $lang = "";
                        if (EasyRdf_Utils::is_associative_array($object)) {
                            $value = $this->rdfxmlObject($object['value']);
                            if (array_key_exists('datatype', $object)) {
                                $dataType = ' rdf:datatype="'.$this->rdfxmlObject($object['datatype']).'"';
                            }
                        } else if (is_object($object) and $object instanceof EasyRdf_Literal) {
                            $value = $object->getValue();
                            if ($object->getDatatype()) {
                                $dataType = ' rdf:datatype="'.$object->getDatatype().'"';
                            }
                            if ($object->getLang()) {
                                $lang = ' xml:lang="'.$object->getLang().'"';
                            }

                        } else {
                            $value = $this->rdfxmlObject($object);
                        }
                        $xml .= "\t<".$tagName.$dataType.$lang.">";
                        // everything between xml tags should be html encoded
                        $value = htmlentities($value, null, 'UTF-8');
                        // validators think that html entities are namespaces,
                        // so encode the &
                        // http://www.semanticoverflow.com/questions/984/html-entities-in-rdfxmlliteral
                        $xml .= str_replace('&', '&amp;', $value);
                        $xml .= "</".$tagName.">\n";
                    }
                    
                }
            }
            $xml .= "</rdf:Description>\n\n";
        }
        // iterate through namepsaces array prefix and output a string.
        $namespaceStr = '';
        foreach ($namespaces as $prefix => $namespace) {
            $namespaceStr .= 'xmlns:'.$prefix.'="'.$namespace.'" ';
        }
        //return false;
        return '<rdf:RDF '. $namespaceStr . ">\n " . $xml . '</rdf:RDF>';
    }
    
}

EasyRdf_Format::registerSerialiser('rdfxml', 'EasyRdf_Serialiser_RdfXml');
