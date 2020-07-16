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

/**
 * @author Wangbin
 */
abstract class AbstractQuery implements DBInterface
{

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var PDOStatement
     */
    protected $pdos;

    /**
     * @var array
     */
    private $query = [];

    /**
     * @var \Nopis\Lib\Database\Params[]
     */
    private $params = [];

    /**
     * SQL语句中的select语句；
     *
     * @param array $args   可变参数，数据表的列名，如果第一个参数是boolean值，则表示 DISTINCT 关键字；
     *                      留空则表示读取所有列；支持如下字符串和数组形式：
     * <ul>
     *      <li>select("f1", "f2", "f3", ...) 或 select("f1, f2, f3, ...") 或 select(["f1", "f2", "f3", ...])</li>
     *      <li>select(true, "f1", "f2", ...) 或 select(true, "f1, f2, ...") 或 select([true, "f1", "f2", ...])</li>
     *      <li>select("mysql_func(f1, ...)", "f2", ...) 或 select(["mysql_func(f1, ...)", "f2", ...])</li>
     *      <li>select([                                                              <br/>
     *  &nbsp;&nbsp;  tb_1_as => [ tb_1_field_1, tb_1_field_2, ... ],       <br/>
     *  &nbsp;&nbsp;  tb_2_as => [ tb_2_field_1, tb_2_field_2, ... ],  ...  <br/>
     *          ])</li>
     * </ul>
     * @return \Nopis\Lib\Database\DBInterface
     *
     * @api
     */
    public function select(...$args)
    {
        $count = count($args);
        if ($count == 0) {
            $this->query['SELECT'] = new Query\Select(null, false);
        } else if ($count == 1) {
            $this->query['SELECT'] = is_bool($args[0]) ? new Query\Select(null, true) : new Query\Select($args[0], false);
        } else {
            $f = array_shift($args);
            $fields = [];
            $distinct = false;
            if (is_bool($f)) {
                $distinct = $f;
            } else {
                $fields[] = $f;
            }
            $fields = array_merge($fields, $args);
            $this->query['SELECT'] = new Query\Select($fields, $distinct);
        }

        return $this;
    }

    /**
     * SQL语句中的insert语句；
     *
     * @param string $table 数据表的表名
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function insert($table)
    {
        $this->query['INSERT'] = new Query\Insert($table);

        return $this;
    }

    /**
     * SQL语句中的update语句；
     *
     * @param string|array $tables 值为字符串或数组；如果是字符串，则表示一个数据表名；<br />
     *                      如果是一维数组，数组的元素由表名组成；如果是二维数组，<br />
     *                      则子数组的第一个元素是表名，第二个元素是第一个元素代表的数据表的别名；<br />
     *                      例：[tbl_name1, tbl_name2, ...] 或 [[tbl_name1, tbl_as1], [tbl_name2, tbl_as2], ...]
     *
     * @return \Nopis\Lib\Database\DBInterface
     *
     * @api
     */
    public function update($tables)
    {
        $this->query['UPDATE'] = new Query\Update($tables);

        return $this;
    }

    /**
     * SQL语句中的delete语句；
     * 例: DELETE FROM t1 WHERE id=2
     *    或 DELETE t1, t2 FROM t1, t2, t3 WHERE t1.id=t2.id AND t2.id=t3.id
     *
     * @param string|array $tables 值为字符串或数组；如果是字符串，则表示一个数据表名；<br />
     *                      如果是一维数组，数组的值是表名，如果数组的键不是数字，则键表示对应数据表的别名；<br />
     *                      例：[tbl_name1, tbl_name2, ...] 或 [tbl_as1, tbl_as2, ...]
     * @param boolean $isAlias 传入的表名是否为表的别名；true 为别名，false 为表名
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function delete($tables = null, $isAlias = false)
    {
        $this->query['DELETE'] = new Query\Delete($tables, $isAlias);

        return $this;
    }

    /**
     * SQL语句中的from语句段；（select语句和delete语句用到）
     *
     * @param string|array $tables 值为字符串或数组；如果是字符串，则表示一个数据表名；<br />
     *                      如果是一维数组，数组的元素由表名组成；如果是二维数组，<br />
     *                      则子数组的第一个元素是表名，第二个元素是第一个元素代表的数据表的别名；<br />
     *                      例：<br />
     *                          字符串: 'pre_tbl_name'<br />
     *                          一维数组: [tbl_name1, tbl_name2, ...]<br />
     *                          二维数组: [[tbl_name1, tbl_as1], [tbl_name2, tbl_as2], ...]
     * @param string  $alias 只有第一个参数是字符串时，第二个参数才起作用，标识第一个参数，即数据表的别名
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function from($tables, $alias = null)
    {
        $this->query['FROM'] = new Query\From($tables, $alias);

        return $this;
    }

    /**
     * SQL语句中的set语句；insert | update 语句用到
     *
     * @param array $values  一维关联数组，数组的键必须是数据表的字段名，<br/>
     *                       例：[field_name1 => field_value1, field_name2 => field_value2]
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function set(array $values)
    {
        $this->query['SET'] = new Query\Set($values, $this);

        return $this;
    }

    /**
     * SQL语句中的values语句；例：INSERT INTO tbl_name VALUES (field_val, ...)
     *
     * @param array $values     数组要求如下：
     * <ul>
     *  <li>一维数组 [ field_1 => value_1, field_2 => value_2, ... ]，则SQL语句一次插入一条数据。</li>
     *  <li>二维数组 [ [ field_1 => value_1, field_2 => value_2, ... ], [...], ... ]，则SQL语句一次插入N条数据</li>
     *  <li>数组的键必须是字段名</li>
     * </ul>
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function values(array $values)
    {
        $this->query['VALUES'] = new Query\Values($values, $this);

        return $this;
    }

    /**
     * SQL语句中的 join 语句；与 on 语句搭配使用；
     *
     * @param string        $table          要连接的表名
     * @param string        $tbl_alias      表的别名，不需要可设置为null
     * @param array|string  $on             表与其他表连接的条件，on语句 <br />
     *                                      [tb1.id, tb2.id] 或[ [tb1.id, tb2.id], [tb1.age, tb2.age] ]
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function join($table, $tbl_alias, $on)
    {
        $this->query['JOIN'][] = new Query\Join($table, $on, ($tbl_alias ?: null));

        return $this;
    }

    /**
     * SQL语句中的 left join 语句；与 on 语句搭配使用；
     *
     * @param string        $table          要连接的表名
     * @param string        $tbl_alias      表的别名，不需要可设置为null
     * @param array|string  $on             表与其他表连接的条件，on语句 <br />
     *                                      [tb1.id, tb2.id] 或[ [tb1.id, tb2.id], [tb1.age, tb2.age] ]
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function leftJoin($table, $tbl_alias, $on)
    {
        $this->query['LEFT_JOIN'][] = new Query\Join($table, $on, ($tbl_alias ?: null), 'left');

        return $this;
    }

    /**
     * SQL语句中的 right join 语句；与 on 语句搭配使用；
     *
     * @param string        $table          要连接的表名
     * @param string        $tbl_alias      表的别名，不需要可设置为null
     * @param array|string  $on             表与其他表连接的条件，on语句 <br />
     *                                      [tb1.id, tb2.id] 或[ [tb1.id, tb2.id], [tb1.age, tb2.age] ]
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function rightJoin($table, $tbl_alias, $on)
    {
        $this->query['RIGHT_JOIN'][] = new Query\Join($table, $on, ($tbl_alias ?: null), 'right');

        return $this;
    }

    /**
     * SQL语句中的 where 语句；
     *
     * @param array|\Nopis\Lib\Database\Params|null $args 可变参数，可以传入数组，字符串<br>
     *                      或者调用函数 _and()|_or()|_in()|_between()的返回值<br>
     *                      例如 ：where("f1", "=", "v1") 或 where(["f1", "=", "v1"])<br>
     *                      或 where(_and(["f1", "=", "v1"], ["f2", ">", "v2"])) 等
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function where(...$args)
    {
        $count = count($args);
        if ($count == 1) {
            $args = $args[0];
        } else if ($count > 3) {
            $args = array_slice($args, 0, 3);
        }
        $this->query['WHERE'] = new Query\Where($args, $this);

        return $this;
    }

    /**
     * SQL语句中的 GROUP BY 分组语句
     *
     * @param array $args  可变参数；分组依据的字段，值为字符串或数组形式；<br>
     *                      如：groupBy("price", "class", ...) 或 groupBy(["price", "class", ...])
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function groupBy(...$args)
    {
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }
        $this->query['GROUP_BY'] = new Query\GroupBy($args);

        return $this;
    }

    /**
     * SQL语句中的 HAVING 语句；需要与分组语句 groupby 配合使用；
     *
     * @param array|\Nopis\Lib\Database\Params $args   可变参数，值为字符串 <br>
     *                      或 调用函数 _and()|_or()|_in()|_between()的返回值<br>
     *                      例如：having($f, '>', $v) 或 having(_and([$f1, '>', $v1], [$f2, 'IS NOT', 'NULL']))
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function having(...$args)
    {
        $count = count($args);
        if ($count == 1) {
            $args = $args[0];
        } else if ($count > 3) {
            $args = array_slice($args, 0, 3);
        }
        $this->query['HAVING'] = new Query\Having($args, $this);

        return $this;
    }

    /**
     * SQL语句中的 ORDER BY 排序语句；
     *
     * @param array|string $orders  如：['age', 'height'] 或 ['age', 'DESC|ASC'] <br />
     *                      或 [['age', 'DESC|ASC'], ['regtime', 'DESC|ASC'], 'workyear', ...] <br />
     *                      或 不定参数 'age', 'height', 'DESC', 'workyear', ...
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function orderBy(...$orders)
    {
        $count = count($orders);
        if ($count == 1 && is_array($orders[0])) {
            $orders = $orders[0];
        } else {
            $tmp = [];
            for ($i = 0; $i < $count; ) {
                if (!isset($orders[$i+1]) || is_array($orders[$i])) {
                    $tmp[] = $orders[$i];
                    $i++;
                } else {
                    $next = strtoupper($orders[$i+1]);
                    if ($next == 'DESC' || $next == 'ASC') {
                        $tmp[] = [$orders[$i], $orders[$i+1]];
                        $i = $i + 2;
                    } else {
                        $tmp[] = $orders[$i];
                        $i++;
                    }
                }
            }
            $orders = $tmp;
        }
        $this->query['ORDER_BY'] = new Query\OrderBy($orders);

        return $this;
    }

    /**
     * SQL语句中的 LIMIT 语句；支持可变参数 limit(row_count) 或 limit(offset, row_count) <br/><br/>
     * &nbsp;&nbsp;&nbsp;&nbsp;   例如：limit(20) 或 limit(0, 20)。 <br/><br/>
     * &nbsp;&nbsp;&nbsp;&nbsp;   如果没有给出参数，则默认为 limit(1) 即 limit 1
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function limit()
    {
        if (func_num_args() > 1) {
            $offset = max(0, (int) func_get_arg(0));
            $rowCount = max(1, (int) func_get_arg(1));
        }
        else {
            $offset = 0;
            $rowCount = func_num_args() == 1 ? max(1, (int) func_get_arg(0)) : 1;
        }
        $this->query['LIMIT'] = new Query\Limit($offset, $rowCount);

        return $this;
    }

    /**
     * SQL语句中的 UNION 语句
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     */
    /*public function union()
    {
        $this->query['UNION'] = new Query\Union();

        return $this;
    }*/

    /**
     * SQL语句中的ON DUPLICATE KEY UPDATE语句；insert语句用到，参数可以是一维或二维数组
     *
     * @param array $set     一维或二维数组；例： <br />
     * <ul>
     *      <li>
     *          ->onDuplicateKeyUpdate(['field_1', 'field_2', ... ])    <br />
     *          则SQL语句为 ON DUPLICATE KEY UPDATE field_1=VALUES(field_1), field_2=VALUES(field_2), ...
     *      </li>
     *      <li>
     *          ->onDuplicateKeyUpdate([[col_1, col_1+1], [col_2, col_2+1], ... ]')    <br />
     *          则SQL语句为 ON DUPLICATE KEY UPDATE col_1=col_1+1, col_2=col_2+1, ...
     *      </li>
     *      <li>或二者混合使用</li>
     * </ul>
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    public function onDuplicateKeyUpdate(...$sets)
    {
        if (count($sets) == 1 && is_array($sets[0])) {
            $sets = $sets[0];
        }
        $this->query['ON_DUPLICATE_KEY_UPDATE'] = new Query\OnDuplicateKeyUpdate($sets);

        return $this;
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
        $this->query['SELECT'] = new Query\Select('COUNT(*)', false);
    }

    /**
     * 返回SQL查询语句字符串
     *
     * @return string
     */
    protected function getQueryStatement()
    {
        if (!empty($this->query['JOIN'])) {
            $this->query['JOIN'] = implode('', $this->query['JOIN']);
        }
        if (!empty($this->query['LEFT_JOIN'])) {
            $this->query['LEFT_JOIN'] = implode('', $this->query['LEFT_JOIN']);
        }
        if (!empty($this->query['RIGHT_JOIN'])) {
            $this->query['RIGHT_JOIN'] = implode('', $this->query['RIGHT_JOIN']);
        }

        $query = implode('', $this->query);

        $this->query = [];
        return $query;
    }

    /**
     * 返回预处理SQl语句的命名参数
     *
     * @return \Nopis\Lib\Database\Params[]
     */
    protected function getQueryParams()
    {
        $merge = [];
        foreach ($this->params as $params)
        {
            $merge = array_merge($merge, $params->getParams());
        }

        $this->params = [];
        return $merge;
    }

    /**
     * 设置预处理语句的命名参数
     *
     * @param \Nopis\Lib\Database\Params $params
     */
    public function setQueryParams(Params $params)
    {
        $this->params[] = $params;
    }

    /**
     * Check if a query is select sql
     *
     * @param string $query
     * @return boolean
     */
    protected function isRead($query)
    {
        return (boolean) preg_match('/^\s*select\s+/i', $query);
    }

    /**
     * SQL语句 SHOW FULL COLUMNS FROM tbl_name ；查询一个数据库表的所有字段的信息
     *
     * @param string $table
     *
     * @return \Nopis\Lib\Database\AbstractQuery
     *
     * @api
     */
    /*public function getTableFields($table)
    {
        $this->query['SHOW_TABLE'] = 'SHOW FULL COLUMNS FROM' . (new Query\Tables((array) $table, $this->tblPrefix));

        return $this;
    }*/
}
