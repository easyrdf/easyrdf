<?php
namespace EasyRdf\Sparql;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2015 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2015 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
use EasyRdf\Exception;
use EasyRdf\Format;
use EasyRdf\Graph;
use EasyRdf\Http;
use EasyRdf\RdfNamespace;
use EasyRdf\Utils;

/**
 * Class for making SPARQL queries using the SPARQL 1.1 Protocol
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2015 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class Client
{
    /** The query/read address of the SPARQL Endpoint */
    private $queryUri = null;

    private $queryUri_has_params = false;

    /** The update/write address of the SPARQL Endpoint */
    private $updateUri = null;

    /** Create a new SPARQL endpoint client
     *
     * If the query and update endpoints are the same, then you
     * only need to give a single URI.
     *
     * @param string $queryUri The address of the SPARQL Query Endpoint
     * @param string $updateUri Optional address of the SPARQL Update Endpoint
     */
    public function __construct($queryUri, $updateUri = null)
    {
        $this->queryUri = $queryUri;

        if (strlen(parse_url($queryUri, PHP_URL_QUERY)) > 0) {
            $this->queryUri_has_params = true;
        } else {
            $this->queryUri_has_params = false;
        }

        if ($updateUri) {
            $this->updateUri = $updateUri;
        } else {
            $this->updateUri = $queryUri;
        }
    }

    /** Get the URI of the SPARQL query endpoint
     *
     * @return string The query URI of the SPARQL endpoint
     */
    public function getQueryUri()
    {
        return $this->queryUri;
    }

    /** Get the URI of the SPARQL update endpoint
     *
     * @return string The query URI of the SPARQL endpoint
     */
    public function getUpdateUri()
    {
        return $this->updateUri;
    }

    /**
     * @depredated
     * @ignore
     */
    public function getUri()
    {
        return $this->queryUri;
    }

    /** Make a query to the SPARQL endpoint
     *
     * SELECT and ASK queries will return an object of type
     * EasyRdf\Sparql\Result.
     *
     * CONSTRUCT and DESCRIBE queries will return an object
     * of type EasyRdf\Graph.
     *
     * @param string $query The query string to be executed
     *
     * @return Result|\EasyRdf\Graph  Result of the query.
     */
    public function query($query)
    {
        return $this->request('query', $query);
    }

    /** Count the number of triples in a SPARQL 1.1 endpoint
     *
     * Performs a SELECT query to estriblish the total number of triples.
     *
     * Counts total number of triples by default but a conditional triple pattern
     * can be given to count of a subset of all triples.
     *
     * @param string $condition Triple-pattern condition for the count query
     *
     * @return integer The number of triples
     */
    public function countTriples($condition = '?s ?p ?o')
    {
        // SELECT (COUNT(*) AS ?count)
        // WHERE {
        //   {?s ?p ?o}
        //   UNION
        //   {GRAPH ?g {?s ?p ?o}}
        // }
        $result = $this->query('SELECT (COUNT(*) AS ?count) {'.$condition.'}');
        return $result[0]->count->getValue();
    }

    /** Get a list of named graphs from a SPARQL 1.1 endpoint
     *
     * Performs a SELECT query to get a list of the named graphs
     *
     * @param string $limit Optional limit to the number of results
     *
     * @return \EasyRdf\Resource[]  array of objects for each named graph
     */
    public function listNamedGraphs($limit = null)
    {
        $query = "SELECT DISTINCT ?g WHERE {GRAPH ?g {?s ?p ?o}}";
        if (!is_null($limit)) {
            $query .= " LIMIT ".(int)$limit;
        }
        $result = $this->query($query);

        // Convert the result object into an array of resources
        $graphs = array();
        foreach ($result as $row) {
            array_push($graphs, $row->g);
        }
        return $graphs;
    }

    /** Make an update request to the SPARQL endpoint
     *
     * Successful responses will return the HTTP response object
     *
     * Unsuccessful responses will throw an exception
     *
     * @param string $query The update query string to be executed
     *
     * @return \EasyRdf\Http\Response HTTP response
     */
    public function update($query)
    {
        return $this->request('update', $query);
    }

    public function insert($data, $graphUri = null)
    {
        #$this->updateData('INSET',
        $query = 'INSERT DATA {';
        if ($graphUri) {
            $query .= "GRAPH <$graphUri> {";
        }
        $query .= $this->convertToTriples($data);
        if ($graphUri) {
            $query .= "}";
        }
        $query .= '}';
        return $this->update($query);
    }

    protected function updateData($operation, $data, $graphUri = null)
    {
        $query = "$operation DATA {";
        if ($graphUri) {
            $query .= "GRAPH <$graphUri> {";
        }
        $query .= $this->convertToTriples($data);
        if ($graphUri) {
            $query .= "}";
        }
        $query .= '}';
        return $this->update($query);
    }

    public function clear($graphUri, $silent = false)
    {
        $query = "CLEAR";
        if ($silent) {
            $query .= " SILENT";
        }
        if (preg_match('/^all|named|default$/i', $graphUri)) {
            $query .= " $graphUri";
        } else {
            $query .= " GRAPH <$graphUri>";
        }
        return $this->update($query);
    }

    /*
     * Internal function to make an HTTP request to SPARQL endpoint
     *
     * @ignore
     */
    protected function request($type, $query)
    {
        $processed_query = $this->preprocessQuery($query);
        $response = $this->executeQuery($processed_query, $type);

        if (!$response->isSuccessful()) {
            throw new Http\Exception("HTTP request for SPARQL query failed", 0, null, $response->getBody());
        }

        if ($response->getStatus() == 204) {
            // No content
            return $response;
        }

        return $this->parseResponseToQuery($response);
    }

    protected function convertToTriples($data)
    {
        if (is_string($data)) {
            return $data;
        } elseif (is_object($data) and $data instanceof Graph) {
            # FIXME: insert Turtle when there is a way of seperateing out the prefixes
            return $data->serialise('ntriples');
        } else {
            throw new Exception(
                "Don't know how to convert to triples for SPARQL query"
            );
        }
    }

    /**
     * Adds missing prefix-definitions to the query
     *
     * Overriding classes may execute arbitrary query-alteration here
     *
     * @param string $query
     * @return string
     */
    protected function preprocessQuery($query)
    {
        // Check for undefined prefixes
        $prefixes = '';
        foreach (RdfNamespace::namespaces() as $prefix => $uri) {
            if (strpos($query, "{$prefix}:") !== false and
                strpos($query, "PREFIX {$prefix}:") === false
            ) {
                $prefixes .= "PREFIX {$prefix}: <{$uri}>\n";
            }
        }

        return $prefixes . $query;
    }

    /**
     * Build http-client object, execute request and return a response
     *
     * @param string $processed_query
     * @param string $type            Should be either "query" or "update"
     *
     * @return Http\Response|\Zend\Http\Response
     * @throws Exception
     */
    protected function executeQuery($processed_query, $type)
    {
        $client = Http::getDefaultHttpClient();
        $client->resetParameters();

        // Tell the server which response formats we can parse
        $sparql_results_types = array(
            'application/sparql-results+json' => 1.0,
            'application/sparql-results+xml' => 0.8
        );

        if ($type == 'update') {
            // accept anything, as "response body of a […] update request is implementation defined"
            // @see http://www.w3.org/TR/sparql11-protocol/#update-success
            $accept = Format::getHttpAcceptHeader($sparql_results_types);
            $this->setHeaders($client, 'Accept', $accept);

            $client->setMethod('POST');
            $client->setUri($this->updateUri);
            $client->setRawData($processed_query);
            $this->setHeaders($client, 'Content-Type', 'application/sparql-update');
        } elseif ($type == 'query') {
            $re = '(?:(?:\s*BASE\s*<.*?>\s*)|(?:\s*PREFIX\s+.+:\s*<.*?>\s*))*'.
                '(CONSTRUCT|SELECT|ASK|DESCRIBE)[\W]';

            $result = null;
            $matched = mb_eregi($re, $processed_query, $result);

            if (false === $matched or count($result) !== 2) {
                // non-standard query. is this something non-standard?
                $query_verb = null;
            } else {
                $query_verb = strtoupper($result[1]);
            }

            if ($query_verb === 'SELECT' or $query_verb === 'ASK') {
                // only "results"
                $accept = Format::formatAcceptHeader($sparql_results_types);
            } elseif ($query_verb === 'CONSTRUCT' or $query_verb === 'DESCRIBE') {
                // only "graph"
                $accept = Format::getHttpAcceptHeader();
            } else {
                // both
                $accept = Format::getHttpAcceptHeader($sparql_results_types);
            }

            $this->setHeaders($client, 'Accept', $accept);

            $encodedQuery = 'query=' . urlencode($processed_query);

            // Use GET if the query is less than 2kB
            // 2046 = 2kB minus 1 for '?' and 1 for NULL-terminated string on server
            if (strlen($encodedQuery) + strlen($this->queryUri) <= 2046) {
                $delimiter = $this->queryUri_has_params ? '&' : '?';

                $client->setMethod('GET');
                $client->setUri($this->queryUri . $delimiter . $encodedQuery);
            } else {
                // Fall back to POST instead (which is un-cacheable)
                $client->setMethod('POST');
                $client->setUri($this->queryUri);
                $client->setRawData($encodedQuery);
                $this->setHeaders($client, 'Content-Type', 'application/x-www-form-urlencoded');
            }
        } else {
            throw new Exception('unexpected request-type: '.$type);
        }

        if ($client instanceof \Zend\Http\Client) {
            return $client->send();
        } else {
            return $client->request();
        }
    }

    /**
     * Parse HTTP-response object into a meaningful result-object.
     *
     * Can be overridden to do custom processing
     *
     * @param Http\Response|\Zend\Http\Response $response
     * @return Graph|Result
     */
    protected function parseResponseToQuery($response)
    {
        list($content_type,) = Utils::parseMimeType($response->getHeader('Content-Type'));

        if (strpos($content_type, 'application/sparql-results') === 0) {
            $result = new Result($response->getBody(), $content_type);
            return $result;
        } else {
            $result = new Graph($this->queryUri, $response->getBody(), $content_type);
            return $result;
        }
    }

    /**
     * Proxy function to allow usage of our Client as well as Zend\Http v2.
     *
     * Zend\Http\Client only accepts an array as first parameter, but our Client wants a name-value pair.
     *
     * @see https://framework.zend.com/apidoc/2.4/classes/Zend.Http.Client.html#method_setHeaders
     *
     * @todo Its only a temporary fix, should be replaced or refined in the future.
     */
    protected function setHeaders($client, $name, $value)
    {
        if ($client instanceof \Zend\Http\Client) {
            $client->setHeaders([$name => $value]);
        } else {
            $client->setHeaders($name, $value);
        }
    }
}
