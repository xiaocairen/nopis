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

class Response implements ResponseInterface
{
    /**
     * @var \Nopis\Lib\Http\ResponseHeader
     */
    protected $headers;

    /**
     * @var \Nopis\Lib\Http\ResponseContent
     */
    protected $content;

    /**
     * @var string
     */
    protected $charset;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $statusText;

    /**
     * @var boolean
     */
    protected $isCompressible;

    /**
     * Status codes translation table.
     *
     * The list of codes is complete according to the
     * {@link http://www.iana.org/assignments/http-status-codes/ Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2012-02-13).
     *
     * Unless otherwise noted, the status code is defined in RFC2616.
     *
     * @var array
     */
    public static $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        422 => 'Unprocessable Entity',                                        // RFC4918
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',                      // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    /**
     * @var boolean
     */
    private $zlib_loaded;

    /**
     * Constructor.
     *
     * @param string $context
     * @param int $status
     * @param array $headers
     */
    public function __construct($context = '', $status = 200, $headers = [])
    {
        $this->headers = new ResponseHeader($headers);
        $this->content = new ResponseContent($context);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.1');
    }

    /**
     * Returns the Response as an HTTP string.
     *
     * The string representation of the Response is the same as the
     * one that will be sent to the client only if the prepare() method
     * has been called before.
     *
     * @return string The Response as an HTTP string
     *
     * @see prepare()
     */
    public function __toString()
    {
        // set HTTP Content-Type
        isset($this->charset) && $this->headers->setContentType($this->headers->getContentType() . ';charset=' . $this->charset);

        return
            sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText)."\r\n".
            $this->headers."\r\n".
            $this->getContent();
    }

    /**
     * Sends HTTP headers.
     *
     * @return Response
     */
    public function sendHeaders()
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return $this;
        }

        // set HTTP Content-Type
        isset($this->charset) && $this->headers->setContentType($this->headers->getContentType() . ';charset=' . $this->charset);

        // check the content if can be compress
        if (false !== ($enc = $this->clientEncoding()) && $this->zlibLoaded()) {
            $this->headers->set('Content-Encoding', $enc[1]);
        }

        // header前 rps=90
        // header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);
        // header后 rps=20 为何上面header输出http协议号，会让rps骤降？？？而下面的header则不会出现rps骤降的情况


        // headers
        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                header($name . ': ' . $value, false, $this->statusCode);
            }
        }

        // cookies
        foreach ($this->headers->getCookies() as $cookie) {
            setcookie($cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly());
        }

        return $this;
    }

    /**
     * Sends content for the current web response.
     *
     * @return Response
     */
    public function sendContent()
    {
        echo $this->compressContent($this->content);

        return $this;
    }

    /**
     * Sends HTTP headers and content, then exit programm.
     *
     * @return null
     */
    public function send()
    {
        // rps=91
        $this->sendHeaders();
        // rps=20
        $this->sendContent();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif ('cli' !== PHP_SAPI) {
            static::closeOutputBuffers(0, true);
            flush();
        }

        exit(0);
    }

    /**
     * Return HTTP headers.
     *
     * @return \Nopis\Lib\Http\ResponseHeader
     */
    public function getHeaders()
    {
        return $this->headers;
    }

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
    public function setContent($content)
    {
        $this->content->setContent($content);

        return $this;
    }

    /**
     * Gets the current response content.
     *
     * @return string Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Sets the HTTP protocol version (1.0 or 1.1).
     *
     * @param string $version The HTTP protocol version
     *
     * @return Response
     */
    public function setProtocolVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Gets the HTTP protocol version.
     *
     * @return string The HTTP protocol version
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * Sets the response status code.
     *
     * @param int $status
     * @return Response
     */
    public function setStatusCode($status)
    {
        $this->statusCode = $status = (int) $status;
        $this->statusText = isset(self::$statusTexts[$status]) ? self::$statusTexts[$status] : '';

        return $this;
    }

    /**
     * Retrieves the status code for the current web response.
     *
     * @return int     Status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the response charset.
     *
     * @param string $charset Character set
     *
     * @return Response
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Retrieves the response charset.
     *
     * @return string Character set
     */
    public function getCharset()
    {
        return $this->charset;
    }

    // http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
    /**
     * Is response invalid?
     *
     * @return bool
     */
    public function isInvalid()
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * Is response informative?
     *
     * @return bool
     */
    public function isInformational()
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * Is response successful?
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Is the response a redirect?
     *
     * @return bool
     */
    public function isRedirection()
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Is there a client error?
     *
     * @return bool
     */
    public function isClientError()
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Was there a server side error?
     *
     * @return bool
     */
    public function isServerError()
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Is the response OK?
     *
     * @return bool
     */
    public function isOk()
    {
        return 200 === $this->statusCode;
    }

    /**
     * Is the response forbidden?
     *
     * @return bool
     */
    public function isForbidden()
    {
        return 403 === $this->statusCode;
    }

    /**
     * Is the response a not found error?
     *
     * @return bool
     */
    public function isNotFound()
    {
        return 404 === $this->statusCode;
    }

    /**
     * Is the response a redirect of some form?
     *
     * @param string $location
     *
     * @return bool
     */
    public function isRedirect($location = null)
    {
        return in_array($this->statusCode, [201, 301, 302, 303, 307, 308]) && (null === $location ?: $location == $this->headers->get('Location'));
    }

    /**
     * Is the response empty?
     *
     * @return bool
     */
    public function isEmpty()
    {
        return in_array($this->statusCode, [204, 304]);
    }

    /**
     * Redirect the request
     *
     * @param string $location
     */
    public function redirect($location, $status = 302)
    {
        $location = trim($location);
        if (!$location || ('/' != $location[0] && !preg_match('/(http|https):\/\/\w+/i', $location))) {
            throw new RedirectPathNotAvailableException(
                sprintf('The Header Location\'s path %s is not available.', $location)
            );
        }

        $this->statusCode = in_array($status, [201, 301, 302, 303, 307, 308]) ? $status : 302;
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);
        header('Location:' . $location);

        exit;
    }

    /**
     * Set the http content can be compressible
     *
     * @param boolean $isCompressible
     */
    public function setCompressible(bool $isCompressible)
    {
        $this->isCompressible = $isCompressible;
    }

    /**
     * Get the encoding method be supported
     *
     * @return array or false
     */
    protected function clientEncoding()
    {
        if (!$this->isCompressible)
            return false;
        if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']))
            return false;
        if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false)
            return ['gzencode', 'gzip'];
        if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false)
            return ['gzdeflate', 'deflate'];
        if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false)
            return ['gzencode', 'x-gzip'];

        return false;
    }

    /**
     * Compress the response content
     *
     * @param string $content
     * @return string
     */
    protected function compressContent($content)
    {
        if (false !== ($enc = $this->clientEncoding())) {
            if (!$this->zlibLoaded()/* || ini_get('zlib.output_compression')*/)
                return $content;

            $content = $enc[0]($content, 4);
        }
        return $content;
    }

    /**
     * @return boolean
     */
    protected function zlibLoaded()
    {
        if ($this->zlib_loaded === null) {
            $this->zlib_loaded = extension_loaded('zlib');
        }
        return $this->zlib_loaded;
    }

    /**
     * Cleans or flushes output buffers up to target level.
     *
     * Resulting level can be greater than target level if a non-removable buffer has been encountered.
     *
     * @param int  $targetLevel The target output buffering level
     * @param bool $flush       Whether to flush or clean the buffers
     */
    public static function closeOutputBuffers($targetLevel, $flush)
    {
        $status = ob_get_status(true);
        $level = count($status);

        while ($level-- > $targetLevel
            && (!empty($status[$level]['del'])
                || (isset($status[$level]['flags'])
                    && ($status[$level]['flags'] & PHP_OUTPUT_HANDLER_REMOVABLE)
                    && ($status[$level]['flags'] & ($flush ? PHP_OUTPUT_HANDLER_FLUSHABLE : PHP_OUTPUT_HANDLER_CLEANABLE))
                )
            )
        ) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }
}