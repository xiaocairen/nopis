<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Database;

use PDO;
use PDOStatement;
use PDOException;
use Exception;
use Nopis\Lib\Database\Exceptions\PDOErrorException;
use Nopis\Lib\Database\Exceptions\PDOSErrorException;

/**
 * Description of DB
 *
 * @author Wangbin
 */
class DB extends AbstractQuery
{

    /**
     * @var \Nopis\Lib\Database\DB
     */
    private static $instance;

    /**
     * @var \Nopis\Lib\Database\_dbm_
     */
    private $dbm;

    /**
     * Constructor.
     *
     * @param array $dbconfig
     */
    private function __construct(array $dbconfig)
    {
        $this->dbm = new _dbm_($dbconfig);
    }

    private function __clone(){}

    /**
     * 返回数据库链接实例
     *
     * @param array $dbconfig
     *
     * @return self
     *
     * @api
     */
    public static function getInstance(array $dbconfig)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self($dbconfig);
        }

        return self::$instance;
    }

    /**
     * 预执行一条SQL语句，
     * 成功时返回 TRUE， 或者在失败时返回 FALSE
     *
     * @param string $query
     * @return boolean
     *
     * @api
     */
    public function prepare($query)
    {
        $this->pdo = $this->_initPDO($query);
        $this->pdos = $this->pdo->prepare($query);

        return $this->pdos instanceof PDOStatement;
    }

    /**
     * 绑定一个参数到指定的变量名，不同于 bindValue() ，<br />
     * 此变量作为引用被绑定，并只在 execute() 被调用的时候才取其值，<br />
     * 成功时返回 TRUE， 或者在失败时返回 FALSE
     *
     * @param int|string $param
     * @param mixed $var
     *
     * @return boolean
     *
     * @throws Exception
     *
     * @api
     */
    public function bindParam($param, $var)
    {
        if (!$this->pdos instanceof PDOStatement)
            throw new Exception('Not found PDOStatment');

        return $this->pdos->bindParam($param, $var);
    }

    /**
     * 把一个值绑定到一个参数，
     * 成功时返回 TRUE， 或者在失败时返回 FALSE
     *
     * @param int|string $param
     * @param mixed $value
     *
     * @return boolean
     *
     * @throws Exception
     *
     * @api
     */
    public function bindValue($param, $value)
    {
        if (!$this->pdos instanceof PDOStatement)
            throw new Exception('Not found PDOStatment');

        return $this->pdos->bindValue($param, $value);
    }

    /**
     * 执行一条预处理语句，
     * 成功时返回 TRUE， 或者在失败时抛出 QueryErrorException 异常
     *
     * @param array $params
     * @return boolean
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     *
     * @api
     */
    public function execute(array $params = null)
    {
        if (false === $this->pdos->execute($params ?: null))
            throw new PDOSErrorException($this->pdos);

        return true;
    }

    /**
     * 执行一条 SQL 语句 ( insert | update | delete 等 )，并返回受影响的行数，<br />
     * 不建议直接传SQL语句参数直接执行，建议先调用本对象提供的 insert | update | delete 等API <br />
     * 再调用 exec() 查询
     *
     * @param string $query
     * @return int   整数表示 insert | update | delete 等受影响的行数
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     *
     * @api
     */
    public function exec($query = null)
    {
        $query = null === $query ? $this->getQueryStatement() : $query;
        if (empty($query))
            throw new Exceptions\QueryErrorException('Not found query statement');

        $this->pdo = $this->_initPDO($query);

        $params = $this->getQueryParams();
        $rowCount = 0;
        if (empty($params)) {
            if (false === ($rowCount = $this->pdo->exec($query)))
                throw new PDOErrorException($this->pdo);

        }
        else {
            $this->pdos = $this->pdo->prepare($query);
            if (!$this->pdos instanceof PDOStatement)
                throw new PDOErrorException($this->pdo);

            if (false === $this->pdos->execute($params))
                throw new PDOSErrorException($this->pdos);


            $rowCount = $this->pdos->rowCount();
        }

        return $rowCount;
    }

    /**
     * 执行一条SQL查询语句 ( 如：select 等 )，如果参数$sql不为空，则调用PDO::query()直接查询；<br />
     * 但对于数据库的CURD基于安全不建议直接查询，建议使用本对象提供的<br />
     *  select | insert | update | delete 等API执行SQL语句，然后在调用 query() 查询
     *
     * @param string $query
     * @return \Nopis\Lib\Database\DB
     *
     * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
     * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
     *
     * @api
     */
    public function query($query = null)
    {
        if ($this->pdos) {
            $this->closeCursor();
        }

        $query = null === $query ? $this->getQueryStatement() : $query;
        if (empty($query))
            throw new Exceptions\QueryErrorException('Not found query statement');

        $this->pdo = $this->_initPDO($query);

        $params = $this->getQueryParams();
        if (empty($params)) {
            if (false === ($this->pdos = $this->pdo->query($query)))
                throw new PDOErrorException($this->pdo);

        }
        else {
            $this->pdos = $this->pdo->prepare($query);
            if (!$this->pdos instanceof PDOStatement)
                throw new PDOErrorException($this->pdo);

            if (false === $this->pdos->execute($params))
                throw new PDOSErrorException($this->pdos);

        }

        return $this;
    }

    /**
     * 从结果集中获取下一行；
     * 如果参数 $fetchTable 不为空，则该参数必须是实现了接口 \Nopis\Lib\Database\TableInterface 的一个类或其对象实例；
     * 此时则把结果集中的字段映射到此对象的属性，并返回这个对象
     *
     * 查询数据为空时，总是返回false
     *
     * @param string|\Nopis\Lib\Database\TableInterface  $fetchTable  实现了接口TableInterface的类的名称或其对象实例
     * @param array   $ctorargs    类的构造函数的参数(当第一个参数是类名时)
     * @return mixed    查询数据为空时，总是返回false
     * @throws Exception
     *
     * @api
     */
    public function fetch($fetchTable = null, array $ctorargs = [])
    {
        if (!$this->pdos instanceof PDOStatement) {
            throw new Exception('Not found PDOStatment');
        }

        if (null !== $fetchTable && in_array('Nopis\Lib\Database\TableInterface', class_implements($fetchTable))) {
            if (is_object($fetchTable)) {
                $this->pdos->setFetchMode(PDO::FETCH_INTO, $fetchTable);
            } elseif (!empty($ctorargs)) {
                $this->pdos->setFetchMode(PDO::FETCH_CLASS, $fetchTable, $ctorargs);
            } else {
                $this->pdos->setFetchMode(PDO::FETCH_CLASS, $fetchTable);
            }
        }

        return $this->pdos->fetch();
    }

    /**
     * 返回一个包含结果集中所有行的数组，数组的元素类型依据给定的第一个参数；
     * 如果参数 $fetchTable 不为空，则该参数必须是实现了接口 \Nopis\Lib\Database\TableInterface 的一个类或其对象实例；
     * 此时则把结果集中的字段映射到此对象的属性，并返回一个包含了此对象集的数组
     *
     * @param string|\Nopis\Lib\Database\TableInterface  $fetchTable  实现了接口TableInterface的类的名称或其对象实例
     * @param array   $ctorargs    类的构造函数的参数(当第一个参数是类名时)
     * @return stdClass[]|\Nopis\Lib\Database\TableInterface[]    结果集为空时返回空数组
     * @throws Exception
     *
     * @api
     */
    public function fetchAll($fetchTable = null, array $ctorargs = [])
    {
        if (!$this->pdos instanceof PDOStatement) {
            throw new Exception('Not found PDOStatment');
        }

        if (null !== $fetchTable && in_array('Nopis\Lib\Database\TableInterface', class_implements($fetchTable))) {
            if (is_object($fetchTable)) {
                $this->pdos->setFetchMode(PDO::FETCH_INTO, $fetchTable);
            } elseif (!empty($ctorargs)) {
                $this->pdos->setFetchMode(PDO::FETCH_CLASS, $fetchTable, $ctorargs);
            } else {
                $this->pdos->setFetchMode(PDO::FETCH_CLASS, $fetchTable);
            }
        }

        return $this->pdos->fetchAll();
    }

    /**
     * 从结果集中的下一行返回单独的一列，如果没有了，则返回 FALSE
     *
     * @param int $index  列的索引
     * @return mixed    没有结果时返回false
     *
     * @api
     */
    public function fetchColumn($index = 0)
    {
        if (!$this->pdos instanceof PDOStatement) {
            throw new Exception('Not found PDOStatment');
        }

        return $this->pdos->fetchColumn($index);
    }

    /**
     * 执行SQL查询语句 SELECT COUNT(*) 查询结果数量
     *
     * @return int
     *
     * @api
     */
    public function count()
    {
        parent::count();

        return $this->query()->fetchColumn();
    }

    /**
     * 为语句设置默认的获取结果集模式，
     * 成功时返回 TRUE， 或者在失败时返回 FALSE
     *
     * @param string $mode  num | assoc | both | obj
     *
     * @return boolean
     */
    public function setFetchMode($mode)
    {
        switch (strtolower(trim($mode))) {
            case 'num':
                return $this->pdos->setFetchMode(PDO::FETCH_NUM);
            case 'assoc':
                return $this->pdos->setFetchMode(PDO::FETCH_ASSOC);
            case 'both':
                return $this->pdos->setFetchMode(PDO::FETCH_BOTH);
            case 'obj':
            default:
                return true;
        };
    }

    /**
     * 返回最后一条insert语句插入行的ID
     *
     * @return int
     *
     * @api
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * 开始一个事务
     *
     * @api
     */
    public function beginTransaction()
    {
        if ($this->inTransaction())
            return;

        if (!$this->pdo->beginTransaction()) {
            throw new PDOErrorException($this->pdo);
        }
    }

    /**
     * 检查是否在一个事务内
     * 如果当前事务处于激活，则返回 TRUE ，否则返回 FALSE
     *
     * @return bool
     *
     * @api
     */
    public function inTransaction()
    {
        $this->pdo = $this->dbm->getWriteableServer();

        return $this->pdo->inTransaction();
    }

    /**
     * 提交一个事务
     *
     * @api
     */
    public function commit()
    {
        if ($this->inTransaction() && !$this->pdo->commit()) {
            throw new PDOErrorException($this->pdo);
        }
    }

    /**
     * 滚回一个事务
     *
     * @api
     */
    public function rollBack()
    {
        if ($this->inTransaction() && !$this->pdo->rollBack()) {
            throw new PDOErrorException($this->pdo);
        }
    }

    /**
     * 转义字符串中的特殊字符，防注入
     *
     * @param string $val
     * @return string
     *
     * @api
     */
    public function quote($val)
    {
        return $this->pdo->quote($val);
    }

    /**
     * 关闭游标，使查询语句能再次被执行
     *
     * @return boolean
     */
    private function closeCursor()
    {
        $this->pdos->closeCursor();
    }

    /**
     * 获取PDO连接
     *
     * @param string $query
     * @return \PDO
     */
    protected function _initPDO($query)
    {
        return $this->isRead($query) ? $this->dbm->getReadableServer() : $this->dbm->getWriteableServer();
    }

    /**
     * ToString.
     *
     * @return string
     */
    public function __toString()
    {
        $query = $this->getQueryStatement();
        $param = $this->getQueryParams();
        if (!$param) {
            return $query;
        } else {
            ob_start();
            echo '<div>' . $query . '</div>';
            echo '<pre>' . print_r($param, true) . '</pre>';

            $ret = ob_get_contents();
            ob_end_clean();

            return $ret;
        }
    }

}

/* ----------------------------------------------------------------------
 * 下面的类使数据库连接类支持读写分离
 * ----------------------------------------------------------------------
 */

/**
 * Description of Dbc
 *
 * @author wb
 */
class _dbm_
{
    /**
     * @var \PDO
     */
    private $writeableServer;

    /**
     * @var \PDO
     */
    private $readableServer;

    /**
     * @var \Nopis\Lib\Database\_dbCollection
     */
    private $dbCollection;

    /**
     * Constructor.
     *
     * @param array $dbconfig
     */
    public function __construct(array $dbconfig)
    {
        $this->dbCollection = new _dbCollection($dbconfig);
    }

    /**
     * Get PDO connection of readable server
     *
     * @return \PDO
     */
    public function getReadableServer()
    {
        if (null === $this->readableServer)
            $this->readableServer = $this->dbCollection->getReadableServer();

        return $this->readableServer;
    }

    /**
     * Get PDO connection of writeable server
     *
     * @return \PDO
     */
    public function getWriteableServer()
    {
        if (null === $this->writeableServer)
            $this->writeableServer = $this->dbCollection->getWriteableServer();

        return $this->writeableServer;
    }
}

class _dbCollection
{
    /**
     * @var boolean
     */
    protected $isAlone;

    /**
     * @var \Nopis\Lib\Database\_dbConnection
     */
    protected $aloneServer;

    /**
     * @var \Nopis\Lib\Database\_dbConnection[]
     */
    protected $writeServers = [];

    /**
     * @var \Nopis\Lib\Database\_dbConnection[]
     */
    protected $readServers = [];

    /**
     * @var int
     */
    protected $readSvrCount = 0;

    /**
     * @var int
     */
    protected $writeSvrCount = 0;

    /**
     * @var array
     */
    protected $dbconfig;

    public function __construct(array $dbconfig)
    {
        $this->dbconfig = $dbconfig;
        if ($this->isAlone()) {
            $this->aloneServer = new _dbConnection($dbconfig);
        } else {
            foreach ($this->dbconfig['read'] as $read) {
                $this->readServers[] = new _dbConnection($read);
            }

            foreach ($this->dbconfig['write'] as $write) {
                $this->writeServers[] = new _dbConnection($write);
            }

            $this->readSvrCount  = count($this->readServers);
            $this->writeSvrCount = count($this->writeServers);
        }
    }

    public function isAlone()
    {
        if (null === $this->isAlone)
            $this->isAlone = empty($this->dbconfig['read']) && empty($this->dbconfig['write']);

        return $this->isAlone;
    }

    /**
     * Get PDO connection of readable server
     *
     * @return \PDO
     * @throws \Exception
     */
    public function getReadableServer()
    {
        $pdo = null;
        if ($this->isAlone()) {
            $pdo = $this->aloneServer->getConnection();
        } else {
            if ($this->readSvrCount == 1) {
                $pdo = $this->readServers[0]->getConnection();
            } else {
                for ($i = 0; $i < $this->readSvrCount; $i++) {
                    $k = mt_rand(0, $this->readSvrCount - 1);
                    $pdo = $this->readServers[$k]->getConnection();
                    if ($pdo instanceof \PDO)
                        break;
                }
            }
        }

        if (null === $pdo) {
            throw new \Exception('Unable to connect readable database server');
        }

        return $pdo;
    }

    /**
     * Get PDO connection of writeable server
     *
     * @return \PDO
     */
    public function getWriteableServer()
    {
        $pdo = null;
        if ($this->isAlone()) {
            $pdo = $this->aloneServer->getConnection();
        } else {
            if ($this->writeSvrCount == 1) {
                $pdo = $this->writeServers[0]->getConnection();
            } else {
                for ($i = 0; $i < $this->writeSvrCount; $i++) {
                    $k = mt_rand(0, $this->writeSvrCount - 1);
                    $pdo = $this->writeServers[$k]->getConnection();
                    if ($pdo instanceof \PDO)
                        break;
                }
            }
        }

        if (null === $pdo) {
            throw new \Exception('Unable to connect writeable database server');
        }

        return $pdo;
    }
}

class _dbConnection
{
    /**
     * @var \PDO
     */
    private $pdo;

    /* ----------------------------------------------------------
     * database config
     * ----------------------------------------------------------
     */
    private $driver;
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $password;
    private $charset;

    public function __construct(array $dbconfig)
    {
        foreach ($dbconfig as $key => $val) {
            $this->$key = $val;
        };
    }

    /**
     * Get PDO object of database connection
     *
     * @return \PDO
     */
    public function getConnection()
    {
        if (!$this->hasConnection()) {
            $this->connect($this->driver, $this->host, $this->port, $this->dbname, $this->user, $this->password, $this->charset);
        }

        return $this->pdo;
    }

    /**
     * 检查是否已连接数据库
     *
     * @return boolean
     */
    protected function hasConnection()
    {
        return $this->pdo instanceof \PDO;
    }

    /**
     * 连接数据库
     *
     * @param string $driver    数据库驱动
     * @param string $host      数据库主机地址
     * @param int    $port      数据库端口
     * @param string $dbname    数据库库名
     * @param string $user      数据库用户名
     * @param string $passwd    数据库密码
     * @param string $charset   与数据库交互所用的编码
     * @throws Exception
     */
    protected function connect($driver, $host, $port, $dbname, $user, $passwd, $charset)
    {
        try {

            $commands       = [];
            $driver         = strtolower($driver);
            $port           = is_int($port * 1) ? $port : null;

            switch ($driver) {
                case 'mariadb':
                    $driver = 'mysql';

                case 'mysql':
                    $dsn = 'mysql:host=' . $host . ($port ? ';port=' . $port : '') . ';dbname=' . $dbname;

                    // Make MySQL using standard quoted identifier
                    $commands[] = 'SET SQL_MODE=ANSI_QUOTES';
                    break;

                case 'pgsql':
                    $dsn = 'pgsql:host=' . $host . ($port ? ';port=' . $port : '') . ';dbname=' . $dbname;
                    break;

                case 'sybase':
                    $dsn = 'dblib:host=' . $host . ($port ? ':' . $port : '') . ';dbname=' . $dbname;
                    break;

                case 'oracle':
                    $dbname = $host ? '//' . $host . ($port ? ':' . $port : ':1521') . '/' . $dbname : $dbname;

                    $dsn = 'oci:dbname=' . $dbname . ($charset ? ';charset=' . $charset : '');
                    break;

                case 'mssql':
                    $dsn = strstr(PHP_OS, 'WIN') ?
                            'sqlsrv:server=' . $host . ($port ? ',' . $port : '') . ';database=' . $dbname :
                            'dblib:host=' . $host . ($port ? ':' . $port : '') . ';dbname=' . $dbname;

                    // Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
                    $commands[] = 'SET QUOTED_IDENTIFIER ON';
                    break;

                case 'sqlite':
                    $dsn = 'sqlite:' . $dbname;
                    $user = null;
                    $passwd = null;
                    break;

                default:
                    throw new Exception(sprintf('Not found the database driver "%s"', $driver));
            }

            if (in_array($driver, ['mariadb', 'mysql', 'pgsql', 'sybase', 'mssql']) && $charset) {
                $commands[] = 'SET NAMES \'' . $charset . '\'';
            }

            $this->pdo = new PDO($dsn, $user, $passwd);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $this->pdo->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
            $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, FALSE);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);

            foreach ($commands as $cmd) {
                $this->pdo->exec($cmd);
            }

        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
}
