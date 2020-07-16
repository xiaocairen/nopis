<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Nopis\Lib\Console\Action;

use Exception;
use PDO;
use Nopis\Lib\Console\OptCommand;
use Nopis\Lib\Config\Neon\Neon;
use Nopis\Lib\FileSystem\File;

/**
 * Description of CreateEntity
 *
 * @author wb
 */
class CreateEntity
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var \Nopis\Lib\Console\OptCommand
     */
    private $optCmd;

    /**
     * @var array
     */
    private $skipTables = [
        'admin', 'admin_group', 'admin_group_map',
        'admin_group_backend', 'backend_map', 'classify',
        'user', 'user_group', 'user_group_map'
    ];

    public function __construct()
    {
        $this->config = [];
        $this->namespace = '';
        $this->optCmd = null;
    }

    public function create(OptCommand $optCmd, $configParam)
    {
        $this->optCmd = $optCmd;

        $fileInfo = new \SplFileInfo($configParam);
        if (!$fileInfo->isFile()) {
            throw new Exception("File[{$cfgFile}] is not exist");
        }
        if (!$fileInfo->isReadable()) {
            throw new Exception("File[{$cfgFile}] is not readable");
        }

        $fileObj = $fileInfo->openFile('r');

        $content = '';
        while (!$fileObj->eof()) {
            $content .= $fileObj->fgets();
        }

        try {
            $this->config = Neon::parse($content);
        } catch (Exception $e) {
            throw new Exception("File[{$cfgFile}] parse error:{$e->getMessage()}");
        }

        if (!isset($this->config['database']) || !isset($this->config['table'])) {
            throw new Exception('Config content is empty');
        }

        $this->validate();

        $this->namespace = trim(trim($this->config['entity_namespace']), '\\');
        $this->pdo = $this->getPdo();

        foreach ($this->config['table'] as $objName => $table) {
            if (in_array($table['tablename'], $this->skipTables)) {
                echo "skip system table {$table['tablename']}\n";
                continue;
            } else {
                echo "create table entity {$table['tablename']} with class name {$objName}\n";
            }
            $this->createEntity($objName, $table['tablename']);
        }
    }

    private function createEntity($objName, $tablename) {
        $stmt = $this->pdo->query('SHOW COLUMNS FROM ' . $tablename);
        if (!$stmt) {
            throw new Exception("Not found database table {$tablename}");
        }

        $fields = $stmt->fetchAll();
        $primaryKey = '';
        $field_list = '';

        foreach ($fields as $field) {
            if ($field->Key == 'PRI') {
                $primaryKey = $field->Field;
                $db_type = explode(' ', $field->Type);
            $field_list .= <<<EOS
    /**
     * @primary primaryKey
     * @db_type {$db_type[0]}
     */
    public \${$field->Field};\n
EOS;
            } else {
                $val = '';
                switch ($field->Null) {
                    case 'YES':
                        if (false !== stripos($field->Type, 'char')) {
                            $val = ' = \'\'';
                        }
                        break;

                    case 'NO':
                        if ('' !== $field->Default) {
                            if (false !== stripos($field->Type, 'char')) {
                                $val = ' = \'' . $field->Default . '\'';
                            } elseif (false !== stripos($field->Type, 'int') || false !== stripos($field->Type, 'decimal')) {
                                $val = ' = ' . $field->Default;
                            }
                        }
                        break;
                }

                $db_type = explode(' ', $field->Type);
                $field_list .= <<<EOS

    /**
     * @db_type {$db_type[0]}
     */
    public \${$field->Field}{$val};\n
EOS;
            }
        }


        $entity = <<<EOS
<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace {$this->namespace};

use nPub\SPI\Persistence\Content\SPIContent;

/**
 * Description of {$objName}
 *
 * @author administrator
 */
class {$objName} extends SPIContent
{

{$field_list}
    /**
     * 返回关系对象列表
     *
     * @return array|null
     */
    public function getAssistants()
    {
        return null;
    }

    /**
     * 返回数据表
     *
     * @return string 数据表
     */
    final public static function getTableName()
    {
        return '{$tablename}';
    }

    /**
     * 返回主键
     *
     * @return string 主键
     */
    final public static function getPrimaryKey()
    {
        return '{$primaryKey}';
    }

}

EOS;

        $file = $this->optCmd->configurator->getRootDir() . '/srv/' . str_replace('\\', '/', $this->namespace) . '/' . $objName . '.php';
        if (file_exists($file)) {
            $bakFile = $this->optCmd->configurator->getRootDir() . '/srv/' . str_replace('\\', '/', $this->namespace) . '/' . $objName . '.bak.php';
            $fh = new File($file);
            if (!$fh->copyTo($bakFile, true)) {
                throw new Exception('Unable to backup old entity file ' . $file);
            }
        }
        $fh = new \SplFileObject($file, 'w+');
        $fh->fwrite($entity);
        unset($fh);

        $service_file = $this->optCmd->configurator->getRootDir() . '/srv/Service/Aggregate/' . $objName . 'Service.php';
        $service_namespace = $this->namespace . '\\' . $objName;
        $service = <<<EOS
<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Service\Aggregate;

use {$service_namespace};

/**
 * Description of {$objName}Service
 *
 * @author administrator
 */
class {$objName}Service extends {$objName}
{

}
EOS;

        if (file_exists($service_file)) {
            $bakFile = $this->optCmd->configurator->getRootDir() . '/srv/Service/Aggregate/' . $objName . 'Service.bak.php';
            $fh = new File($service_file);
            if (!$fh->copyTo($bakFile, true)) {
                throw new Exception('Unable to backup old service file ' . $service_file);
            }
        }
        $fh = new \SplFileObject($service_file, 'w+');
        $fh->fwrite($service);
        unset($fh);

        $delegate_file = $this->optCmd->configurator->getRootDir() . '/srv/Service/ServiceDelegateInterface.php';
        $fh = new \SplFileObject($delegate_file, 'r+');
        $delegate_contents = '';
        while (!$fh->eof()) {
            $delegate_contents .= $fh->current();
            $fh->next();
        }
        unset($fh);

        $delegate_contents = trim($delegate_contents);
        $delegate_contents = rtrim($delegate_contents, '}');
        $delegate_contents = rtrim($delegate_contents);

        $interface_name = 'interface ServiceDelegateInterface';
        $pos = strpos($delegate_contents, $interface_name);
        $delegate_contents = substr($delegate_contents, $pos + strlen($interface_name) + 3);

        $delegate_contents = <<<EOS
<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Service;

interface ServiceDelegateInterface
{

    /**
     * @return \Service\Aggregate\\{$objName}Service
     */
    public function get{$objName}Service(array \$args = null);

{$delegate_contents}

}
EOS;

        $fh = new \SplFileObject($delegate_file, 'w+');
        $fh->fwrite($delegate_contents);
        unset($fh);
    }

    private function validate()
    {
        if (!$this->config)
            throw new Exception('Config content is empty');

        // database
        if (!isset($this->config['database']) || empty($this->config['database'])) {
            throw new Exception('Database config is empty');
        }
        if (!isset($this->config['database']['driver']) || empty($this->config['database']['driver'])) {
            throw new Exception('Database driver is empty');
        }
        if (!isset($this->config['database']['host']) || empty($this->config['database']['host'])) {
            throw new Exception('Database host is empty');
        }
        if (!isset($this->config['database']['port']) || empty($this->config['database']['port'])) {
            throw new Exception('Database port is empty');
        }
        if (!isset($this->config['database']['dbname']) || empty($this->config['database']['dbname'])) {
            throw new Exception('Database dbname is empty');
        }
        if (!isset($this->config['database']['user']) || empty($this->config['database']['user'])) {
            throw new Exception('Database user is empty');
        }
        if (!isset($this->config['database']['password']) || empty($this->config['database']['password'])) {
            throw new Exception('Database password is empty');
        }
        if (!isset($this->config['database']['charset']) || empty($this->config['database']['charset'])) {
            throw new Exception('Database charset is empty');
        }

        // entity_namespace
        if (!isset($this->config['entity_namespace']) || !is_string($this->config['entity_namespace']) || empty($this->config['entity_namespace'])) {
            throw new Exception('entity_namespace is empty or not a string');
        }

        // table
        if (!isset($this->config['table']) || empty($this->config['table'])) {
            throw new Exception('Table is empty');
        }
        if (!is_array($this->config['table'])) {
            throw new Exception('Table config is not an array');
        }

        foreach ($this->config['table'] as $key => $arr) {
            if (!is_array($arr)) {
                throw new Exception('Table "' . $key . '" config is not an array');
            }
            if (!isset($arr['tablename']) || !is_string($arr['tablename']) || empty($arr['tablename'])) {
                throw new Exception('At table "' . $key . '" config, tablename is no set or not a string');
            }
        }
    }

    /**
     * @return \PDO
     * @throws Exception
     */
    private function getPdo()
    {
        $db = $this->config['database'];

        switch ($db['driver']) {
            case 'mariadb':
                $driver = 'mysql';

            case 'mysql':
                $dsn = 'mysql:host=' . $db['host'] . ($db['port'] ? ';port=' . $db['port'] : '') . ';dbname=' . $db['dbname'];

                // Make MySQL using standard quoted identifier
                $commands[] = 'SET SQL_MODE=ANSI_QUOTES';
                break;

            case 'pgsql':
                $dsn = 'pgsql:host=' . $db['host'] . ($db['port'] ? ';port=' . $db['port'] : '') . ';dbname=' . $db['dbname'];
                break;

            case 'sybase':
                $dsn = 'dblib:host=' . $db['host'] . ($db['port'] ? ':' . $db['port'] : '') . ';dbname=' . $db['dbname'];
                break;

            case 'oracle':
                $dbname = $db['host'] ? '//' . $db['host'] . ($db['port'] ? ':' . $db['port'] : ':1521') . '/' . $db['dbname'] : $db['dbname'];

                $dsn = 'oci:dbname=' . $dbname . ($charset ? ';charset=' . $charset : '');
                break;

            case 'mssql':
                $dsn = strstr(PHP_OS, 'WIN') ?
                        'sqlsrv:server=' . $db['host'] . ($db['port'] ? ',' . $db['port'] : '') . ';database=' . $db['dbname'] :
                        'dblib:host=' . $db['host'] . ($db['port'] ? ':' . $db['port'] : '') . ';dbname=' . $db['dbname'];

                // Keep MSSQL QUOTED_IDENTIFIER is ON for standard quoting
                $commands[] = 'SET QUOTED_IDENTIFIER ON';
                break;

            case 'sqlite':
                $dsn = 'sqlite:' . $db['dbname'];
                break;

            default:
                throw new Exception(sprintf('Not found the database driver "%s"', $driver));
        }

        $pdo = new PDO($dsn, $db['user'], $db['password']);

        $pdo->query('SET NAMES ' . $db['charset']);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $pdo->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_TO_STRING);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);

        foreach ($commands as $cmd) {
            $pdo->exec($cmd);
        }
        return $pdo;
    }
}
