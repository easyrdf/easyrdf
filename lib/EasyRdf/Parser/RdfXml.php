<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2010-2011 Nicholas J Humfrey
 * Copyright (c) 2004-2010 Benjamin Nowack (based on ARC2_RDFXMLParser.php)
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
 * @copyright  Copyright (c) 2010 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */


/**
 * A pure-php class to parse RDF/XML.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2010 Nicholas J Humfrey
 *             Copyright (c) 2004-2010 Benjamin Nowack (based on ARC2_RDFXMLParser.php)
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Parser_RdfXml extends EasyRdf_Parser
{
    private $_state;
    private $_xLang;
    private $_xBase;
    private $_xml;
    private $_rdf;
    private $_nsp;
    private $_sStack;
    private $_sCount;

    /**
     * Constructor
     *
     * @return object EasyRdf_Parser_RdfXml
     */
    public function __construct()
    {
    }

    /** @ignore */
    protected function init($graph, $base)
    {
        $this->_graph = $graph;
        $this->_state = 0;
        $this->_xLang = null;
        $this->_xBase = new EasyRdf_ParsedUri($base);
        $this->_xml = 'http://www.w3.org/XML/1998/namespace';
        $this->_rdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
        $this->_nsp = array($this->_xml => 'xml', $this->_rdf => 'rdf');
        $this->_sStack = array();
        $this->_sCount = 0;
    }

    /** @ignore */
    protected function initXMLParser()
    {
        if (!isset($this->_xmlParser)) {
            $parser = xml_parser_create_ns('UTF-8', '');
            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            xml_set_element_handler($parser, 'startElementHandler', 'endElementHandler');
            xml_set_character_data_handler($parser, 'cdataHandler');
            xml_set_start_namespace_decl_handler($parser, 'newNamespaceHandler');
            xml_set_object($parser, $this);
            $this->_xmlParser = $parser;
        }
    }

    /** @ignore */
    protected function pushS(&$s)
    {
        $s['pos'] = $this->_sCount;
        $this->_sStack[$this->_sCount] = $s;
        $this->_sCount++;
    }

    /** @ignore */
    protected function popS()
    {
        $r = array();
        $this->_sCount--;
        for ($i = 0, $iMax = $this->_sCount; $i < $iMax; $i++) {
            $r[$i] = $this->_sStack[$i];
        }
        $this->_sStack = $r;
    }

    /** @ignore */
    protected function updateS($s)
    {
        $this->_sStack[$s['pos']] = $s;
    }

    /** @ignore */
    protected function getParentS()
    {
        if ($this->_sCount && isset($this->_sStack[$this->_sCount - 1])) {
            return $this->_sStack[$this->_sCount - 1];
        } else {
            return false;
        }
    }

    /** @ignore */
    protected function getParentXBase()
    {
        if ($p = $this->getParentS()) {
            if (isset($p['p_x_base']) && $p['p_x_base']) {
                return $p['p_x_base'];
            } elseif (isset($p['x_base'])) {
                return $p['x_base'];
            } else {
                return '';
            }
        } else {
            return $this->_xBase;
        }
    }

    /** @ignore */
    protected function getParentXLang()
    {
        if ($p = $this->getParentS()) {
            if (isset($p['p_x_lang']) && $p['p_x_lang']) {
                return $p['p_x_lang'];
            } elseif (isset($p['x_lang'])) {
                return $p['x_lang'];
            } else {
                return null;
            }
        } else {
            return $this->_xLang;
        }
    }

    /** @ignore */
    protected function splitURI($v)
    {
        /* auto-splitting on / or # */
        if (preg_match('/^(.*[\/\#])([^\/\#]+)$/', $v, $m)) return array($m[1], $m[2]);
        /* auto-splitting on last special char, e.g. urn:foo:bar */
        if (preg_match('/^(.*[\:\/])([^\:\/]+)$/', $v, $m)) return array($m[1], $m[2]);
        return array($v, '');
    }

    /** @ignore */
    protected function add($s, $p, $o, $sType, $oType, $oDatatype = null, $oLang = null)
    {
        $this->addTriple(
            $s, $p,
            array(
                'type' => $oType,
                'value' => $o,
                'lang' => $oLang,
                'datatype' => $oDatatype
            )
        );
    }

    /** @ignore */
    protected function reify($t, $s, $p, $o, $sType, $oType, $oDatatype=null, $oLang=null)
    {
        $this->add($t, $this->_rdf.'type', $this->_rdf.'Statement', 'uri', 'uri');
        $this->add($t, $this->_rdf.'subject', $s, 'uri', $sType);
        $this->add($t, $this->_rdf.'predicate', $p, 'uri', 'uri');
        $this->add($t, $this->_rdf.'object', $o, 'uri', $oType, $oDatatype, $oLang);
    }

    /** @ignore */
    protected function startElementHandler($p, $t, $a)
    {
        switch($this->_state) {
            case 0:
                return $this->startState0($t, $a);
            case 1:
                return $this->startState1($t, $a);
            case 2:
                return $this->startState2($t, $a);
            case 4:
                return $this->startState4($t, $a);
            case 5:
                return $this->startState5($t, $a);
            case 6:
                return $this->startState6($t, $a);
            default:
                throw new EasyRdf_Exception(
                    'startElementHandler() called at state ' . $this->_state . ' in '.$t
                );
        }
    }

    /** @ignore */
    protected function endElementHandler($p, $t)
    {
        switch($this->_state){
            case 1:
                return $this->endState1($t);
            case 2:
                return $this->endState2($t);
            case 3:
                return $this->endState3($t);
            case 4:
                return $this->endState4($t);
            case 5:
                return $this->endState5($t);
            case 6:
                return $this->endState6($t);
            default:
                throw new EasyRdf_Exception(
                    'endElementHandler() called at state ' . $this->_state . ' in '.$t
                );
        }
    }

    /** @ignore */
    protected function cdataHandler($p, $d)
    {
        switch($this->_state){
            case 4:
                return $this->cdataState4($d);
            case 6:
                return $this->cdataState6($d);
            default:
                return false;
        }
    }

    /** @ignore */
    protected function newNamespaceHandler($p, $prf, $uri)
    {
        $this->_nsp[$uri] = isset($this->_nsp[$uri]) ? $this->_nsp[$uri] : $prf;
    }

    /** @ignore */
    protected function startState0($t, $a)
    {
        $this->_state = 1;
        if ($t !== $this->_rdf.'RDF') {
            $this->startState1($t, $a);
        }
    }

    /** @ignore */
    protected function startState1($t, $a)
    {
        $s = array(
            'x_base' => $this->getParentXBase(),
            'x_lang' => $this->getParentXLang(),
            'li_count' => 0,
        );

        if (isset($a[$this->_xml.'base'])) {
            $s['x_base'] = $this->_xBase->resolve($a[$this->_xml.'base']);
        }

        if (isset($a[$this->_xml.'lang'])) {
            $s['x_lang'] = $a[$this->_xml.'lang'];
        }

        /* ID */
        if (isset($a[$this->_rdf.'ID'])) {
            $s['type'] = 'uri';
            $s['value'] = $s['x_base']->resolve('#'.$a[$this->_rdf.'ID']);
            /* about */
        } elseif (isset($a[$this->_rdf.'about'])) {
            $s['type'] = 'uri';
            $s['value'] = $s['x_base']->resolve($a[$this->_rdf.'about']);
            /* bnode */
        } else {
            $s['type'] = 'bnode';
            if (isset($a[$this->_rdf.'nodeID'])) {
                $s['value'] = $this->remapBnode($a[$this->_rdf.'nodeID']);
            } else {
                $s['value'] = $this->_graph->newBNodeId();
            }
        }

        /* sub-node */
        if ($this->_state === 4) {
            $supS = $this->getParentS();
            /* new collection */
            if (isset($supS['o_is_coll']) && $supS['o_is_coll']) {
                $coll = array(
                    'type' => 'bnode',
                    'value' => $this->_graph->newBNodeId(),
                    'is_coll' => true,
                    'x_base' => $s['x_base'],
                    'x_lang' => $s['x_lang']
                );
                $this->add($supS['value'], $supS['p'], $coll['value'], $supS['type'], $coll['type']);
                $this->add($coll['value'], $this->_rdf.'first', $s['value'], $coll['type'], $s['type']);
                $this->pushS($coll);

            /* new entry in existing coll */
            } elseif (isset($supS['is_coll']) && $supS['is_coll']) {
                $coll = array(
                'type' => 'bnode',
                'value' => $this->_graph->newBNodeId(),
                'is_coll' => true,
                'x_base' => $s['x_base'],
                'x_lang' => $s['x_lang']
                );
                $this->add($supS['value'], $this->_rdf.'rest', $coll['value'], $supS['type'], $coll['type']);
                $this->add($coll['value'], $this->_rdf.'first', $s['value'], $coll['type'], $s['type']);
                $this->pushS($coll);
                /* normal sub-node */
            } elseif (isset($supS['p']) && $supS['p']) {
                $this->add($supS['value'], $supS['p'], $s['value'], $supS['type'], $s['type']);
            }
        }
        /* typed node */
        if ($t !== $this->_rdf.'Description') {
            $this->add($s['value'], $this->_rdf.'type', $t, $s['type'], 'uri');
        }
        /* (additional) typing attr */
        if (isset($a[$this->_rdf.'type'])) {
            $this->add($s['value'], $this->_rdf.'type', $a[$this->_rdf.'type'], $s['type'], 'uri');
        }

        /* Seq|Bag|Alt */
//         if (in_array($t, array($this->_rdf.'Seq', $this->_rdf.'Bag', $this->_rdf.'Alt'))) {
//             # FIXME: what is this?
//             $s['is_con'] = true;
//         }

        /* any other attrs (skip rdf and xml, except rdf:_, rdf:value, rdf:Seq) */
        foreach ($a as $k => $v) {
            if (((strpos($k, $this->_xml) === false) && (strpos($k, $this->_rdf) === false)) ||
                preg_match('/(\_[0-9]+|value|Seq|Bag|Alt|Statement|Property|List)$/', $k)) {
                if (strpos($k, ':')) {
                    $this->add($s['value'], $k, $v, $s['type'], 'literal', null, $s['x_lang']);
                }
            }
        }
        $this->pushS($s);
        $this->_state = 2;
    }

    /** @ignore */
    protected function startState2($t, $a)
    {
        $s = $this->getParentS();
        foreach (array('p_x_base', 'p_x_lang', 'p_id', 'o_is_coll') as $k) {
            unset($s[$k]);
        }
        /* base */
        if (isset($a[$this->_xml.'base'])) {
            $s['p_x_base'] = $s['x_base']->resolve($a[$this->_xml.'base']);
        }
        $b = isset($s['p_x_base']) && $s['p_x_base'] ? $s['p_x_base'] : $s['x_base'];
        /* lang */
        if (isset($a[$this->_xml.'lang'])) {
            $s['p_x_lang'] = $a[$this->_xml.'lang'];
        }
        $l = isset($s['p_x_lang']) && $s['p_x_lang'] ? $s['p_x_lang'] : $s['x_lang'];
        /* adjust li */
        if ($t === $this->_rdf.'li') {
            $s['li_count']++;
            $t = $this->_rdf.'_'.$s['li_count'];
        }
        /* set p */
        $s['p'] = $t;
        /* reification */
        if (isset($a[$this->_rdf.'ID'])) {
            $s['p_id'] = $a[$this->_rdf.'ID'];
        }
        $o = array('value' => null, 'type' => null, 'x_base' => $b, 'x_lang' => $l);
        /* resource/rdf:resource */
        if (isset($a['resource'])) {
            $a[$this->_rdf.'resource'] = $a['resource'];
            unset($a['resource']);
        }
        if (isset($a[$this->_rdf.'resource'])) {
            $o['type'] = 'uri';
            $o['value'] = $b->resolve($a[$this->_rdf.'resource']);
            $this->add($s['value'], $s['p'], $o['value'], $s['type'], $o['type']);
            /* type */
            if (isset($a[$this->_rdf.'type'])) {
                $this->add(
                    $o['value'], $this->_rdf.'type',
                    $a[$this->_rdf.'type'],
                    'uri', 'uri'
                );
            }
            /* reification */
            if (isset($s['p_id'])) {
                $this->reify(
                    $b->resolve('#'.$s['p_id']),
                    $s['value'], $s['p'], $o['value'],
                    $s['type'], $o['type']
                );
                unset($s['p_id']);
            }
            $this->_state = 3;
        /* named bnode */
        } elseif (isset($a[$this->_rdf.'nodeID'])) {
            $o['value'] = $this->remapBnode($a[$this->_rdf.'nodeID']);
            $o['type'] = 'bnode';
            $this->add($s['value'], $s['p'], $o['value'], $s['type'], $o['type']);
            $this->_state = 3;
            /* reification */
            if (isset($s['p_id'])) {
                $this->reify(
                    $b->resolve('#'.$s['p_id']),
                    $s['value'], $s['p'], $o['value'],
                    $s['type'], $o['type']
                );
            }
            /* parseType */
        } elseif (isset($a[$this->_rdf.'parseType'])) {
            if ($a[$this->_rdf.'parseType'] === 'Literal') {
                $s['o_xml_level'] = 0;
                $s['o_xml_data'] = '';
                $s['p_xml_literal_level'] = 0;
                $s['ns'] = array();
                $this->_state = 6;
            } elseif ($a[$this->_rdf.'parseType'] === 'Resource') {
                $o['value'] = $this->_graph->newBNodeId();
                $o['type'] = 'bnode';
                $o['hasClosingTag'] = 0;
                $this->add($s['value'], $s['p'], $o['value'], $s['type'], $o['type']);
                $this->pushS($o);
                /* reification */
                if (isset($s['p_id'])) {
                    $this->reify(
                        $b->resolve('#'.$s['p_id']),
                        $s['value'], $s['p'], $o['value'],
                        $s['type'], $o['type']
                    );
                    unset($s['p_id']);
                }
                $this->_state = 2;
            } elseif ($a[$this->_rdf.'parseType'] === 'Collection') {
                $s['o_is_coll'] = true;
                $this->_state = 4;
            }
        /* sub-node or literal */
        } else {
            $s['o_cdata'] = '';
            if (isset($a[$this->_rdf.'datatype'])) {
                $s['o_datatype'] = $a[$this->_rdf.'datatype'];
            }
            $this->_state = 4;
        }
        /* any other attrs (skip rdf and xml) */
        foreach ($a as $k => $v) {
            if (((strpos($k, $this->_xml) === false) &&
             (strpos($k, $this->_rdf) === false)) ||
             preg_match('/(\_[0-9]+|value)$/', $k)) {
                if (strpos($k, ':')) {
                    if (!$o['value']) {
                        $o['value'] = $this->_graph->newBNodeId();
                        $o['type'] = 'bnode';
                        $this->add($s['value'], $s['p'], $o['value'], $s['type'], $o['type']);
                    }
                    /* reification */
                    if (isset($s['p_id'])) {
                        $this->reify(
                            $b->resolve('#'.$s['p_id']),
                            $s['value'], $s['p'], $o['value'],
                            $s['type'], $o['type']
                        );
                        unset($s['p_id']);
                    }
                    $this->add($o['value'], $k, $v, $o['type'], 'literal');
                    $this->_state = 3;
                }
            }
        }
        $this->updateS($s);
    }

    /** @ignore */
    protected function startState4($t, $a)
    {
        return $this->startState1($t, $a);
    }

    /** @ignore */
    protected function startState5($t, $a)
    {
        $this->_state = 4;
        return $this->startState4($t, $a);
    }

    /** @ignore */
    protected function startState6($t, $a)
    {
        $s = $this->getParentS();
        $data = isset($s['o_xml_data']) ? $s['o_xml_data'] : '';
        $ns = isset($s['ns']) ? $s['ns'] : array();
        $parts = $this->splitURI($t);
        if (count($parts) === 1) {
            $data .= '<'.$t;
        } else {
            $nsUri = $parts[0];
            $name = $parts[1];
            if (!isset($this->_nsp[$nsUri])) {
                foreach ($this->_nsp as $tmp1 => $tmp2) {
                    if (strpos($t, $tmp1) === 0) {
                        $nsUri = $tmp1;
                        $name = substr($t, strlen($tmp1));
                        break;
                    }
                }
            }

            $nsp = isset($this->_nsp[$nsUri]) ? $this->_nsp[$nsUri] : '';
            $data .= $nsp ? '<' . $nsp . ':' . $name : '<' . $name;
            /* ns */
            if (!isset($ns[$nsp.'='.$nsUri]) || !$ns[$nsp.'='.$nsUri]) {
                $data .= $nsp ? ' xmlns:'.$nsp.'="'.$nsUri.'"' : ' xmlns="'.$nsUri.'"';
                $ns[$nsp.'='.$nsUri] = true;
                $s['ns'] = $ns;
            }
        }
        foreach ($a as $k => $v) {
            $parts = $this->splitURI($k);
            if (count($parts) === 1) {
                $data .= ' '.$k.'="'.$v.'"';
            } else {
                $nsUri = $parts[0];
                $name = $parts[1];
                $nsp = isset($this->_nsp[$nsUri]) ? $this->_nsp[$nsUri] : '';
                $data .= $nsp ? ' '.$nsp.':'.$name.'="'.$v.'"' : ' '.$name.'="'.$v.'"' ;
            }
        }
        $data .= '>';
        $s['o_xml_data'] = $data;
        $s['o_xml_level'] = isset($s['o_xml_level']) ? $s['o_xml_level'] + 1 : 1;
        if ($t == $s['p']) {/* xml container prop */
            $s['p_xml_literal_level'] = isset($s['p_xml_literal_level']) ? $s['p_xml_literal_level'] + 1 : 1;
        }
        $this->updateS($s);
    }

    /** @ignore */
    protected function endState1($t)
    {
        /* end of doc */
        $this->_state = 0;
    }

    /** @ignore */
    protected function endState2($t)
    {
        /* expecting a prop, getting a close */
        if ($s = $this->getParentS()) {
            $hasClosingTag = (isset($s['hasClosingTag']) && !$s['hasClosingTag']) ? 0 : 1;
            $this->popS();
            $this->_state = 5;
            if ($s = $this->getParentS()) {
                /* new s */
                if (!isset($s['p']) || !$s['p']) {
                    /* p close after collection|parseType=Resource|node close after p close */
                    $this->_state = $this->_sCount ? 4 : 1;
                    if (!$hasClosingTag) {
                        $this->_state = 2;
                    }
                } elseif (!$hasClosingTag) {
                    $this->_state = 2;
                }
            }
        }
    }

    /** @ignore */
    protected function endState3($t)
    {
        /* p close */
        $this->_state = 2;
    }

    /** @ignore */
    protected function endState4($t)
    {
        /* empty p | pClose after cdata | pClose after collection */
        if ($s = $this->getParentS()) {
            $b = isset($s['p_x_base']) && $s['p_x_base'] ?
                $s['p_x_base'] : (isset($s['x_base']) ? $s['x_base'] : '');
            if (isset($s['is_coll']) && $s['is_coll']) {
                $this->add($s['value'], $this->_rdf.'rest', $this->_rdf.'nil', $s['type'], 'uri');
                /* back to collection start */
                while ((!isset($s['p']) || ($s['p'] != $t))) {
                    $subS = $s;
                    $this->popS();
                    $s = $this->getParentS();
                }
                /* reification */
                if (isset($s['p_id']) && $s['p_id']) {
                    $this->reify(
                        $b->resolve('#'.$s['p_id']),
                        $s['value'], $s['p'], $subS['value'],
                        $s['type'], $subS['type']
                    );
                }
                unset($s['p']);
                $this->updateS($s);
            } else {
                $dt = isset($s['o_datatype']) ? $s['o_datatype'] : null;
                $l = isset($s['p_x_lang']) && $s['p_x_lang'] ?
                     $s['p_x_lang'] : (isset($s['x_lang']) ? $s['x_lang'] : null);
                $o = array('type' => 'literal', 'value' => $s['o_cdata']);
                $this->add(
                    $s['value'], $s['p'],
                    $o['value'], $s['type'],
                    $o['type'], $dt, $l
                );
                /* reification */
                if (isset($s['p_id']) && $s['p_id']) {
                    $this->reify(
                        $b->resolve('#'.$s['p_id']),
                        $s['value'], $s['p'],
                        $o['value'], $s['type'],
                        $o['type'], $dt, $l
                    );
                }
                unset($s['o_cdata']);
                unset($s['o_datatype']);
                unset($s['p']);
                $this->updateS($s);
            }
            $this->_state = 2;
        }
    }

    /** @ignore */
    protected function endState5($t)
    {
        /* p close */
        if ($s = $this->getParentS()) {
            unset($s['p']);
            $this->updateS($s);
            $this->_state = 2;
        }
    }

    /** @ignore */
    protected function endState6($t)
    {
        if ($s = $this->getParentS()) {
            $l = isset($s['p_x_lang']) && $s['p_x_lang'] ?
                $s['p_x_lang'] :
                (isset($s['x_lang']) ? $s['x_lang'] : null);
            $data = $s['o_xml_data'];
            $level = $s['o_xml_level'];
            if ($level === 0) {
                /* pClose */
                $this->add(
                    $s['value'], $s['p'],
                    trim($data, ' '), $s['type'],
                    'literal', $this->_rdf.'XMLLiteral', $l
                );
                unset($s['o_xml_data']);
                $this->_state = 2;
            } else {
                $parts = $this->splitURI($t);
                if (count($parts) == 1) {
                    $data .= '</'.$t.'>';
                } else {
                    $nsUri = $parts[0];
                    $name = $parts[1];
                    if (!isset($this->_nsp[$nsUri])) {
                        foreach ($this->_nsp as $tmp1 => $tmp2) {
                            if (strpos($t, $tmp1) === 0) {
                                $nsUri = $tmp1;
                                $name = substr($t, strlen($tmp1));
                                break;
                            }
                        }
                    }
                    $nsp = isset($this->_nsp[$nsUri]) ? $this->_nsp[$nsUri] : '';
                    $data .= $nsp ? '</'.$nsp.':'.$name.'>' : '</'.$name.'>';
                }
                $s['o_xml_data'] = $data;
                $s['o_xml_level'] = $level - 1;
                if ($t == $s['p']) {
                    /* xml container prop */
                    $s['p_xml_literal_level']--;
                }
            }
            $this->updateS($s);
        }
    }

    /** @ignore */
    protected function cdataState4($d)
    {
        if ($s = $this->getParentS()) {
            $s['o_cdata'] = isset($s['o_cdata']) ? $s['o_cdata'] . $d : $d;
            $this->updateS($s);
        }
    }

    /** @ignore */
    protected function cdataState6($d)
    {
        if ($s = $this->getParentS()) {
            if (isset($s['o_xml_data']) || preg_match("/[\n\r]/", $d) || trim($d)) {
                $d = htmlspecialchars($d, ENT_NOQUOTES);
                $s['o_xml_data'] = isset($s['o_xml_data']) ? $s['o_xml_data'] . $d : $d;
            }
            $this->updateS($s);
        }
    }

    /**
      * Parse an RDF/XML document into an EasyRdf_Graph
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

        if ($format != 'rdfxml') {
            throw new EasyRdf_Exception(
                "EasyRdf_Parser_RdfXml does not support: $format"
            );
        }

        $this->init($graph, $baseUri);
        $this->resetBnodeMap();

        /* xml parser */
        $this->initXMLParser();

        /* parse */
        if (!xml_parse($this->_xmlParser, $data, false)) {
            throw new EasyRdf_Exception(
                'XML error: "' . xml_error_string(xml_get_error_code($this->_xmlParser)) .
                '" at line ' . xml_get_current_line_number($this->_xmlParser)
            );
        }

        xml_parser_free($this->_xmlParser);

        return $this->_tripleCount;
    }
}
