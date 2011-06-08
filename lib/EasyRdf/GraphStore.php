<?php

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) 2009-2011 Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 * @version    $Id$
 */

/**
 * A class for fetching, saving and deleting graphs to a Graph Store.
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
 * @license    http://www.opensource.org/licenses/bsd-license.php
 */
class EasyRdf_GraphStore
{
    /** The address of the GraphStore Endpoint */
    private $_uri = null;


    public function __construct($uri)
    {
        $this->_uri = $uri;
    }

    /** Get the URI of the graph store
     *
     * @return string The URI of the graph store
     */
    public function getUri()
    {
        return $this->_uri;
    }
    
    public function get($uriRef)
    {
        $graphUri = EasyRdf_Utils::resolveUriReference($this->_uri, $uriRef);
        $dataUrl = $this->urlForGraph($graphUri);
        $graph = new EasyRdf_Graph($graphUri);
        $graph->load($dataUrl);
        return $graph;
    }
    
    protected function sendGraph($method, $graph, $uriRef, $format)
    {
        if (is_object($graph) and $graph instanceof EasyRdf_Graph) {
            if ($uriRef == null)
                $uriRef = $graph->getUri();
            $data = $graph->serialise($format);
        } else {
            $data = $graph;
        }
        
        $formatObj = EasyRdf_Format::getFormat($format);
        $mimeType = $formatObj->getDefaultMimeType();

        $graphUri = EasyRdf_Utils::resolveUriReference($this->_uri, $uriRef);
        $dataUrl = $this->urlForGraph($graphUri);

        $client = EasyRdf_Http::getDefaultHttpClient();
        $client->setUri($dataUrl);
        $client->setMethod($method);
        $client->setRawData($data);
        $client->setHeaders('Content-Type', $mimeType);
        $client->setHeaders('Content-Length', strlen($data));
        $response = $client->request();
        if (!$response->isSuccessful()) {
            throw new EasyRdf_Exception(
                "HTTP request for $dataUrl failed: ".$response->getMessage()
            );
        }
        return $response;
    }
    
    public function replace($graph, $uriRef=null, $format='ntriples')
    {
        return $this->sendGraph('PUT', $graph, $uriRef, $format);
    }
    
    public function insert($graph, $uriRef=null, $format='ntriples')
    {
        return $this->sendGraph('POST', $graph, $uriRef, $format);
    }
        
    public function delete($uriRef)
    {
        $graphUri = EasyRdf_Utils::resolveUriReference($this->_uri, $uriRef);
        $dataUrl = $this->urlForGraph($graphUri);

        $client = EasyRdf_Http::getDefaultHttpClient();
        $client->setUri($dataUrl);
        $client->setMethod('DELETE');
        $response = $client->request();
        if (!$response->isSuccessful()) {
            throw new EasyRdf_Exception(
                "HTTP request to delete $dataUrl failed: ".$response->getMessage()
            );
        }
        return $response;
    }
    
    public function urlForGraph($url)
    {
        if (strpos($url, $this->_uri) === false) {
            $url = $this->_uri."?graph=".urlencode($url);
        }
        return $url;
    }

    /** Magic method to return URI of the graph store when casted to string
     *
     * @return string The URI of the graph store
     */
    public function __toString()
    {
        return $this->_uri == null ? '' : $this->_uri;
    }
}
