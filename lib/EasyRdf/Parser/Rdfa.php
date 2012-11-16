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
 * Class to parse RDFa 1.1 with no external dependancies.
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
    const RDF_XML_LITERAL = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#XMLLiteral';
    const TERM_REGEXP = '/^([a-zA-Z_])([0-9a-zA-Z_\.-]*)$/';

    public $_debug = FALSE;

    /**
     * Constructor
     *
     * @return object EasyRdf_Parser_Rdfa
     */
    public function __construct()
    {
    }

    protected function addTriple($resource, $property, $value)
    {
        if ($this->_debug)
            print "Adding triple: $resource -> $property -> ".$value['type'].':'.$value['value']."\n";
        $count = $this->_graph->add($resource, $property, $value);
        $this->_tripleCount += $count;
        return $count;
    }

    protected function generateList($subject, $property, $list)
    {
        $current = $subject;
        $prop = $property;

        // Output a blank node for each item in the list
        foreach($list as $item) {
            $newNode = $this->_graph->newBNodeId();
            $this->addTriple($current, $prop, array('type' => 'bnode', 'value' => $newNode));
            $this->addTriple($newNode, 'rdf:first', $item);

            $current = $newNode;
            $prop = 'rdf:rest';
        }

        // Finally, terminate the list
        $this->addTriple(
            $current,
            $prop,
            array('type' => 'uri', 'value' => EasyRdf_Namespace::expand('rdf:nil'))
        );
    }

    protected function addToList($listMapping, $property, $value)
    {
        if ($this->_debug)
            print "Adding to list: $property -> ".$value['type'].':'.$value['value']."\n";

        // Create property in the list mapping if it doesn't already exist
        if (!isset($listMapping->$property))
           $listMapping->$property = array();
        array_push($listMapping->$property, $value);
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
        print ' '.$node->nodeName."\n";

        if ($node->hasAttributes()) {
            foreach($node->attributes as $attr) {
                print $indent.' '.$attr->nodeName." => ".$attr->nodeValue."\n";
            }
        }
    }

    protected function guessTimeDatatype($value)
    {
        if (preg_match("/^-?\d{4}-\d{2}-\d{2}(Z|[\-\+]\d{2}:\d{2})?$/", $value)) {
            return 'http://www.w3.org/2001/XMLSchema#date';
        } elseif (preg_match("/^\d{2}:\d{2}:\d{2}(Z|[\-\+]\d{2}:\d{2})?$/", $value)) {
            return 'http://www.w3.org/2001/XMLSchema#time';
        } elseif (preg_match("/^-?\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(Z|[\-\+]\d{2}:\d{2})?$/", $value)) {
            return 'http://www.w3.org/2001/XMLSchema#dateTime';
        } elseif (preg_match("/^P(\d+Y)?(\d+M)?(\d+D)?T?(\d+H)?(\d+M)?(\d+S)?$/", $value)) {
            return 'http://www.w3.org/2001/XMLSchema#duration';
        } elseif (preg_match("/^\d{4}$/", $value)) {
            return 'http://www.w3.org/2001/XMLSchema#gYear';
        } elseif (preg_match("/^\d{4}-\d{2}$/", $value)) {
            return 'http://www.w3.org/2001/XMLSchema#gYearMonth';
        } else {
            return NULL;
        }
    }

    protected function initialContext()
    {
        $context = array(
            'prefixes' => array(),
            'vocab' => NULL,
            'subject' => $this->_baseUri,
            'property' => NULL,
            'object' => NULL,
            'terms' => array(),
            'incompleteRels' => array(),
            'incompleteRevs' => array(),
            'listMapping' => NULL,
            'lang' => NULL,
            'path' => ''
        );

        // Set the default prefix
        $context['prefixes'][''] = 'http://www.w3.org/1999/xhtml/vocab#';

        // RDFa 1.1 default term mapping
        $context['terms']['describedby'] = 'http://www.w3.org/2007/05/powder-s#describedby';
        $context['terms']['license'] = 'http://www.w3.org/1999/xhtml/vocab#license';
        $context['terms']['role'] = 'http://www.w3.org/1999/xhtml/vocab#role';

        return $context;
    }

    protected function expandCurie($node, &$context, $value)
    {
        if (preg_match("/^(\w*?):(.*)$/", $value, $matches)) {
            list (, $prefix, $local) = $matches;
            $prefix = strtolower($prefix);
            if ($prefix === '_') {
                // It is a bnode
                return $this->remapBnode(substr($value, 2));
            } elseif (empty($prefix) and $context['vocab']) {
                // Empty prefix
                return $context['vocab'] . $local;
            } elseif (isset($context['prefixes'][$prefix])) {
                return $context['prefixes'][$prefix] . $local;
            } elseif ($uri = $node->lookupNamespaceURI($prefix)) {
                return $uri . $local;
            } elseif (!empty($prefix) and $uri = EasyRdf_Namespace::get($prefix)) {
                // Expand using well-known prefixes
                return $uri . $local;
            }
        }
    }

    protected function processUri($node, &$context, $value, $isProp=false)
    {
        if (preg_match("/^\[(.*)\]$/", $value, $matches)) {
            // Safe CURIE
            return $this->expandCurie($node, $context, $matches[1]);
        } elseif (preg_match(self::TERM_REGEXP, $value) and $isProp) {
            $term = strtolower($value);
            if ($context['vocab']) {
                return $context['vocab'] . $value;
            } elseif (isset($context['terms'][$term])) {
                return $context['terms'][$term];
            }
        } elseif (substr($value, 0, 2) === '_:' and $isProp) {
            return NULL;
        } else {
            $uri = $this->expandCurie($node, $context, $value);
            if ($uri) {
                return $uri;
            } else {
                $parsed = new EasyRdf_ParsedUri($value);
                if ($parsed->isAbsolute()) {
                    return $value;
                } elseif ($isProp) {
                    // Properties can't be relative URIs
                    return NULL;
                } elseif ($this->_baseUri) {
                    return $this->_baseUri->resolve($parsed);
                }
            }
        }
    }

    protected function processUriList($node, $context, $values)
    {
        if (!$values)
            return array();

        $uris = array();
        foreach(preg_split("/\s+/", $values) as $value) {
            $uri = $this->processUri($node, $context, $value, true);
            if ($uri) array_push($uris, $uri);
        }
        return $uris;
    }

    protected function processNode($node, &$context, $depth=1)
    {
        if ($this->_debug)
            $this->printNode($node, $depth);

        // Step 1: establish local variables
        $subject = NULL;
        $object = NULL;
        $revs = array();
        $rels = array();
        $lang = $context['lang'];
        $incompleteRels = array();
        $incompleteRevs = array();

        if ($node->nodeType === XML_ELEMENT_NODE)
            $context['path'] .= '/' . $node->nodeName;

        if ($node->hasAttributes()) {
            $about = $node->hasAttribute('about') ? $node->getAttribute('about') : NULL;
            $href = $node->hasAttribute('href') ? $node->getAttribute('href') : NULL;
            $resource = $node->hasAttribute('resource') ? $node->getAttribute('resource') : NULL;
            $src = $node->hasAttribute('src') ? $node->getAttribute('src') : NULL;

            $property = $node->getAttribute('property');
            $rel = $node->getAttribute('rel');
            $rev = $node->getAttribute('rev');
            $typeof = $node->getAttribute('typeof');
            $vocab = $node->getAttribute('vocab');

            // Step 2: Default vocabulary
            if ($vocab) {
                $context['vocab'] = $vocab;
                $vocab = array('type' => 'uri', 'value' => $vocab );
                $this->addTriple($this->_baseUri, 'rdfa:usesVocabulary', $vocab);
            }

            // Step 3: Set prefix mappings
            if ($node->hasAttribute('prefix')) {
                $mappings = preg_split("/\s+/", $node->getAttribute('prefix'));
                while(count($mappings)) {
                    $prefix = strtolower(array_shift($mappings));
                    $uri = array_shift($mappings);

                    if (substr($prefix, -1) === ':') {
                        $prefix = substr($prefix, 0, -1);
                    } else {
                        continue;
                    }

                    if ($prefix === '_') {
                        continue;
                    } elseif (!empty($prefix)) {
                        $context['prefixes'][$prefix] = $uri;
                        if ($this->_debug)
                            print "Prefix: $prefix => $uri\n";
                    }
                }
            }

            // Step 4
            if ($node->hasAttributeNS(self::XML_NS, 'lang')) {
                $lang = $node->getAttributeNS(self::XML_NS, 'lang');
            } elseif ($node->hasAttribute('lang')) {
                $lang = $node->getAttribute('lang');
            }

            if (!$rel and !$rev) {
                // Step 5: Establish a new subject if no rel/rev
                if ($about !== NULL) {
                    $subject = $this->processUri($node, $context, $about);
                } elseif ($resource !== NULL) {
                    $subject = $this->processUri($node, $context, $resource);
                } elseif ($href !== NULL) {
                    $subject = $this->processUri($node, $context, $href);
                } elseif ($src !== NULL) {
                    $subject = $this->processUri($node, $context, $src);
                }

            } else {
                // Step 6
                // If the current element does contain a @rel or @rev attribute, then the next step is to
                // establish both a value for new subject and a value for current object resource:
                if ($about !== NULL) {
                    $subject = $this->processUri($node, $context, $about);
                }

                if ($resource !== NULL) {
                    $object = $this->processUri($node, $context, $resource);
                } elseif ($href !== NULL) {
                    $object = $this->processUri($node, $context, $href);
                } elseif ($src !== NULL) {
                    $object = $this->processUri($node, $context, $src);
                }

                $revs = $this->processUriList($node, $context, $rev);
                $rels = $this->processUriList($node, $context, $rel);
            }

            // Establish a subject if there isn't one
            if (is_null($subject)) {
                if ($depth <= 2 or $context['path'] === '/html/head') {
                    $subject = $context['object'];
                } elseif ($depth <= 2) {
                    $subject = $this->_baseUri;
                } elseif ($typeof) {
                    $subject = $this->_graph->newBNodeId();
                } else {
                    $subject = $context['object'];
                }
            }

            // Step 7: Process @typeof if there is a subject
            if ($subject and $typeof) {
                foreach($this->processUriList($node, $context, $typeof) as $type) {
                    $this->addTriple(
                        $subject,
                        'rdf:type',
                        array('type' => 'uri', 'value' => $type)
                    );
                }
            }

            // Step 8: Create new List mapping if the subject has changed
            if ($subject and $subject !== $context['subject']) {
                $listMapping = new StdClass();
            } else {
                $listMapping = $context['listMapping'];
            }

            // Step 9: Generate triples with given object
            if ($subject and $object) {
                foreach($rels as $prop) {
                    $obj = array('type' => 'uri', 'value' => $object);
                    if ($node->hasAttribute('inlist')) {
                        $this->addToList($listMapping, $prop, $obj);
                    } else {
                        $this->addTriple($subject, $prop, $obj);
                    }
                }

                foreach($revs as $prop) {
                    $this->addTriple(
                        $object,
                        $prop,
                        array('type' => 'uri', 'value' => $subject)
                    );
                }
            } elseif ($rels or $revs) {
                // Step 10: Incomplete triples and bnode creation
                $object = $this->_graph->newBNodeId();
                if ($rels) {
                    if ($node->hasAttribute('inlist')) {
                        foreach($rels as $prop) {
                            # FIXME: add support for incomplete lists
                            if (!isset($listMapping->$prop)) {
                                $listMapping->$prop = array();
                            }
                        }
                    } else {
                        $incompleteRels = $rels;
                        if ($this->_debug)
                            print "Incomplete rels: ".implode(',',$rels)."\n";
                    }
                }

                if ($revs) {
                    $incompleteRevs = $revs;
                    if ($this->_debug)
                        print "Incomplete revs: ".implode(',',$revs)."\n";
                }
            }

            // Step 11
            if ($subject and $property) {
                $literal = array('type' => 'literal');
                $datatype = NULL;

                if ($datatype = $node->getAttribute('datatype')) {
                    $datatype = $this->processUri($node, $context, $datatype, true);
                } elseif ($lang) {
                    $literal['lang'] = $lang;
                }

                if ($node->nodeName === 'data' and $node->hasAttribute('value')) {
                    $literal['value'] = $node->getAttribute('value');
                } elseif ($node->hasAttribute('datetime')) {
                    $literal['value'] = $node->getAttribute('datetime');
                    $datetime = TRUE;
                } elseif ($node->hasAttribute('content')) {
                    $literal['value'] = $node->getAttribute('content');
                } elseif ($datatype === self::RDF_XML_LITERAL) {
                    $literal['value'] = '';
                    foreach($node->childNodes as $child) {
                        $literal['value'] .= $child->C14N();
                    }
                } else {
                    $literal['value'] = $node->textContent;
                }

                if ($datatype) {
                    $literal['datatype'] = $datatype;
                } elseif (isset($datetime) or $node->nodeName === 'time') {
                    $literal['datatype'] = $this->guessTimeDatatype($literal['value']);
                }

                // Add each of the properties
                foreach($this->processUriList($node, $context, $property) as $prop) {
                    if ($node->hasAttribute('inlist')) {
                        $this->addToList($listMapping, $prop, $literal);
                    } elseif ($subject) {
                        $this->addTriple($subject, $prop, $literal);
                    }
                }
            }

            // Step 12: Complete the incomplete triples from the evaluation context
            if ($subject and ($context['incompleteRels'] or $context['incompleteRevs'])) {
                foreach($context['incompleteRels'] as $prop) {
                    $this->addTriple(
                        $context['subject'],
                        $prop,
                        array('type' => 'uri', 'value' => $subject)
                    );
                }

                foreach($context['incompleteRevs'] as $prop) {
                    $this->addTriple(
                        $subject,
                        $prop,
                        array('type' => 'uri', 'value' => $context['subject'])
                    );
                }
            }
        }

        // Step 13: create a new evaluation context and proceed recursively
        if ($node->hasChildNodes()) {
            // Prepare a new evaluation context
            $newContext = $context;
            if ($object) {
                $newContext['object'] = $object;
            } elseif ($subject) {
                $newContext['object'] = $subject;
            } else {
                $newContext['object'] = $context['subject'];
            }
            if ($subject)
                $newContext['subject'] = $subject;
            $newContext['lang'] = $lang;
            $newContext['incompleteRels'] = $incompleteRels;
            $newContext['incompleteRevs'] = $incompleteRevs;
            if (isset($listMapping))
                $newContext['listMapping'] = $listMapping;
            foreach($node->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE)
                    $this->processNode($child, $newContext, $depth+1);
            }
        }

        // Step 14: create triples for lists
        if (!empty($listMapping)) {
            foreach($listMapping as $prop => $list) {
                if ($context['listMapping'] !== $listMapping) {
                    if ($this->_debug)
                        print "Need to create triples for $prop => ".count($list)." items\n";
                    $this->generateList($subject, $prop, $list);
                }
            }
        }
    }

    /**
     * Parse RDFa 1.1 into an EasyRdf_Graph
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

        libxml_use_internal_errors(true);

        // Parse the document into DOM
        $doc = new DOMDocument();
        $doc->loadXML($data, LIBXML_NONET);

        // Establish the base
        # FIXME: only do this if document is XHTML
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('xh', "http://www.w3.org/1999/xhtml");
        $nodeList = $xpath->query('/xh:html/xh:head/xh:base');
        if ($node = $nodeList->item(0) and $href = $node->getAttribute('href')) {
            $this->_baseUri = new EasyRdf_ParsedUri($href);
        }

        // Remove the fragment from the base URI
        $this->_baseUri->setFragment(NULL);

        // Initialise evaluation context
        $context = $this->initialContext();

        // Recursively process XML nodes
        $this->processNode($doc, $context);

        return $this->_tripleCount;
    }

}
