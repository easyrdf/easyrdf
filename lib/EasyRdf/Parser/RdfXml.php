<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2010 Nicholas J Humfrey
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
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Parser_RdfXml extends EasyRdf_Parser
{

    /**
     * Constructor
     *
     * @return object EasyRdf_Parser_Rapper
     */
    public function __construct()
    {
    }
    
    protected function init($graph, $base)
    {
      $this->base = $base;
      $this->errors = array();
      $this->warnings = array();
      $this->bnode_prefix = 'arc'.substr(md5(uniqid(rand())), 0, 4).'b';
      $this->bnode_id = 0;

      $this->graph = $graph;
      $this->encoding = false;
      $this->state = 0;
      $this->x_lang = '';
      $this->x_base = $base;
      $this->xml = 'http://www.w3.org/XML/1998/namespace';
      $this->rdf = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#';
      $this->nsp = array($this->xml => 'xml', $this->rdf => 'rdf');
      $this->s_stack = array();
      $this->s_count = 0;
      $this->target_encoding = '';
    }

  protected function createBnodeID(){
    $this->bnode_id++;
    return '_:' . $this->bnode_prefix . $this->bnode_id;
  }

  protected function calcURI($path, $base = "") {
    /* quick check */
    if (preg_match("/^[a-z0-9\_]+\:/i", $path)) {/* abs path or bnode */
      return $path;
    }
    if (preg_match('/^\$\{.*\}/', $path)) {/* placeholder, assume abs URI */
      return $path;
    }
    if (preg_match("/^\/\//", $path)) {/* net path, assume http */
      return 'http:' . $path;
    }
    /* other URIs */
    $base = $base ? $base : $this->base;
    $base = preg_replace('/\#.*$/', '', $base);
    if ($path === true) {/* empty (but valid) URIref via turtle parser: <> */
      return $base;
    }
    $path = preg_replace("/^\.\//", '', $path);
    $root = preg_match('/(^[a-z0-9]+\:[\/]{1,3}[^\/]+)[\/|$]/i', $base, $m) ? $m[1] : $base; /* w/o trailing slash */
    $base .= ($base == $root) ? '/' : '';
    if (preg_match('/^\//', $path)) {/* leading slash */
      return $root . $path;
    }
    if (!$path) {
      return $base;
    }
    if (preg_match('/^([\#\?])/', $path, $m)) {
      return preg_replace('/\\' .$m[1]. '.*$/', '', $base) . $path;
    }
    if (preg_match('/^(\&)(.*)$/', $path, $m)) {/* not perfect yet */
      return preg_match('/\?/', $base) ? $base . $m[1] . $m[2] : $base . '?' . $m[2];
    }
    if (preg_match("/^[a-z0-9]+\:/i", $path)) {/* abs path */
      return $path;
    }
    /* rel path: remove stuff after last slash */
    $base = substr($base, 0, strrpos($base, '/')+1);
    /* resolve ../ */
    while (preg_match('/^(\.\.\/)(.*)$/', $path, $m)) {
      $path = $m[2];
      $base = ($base == $root.'/') ? $base : preg_replace('/^(.*\/)[^\/]+\/$/', '\\1', $base);
    }
    return $base . $path;
  }

  protected function calcBase($path) {
    $r = $path;
    $r = preg_replace('/\#.*$/', '', $r);/* remove hash */
    $r = preg_replace('/^\/\//', 'http://', $r);/* net path (//), assume http */
    if (preg_match('/^[a-z0-9]+\:/', $r)) {/* scheme, abs path */
      while (preg_match('/^(.+\/)(\.\.\/.*)$/U', $r, $m)) {
        $r = $this->calcURI($m[1], $m[2]);
      }
      return $r;
    }
    return 'file://' . realpath($r);/* real path */
  }

  protected function initXMLParser() {
    if (!isset($this->xml_parser)) {
      $enc = preg_match('/^(utf\-8|iso\-8859\-1|us\-ascii)$/i', $this->getEncoding(), $m) ? $m[1] : 'UTF-8';
      $parser = xml_parser_create_ns($enc, '');
      xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 0);
      xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
      xml_set_element_handler($parser, 'open', 'close');
      xml_set_character_data_handler($parser, 'cdata');
      xml_set_start_namespace_decl_handler($parser, 'nsDecl');
      xml_set_object($parser, $this);
      $this->xml_parser = $parser;
    }
  }

  protected function getEncoding($src = 'config') {
    if ($src == 'parser') {
      return $this->target_encoding;
    }
    elseif (($src == 'config') && $this->encoding) {
      return $this->encoding;
    }
#    return $this->reader->getEncoding();
    return '';
  }
  
  protected function pushS(&$s) {
    $s['pos'] = $this->s_count;
    $this->s_stack[$this->s_count] = $s;
    $this->s_count++;
  }
  
  protected function popS(){/* php 4.0.x-safe */
    $r = array();
    $this->s_count--;
    for ($i = 0, $i_max = $this->s_count; $i < $i_max; $i++) {
      $r[$i] = $this->s_stack[$i];
    }
    $this->s_stack = $r;
  }
  
  protected function updateS($s) {
    $this->s_stack[$s['pos']] = $s;
  }
  
  protected function getParentS() {
    return ($this->s_count && isset($this->s_stack[$this->s_count - 1])) ? $this->s_stack[$this->s_count - 1] : false;
  }
  
  protected function getParentXBase() {
    if ($p = $this->getParentS()) {
      return isset($p['p_x_base']) && $p['p_x_base'] ? $p['p_x_base'] : (isset($p['x_base']) ? $p['x_base'] : '');
    }
    return $this->x_base;
  }

  protected function getParentXLang() {
    if ($p = $this->getParentS()) {
      return isset($p['p_x_lang']) && $p['p_x_lang'] ? $p['p_x_lang'] : (isset($p['x_lang']) ? $p['x_lang'] : '');
    }
    return $this->x_lang;
  }

  protected function addTriple($s, $p, $o, $s_type, $o_type, $o_dt = '', $o_lang = '') {
    $subject = $this->graph->resource($s);
    
    if ($o_type == 'uri' or $o_type == 'bnode') {
      $object = $this->graph->resource($o);
    } else if ($o_type == 'literal') {
      $object = new EasyRdf_Literal($o, $o_lang, $o_dt);
    }

    $subject->add($p, $object);
  }

  protected function reify($t, $s, $p, $o, $s_type, $o_type, $o_dt = '', $o_lang = '') {
    $this->addTriple($t, $this->rdf.'type', $this->rdf.'Statement', 'uri', 'uri');
    $this->addTriple($t, $this->rdf.'subject', $s, 'uri', $s_type);
    $this->addTriple($t, $this->rdf.'predicate', $p, 'uri', 'uri');
    $this->addTriple($t, $this->rdf.'object', $o, 'uri', $o_type, $o_dt, $o_lang);
  }
  
  protected function open($p, $t, $a) {
    //echo "state is $this->state\n";
    //echo "opening $t\n";
    switch($this->state) {
      case 0: return $this->h0Open($t, $a);
      case 1: return $this->h1Open($t, $a);
      case 2: return $this->h2Open($t, $a);
      case 4: return $this->h4Open($t, $a);
      case 5: return $this->h5Open($t, $a);
      case 6: return $this->h6Open($t, $a);
      default: $this->addError('open() called at state ' . $this->state . ' in '.$t);
    }
  }

  protected function close($p, $t) {
    //echo "state is $this->state\n";
    //echo "closing $t\n";
    switch($this->state){
      case 1: return $this->h1Close($t);
      case 2: return $this->h2Close($t);
      case 3: return $this->h3Close($t);
      case 4: return $this->h4Close($t);
      case 5: return $this->h5Close($t);
      case 6: return $this->h6Close($t);
      default: $this->addError('close() called at state ' . $this->state . ' in '.$t);
    }
  }

  protected function cdata($p, $d) {
    //echo "state is $this->state\n";
    //echo "cdata\n";
    switch($this->state){
      case 4: return $this->h4Cdata($d);
      case 6: return $this->h6Cdata($d);
      default: return false;
    }
  }
  
  protected function nsDecl($p, $prf, $uri) {
    $this->nsp[$uri] = isset($this->nsp[$uri]) ? $this->nsp[$uri] : $prf;
  }

  protected function h0Open($t, $a) {
    $this->state = 1;
    if ($t !== $this->rdf.'RDF') {
      $this->h1Open($t, $a);
    }
  }

  protected function h1Open($t, $a) {
    $s = array(
      'x_base' => isset($a[$this->xml.'base']) ? $this->calcURI($a[$this->xml.'base']) : $this->getParentXBase(), 
      'x_lang' => isset($a[$this->xml.'lang']) ? $a[$this->xml.'lang'] : $this->getParentXLang(),
      'li_count' => 0,
    );
    /* ID */
    if (isset($a[$this->rdf.'ID'])) {
      $s['type'] = 'uri';
      $s['value'] = $this->calcURI('#'.$a[$this->rdf.'ID'], $s['x_base']);
    }
    /* about */
    elseif (isset($a[$this->rdf.'about'])) {
      $s['type'] = 'uri';
      $s['value'] = $this->calcURI($a[$this->rdf.'about'], $s['x_base']);
    }
    /* bnode */
    else {
      $s['type'] = 'bnode';
      if (isset($a[$this->rdf.'nodeID'])) {
        $s['value'] = '_:'.$a[$this->rdf.'nodeID'];
      }
      else {
        $s['value'] = $this->createBnodeID();
      }
    }
    /* sub-node */
    if ($this->state === 4) {
      $sup_s = $this->getParentS();
      /* new collection */
      if (isset($sup_s['o_is_coll']) && $sup_s['o_is_coll']) {
        $coll = array('value' => $this->createBnodeID(), 'type' => 'bnode', 'is_coll' => true, 'x_base' => $s['x_base'], 'x_lang' => $s['x_lang']);
        $this->addTriple($sup_s['value'], $sup_s['p'], $coll['value'], $sup_s['type'], $coll['type']);
        $this->addTriple($coll['value'], $this->rdf . 'first', $s['value'], $coll['type'], $s['type']);
        $this->pushS($coll);
      }
      /* new entry in existing coll */
      elseif (isset($sup_s['is_coll']) && $sup_s['is_coll']) {
        $coll = array('value' => $this->createBnodeID(), 'type' => 'bnode', 'is_coll' => true, 'x_base' => $s['x_base'], 'x_lang' => $s['x_lang']);
        $this->addTriple($sup_s['value'], $this->rdf . 'rest', $coll['value'], $sup_s['type'], $coll['type']);
        $this->addTriple($coll['value'], $this->rdf . 'first', $s['value'], $coll['type'], $s['type']);
        $this->pushS($coll);
      }
      /* normal sub-node */
      elseif(isset($sup_s['p']) && $sup_s['p']) {
        $this->addTriple($sup_s['value'], $sup_s['p'], $s['value'], $sup_s['type'], $s['type']);
      }
    }
    /* typed node */
    if ($t !== $this->rdf.'Description') {
      $this->addTriple($s['value'], $this->rdf.'type', $t, $s['type'], 'uri');
    }
    /* (additional) typing attr */
    if (isset($a[$this->rdf.'type'])) {
      $this->addTriple($s['value'], $this->rdf.'type', $a[$this->rdf.'type'], $s['type'], 'uri');
    }
    /* Seq|Bag|Alt */
    if (in_array($t, array($this->rdf.'Seq', $this->rdf.'Bag', $this->rdf.'Alt'))) {
      $s['is_con'] = true;
    }
    /* any other attrs (skip rdf and xml, except rdf:_, rdf:value, rdf:Seq) */
    foreach($a as $k => $v) {
      if (((strpos($k, $this->xml) === false) && (strpos($k, $this->rdf) === false)) || preg_match('/(\_[0-9]+|value|Seq|Bag|Alt|Statement|Property|List)$/', $k)) {
        if (strpos($k, ':')) {
          $this->addTriple($s['value'], $k, $v, $s['type'], 'literal', '', $s['x_lang']);
        }
      }
    }
    $this->pushS($s);
    $this->state = 2;
  }

  protected function h2Open($t, $a) {
    $s = $this->getParentS();
    foreach (array('p_x_base', 'p_x_lang', 'p_id', 'o_is_coll') as $k) {
      unset($s[$k]);
    }
    /* base */
    if (isset($a[$this->xml.'base'])) {
      $s['p_x_base'] = $this->calcURI($a[$this->xml.'base'], $s['x_base']);
    }
    $b = isset($s['p_x_base']) && $s['p_x_base'] ? $s['p_x_base'] : $s['x_base'];
    /* lang */
    if (isset($a[$this->xml.'lang'])) {
      $s['p_x_lang'] = $a[$this->xml.'lang'];
    }
    $l = isset($s['p_x_lang']) && $s['p_x_lang'] ? $s['p_x_lang'] : $s['x_lang'];
    /* adjust li */
    if ($t === $this->rdf.'li') {
      $s['li_count']++;
      $t = $this->rdf.'_'.$s['li_count'];
    }
    /* set p */
    $s['p'] = $t;
		/* reification */
    if (isset($a[$this->rdf.'ID'])) {
      $s['p_id'] = $a[$this->rdf.'ID'];
    }
    $o = array('value' => '', 'type' => '', 'x_base' => $b, 'x_lang' => $l);
    /* resource/rdf:resource */
    if (isset($a['resource'])) {
      $a[$this->rdf . 'resource'] = $a['resource'];
      unset($a['resource']);
    }
    if (isset($a[$this->rdf.'resource'])) {
      $o['value'] = $this->calcURI($a[$this->rdf.'resource'], $b);
      $o['type'] = 'uri';
      $this->addTriple($s['value'], $s['p'], $o['value'], $s['type'], $o['type']);
      /* type */
      if (isset($a[$this->rdf.'type'])) {
        $this->addTriple($o['value'], $this->rdf.'type', $a[$this->rdf.'type'], 'uri', 'uri');
      }
      /* reification */
      if (isset($s['p_id'])) {
        $this->reify($this->calcURI('#'.$s['p_id'], $b), $s['value'], $s['p'], $o['value'], $s['type'], $o['type']);
        unset($s['p_id']);
      }
      $this->state = 3;
    }
    /* named bnode */
    elseif (isset($a[$this->rdf.'nodeID'])) {
      $o['value'] = '_:' . $a[$this->rdf.'nodeID'];
      $o['type'] = 'bnode';
      $this->addTriple($s['value'], $s['p'], $o['value'], $s['type'], $o['type']);
      $this->state = 3;
      /* reification */
      if (isset($s['p_id'])) {
        $this->reify($this->calcURI('#'.$s['p_id'], $b), $s['value'], $s['p'], $o['value'], $s['type'], $o['type']);
      }
    }
    /* parseType */
    elseif (isset($a[$this->rdf.'parseType'])) {
      if ($a[$this->rdf.'parseType'] === 'Literal') {
        $s['o_xml_level'] = 0;
        $s['o_xml_data'] = '';
        $s['p_xml_literal_level'] = 0;
        $s['ns'] = array();
        $this->state = 6;
      }
      elseif ($a[$this->rdf.'parseType'] === 'Resource') {
        $o['value'] = $this->createBnodeID();
        $o['type'] = 'bnode';
        $o['has_closing_tag'] = 0;
        $this->addTriple($s['value'], $s['p'], $o['value'], $s['type'], $o['type']);
        $this->pushS($o);
        /* reification */
        if (isset($s['p_id'])) {
          $this->reify($this->calcURI('#'.$s['p_id'], $b), $s['value'], $s['p'], $o['value'], $s['type'], $o['type']);
          unset($s['p_id']);
        }
        $this->state = 2;
      }
      elseif ($a[$this->rdf.'parseType'] === 'Collection') {
        $s['o_is_coll'] = true;
        $this->state = 4;
      }
    }
    /* sub-node or literal */
    else {
      $s['o_cdata'] = '';
      if (isset($a[$this->rdf.'datatype'])) {
        $s['o_datatype'] = $a[$this->rdf.'datatype'];
      }
      $this->state = 4;
    }
    /* any other attrs (skip rdf and xml) */
    foreach($a as $k => $v) {
      if (((strpos($k, $this->xml) === false) && (strpos($k, $this->rdf) === false)) || preg_match('/(\_[0-9]+|value)$/', $k)) {
        if (strpos($k, ':')) {
          if (!$o['value']) {
            $o['value'] = $this->createBnodeID();
            $o['type'] = 'bnode';
            $this->addTriple($s['value'], $s['p'], $o['value'], $s['type'], $o['type']);
          }
          /* reification */
          if (isset($s['p_id'])) {
            $this->reify($this->calcURI('#'.$s['p_id'], $b), $s['value'], $s['p'], $o['value'], $s['type'], $o['type']);
            unset($s['p_id']);
          }
          $this->addTriple($o['value'], $k, $v, $o['type'], 'literal');
          $this->state = 3;
        }
      }
    }
    $this->updateS($s);
  }

  protected function h4Open($t, $a) {
    return $this->h1Open($t, $a);
  }

  protected function h5Open($t, $a) {
    $this->state = 4;
    return $this->h4Open($t, $a);
  }

  protected function h6Open($t, $a) {
    $s = $this->getParentS();
    $data = isset($s['o_xml_data']) ? $s['o_xml_data'] : '';
    $ns = isset($s['ns']) ? $s['ns'] : array();
    $parts = $this->splitURI($t);
    if (count($parts) === 1) {
      $data .= '<'.$t;
    }
    else {
      $ns_uri = $parts[0];
      $name = $parts[1];
      if (!isset($this->nsp[$ns_uri])) {
        foreach ($this->nsp as $tmp1 => $tmp2) {
          if (strpos($t, $tmp1) === 0) {
            $ns_uri = $tmp1;
            $name = substr($t, strlen($tmp1));
            break;
          }
        }
      }
      $nsp = $this->nsp[$ns_uri];
      $data .= $nsp ? '<' . $nsp . ':' . $name : '<' . $name;
      /* ns */
      if (!isset($ns[$nsp.'='.$ns_uri]) || !$ns[$nsp.'='.$ns_uri]) {
        $data .= $nsp ? ' xmlns:'.$nsp.'="'.$ns_uri.'"' : ' xmlns="'.$ns_uri.'"';
        $ns[$nsp.'='.$ns_uri] = true;
        $s['ns'] = $ns;
      }
    }
    foreach ($a as $k => $v) {
      $parts = $this->splitURI($k);
      if (count($parts) === 1) {
        $data .= ' '.$k.'="'.$v.'"';
      }
      else {
        $ns_uri = $parts[0];
        $name = $parts[1];
        $nsp = $this->v($ns_uri, '', $this->nsp);
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

  protected function h1Close($t) {/* end of doc */
    $this->state = 0;
  }
  
  protected function h2Close($t) {/* expecting a prop, getting a close */
    if ($s = $this->getParentS()) {
      $has_closing_tag = (isset($s['has_closing_tag']) && !$s['has_closing_tag']) ? 0 : 1;
      $this->popS();
      $this->state = 5;
      if ($s = $this->getParentS()) {/* new s */
        if (!isset($s['p']) || !$s['p']) {/* p close after collection|parseType=Resource|node close after p close */
          $this->state = $this->s_count ? 4 : 1;
          if (!$has_closing_tag) {
            $this->state = 2;
          }
        }
        elseif (!$has_closing_tag) {
          $this->state = 2;
        }
      }
    }
  }
  
  protected function h3Close($t) {/* p close */
    $this->state = 2;
  }
  
  protected function h4Close($t) {/* empty p | pClose after cdata | pClose after collection */
    if ($s = $this->getParentS()) {
      $b = isset($s['p_x_base']) && $s['p_x_base'] ? $s['p_x_base'] : (isset($s['x_base']) ? $s['x_base'] : '');
      if (isset($s['is_coll']) && $s['is_coll']) {
        $this->addTriple($s['value'], $this->rdf . 'rest', $this->rdf . 'nil', $s['type'], 'uri');
        /* back to collection start */
        while ((!isset($s['p']) || ($s['p'] != $t))) {
          $sub_s = $s;
          $this->popS();
          $s = $this->getParentS();
        }
        /* reification */
        if (isset($s['p_id']) && $s['p_id']) {
          $this->reify($this->calcURI('#'.$s['p_id'], $b), $s['value'], $s['p'], $sub_s['value'], $s['type'], $sub_s['type']);
        }
        unset($s['p']);
        $this->updateS($s);
      }
      else {
        $dt = isset($s['o_datatype']) ? $s['o_datatype'] : '';
        $l = isset($s['p_x_lang']) && $s['p_x_lang'] ? $s['p_x_lang'] : (isset($s['x_lang']) ? $s['x_lang'] : '');
        $o = array('type' => 'literal', 'value' => $s['o_cdata']);
        $this->addTriple($s['value'], $s['p'], $o['value'], $s['type'], $o['type'], $dt, $l);
        /* reification */
        if (isset($s['p_id']) && $s['p_id']) {
          $this->reify($this->calcURI('#'.$s['p_id'], $b), $s['value'], $s['p'], $o['value'], $s['type'], $o['type'], $dt, $l);
        }
        unset($s['o_cdata']);
        unset($s['o_datatype']);
        unset($s['p']);
        $this->updateS($s);
      }
      $this->state = 2;
    }
  }
  
  protected function h5Close($t) {/* p close */
    if ($s = $this->getParentS()) {
      unset($s['p']);
      $this->updateS($s);
      $this->state = 2;
    }
  }

  protected function h6Close($t) {
    if ($s = $this->getParentS()) {
      $l = isset($s['p_x_lang']) && $s['p_x_lang'] ? $s['p_x_lang'] : (isset($s['x_lang']) ? $s['x_lang'] : '');
      $data = $s['o_xml_data'];
      $level = $s['o_xml_level'];
      if ($level === 0) {/* pClose */
        $this->addTriple($s['value'], $s['p'], trim($data, ' '), $s['type'], 'literal', $this->rdf.'XMLLiteral', $l);
        unset($s['o_xml_data']);
        $this->state = 2;
      }
      else {
        $parts = $this->splitURI($t);
        if (count($parts) == 1) {
          $data .= '</'.$t.'>';
        }
        else {
          $ns_uri = $parts[0];
          $name = $parts[1];
          if (!isset($this->nsp[$ns_uri])) {
            foreach ($this->nsp as $tmp1 => $tmp2) {
              if (strpos($t, $tmp1) === 0) {
                $ns_uri = $tmp1;
                $name = substr($t, strlen($tmp1));
                break;
              }
            }
          }
          $nsp = $this->nsp[$ns_uri];
          $data .= $nsp ? '</'.$nsp.':'.$name.'>' : '</'.$name.'>';
        }
        $s['o_xml_data'] = $data;
        $s['o_xml_level'] = $level - 1;
        if ($t == $s['p']) {/* xml container prop */
          $s['p_xml_literal_level']--;
        }
      }
      $this->updateS($s);
    }
  }

  protected function h4Cdata($d) {
    if ($s = $this->getParentS()) {
      $s['o_cdata'] = isset($s['o_cdata']) ? $s['o_cdata'] . $d : $d;
      $this->updateS($s);
    }
  }

  protected function h6Cdata($d) {
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
      * @param string $graph    the graph to load the data into
      * @param string $data     the RDF document data
      * @param string $format   the format of the input data
      * @param string $baseUri  the base URI of the data being parsed
      * @return boolean         true if parsing was successful
      */
    public function parse($graph, $data, $format, $baseUri, $iso_fallback = false)
    {
        parent::checkParseParams($graph, $data, $format, $baseUri);

        if ($format != 'rdfxml') {
            throw new EasyRdf_Exception(
                "EasyRdf_Parser_RdfXml does not support: $format"
            );
        }

        $this->init($graph, $baseUri);

        /* xml parser */
        $this->initXMLParser();

        
        /* parse */
        if ($iso_fallback) {
          $data = '<?xml version="1.0" encoding="ISO-8859-1"?>' . "\n" . preg_replace('/^\<\?xml [^\>]+\?\>\s*/s', '', $data);
        }
        if (!xml_parse($this->xml_parser, $data, false)) {
          $error_str = xml_error_string(xml_get_error_code($this->xml_parser));
          $line = xml_get_current_line_number($this->xml_parser);
          $this->tmp_error = 'XML error: "' . $error_str . '" at line ' . $line . ' (parsing as ' . $this->getEncoding() . ')';
          if (!$iso_fallback && preg_match("/Invalid character/i", $error_str)) {
            xml_parser_free($this->xml_parser);
            unset($this->xml_parser);
            $this->encoding = 'ISO-8859-1';
            return $this->parse($graph, $data, $format, $baseUri, true);
          }
          else {
            return $this->addError($this->tmp_error);
          }
        }

        $this->target_encoding = xml_parser_get_option($this->xml_parser, XML_OPTION_TARGET_ENCODING);
        xml_parser_free($this->xml_parser);


        // Success
        return true;
    }
}

EasyRdf_Format::registerParser('rdfxml', 'EasyRdf_Parser_RdfXml');
