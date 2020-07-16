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

class ResponseHeader implements \IteratorAggregate, \Countable
{
    const COOKIES_FLAT = 'flat';
    const COOKIES_ARRAY = 'array';

    /**
     * HTTP headers.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * HTTP cookies.
     *
     * @var array
     */
    protected $cookies = [];

    /**
     * Constructor.
     *
     * @param array $headers An array of HTTP headers
     */
    public function __construct(array $headers = array())
    {
        foreach ($headers as $key => $values) {
            $this->set($key, $values);
        }
        array_key_exists('Content-Type', $headers) || $this->setContentType();
    }

    /**
     * Returns the headers as a string.
     *
     * @return string The headers
     */
    public function __toString()
    {
        if (!$this->headers) {
            return '';
        }

        $max = max(array_map('strlen', array_keys($this->headers))) + 1;
        $headerStr = '';
        ksort($this->headers);
        foreach ($this->headers as $name => $values) {
            $name = implode('-', array_map('ucfirst', explode('-', $name)));
            foreach ($values as $value) {
                $headerStr .= sprintf("%-{$max}s %s\r\n", $name . ':', $value);
            }
        }

        return $headerStr;
    }

    /**
     * Sets a header by name.
     *
     * @param string       $key     The key
     * @param string|array $values  The value or an array of values
     * @param bool         $replace Whether to replace the actual value or not (true by default)
     */
    public function set($key, $values, $replace = true)
    {
        $key = strtr(strtolower($key), '_', '-');

        $values = array_values((array) $values);

        if (true === $replace || !isset($this->headers[$key])) {
            $this->headers[$key] = $values;
        } else {
            $this->headers[$key] = array_merge($this->headers[$key], $values);
        }
    }

    /**
     * Returns a header value by name.
     *
     * @param string  $key     The header name
     * @param mixed   $default The default value
     * @param bool    $first   Whether to return the first value or all header values
     *
     * @return string|array The first header value if $first is true, an array of values otherwise
     */
    public function get($key, $default = null, $first = true)
    {
        $key = strtr(strtolower($key), '_', '-');

        if (!array_key_exists($key, $this->headers)) {
            if (null === $default) {
                return $first ? null : array();
            }

            return $first ? $default : array($default);
        }

        if ($first) {
            return count($this->headers[$key]) ? $this->headers[$key][0] : $default;
        }

        return $this->headers[$key];
    }

    /**
     * Returns true if the HTTP header is defined.
     *
     * @param string $key The HTTP header
     *
     * @return bool    true if the parameter exists, false otherwise
     */
    public function has($key)
    {
        return array_key_exists(strtr(strtolower($key), '_', '-'), $this->headers);
    }

    /**
     * Returns true if the given HTTP header contains the given value.
     *
     * @param string $key   The HTTP header name
     * @param string $value The HTTP value
     *
     * @return bool    true if the value is contained in the header, false otherwise
     *
     * @api
     */
    public function contains($key, $value)
    {
        return in_array($value, $this->get($key, null, false));
    }

    /**
     * Replaces the current HTTP headers by a new set.
     *
     * @param array $headers An array of HTTP headers
     */
    public function replace(array $headers = array())
    {
        $this->headers = array();
        $this->add($headers);
    }

    /**
     * Adds new headers the current HTTP headers set.
     *
     * @param array $headers An array of HTTP headers
     */
    public function add(array $headers)
    {
        foreach ($headers as $key => $values) {
            $this->set($key, $values);
        }
    }

    /**
     * Returns the headers.
     *
     * @return array An array of headers
     *
     * @api
     */
    public function all()
    {
        return $this->headers;
    }

    /**
     * Delete a header.
     *
     * @param string $key The HTTP header name
     */
    public function delete($key)
    {
        $key = strtr(strtolower($key), '_', '-');

        unset($this->headers[$key]);

        if ('cache-control' === $key) {
            $this->cacheControl = array();
        }
    }

    /**
     * Returns the HTTP header value converted to a date.
     *
     * @param string    $key     The parameter key
     * @param \DateTime $default The default value
     *
     * @return null|\DateTime The parsed DateTime or the default value if the header does not exist
     *
     * @throws \RuntimeException When the HTTP header is not parseable
     */
    public function getDate($key, \DateTime $default = null)
    {
        if (null === $value = $this->get($key)) {
            return $default;
        }

        if (false === $date = \DateTime::createFromFormat(DATE_RFC2822, $value)) {
            throw new \RuntimeException(sprintf('The %s HTTP header is not parseable (%s).', $key, $value));
        }

        return $date;
    }

    /**
     * Set HTTP Content-Type
     *
     * @param string $contentType
     */
    public function setContentType($contentType = 'text/html')
    {
        $this->set('Content-Type', $contentType);
    }

    /**
     * Get HTTP Content-Type
     *
     * @return array
     */
    public function getContentType()
    {
        return $this->get('Content-Type');
    }

    /**
     * new a cookie object
     *
     * @param string                   $name     The name of the cookie
     * @param string                   $value    The value of the cookie
     * @param int|string|\DateTime     $expire   The time the cookie expires
     * @param string                   $path     The path on the server in which the cookie will be available on
     * @param string                   $domain   The domain that the cookie is available to
     * @param bool                     $secure   Whether the cookie should only be transmitted over a secure HTTPS connection from the client
     * @param bool                     $httpOnly Whether the cookie will be made accessible only through the HTTP protocol
     *
     * @return \Nopis\Lib\Http\Cookie
     */
    public function newCookie($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true)
    {
        return new Cookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    /**
     * Sets a cookie.
     *
     * @param Cookie $cookie
     */
    public function setCookie(Cookie $cookie)
    {
        $this->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
    }

    /**
     * Removes a cookie from the array, but does not unset it in the browser
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     */
    public function removeCookie($name, $path = '/', $domain = null)
    {
        if (null === $path) {
            $path = '/';
        }

        unset($this->cookies[$domain][$path][$name]);

        if (empty($this->cookies[$domain][$path])) {
            unset($this->cookies[$domain][$path]);

            if (empty($this->cookies[$domain])) {
                unset($this->cookies[$domain]);
            }
        }
    }

    public function getCookie($name, $path = '/', $domain = null)
    {
        if (null === $path) {
            $path = '/';
        }
        if (!isset($this->cookies[$domain]) || !isset($this->cookies[$domain][$path]) || !isset($this->cookies[$domain][$path][$name])) {
            return null;
        }

        return $this->cookies[$domain][$path][$name];
    }

    /**
     * Returns an array with all cookies
     *
     * @param string $format
     *
     * @throws \InvalidArgumentException When the $format is invalid
     *
     * @return array
     */
    public function getCookies($format = self::COOKIES_FLAT)
    {
        if (!in_array($format, array(self::COOKIES_FLAT, self::COOKIES_ARRAY))) {
            throw new \InvalidArgumentException(sprintf('Format "%s" invalid (%s).', $format, implode(', ', array(self::COOKIES_FLAT, self::COOKIES_ARRAY))));
        }

        if (self::COOKIES_ARRAY === $format) {
            return $this->cookies;
        }

        $flattenedCookies = array();
        foreach ($this->cookies as $path) {
            foreach ($path as $cookies) {
                foreach ($cookies as $cookie) {
                    $flattenedCookies[] = $cookie;
                }
            }
        }

        return $flattenedCookies;
    }

    /**
     * Clears a cookie in the browser
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     */
    public function clearCookie($name, $path = '/', $domain = null)
    {
        $this->setCookie(new Cookie($name, null, 1, $path, $domain));
    }


    /**
     * Returns an iterator for headers.
     *
     * @return \ArrayIterator An \ArrayIterator instance
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->headers);
    }

    /**
     * Returns the number of headers.
     *
     * @return int The number of headers
     */
    public function count()
    {
        return count($this->headers);
    }
}