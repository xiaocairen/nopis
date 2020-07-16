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

class Server extends Parameters
{
    /**
     * @var \Nopis\Lib\Http\Url
     */
    private $url;

    /**
     * @var string
     */
    private $method = null;

    /**
     * @var string
     */
    private $queryString = null;

    /**
     * @var string
     */
    private $requestUri = null;

    /**
     * @param array $parameters
     * @param \Nopis\Lib\Http\Request::$url $url
     */
    public function __construct(array $parameters, & $url)
    {
        parent::__construct($parameters);

        $this->url = $url = new Url($this->getUrl());
    }

    /**
     * Gets the HTTP headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = [];
        $contentHeaders = ['CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true];
        foreach ($this->parameters as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            }
            // CONTENT_* are not prefixed with HTTP_
            elseif (isset($contentHeaders[$key])) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * Return $_SERVER['HTTP_HOST']
     *
     * @return string
     */
    public function getHttpHost()
    {
        return $this->url->getHost();
    }

    /**
     * Returns HTTP request method (GET, POST, HEAD, PUT, ...). The method is case-sensitive.
     *
     * @return string
     */
    public function getMethod()
    {
        return strtoupper($this->get('REQUEST_METHOD'));
    }

    /**
     * Returns $_SERVER['HTTP_HOST']
     *
     * @return string
     */
    public function getQueryString()
    {
        if (null === $this->queryString) {
            $this->queryString = $this->get('QUERY_STRING');
        }
        return $this->queryString;
    }

    /**
     * Returns the REQUEST_URI taking into account
     * platform differences between Apache and IIS
     *
     * @return string
     */
    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->setRequestUri();
        }
        return $this->requestUri;
    }

    /**
     * Set the REQUEST_URI on which the instance operates
     *
     * If no request URI is passed, uses the value in $_SERVER['REQUEST_URI'],
     * $_SERVER['HTTP_X_REWRITE_URL'], or $_SERVER['ORIG_PATH_INFO'] + $_SERVER['QUERY_STRING'].
     *
     * @param string $requestUri
     */
    private function setRequestUri()
    {
        if ($this->has('HTTP_X_REWRITE_URL')) {
            // check this first so IIS will catch
            $requestUri = $this->get('HTTP_X_REWRITE_URL');
        } elseif (
        /* IIS7 with URL Rewrite: make sure we get  */
        /* the unencoded url (double slash problem) */
                $this->has('IIS_WasUrlRewritten')
                && $this->get('IIS_WasUrlRewritten') == '1'
                && $this->has('UNENCODED_URL')
                && $this->get('UNENCODED_URL') != ''
        ) {
            $requestUri = $this->get('UNENCODED_URL');
        } elseif ($this->has('REQUEST_URI')) {
            $requestUri = $this->get('REQUEST_URI');
            /* Http proxy reqs setup request uri with scheme */
            /* and host [and port] + the url path, only use url path */
            $schemeAndHttpHost = $this->url->getScheme() . '://' . $this->url->getHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        } elseif ($this->has('ORIG_PATH_INFO')) { // IIS 5.0, PHP as CGI
            $requestUri  = $this->get('ORIG_PATH_INFO');
            $queryString = $this->getQueryString();
            $requestUri .= empty($queryString) ? '' : '?' . $queryString;
        } else {
            return null;
        }

        return $requestUri;
    }

    /**
     * 返回当前访问的url地址
     *
     * @return string
     */
    private function getUrl()
    {
        if (false !== strpos($this->get('REQUEST_URI'), '.shtml')) {
            $url = ($this->get('REQUEST_SCHEME') ?: 'http') . '://' . $this->get('HTTP_HOST') . '/' . $this->get('QUERY_STRING');
        } else {
            $url = ($this->get('REQUEST_SCHEME') ?: 'http') . '://' . $this->get('HTTP_HOST') . $this->get('REQUEST_URI');
        }
        return $url;
    }

}
