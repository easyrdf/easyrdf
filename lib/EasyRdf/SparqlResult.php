<?php

class EasyRdf_SparqlResult extends ArrayIterator
{
    protected $_type = null;
    protected $_boolean = null;

    protected $_ordered = null;
    protected $_distinct = null;
    protected $_fields = array();

    public function __construct($data, $mimeType)
    {
        if ($mimeType == 'application/sparql-results+xml') {
            return $this->_parseXml($data);
        } else if ($mimeType == 'application/sparql-results+json') {
            return $this->_parseJson($data);
        } else {
            throw new EasyRdf_Exception(
                "Unable to parse SPARQL results in format: $mimeType"
            );
        }
    }
    
    public function getType()
    {
        return $this->_type;
    }
    
    public function getBoolean()
    {
        return $this->_boolean;
    }
    
    public function isTrue()
    {
        return $this->_boolean == true;
    }
    
    public function isFalse()
    {
        return $this->_boolean == false;
    }
    
    public function numFields()
    {
        return count($this->_fields);    
    }
    
    public function numRows()
    {
        return count($this);
    }
    
    public function getFields()
    {
        return $this->_fields;
    }

    public function dump($html=true)
    {
        if ($this->getType() == 'bindings') {
            $result = '';
            if ($html) {
                $result .= "<table class='sparql-results' style='border-collapse:collapse'>";
                $result .= "<tr>";
                foreach ($this->_fields as $field) {
                    $result .= "<th style='border:solid 1px #000;padding:4px;".
                               "vertical-align:top;background-color:#eee;'>".
                               "?$field</th>";
                }
                $result .= "</tr>";
                foreach ($this as $row) {
                    $result .= "<tr>";
                    foreach ($this->_fields as $field) {
                        $result .= "<td style='border:solid 1px #000;padding:4px;".
                                   "vertical-align:top'>".
                                   $row->$field->dumpValue($html)."</td>";
                    }
                    $result .= "</tr>";
                }
                $result .= "</table>";
            } else {
                # FIXME: add text/plain code
            }
            return $result;
        } else if ($this->getType() == 'boolean') {
            $str = ($this->_boolean ? 'true' : 'false');
            if ($html) {
                return "<p>Result: <span style='font-weight:bold'>$str</span></p>";
            } else {
                return $str;
            }
        } else {
            # throw exception
        }
    }

    # FIXME: move this to EasyRdf_Term::create() ?
    protected function _newTerm($data)
    {
        switch($data['type']) {
          case 'bnode':
            return new EasyRdf_Resource('_:'.$data['value']);
          case 'uri':
            return new EasyRdf_Resource($data['value']);
          case 'literal':
            return new EasyRdf_Literal($data);
          default:
            throw new EasyRdf_Exception(
                "Unknown term type: ".$data['type']
            );
        }
    }

    protected function _parseXml($data)
    {
        $doc = new DOMDocument();
        $doc->loadXML($data);
        # FIXME: check for SPARQL top-level element
        
        # Is it the result of an ASK query?
        $boolean = $doc->getElementsByTagName('boolean');
        if ($boolean->length) {
            $this->_type = 'boolean';
            $value = $boolean->item(0)->nodeValue;
            $this->_boolean = $value == 'true' ? true : false;
            return;
        }
        
        # Get a list of variables from the header
        $head = $doc->getElementsByTagName('head');
        if ($head->length) {
            $variables = $head->item(0)->getElementsByTagName('variable');
            foreach ($variables as $variable) {
                $this->_fields[] = $variable->getAttribute('name');
            }
        }

        # Is it the result of a SELECT query?
        $results = $doc->getElementsByTagName('result');
        if ($results->length) {
            $this->_type = 'bindings';
            foreach ($results as $result) {
                $bindings = $result->getElementsByTagName('binding');
                $t = new stdClass();
                foreach ($bindings as $binding) {
                    $key = $binding->getAttribute('name');
                    $term = $binding->firstChild;
                    $data = array(
                        'type' => $term->nodeName,
                        'lang' => $term->getAttribute('lang'),
                        'datatype' => $term->getAttribute('datatype'),
                        'value' => $term->nodeValue
                    );
                    $t->$key = $this->_newTerm($data);
                }
                $this[] = $t;
            }
            return $this;
        }
        
        # FIXME: throw exception?
    }

    protected function _parseJson($data)
    {
        // Decode JSON to an array
        $data = json_decode($data, true);
        
        if (isset($data['boolean'])) {
            $this->_type = 'boolean';
            $this->_boolean = $data['boolean'];
        } else if (isset($data['results'])) {
            $this->_type = 'bindings';
            if (isset($data['head']['vars'])) {
                $this->_fields = $data['head']['vars'];
            }

            foreach ($data['results']['bindings'] as $row) {
              $t = new stdClass();
              foreach ($row as $key => $value) {
                  $t->$key = $this->_newTerm($value);
              }
              $this[] = $t;
            }
            return $this;
        } else {
            # FIXME: throw exception?
        }
    }
    
    public function __toString()
    {
        if ($this->_type == 'boolean') {
            return $this->_boolean ? 'true' : 'false';
        } else if ($this->_type == 'bindings') {
            # FIXME: implement this
        } else {
            # FIXME: throw exception?
        }
    }
   

}
