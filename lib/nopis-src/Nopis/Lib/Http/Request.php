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

class Request implements RequestInterface
{

    /**
     * @var string
     */
    private $method;

    /**
     * @var \Nopis\Lib\Http\Url
     */
    private $url;

    /**
     * @var \Nopis\Lib\Http\Parameters
     */
    private $query;

    /**
     * @var \Nopis\Lib\Http\Parameters
     */
    private $post;

    /**
     * @var \Nopis\Lib\Http\Parameters
     */
    private $cookies;

    /**
     * @var \Nopis\Lib\Http\Files
     */
    private $files;

    /**
     * @var \Nopis\Lib\Http\Server
     */
    private $server;

    /**
     * @var \Nopis\Lib\Http\Headers
     */
    private $headers;

    /**
     * @var \Nopis\Lib\Http\Session\SessionInterface
     */
    private $session;

    /**
     * @var \Nopis\Lib\Http\Request
     */
    private static $_instance;

    /**
     * @var \Nopis\Lib\Http\ApplicationInterface
     */
    private $app;

    /**
     * construct function
     */
    private function __construct
    (
        array $query   = [],
        array $post    = [],
        array $cookies = [],
        array $files   = [],
        array $server  = []
    )
    {
        $this->query   = new Parameters($query);
        $this->post    = new Parameters($post);
		$this->cookies = new Parameters($cookies);
		$this->files   = new Files($files);
        $this->server  = new Server($server, $this->url);
        $this->headers = new Headers($this->server->getHeaders());
        $this->session = new Session\Session();

    }

    /**
     * forbid clone the Request Object
     */
    private function __clone()
    {
        trigger_error('Clone Request(Object) is not allow!', E_USER_ERROR);
    }

    /**
     * @return \Nopis\Lib\Http\Request
     */
    public static function getInstance()
    {
        if (!(static::$_instance instanceof static)) {
            // filter all variables via the script.
            static::canonicalize();

            static::$_instance = new static($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
        }
        return static::$_instance;
    }

    /**
     * Return current url
     *
     * @return \Nopis\Lib\Http\Url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Return current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->url->getAbsoluteUrl();
    }

    /**
     * Returns query string provided to the script via URL query ($_GET).
     * If no key is passed, returns the entire array.
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->url->getQuery();
    }

    /**
     * Returns variable provided to the script via GET method ($_GET).
     *
     * @param  string $key
     * @param  mixed  $default value
     * @param  bool   $deep    If true, a path like foo[bar] will find deeper items
     * @return mixed
     */
    public function get($key, $default = NULL, $deep = false)
    {
        return $this->query->get($key, $default, $deep);
    }

    /**
     * Set a GET or POST params.
     *
     * @param string $key
     * @param mixed $value
     * @param string $type GET|POST
     */
    public function set($key, $value, $type = 'GET')
    {
        switch (strtoupper($type)) {
            case 'GET':
                $this->query->set($key, $value);
                break;

            case 'POST':
                $this->post->set($key, $value);
                break;
        }
    }

    /**
     * Returns all variables provided to the script via GET method ($_GET).
     *
     * @return array
     */
    public function getAll()
    {
        return $this->query->all();
    }

    /**
     * Returns variable provided to the script via POST method ($_POST).
     *
     * @param  string $key
     * @param  mixed  $default value
     * @param  bool   $deep    If true, a path like foo[bar] will find deeper items
     * @return mixed
     */
    public function getPost($key, $default = NULL, $deep = false)
    {
        return $this->post->get($key, $default, $deep);
    }

    /**
     * Returns all variables provided to the script via POST method ($_POST).
     *
     * @return array
     */
    public function getPosts()
    {
        return $this->post->all();
    }

    /**
     * $_POST or $_GET
     *
     * Returns variable provided to the script via POST or GET method ($_POST or $_GET), POST first.
     *
     * @param  string key
     * @param  mixed  default value
     * @return mixed
     */
    public function getRequest($key, $default = null)
    {
        $post = $this->post->get($key);
        if (null !== $post)
            return $post;

        $get = $this->query->get($key);
        if (null !== $get)
            return $get;

        return $default;
    }

    /**
     * Returns uploaded file.
     *
     * @param  string $key (or more keys)
     * @param  bool   $deep    If true, a path like foo[bar] will find deeper items
     * @return \Nopis\Lib\Http\File\UploadFile
     */
    public function getFile($key, $deep = false)
    {
        return $this->files->get($key, null, $deep);
    }

    /**
     * Returns uploaded files.
     *
     * @return \Nopis\Lib\Http\File\UploadFile[]
     */
    public function getFiles()
    {
        return $this->files->all();
    }

    /**
     * Returns variable provided to the script via HTTP cookies.
     *
     * @param  string $key
     * @param  mixed  $default value
     * @return mixed
     */
    public function getCookie($key, $default = NULL)
    {
        return $this->cookies->get($key, $default);
    }

    /**
     * Returns all variables provided to the script via HTTP cookies.
     *
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies->all();
    }

    /**
     * Returns variable provided to the script via HTTP $_SERVER.
     *
     * @param  string $key
     * @return string
     */
    public function getServer($key)
    {
        return $this->server->get($key);
    }

    /**
     * Returns variable provided to the script via HTTP $_SERVER.
     *
     * @return array
     */
    public function getServers()
    {
        return $this->server->all();
    }

    /**
     * Return the value of the HTTP header. Pass the header name as the
     * plain, HTTP-specified header name (e.g. 'Accept-Encoding').
     *
     * @param  string $header
     * @param  mixed  $default
     * @return mixed
     */
    public function getHeader($header, $default = NULL)
    {
        return $this->headers->get($header, $default);
    }

    /**
     * Returns all HTTP headers.
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * Returns the HTTP REFERER.
     *
     * @return \Nopis\Lib\Http\Url
     */
    public function getReferer()
    {
        return new Url($this->headers->get('referer') ?: $this->getCurrentUrl());
        // return null !== $this->headers->get('referer') ? new Url($this->headers->get('referer')) : null;
    }

    /**
     * Returns HTTP request method (GET, POST, HEAD, PUT, ...). The method is case-sensitive.
     *
     * @return string
     */
    public function getMethod()
    {
        if (null === $this->method) {
            $this->method = $this->server->getMethod();
        }
        return $this->method;
    }

    /**
     * Sets the Session.
     *
     * @param \Nopis\Lib\Http\Session\SessionInterface $session The Session
     */
    public function setSession(Session\SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Gets the Session.
     *
     * @return \Nopis\Lib\Http\Session\SessionInterface The session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Whether the request contains a Session object.
     *
     * This method does not give any information about the state of the session object,
     * like whether the session is started or not. It is just a way to check if this Request
     * is associated with a Session instance.
     *
     * @return bool    true when the Request contains a Session object, false otherwise
     */
    public function hasSession()
    {
        return null !== $this->session;
    }

    /**
     * Set the Application
     *
     * @param \Nopis\Lib\Http\ApplicationInterface $app
     */
    public function setApp(ApplicationInterface $app)
    {
        $this->app = $app;
    }

    /**
     * Forward the request to other path no via client browser
     *
     * @param string $path
     */
    public function forward($path)
    {
        $path = trim($path);
        if ('/' != $path[0]) {
            throw new RedirectPathNotAvailableException(
                sprintf(
                    'The Forward Path %s is not available.',
                    $path
                )
            );
        }

        $this->getUrl()->setPath($path);
        $this->app->getResponse()->send();
    }

    /**
     * Checks HTTP request method.
     * @param  string $method
     * @return bool
     */
    public function isMethod($method)
    {
        return strtoupper($method) === $this->getMethod();
    }

    /**
     * @return bool
     */
    public function isPost()
    {
        return $this->isMethod(Request::POST);
    }

    /**
     * Is the request is sent via secure channel (https).
     * @return bool
     */
    public function isSecured()
    {
        return $this->url->getScheme() === 'https';
    }

    /**
     * Is AJAX request?
     * @return bool
     */
    public function isAjax()
    {
        return $this->headers->get('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Is request from mobile browser
     *
     * @return boolean
     */
    public function isMobile()
    {
        // 测试用
        if ($this->get('debug_mobile') == 'yes')
            return true;
        if (!empty($this->server->get('HTTP_X_WAP_PROFILE')))
            return true;
        if (!empty($this->server->get('HTTP_VIA')) && stristr($this->server->get('HTTP_VIA'), 'wap'))
            return true;
        if (stristr($this->server->get('HTTP_USER_AGENT'), 'iPhone OS') || stristr($this->server->get('HTTP_USER_AGENT'), 'Android'))
            return true;

        return false;
    }

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
    public function convertUrl2Get($queryString)
    {
        if (!is_array($queryString)) {
            $queryString = trim(trim(trim($queryString), '/'));
            if (empty($queryString)) {
                return;
            }
            $queryString = explode('/', $queryString);
        }

        $get = [];
        do {
            if (isset($k) && isset($get[$k]) && empty($get[$k])) {
                $get[$k] = current($queryString);
            } else {
                $req = current($queryString);
                if (false !== strrpos($req, '.')) {
                    $cb = explode('.', $req);
                    $kb = '';
                    if (count($cb) > 1) {
                        do {
                            $kb .= $kb ? '.' . current($cb) : current($cb);
                            $val = substr($req, strlen($kb) + 1);
                            if ($val && (!isset($get[$kb]) || empty($get[$kb]))) {
                                $get[$kb] = $val;
                            }
                        } while (next($cb) !== false);
                    }
                } else {
                    $k = $req;
                    $get[$k] = '';
                }
            }
        } while (next($queryString) !== false);

        if ($get) {
            foreach ($get as $k => $v) {
                if (isset($_GET[$k]))
                    continue;
                $_GET[$k] = $v;
                $this->set($k, $v, 'GET');
            }
            // $this->query->add($get);
        }
    }

    /**
     * filter all variables provided to the script via GET or POST or HTTP cookies.
     * @return null
     */
    private static function canonicalize()
    {
        $allow = [
            '_GET'    => 1, '_POST'   => 1, '_FILES'   => 1, '_COOKIE'  => 1,
            '_SERVER' => 1, '_SESSION' => 1, '_REQUEST' => 1,
            'GLOBALS' => 1, '_ENV'    => 1,
        ];

        foreach ($GLOBALS as $key => $val) {
            if (!isset($allow[$key]))
                unset($GLOBALS[$key]);
        }

        $trim = function (& $gpc) use (& $trim) {
            foreach ($gpc as & $r)
                is_array($r) ? $trim($r) : ($r = trim($r));
        };

        foreach ($GLOBALS as $key => $val) {
            if ('_GET' == $key || '_POST' == $key || '_COOKIE' == $key || '_REQUEST' == $key)
                $trim($GLOBALS[$key]);
        }
    }

}
