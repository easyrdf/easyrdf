<?php
namespace EasyRdf;

/**
 * EasyRdf
 *
 * LICENSE
 *
 * Copyright (c) Nicholas J Humfrey.  All rights reserved.
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
 * @copyright  Copyright (c) Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use EasyRdf\Http\Response;

/**
 * Static class to set the HTTP client used by EasyRdf
 *
 * @package    EasyRdf
 * @copyright  Copyright (c) Nicholas J Humfrey
 * @license    https://www.opensource.org/licenses/bsd-license.php
 */
class Http
{
    /** The default components */
    private static $defaultHttpClient = null;
    private static $defaultRequestFactory = null;
    private static $defaultResponseFactory = null;
    private static $defaultStreamFactory = null;
    private static $defaultUriFactory = null;

    /** Set the HTTP components used to fetch RDF data
     *
     * If some of the componentes are not explicitely defined, default
     * implementations are internally provided
     *
     * @param  Psr\Http\Client\ClientInterface           $httpClient The new PSR-18 HTTP client object
     * @param  Psr\Http\Client\RequestFactoryInterface   $requestFactory The new PSR-17 Request factory object
     * @param  Psr\Http\Client\ResponseFactoryInterface  $responseFactory The new PSR-17 Response factory object
     * @param  Psr\Http\Client\StreamFactoryInterface    $streamFactory The new PSR-17 Stream factory object
     * @param  Psr\Http\Client\UriFactoryInterface       $uriFactory The new PSR-17 Uri factory object
     *
     * @return Psr\Http\Client\ClientInterface The new HTTP client object
     */
    public static function setDefaultHttpClient(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory = null,
        ResponseFactoryInterface $responseFactory = null,
        StreamFactoryInterface $streamFactory = null,
        UriFactoryInterface $uriFactory = null
    ) {
        self::$defaultRequestFactory = $requestFactory;
        self::$defaultResponseFactory = $responseFactory;
        self::$defaultStreamFactory = $streamFactory;
        self::$defaultUriFactory = $uriFactory;
        return self::$defaultHttpClient = $httpClient;
    }

    /** Verify all elements for HTTP request are inited
     *
     * If some of the involved components is not defined, provides to
     * instantiate with defaults
     */
    private static function ensureClientInited()
    {
        if (!isset(self::$defaultRequestFactory) || !isset(self::$defaultResponseFactory) || !isset(self::$defaultStreamFactory) || !isset(self::$defaultUriFactory)) {
            $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

            if (!isset(self::$defaultRequestFactory)) {
                self::$defaultRequestFactory = $psr17Factory;
            }

            if (!isset(self::$defaultResponseFactory)) {
                self::$defaultResponseFactory = $psr17Factory;
            }

            if (!isset(self::$defaultStreamFactory)) {
                self::$defaultStreamFactory = $psr17Factory;
            }

            if (!isset(self::$defaultUriFactory)) {
                self::$defaultUriFactory = $psr17Factory;
            }

            if (!isset(self::$defaultHttpClient)) {
                self::$defaultHttpClient = new \Buzz\Client\Curl($psr17Factory);
            }
        }

        if (!isset(self::$defaultHttpClient)) {
            self::$defaultHttpClient = new \Buzz\Client\Curl(self::$defaultResponseFactory);
        }
    }

    /** Return the actual default HTTP client
     *
     * @return Psr\Http\Client\ClientInterface The configured HTTP client object
     */
    public static function getDefaultHttpClient()
    {
        self::ensureClientInited();
        return self::$defaultHttpClient;
    }

    /** Return the actual default Request factory
     *
     * @return Psr\Http\Client\RequestFactoryInterface The configured Request factory object
     */
    public static function getDefaultRequestFactory()
    {
        self::ensureClientInited();
        return self::$defaultRequestFactory;
    }

    /** Return the actual default Response factory
     *
     * @return Psr\Http\Client\ResponseFactoryInterface The configured Response factory object
     */
    public static function getDefaultResponseFactory()
    {
        self::ensureClientInited();
        return self::$defaultResponseFactory;
    }

    /** Return the actual default Stream factory
     *
     * @return Psr\Http\Client\StreamFactoryInterface The configured Stream factory object
     */
    public static function getDefaultStreamFactory()
    {
        self::ensureClientInited();
        return self::$defaultStreamFactory;
    }

    /** Convenience function to send a HTTP request
     *
     * The parameters for the request are expressed with an indexed array.
     * The valid keys are:
     * - url: the URL for the request. Is eventually wrapped by the configured UriFactoryInterface
     * - method: the method of the request. If not defined, defaults to GET
     * - body: the optional payload for the request
     * - headers: an optional indexed array with custom HTTP headers
     *
     * @param  array  $params  Parameters for the request
     *
     * @return EasyRdf\Http\Response The obtained response
     */
    public static function makeRequest($params)
    {
        self::ensureClientInited();

        $method = $params['method'] ?? 'GET';
        $uri = self::$defaultUriFactory->createUri($params['url']);
        $request = self::$defaultRequestFactory->createRequest($method, $uri);

        if (isset($params['body']) && !is_null($params['body'])) {
            $request = $request->withBody(self::$defaultStreamFactory->createStream($params['body']));
        }

        if (isset($params['headers'])) {
            foreach($params['headers'] as $key => $value) {
                $request = $request->withHeader($key, $value);
            }
        }

        $raw = self::$defaultHttpClient->sendRequest($request);
        return new Response($raw);
    }
}
