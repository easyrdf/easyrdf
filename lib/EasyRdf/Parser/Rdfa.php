<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2012 Nicholas J Humfrey.
 * All rights reserved.
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
 *             Copyright (c) 1997-2006 Aduna (http://www.aduna-software.com/)
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

/**
 * Class to parse RDFa with no external dependancies.
 *
 * http://www.w3.org/TR/rdfa-core/
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Parser_Rdfa extends EasyRdf_Parser
{
    const XML_NS = 'http://www.w3.org/XML/1998/namespace';

    /**
     * Constructor
     *
     * @return object EasyRdf_Parser_Rdfa
     */
    public function __construct()
    {
    }

    protected function resolve($uri)
    {
        if ($this->_baseUri) {
            return $this->_graph->resource(
                $this->_baseUri->resolve($uri)
            );
        } else {
            return $this->_graph->resource($uri);
        }
    }

    protected function addTriple($resource, $property, $value)
    {
        print "Adding triple: $resource -> $property -> $value\n";
        $count = $this->_graph->add($resource, $property, $value);
        $this->_tripleCount += $count;
        return $count;
    }

    protected function printNode($node, $depth) {
        $indent = str_repeat('  ', $depth);
        print $indent;
        switch($node->nodeType) {
            case XML_ELEMENT_NODE: print 'node'; break;
            case XML_ATTRIBUTE_NODE: print 'attr'; break;
            case XML_TEXT_NODE: print 'text'; break;
            case XML_CDATA_SECTION_NODE: print 'cdata'; break;
            case XML_ENTITY_REF_NODE: print 'entref'; break;
            case XML_ENTITY_NODE: print 'entity'; break;
            case XML_PI_NODE: print 'pi'; break;
            case XML_COMMENT_NODE: print 'comment'; break;
            case XML_DOCUMENT_NODE: print 'doc'; break;
            case XML_DOCUMENT_TYPE_NODE: print 'doctype'; break;
            case XML_HTML_DOCUMENT_NODE: print 'html'; break;
            default: throw new Excpetion("unknown node type: ".$node->nodeType); break;
        }
        print ' '.$node->nodeName;
        print "\n";

        if ($node->hasAttributes()) {
            foreach($node->attributes as $attr) {
                print $indent.' '.$attr->nodeName." => ".$attr->nodeValue."\n";
            }
        }
    }

    protected function expandCurie($node, $context, $property)
    {
        $value = $node->getAttribute($property);
        if (preg_match("/^(\w+?):([\w\-]+)$/", $value, $matches)) {
            list (, $prefix, $local) = $matches;
            $prefix = strtolower($prefix);
            if (isset($context['namespaces'][$prefix])) {
                return $context['namespaces'][$prefix] . $local;
            } else {
                $uri = $node->lookupNamespaceURI($prefix);
                if ($uri) {
                    return $uri . $local;
                } else {
                    error_log("Unknown namespace: $prefix");
                }
            }
        } elseif (isset($context['namespaces'][''])) {
            return $context['namespaces'][''] . $value;
        }
    }

    protected function getSubject($node, $context)
    {
        if ($node->hasAttribute('about')) {
            return $this->resolve(
                $node->getAttribute('about')
            );
        } elseif ($context['object']) {
            return $context['object'];
        }

        if ($node->hasAttribute('typeof')) {
            #FIXME: create rdf:type triple
        }

        return $context['subject'];
    }

    protected function getObject($node)
    {
        if ($node->hasAttribute('href')) {
            return $this->resolve(
                $node->getAttribute('href')
            );
        }
    }

    protected function processNode($node, $context, $depth=1)
    {
        $this->printNode($node, $depth);

        if ($node->hasAttributes()) {

            $subject = $context['subject'];
            $object = $context['object'];

            // Step 2
            if ($node->hasAttribute('vocab')) {
                if ($vocab = $node->getAttribute('vocab')) {
                    $context['namespaces'][''] = $vocab;
                    $vocab = $context['graph']->resource( $vocab );
                    $this->addTriple($this->_baseUri, 'rdfa:usesVocabulary', $vocab);
                } else {
                    $context['namespaces'][''] = NULL;
                }
            }

            // Step 3
            if ($node->hasAttribute('prefix')) {
                $prefix = $node->getAttribute('prefix');
                $context['namespaces'][strtolower($prefix)] = $local;
            }

            // Step 4
            if ($node->hasAttributeNS(self::XML_NS, 'lang')) {
                $context['lang'] = $node->getAttributeNS(self::XML_NS, 'lang');
            }

            if ($node->hasAttribute('lang')) {
                $context['lang'] = $node->getAttribute('lang');
            }

            if (!$node->hasAttribute('rel') and !$node->hasAttribute('rev')) {
                // Step 5
                $subject = $this->getSubject($node, $context);

            } else {
                // Step 6
                $subject = $this->getSubject($node, $context);
                $object = $this->getObject($node, $context);

                if ($node->hasAttribute('rev')) {
                    $property = $this->expandCurie($node, $context, 'rev');
                    $this->addTriple($object, $property, $subject);
                }

                if ($node->hasAttribute('rel')) {
                    $property = $this->expandCurie($node, $context, 'rel');
                    $this->addTriple($subject, $property, $object);
                }
            }

            // Step 11
            if ($node->hasAttribute('property')) {
                $property = $this->expandCurie($node, $context, 'property');
                $datatype = $this->expandCurie($node, $context, 'datatype');

                if ($node->hasAttribute('content')) {
                    $value = new EasyRdf_Literal(
                        $node->getAttribute('content'),
                        $context['lang'],
                        $datatype
                    );
                } else {
                    $value = new EasyRdf_Literal(
                        $node->textContent,
                        $context['lang'],
                        $datatype
                    );
                }
                $this->addTriple($subject, $property, $value);
            }
        }

        // Step 13
        if ($node->hasChildNodes()) {
            foreach($node->childNodes as $child) {
                $this->processNode($child, $context, $depth+1);
            }
        }
    }

    /**
     * Parse RDFa into an EasyRdf_Graph
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

        if ($format != 'rdfa') {
            throw new EasyRdf_Exception(
                "EasyRdf_Parser_Rdfa does not support: $format"
            );
        }

        // Step 1: Initialise evaluation context
        $context = array(
            'namespaces' => array(),
            'skipElement' => false,
            'subject' => $this->_graph->resource($this->_baseUri),
            'property' => NULL,
            'object' => NULL,
            'lang' => NULL,
            'datatype' => NULL
        );

        libxml_use_internal_errors(true);

        $doc = new DOMDocument();
        $doc->loadXML($data, LIBXML_NONET);
        $this->processNode($doc, $context);

        return $this->_tripleCount;
    }

}
