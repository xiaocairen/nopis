<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Http;

interface ResponseInterface
{

    /**
     * Sends HTTP headers.
     *
     * @return Response
     */
    public function sendHeaders();

    /**
     * Sends content for the current web response.
     *
     * @return Response
     */
    public function sendContent();

    /**
     * Sends HTTP headers and content, then exit programm.
     *
     * @return null
     */
    public function send();

    /**
     * Return HTTP headers.
     *
     * @return \Nopis\Lib\Http\ResponseHeader
     */
    public function getHeaders();

    /**
     * Sets the response content.
     *
     * Valid types are strings, numbers, null, and objects that implement a __toString() method.
     *
     * @param mixed $content Content that can be cast to string
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function setContent($content);

    /**
     * Gets the current response content.
     *
     * @return string Content
     */
    public function getContent();

    /**
     * Sets the HTTP protocol version (1.0 or 1.1).
     *
     * @param string $version The HTTP protocol version
     *
     * @return Response
     */
    public function setProtocolVersion($version);

    /**
     * Gets the HTTP protocol version.
     *
     * @return string The HTTP protocol version
     */
    public function getProtocolVersion();

    /**
     * Sets the response status code.
     *
     * @param int $status
     * @return Response
     */
    public function setStatusCode($status);

    /**
     * Retrieves the status code for the current web response.
     *
     * @return int     Status code
     */
    public function getStatusCode();

    /**
     * Sets the response charset.
     *
     * @param string $charset Character set
     *
     * @return Response
     */
    public function setCharset($charset);

    /**
     * Retrieves the response charset.
     *
     * @return string Character set
     */
    public function getCharset();

    // http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
    /**
     * Is response invalid?
     *
     * @return bool
     */
    public function isInvalid();

    /**
     * Is response informative?
     *
     * @return bool
     */
    public function isInformational();

    /**
     * Is response successful?
     *
     * @return bool
     */
    public function isSuccessful();

    /**
     * Is the response a redirect?
     *
     * @return bool
     */
    public function isRedirection();

    /**
     * Is there a client error?
     *
     * @return bool
     */
    public function isClientError();

    /**
     * Was there a server side error?
     *
     * @return bool
     */
    public function isServerError();

    /**
     * Is the response OK?
     *
     * @return bool
     */
    public function isOk();

    /**
     * Is the response forbidden?
     *
     * @return bool
     */
    public function isForbidden();

    /**
     * Is the response a not found error?
     *
     * @return bool
     */
    public function isNotFound();

    /**
     * Is the response a redirect of some form?
     *
     * @param string $location
     *
     * @return bool
     */
    public function isRedirect($location = null);

    /**
     * Is the response empty?
     *
     * @return bool
     */
    public function isEmpty();

    /**
     * Redirect the request
     *
     * @param string $location
     */
    public function redirect($location, $status = 301);

    /**
     * Set the http content can be compressible
     *
     * @param boolean $isCompressible
     */
    public function setCompressible(bool $isCompressible);

    /**
     * Cleans or flushes output buffers up to target level.
     *
     * Resulting level can be greater than target level if a non-removable buffer has been encountered.
     *
     * @param int  $targetLevel The target output buffering level
     * @param bool $flush       Whether to flush or clean the buffers
     */
    public static function closeOutputBuffers($targetLevel, $flush);
}