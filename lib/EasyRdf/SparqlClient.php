<?php

class EasyRdf_SparqlClient
{
    /** The address of the SPARQL Endpoint */
    private $_uri = null;
    
    protected $_config = array();
    

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

    public function query($query)
    {
        # Add namespaces to the queryString
        $prefixes = '';
        foreach (EasyRdf_Namespace::namespaces() as $prefix => $uri) {
            if (strpos($query, "$prefix:") !== false and
                strpos($query, "PREFIX $prefix:") === false) {
                $prefixes .=  "PREFIX $prefix: <$uri>\n";
            }
        }

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
        $client->setParameterGet('query', $prefixes . $query);

        $response = $client->request();
        if ($response->isSuccessful()) {
            $type = $response->getHeader('Content-Type');
            if (strpos($type, 'application/sparql-results') === 0) {
                return new EasyRdf_SparqlResult($response->getBody(), $type);
            } else {
                return new EasyRdf_Graph($this->_uri, $response->getBody(), $type);
            }
        } else {
            throw new EasyRdf_Exception(
                "HTTP request for SPARQL query failed: ".$response->getBody()
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
