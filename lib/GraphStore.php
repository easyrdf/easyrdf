<?php
namespace EasyRdf;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2014 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2014 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */

/**
 * A class for fetching, saving and deleting graphs to a Graph Store.
 * Implementation of the SPARQL 1.1 Graph Store HTTP Protocol.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2014 Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class GraphStore
{
    /**
     * Use to reference default graph of triplestore
     */
    const DEFAULT_GRAPH = 'urn:easyrdf:default-graph';

    /** The address of the GraphStore endpoint */
    private $uri = null;
    private $parsedUri = null;


    /** Create a new SPARQL Graph Store client
     *
     * @param string $uri The address of the graph store endpoint
     */
    public function __construct($uri)
    {
        $this->uri = $uri;
        $this->parsedUri = new ParsedUri($uri);
    }

    /** Get the URI of the graph store
     *
     * @return string The URI of the graph store
     */
    public function getUri()
    {
        return $this->uri;
    }

    /** Fetch a named graph from the graph store
     *
     * The URI can either be a full absolute URI or
     * a URI relative to the URI of the graph store.
     *
     * @param string $uriRef The URI of graph desired
     *
     * @return Graph The graph requested
     */
    public function get($uriRef)
    {
        if ($uriRef === self::DEFAULT_GRAPH) {
            $dataUrl = $this->urlForGraph(self::DEFAULT_GRAPH);
            $graph = new Graph();
        } else {
            $graphUri = $this->parsedUri->resolve($uriRef)->toString();
            $dataUrl = $this->urlForGraph($graphUri);

            $graph = new Graph($graphUri);
        }

        $graph->load($dataUrl);

        return $graph;
    }

    /**
     * Fetch default graph from the graph store
     * @return Graph
     */
    public function getDefault()
    {
        return $this->get(self::DEFAULT_GRAPH);
    }

    /** Send some graph data to the graph store
     *
     * This method is used by insert() and replace()
     *
     * @ignore
     */
    protected function sendGraph($method, $graph, $uriRef, $format)
    {
        if (is_object($graph) and $graph instanceof Graph) {
            if ($uriRef === null) {
                $uriRef = $graph->getUri();
            }
            $data = $graph->serialise($format);
        } else {
            $data = $graph;
        }

        if ($uriRef === null) {
            throw new \InvalidArgumentException('Graph IRI is not specified');
        }

        $formatObj = Format::getFormat($format);
        $mimeType = $formatObj->getDefaultMimeType();

        if ($uriRef === self::DEFAULT_GRAPH) {
            $dataUrl = $this->urlForGraph(self::DEFAULT_GRAPH);
        } else {
            $graphUri = $this->parsedUri->resolve($uriRef)->toString();
            $dataUrl = $this->urlForGraph($graphUri);
        }

        $client = Http::getDefaultHttpClient();
        $client->resetParameters(true);
        $client->setUri($dataUrl);
        $client->setMethod($method);
        $client->setRawData($data);
        $client->setHeaders('Content-Type', $mimeType);

        $response = $client->request();

        if (!$response->isSuccessful()) {
            throw new Exception(
                "HTTP request for {$dataUrl} failed: ".$response->getMessage()
            );
        }

        return $response;
    }

    /** Replace the contents of a graph in the graph store with new data
     *
     * The $graph parameter is the EasyRdf\Graph object to be sent to the
     * graph store. Alternatively it can be a string, already serialised.
     *
     * The URI can either be a full absolute URI or
     * a URI relative to the URI of the graph store.
     *
     * The $format parameter can be given to specify the serialisation
     * used to send the graph data to the graph store.
     *
     * @param Graph|string $graph  Data
     * @param string       $uriRef The URI of graph to be replaced
     * @param string       $format The format of the data to send to the graph store
     *
     * @return Http\Response The response from the graph store
     */
    public function replace($graph, $uriRef = null, $format = 'ntriples')
    {
        return $this->sendGraph('PUT', $graph, $uriRef, $format);
    }

    /**
     * Replace the contents of default graph in the graph store with new data
     *
     * The $graph parameter is the EasyRdf\Graph object to be sent to the
     * graph store. Alternatively it can be a string, already serialised.
     *
     * The $format parameter can be given to specify the serialisation
     * used to send the graph data to the graph store.
     *
     * @param Graph|string $graph  Data
     * @param string       $format The format of the data to send to the graph store
     *
     * @return Http\Response The response from the graph store
     */
    public function replaceDefault($graph, $format = 'ntriples')
    {
        return self::replace($graph, self::DEFAULT_GRAPH, $format);
    }

    /** Add data to a graph in the graph store
     *
     * The $graph parameter is the EasyRdf\Graph object to be sent to the
     * graph store. Alternatively it can be a string, already serialised.
     *
     * The URI can either be a full absolute URI or
     * a URI relative to the URI of the graph store.
     *
     * The $format parameter can be given to specify the serialisation
     * used to send the graph data to the graph store.
     *
     * @param Graph|string  $graph  Data
     * @param string        $uriRef The URI of graph to be added to
     * @param string        $format The format of the data to send to the graph store
     *
     * @return Http\Response  The response from the graph store
     */
    public function insert($graph, $uriRef = null, $format = 'ntriples')
    {
        return $this->sendGraph('POST', $graph, $uriRef, $format);
    }

    /**
     * Add data to default graph of the graph store
     *
     * The $graph parameter is the EasyRdf\Graph object to be sent to the
     * graph store. Alternatively it can be a string, already serialised.
     *
     * The $format parameter can be given to specify the serialisation
     * used to send the graph data to the graph store.
     *
     * @param Graph|string  $graph  Data
     * @param string        $format The format of the data to send to the graph store
     *
     * @return Http\Response  The response from the graph store
     */
    public function insertIntoDefault($graph, $format = 'ntriples')
    {
        return $this->insert($graph, self::DEFAULT_GRAPH, $format);
    }

    /** Delete named graph content from the graph store
     *
     * The URI can either be a full absolute URI or
     * a URI relative to the URI of the graph store.
     *
     * @param string $uriRef The URI of graph to be added to
     *
     * @throws Exception
     * @return Http\Response The response from the graph store
     */
    public function delete($uriRef)
    {
        if ($uriRef === self::DEFAULT_GRAPH) {
            $dataUrl = $this->urlForGraph(self::DEFAULT_GRAPH);
        } else {
            $graphUri = $this->parsedUri->resolve($uriRef)->toString();
            $dataUrl = $this->urlForGraph($graphUri);
        }

        $client = Http::getDefaultHttpClient();
        $client->resetParameters(true);
        $client->setUri($dataUrl);
        $client->setMethod('DELETE');
        $response = $client->request();

        if (!$response->isSuccessful()) {
            throw new Exception(
                "HTTP request to delete {$dataUrl} failed: ".$response->getMessage()
            );
        }

        return $response;
    }

    /**
     * Delete default graph content from the graph store
     *
     * @return Http\Response
     * @throws Exception
     */
    public function deleteDefault()
    {
        return $this->delete(self::DEFAULT_GRAPH);
    }

    /** Work out the full URL for a graph store request.
     *  by checking if if it is a direct or indirect request.
     *  @ignore
     */
    protected function urlForGraph($url)
    {
        if ($url === self::DEFAULT_GRAPH) {
            $url = $this->uri.'?default';
        } elseif (strpos($url, $this->uri) === false) {
            $url = $this->uri."?graph=".urlencode($url);
        }

        return $url;
    }

    /** Magic method to return URI of the graph store when casted to string
     *
     * @return string The URI of the graph store
     */
    public function __toString()
    {
        return empty($this->uri) ? '' : $this->uri;
    }
}
