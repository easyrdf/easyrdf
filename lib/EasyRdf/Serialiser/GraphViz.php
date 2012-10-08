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
 * Class to serialise an EasyRdf_Graph to GraphViz
 * Depends upon the GraphViz 'dot' command line tools.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2012 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_Serialiser_GraphViz extends EasyRdf_Serialiser
{
    private $_dotCommand = 'dot';
    private $_useLabels = false;
    private $_onlyLabelled = false;
    private $_attributes = array('charset' => 'utf-8');

    /**
     * Constructor
     *
     * @return object EasyRdf_Serialiser_GraphViz
     */
    public function __construct()
    {
    }

    public function setDotCommand($cmd)
    {
        $this->_dotCommand = $cmd;
    }

    public function getDotCommand()
    {
        return $this->_dotCommand;
    }

    public function getUseLabels()
    {
        return $this->_useLabels;
    }

    public function setUseLabels($useLabels)
    {
        $this->_useLabels = $useLabels;
        return $this;
    }

    public function getOnlyLabelled()
    {
        return $this->_onlyLabelled;
    }

    public function setOnlyLabelled($onlyLabelled)
    {
        $this->_onlyLabelled = $onlyLabelled;
        return $this;
    }

    public function setAttribute($name, $value)
    {
        $this->_attributes[$name] = $value;
        return $this;
    }

    public function getAttribute($name)
    {
        return $this->_attributes[$name];
    }

    protected function nodeName($entity)
    {
        if ($entity instanceof EasyRdf_Resource) {
            if ($entity->isBnode()) {
                return "B".$entity->getUri();
            } else {
                return "R".$entity->getUri();
            }
        } else {
            return "L".$entity;
        }
    }

    /**
     * Returns a safe "ID" in DOT syntax
     *
     * @param string  $input string to use as "ID"
     * @return string The escaped string
     * @ignore
     */
    protected function escape($input)
    {
        if (preg_match('/^([a-z_][a-z_0-9]*|-?(\.[0-9]+|[0-9]+(\.[0-9]*)?))$/i', $input)) {
            return $input;
        } else {
            return '"'.str_replace(
                array("\r\n", "\n", "\r", '"'),
                array('\n',   '\n', '\n', '\"'),
                $input
            ).'"';
        }
    }

    protected function escapeAttributes($array)
    {
        $items = '';
        foreach ($array as $k => $v) {
            $items[] = $this->escape($k).'='.$this->escape($v);
        }
        return '['.implode(',', $items).']';
    }

    protected function serialiseRow($node1, $node2=null, $attributes=array())
    {
        $result = '  '.$this->escape($node1);
        if ($node2)
            $result .= ' -> '.$this->escape($node2);
        if (count($attributes))
            $result .= ' '.$this->escapeAttributes($attributes);
        return $result.";\n";
    }

    protected function serialiseDot($graph)
    {
        $result = "digraph {\n";

        // Write the graph attributes
        foreach ($this->_attributes as $k => $v) {
            $result .= '  '.$this->escape($k).'='.$this->escape($v).";\n";
        }

        // Go through each of the properties and write the edges
        $nodes = array();
        $result .= "\n  // Edges\n";
        foreach ($graph->resources() as $resource) {
            $name1 = $this->nodeName($resource);
            foreach ($resource->propertyUris() as $property) {
                $label = null;
                if ($this->_useLabels)
                    $label = $graph->resource($property)->label();
                if ($label === null) {
                    if ($this->_onlyLabelled == true)
                        continue;
                    else
                        $label = EasyRdf_Namespace::shorten($property);
                }
                foreach ($resource->all("<$property>") as $value) {
                    $name2 = $this->nodeName($value);
                    $nodes[$name1] = $resource;
                    $nodes[$name2] = $value;
                    $result .= $this->serialiseRow(
                        $name1, $name2,
                        array('label' => $label)
                    );
                }
            }
        }

        ksort($nodes);

        $result .= "\n  // Nodes\n";
        foreach ($nodes as $name => $node) {
            $type = substr($name, 0, 1);
            $label = '';
            if ($type == 'R') {
                if ($this->_useLabels)
                    $label = $node->label();
                if (!$label)
                    $label = $node->shorten();
                if (!$label)
                    $label = $node->getURI();
                    $result .= $this->serialiseRow(
                        $name, null,
                        array(
                            'URL'   => $node->getURI(),
                            'label' => $label,
                            'shape' => 'ellipse',
                            'color' => 'blue'
                        )
                    );
            } elseif ($type == 'B') {
                if ($this->_useLabels)
                    $label = $node->label();
                    $result .= $this->serialiseRow(
                        $name, null,
                        array(
                            'label' => $label,
                            'shape' => 'circle',
                            'color' => 'green'
                        )
                    );
            } else {
                $result .= $this->serialiseRow(
                    $name, null,
                    array(
                        'label' => strval($node),
                        'shape' => 'record',
                    )
                );
            }

        }

        $result .= "}\n";

        return $result;
    }

    public function renderImage($graph, $format='png')
    {
        $dot = $this->serialiseDot($graph);

        return EasyRdf_Utils::execCommandPipe(
            $this->_dotCommand, array("-T$format"), $dot
        );
    }

    /**
     * Serialise an EasyRdf_Graph into a GraphViz dot document.
     *
     * Supported output format names: dot, gif, png, svg
     *
     * @param string $graph An EasyRdf_Graph object.
     * @param string $format The name of the format to convert to.
     * @return string The RDF in the new desired format.
     */
    public function serialise($graph, $format)
    {
        parent::checkSerialiseParams($graph, $format);

        switch($format) {
          case 'dot':
              return $this->serialiseDot($graph);
          case 'png':
          case 'gif':
          case 'svg':
              return $this->renderImage($graph, $format);
          default:
              throw new EasyRdf_Exception(
                  "EasyRdf_Serialiser_GraphViz does not support: $format"
              );
        }
    }

}
