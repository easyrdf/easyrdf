<?php

class EasyRdf_SparqlClient
{
    /** The address of the SPARQL Endpoint */
    private $_uri = null;

    public function __construct($uri)
    {
        $this->_uri = $uri;
    }

    /** Get the URI of the SPARQL endpoint
     *
     * @return string The URI of the SPARQL endpoint
     */
    public function getUri()
    {
        return $this->_uri;
    }

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
                "Unknown type: ".$data['type']
            );
        }
    }

    protected function _parseXmlResponse($response)
    {
        $doc = new DOMDocument();
        $doc->loadXML($response->getBody());

        # Is it the result of an ASK query?
        $boolean = $doc->getElementsByTagName('boolean');
        if ($boolean->length) {
            $value = $boolean->item(0)->nodeValue;
            return $value == 'true' ? true : false;
        }
        
        # Is it the result of a SELECT query?
        $results = $doc->getElementsByTagName('result');
        if ($results->length) {
            $r = array();
            foreach ($results as $result) {
                $bindings = $result->getElementsByTagName('binding');
                $t = array();
                foreach ($bindings as $binding) {
                    $key = $binding->getAttribute('name');
                    $term = $binding->firstChild;
                    $data = array(
                        'type' => $term->nodeName,
                        'lang' => $term->getAttribute('lang'),
                        'datatype' => $term->getAttribute('datatype'),
                        'value' => $term->nodeValue
                    );
                    $t[$key] = $this->_newTerm($data);
                }
                $r[] = $t;
            }
            return $r;
        }
        
        # FIXME: throw exception?
    }

    protected function _parseJsonResponse($response)
    {
        // Decode JSON to an array
        $data = json_decode($response->getBody(), true);
        
        if (isset($data['boolean'])) {
            return $data['boolean'];
        } else if (isset($data['results'])) {
            $r = array();
            foreach ($data['results']['bindings'] as $row) {
              $t = array();
              foreach ($row as $key => $value) {
                  $t[$key] = $this->_newTerm($value);
              }
              $r[] = $t;
            }
            return $r;
        } else {
            # FIXME: throw exception?
        }
    }

    public function query($query)
    {
        $client = EasyRdf_Http::getDefaultHttpClient();
        $client->resetParameters();
        $client->setUri($this->_uri);
        $client->setMethod('GET');

        $accept = EasyRdf_Format::getHttpAcceptHeader(
            array(
              'application/sparql-results+json' => 1.0,
              'application/sparql-results+xml' => 0.8
            )
        );
        $client->setHeaders('Accept', $accept);
        $client->setParameterGet('query', $query);

        $response = $client->request();
        if ($response->isSuccessful()) {
            $type = $response->getHeader('Content-Type');
            if ($type == 'application/sparql-results+xml') {
                return $this->_parseXmlResponse($response);
            } else if ($type == 'application/sparql-results+json') {
                return $this->_parseJsonResponse($response);
            } else {
                $graph = new EasyRdf_Graph();
                $graph->parse($response->getBody(), $type, $this->_uri);
                return $graph;
            }
        } else {
            throw new EasyRdf_Exception(
                "HTTP request for SPARQL query failed: ".$response->getMessage()
            );
        }
    }


    /** Magic method to return URI of the SPARQL endpoint when casted to string
     *
     * @return string The URI of the SPARQL endpoint
     */
    public function __toString()
    {
        return $this->_uri == null ? '' : $this->_uri;
    }
}
