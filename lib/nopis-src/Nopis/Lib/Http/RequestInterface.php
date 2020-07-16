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

interface RequestInterface
{

    /** HTTP request method */
    const
            GET    = 'GET',
            POST   = 'POST',
            HEAD   = 'HEAD',
            PUT    = 'PUT',
            DELETE = 'DELETE';

    /**
     * Return a singleton object.
     *
     * @return \Nopis\Lib\Http\RequestInterface
     */
    public static function getInstance();

    /**
     * Returns URL object.
     *
     * @return \Nopis\Lib\Http\Url
     */
    public function getUrl();

    /**
     * Return current url
     *
     * @return string
     */
    public function getCurrentUrl();

    /**
     * Returns query string provided to the script via URL ($_GET).
     * If no key is passed, returns the entire array.
     *
     * @return string
     */
    public function getQueryString();

    /**
     * $_GET
     *
     * Returns variable provided to the script via GET method ($_GET).
     *
     * @param  string $key
     * @param  mixed  $default value
     * @param  bool   $deep    If true, a path like foo[bar] will find deeper items
     * @return mixed
     */
    public function get($key, $default = NULL, $deep = false);

    /**
     * Set a GET or POST params.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type GET|POST
     */
    public function set($key, $value, $type = 'GET');

    /**
     * All $_GET
     *
     * Returns all variables provided to the script via GET method ($_GET).
     *
     * @return array
     */
    public function getAll();

    /**
     * $_POST
     *
     * Returns variable provided to the script via POST method ($_POST).
     *
     * @param  string key
     * @param  mixed  default value
     * @param  bool   $deep    If true, a path like foo[bar] will find deeper items
     * @return mixed
     */
    public function getPost($key, $default = NULL, $deep = false);

    /**
     * All $_POST
     *
     * Returns all variables provided to the script via POST method ($_POST).
     *
     * @return array
     */
    public function getPosts();

    /**
     * $_POST or $_GET
     *
     * Returns variable provided to the script via POST or GET method ($_POST or $_GET), POST first.
     *
     * @param  string key
     * @param  mixed  default value
     * @return mixed
     */
    public function getRequest($key, $default = NULL);

    /**
     * Returns UploadedFile instance.
     *
     * @param  string key (or more keys)
     * @param  bool   $deep    If true, a path like foo[bar] will find deeper items
     * @return \Nopis\Lib\Http\File\UploadedFile
     */
    public function getFile($key, $deep = false);

    /**
     * Returns all UploadedFile instance.
     *
     * @return \Nopis\Lib\Http\File\UploadedFile[]
     */
    public function getFiles();

    /**
     * Returns variable provided to the script via HTTP cookies.
     *
     * @param  string $key
     * @param  mixed  $default value
     * @return string
     */
    public function getCookie($key, $default = NULL);

    /**
     * Returns all variables provided to the script via HTTP cookies.
     *
     * @return array
     */
    public function getCookies();

    /**
     * Return the $_SERVER with the $key
     *
     * @param  string $key
     * @return string
     */
    public function getServer($key);

    /**
     * Return the $_SERVER
     *
     * @return array
     */
    public function getServers();

    /**
     * Return the value of the HTTP header. Pass the header name as the
     * plain, HTTP-specified header name (e.g. 'Accept-Encoding').
     *
     * @param  string
     * @param  mixed
     * @return mixed
     */
    public function getHeader($header, $default = NULL);

    /**
     * Returns all HTTP headers.
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Returns the HTTP REFERER.
     *
     * @return \Nopis\Lib\Http\Url
     */
    public function getReferer();

    /**
     * Returns HTTP request method (GET, POST, HEAD, PUT, ...). The method is case-sensitive.
     *
     * @return string
     */
    public function getMethod();

    /**
     * Sets the Session.
     *
     * @param \Nopis\Lib\Http\Session\SessionInterface $session The Session
     */
    public function setSession(Session\SessionInterface $session);

    /**
     * Gets the Session.
     *
     * @return \Nopis\Lib\Http\Session\SessionInterface The session
     */
    public function getSession();

    /**
     * Whether the request contains a Session object.
     *
     * This method does not give any information about the state of the session object,
     * like whether the session is started or not. It is just a way to check if this Request
     * is associated with a Session instance.
     *
     * @return bool    true when the Request contains a Session object, false otherwise
     */
    public function hasSession();

    /**
     * Set the Application
     *
     * @param \Nopis\Lib\Http\ApplicationInterface $app
     */
    public function setApp(ApplicationInterface $app);

    /**
     * Forward the request to other path no via client browser
     *
     * @param type $path
     */
    public function forward($path);

    /**
     * Checks HTTP request method.
     *
     * @param  string
     * @return bool
     */
    public function isMethod($method);

    /**
     * check the REQUEST_METHOD if POST
     *
     * @return bool
     */
    public function isPost();

    /**
     * Is the request is sent via secure channel (https).
     *
     * @return bool
     */
    public function isSecured();

    /**
     * Is AJAX request?
     *
     * @return bool
     */
    public function isAjax();

    /**
     * Is request from mobile browser
     *
     * @return boolean
     */
    public function isMobile();

    /**
     * Convert the surplus path string into $_GET
     * a typical url : www.domain.com/goods_list?name=wangb&page=1
     * now!!!
     * eg. Convert url like www.domain.com/goods_list/name/wangb/page/1
     * or www.domain.com/goods_list/name.wangb/page.1
     * TO $_GET['name'] = wangb AND $_GET['page'] = 1
     *
     * @param string $queryString
     * @return null
     */
    public function convertUrl2Get($queryString);
}
