<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Database {

    /**
     * @author Wangbin
     */
    interface DBInterface
    {

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
        public function select(...$args);

        /**
         * SQL语句中的insert语句；
         *
         * @param string $table 数据表的表名
         * @return \Nopis\Lib\Database\DBInterface
         *
         * @api
         */
        public function insert($table);

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
        public function update($tables);

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
         * @return \Nopis\Lib\Database\DBInterface
         *
         * @api
         */
        public function delete($tables = null, $isAlias = false);

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
         * @return \Nopis\Lib\Database\DBInterface
         *
         * @api
         */
        public function from($tables, $alias = null);

        /**
         * SQL语句中的set语句；insert | update 语句用到
         *
         * @param array $values  一维关联数组，数组的键必须是数据表的字段名，<br/>
         *                       例：[field_name1 => field_value1, field_name2 => field_value2]
         *
         * @return \Nopis\Lib\Database\DBInterface
         *
         * @api
         */
        public function set(array $values);

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
         * @return \Nopis\Lib\Database\DBInterface
         *
         * @api
         */
        public function values(array $values);

        /**
         * SQL语句中的 join 语句；与 on 语句搭配使用；
         *
         * @param string        $table          要连接的表名
         * @param string        $tbl_alias      表的别名，不需要可设置为null
         * @param array|string  $on             表与其他表连接的条件，on语句 <br />
         *                                      [tb1.id, tb2.id] 或[ [tb1.id, tb2.id], [tb1.age, tb2.age] ]
         *
         * @return \Nopis\Lib\Database\DBInterface
         *
         * @api
         */
        public function join($table, $tbl_alias, $on);

        /**
         * SQL语句中的 left join 语句；与 on 语句搭配使用；
         *
         * @param string        $table          要连接的表名
         * @param string        $tbl_alias      表的别名，不需要可设置为null
         * @param array|string  $on             表与其他表连接的条件，on语句 <br />
         *                                      [tb1.id, tb2.id] 或[ [tb1.id, tb2.id], [tb1.age, tb2.age] ]
         *
         * @return \Nopis\Lib\Database\DBInterface
         *
         * @api
         */
        public function leftJoin($table, $tbl_alias, $on);

        /**
         * SQL语句中的 right join 语句；与 on 语句搭配使用；
         *
         * @param string        $table          要连接的表名
         * @param string        $tbl_alias      表的别名，不需要可设置为null
         * @param array|string  $on             表与其他表连接的条件，on语句 <br />
         *                                      [tb1.id, tb2.id] 或[ [tb1.id, tb2.id], [tb1.age, tb2.age] ]
         *
         * @return \Nopis\Lib\Database\DBInterface
         *
         * @api
         */
        public function rightJoin($table, $tbl_alias, $on);

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
        public function where(...$args);

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
        public function groupBy(...$args);

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
        public function having(...$args);

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
        public function orderBy(...$orders);

       /**
        * SQL语句中的 LIMIT 语句；支持可变参数 limit(row_count) 或 limit(offset, row_count) <br/><br/>
        * &nbsp;&nbsp;&nbsp;&nbsp;   例如：limit(20) 或 limit(0, 20)。 <br/><br/>
        * &nbsp;&nbsp;&nbsp;&nbsp;   如果没有给出参数，则默认为 limit(1) 即 limit 1
        *
        * @return \Nopis\Lib\Database\DBInterface
        *
        * @api
        */
        public function limit();

       /**
        * SQL语句中的 UNION 语句
        *
        * @return \Nopis\Lib\Database\DBInterface
        */
       //public function union();

        /**
         * SQL语句中的ON DUPLICATE KEY UPDATE语句；insert语句用到，参数可以是一维或二维数组
         *
         * @param array $sets    可变参数，值为字符串，或一维、二维数组；例： <br />
         * <ul>
         *      <li>
         *          onDuplicateKeyUpdate("f1", "f2", ...)    或<br />
         *          onDuplicateKeyUpdate(["f1", "f2", ... ])    <br />
         *          则SQL语句为 ON DUPLICATE KEY UPDATE f1=VALUES(f1), f2=VALUES(f2), ...
         *      </li>
         *      <li>
         *          onDuplicateKeyUpdate([[f1, f1 + 1], [f2, f2 + 1], ... ])    <br />
         *          则SQL语句为 ON DUPLICATE KEY UPDATE f1=f1+1, f2=f2+1, ...
         *      </li>
         *      <li>或二者混合使用</li>
         * </ul>
         *
         * @return \Nopis\Lib\Database\DBInterface
         *
         * @api
         */
        public function onDuplicateKeyUpdate(...$sets);

        /**
         * 执行SQL查询语句 SELECT COUNT(*) 查询结果数量，此查询不需要先执行query()，默认内部已执行
         *
         * @return int
         *
         * @api
         */
        public function count();

        /**
         * 返回SQL查询语句字符串
         *
         * @return string
         */
        //public function getQueryStatement();

        /**
         * 返回预处理SQl语句的命名参数
         *
         * @return \Nopis\Lib\Database\Params[]
         */
        //public function getQueryParams();

        /**
         * 设置预处理语句的命名参数
         *
         * @param \Nopis\Lib\Database\Params $params
         */
        public function setQueryParams(Params $params);

        /**
         * SQL语句 SHOW FULL COLUMNS FROM tbl_name ；查询一个数据库表的所有字段的信息
         *
         * @param string $table
         *
         * @return \Nopis\Lib\Database\DBInterface
         *
         * @api
         */
        // public function getTableFields($table);


        //** 下面是 PDO 和 PDOStatement 的封装API */

        /**
         * 预执行一条SQL语句，
         * 成功时返回 TRUE， 或者在失败时返回 FALSE
         *
         * @param string $query
         * @return boolean
         *
         * @api
         */
        public function prepare($query);

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
        public function bindParam($param, $var);

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
        public function bindValue($param, $value);

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
        public function execute(array $params = null);

        /**
         * 执行一条 SQL 语句 ( insert | update | delete 等 )，并返回受影响的行数，<br />
         * 不建议直接传SQL语句参数直接执行，建议先调用本对象提供的 insert | update | delete 等API <br />
         * 再调用 exec() 查询
         *
         * @param string $query
         * @return int   insert | update | delete 等受影响的行数
         *
         * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
         * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
         *
         * @api
         */
        public function exec($query = null);

        /**
         * 执行一条SQL查询语句 ( 如：select 等 )，如果参数$sql不为空，则调用PDO::query()直接查询；<br />
         * 但对于数据库的CURD基于安全不建议直接查询，建议使用本对象提供的<br />
         *  select | insert | update | delete 等API执行SQL语句，然后在调用 query() 查询
         *
         * @param string $query
         * @return \Nopis\Lib\Database\DBInterface
         *
         * @throws \Nopis\Lib\Database\Exceptions\PDOErrorException
         * @throws \Nopis\Lib\Database\Exceptions\PDOSErrorException
         *
         * @api
         */
        public function query($query = null);

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
        public function fetch($fetchTable = null, array $ctorargs = []);

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
        public function fetchAll($fetchTable = null, array $ctorargs = []);

        /**
         * 从结果集中的下一行返回单独的一列，如果没有了，则返回 FALSE
         *
         * @param int $index  列的索引
         * @return mixed    没有结果时返回false
         *
         * @api
         */
        public function fetchColumn($index = 0);

        /**
         * 为语句设置默认的获取结果集模式，
         * 成功时返回 TRUE， 或者在失败时返回 FALSE
         *
         * @param string $mode  num | assoc | both | obj
         */
        public function setFetchMode($mode);

        /**
         * 返回最后一条insert语句插入行的ID
         *
         * @return int
         *
         * @api
         */
        public function lastInsertId();

        /**
         * 开始一个事务
         *
         * @api
         */
        public function beginTransaction();

        /**
         * 检查是否在一个事务内
         * 如果当前事务处于激活，则返回 TRUE ，否则返回 FALSE
         *
         * @return bool
         *
         * @api
         */
        public function inTransaction();

        /**
         * 提交一个事务
         *
         * @api
         */
        public function commit();

        /**
         * 滚回一个事务
         *
         * @api
         */
        public function rollBack();

        /**
         * 转义字符串中的特殊字符，防注入
         *
         * @param string $val
         * @return string
         *
         * @api
         */
        public function quote($val);


    }
}


namespace {
    /**
     * 判断一个一维数组的所有键是否全非数字，即是否为关联数组；<br />
     * 是关联数组返回 true ， 否则返回 false
     *
     * @param array $arr
     * @return boolean
     */
    function is_assoc(array $arr)
    {
        $is_assoc = true;
        foreach (array_keys($arr) as $key) {
            is_int($key) && $is_assoc = false;
        }
        return $is_assoc;
    }

    /**
     * 生成连接标准SQL的 WHERE、 HAVING 子句的 AND 部分，参数个数任意   <br />
     * 参数类型为二维数组 （每个子数组包含三个元素，如： [$field, '=', $value] 等  ） <br />
     * 或 类 \Nopis\Lib\Database\Params 的一个实例
     *
     * @param mixed $args 可变参数，参数类型为包含三个元素的数组 或者 类 \Nopis\Lib\Database\Params 的一个实例
     * @return \Nopis\Lib\Database\Params
     * @throws \InvalidArgumentException
     *
     * @api
     */
    function _and_(...$args)
    {
        if (2 > count($args)) {
            throw new \InvalidArgumentException(
                sprintf('Function "_and_()" expect 2 arguments at least, %d given', func_num_args())
            );
        }

        $and = $params = [];
        foreach ($args as $k => $arg) {
            check_query_arg($arg, '_and_');
            if (is_array($arg)) {   // [$field, '>', $value] 或者 [$field, 'IS NOT', 'NULL']
                $arg[1] = strtoupper(trim($arg[1]));

                if ($arg[1] === 'IS' || $arg[1] === 'IS NOT') {
                    $and[] = $arg[0] . ' ' . $arg[1] . ' ' . strtoupper(trim($arg[2]));
                } else {
                    $placeholder = ':' . preg_replace('/[^a-zA-Z0-9_]/', '_', $arg[0] . '_' . $k);
                    $params[$placeholder] = $arg[2];
                    $and[] = $arg[0] . ' ' . $arg[1] . ' ' . $placeholder;
                }
            } elseif ($arg instanceof \Nopis\Lib\Database\Params) {
                $params = array_merge($params, $arg->getParams());
                $and[] = '(' . $arg->getQuery() . ')';
            }
        }

        $query = implode(' AND ', $and);

        $newParams = new \Nopis\Lib\Database\Params();
        $newParams->setParams($params);
        $newParams->setQuery($query);

        return $newParams;
    }

    /**
     * 生成连接标准SQL的 WHERE、 HAVING 子句的 OR 部分，参数个数任意   <br />
     * 参数类型为数组 （包含三个元素，如： [$field, '=', $value] 等  ）   <br />
     * 或 类 \Nopis\Lib\Database\Params 的一个实例
     *
     * @param mixed $args 可变参数，参数类型为包含三个元素的数组 或者 类 \Nopis\Lib\Database\Params 的一个实例
     * @return \Nopis\Lib\Database\Params
     * @throws \InvalidArgumentException
     *
     * @api
     */
    function _or_(...$args)
    {
        if (2 > count($args)) {
            throw new \InvalidArgumentException(
                sprintf('Function "_or_()" expect 2 arguments at least, %d given', func_num_args())
            );
        }

        $or = $params = [];
        foreach ($args as $k => $arg) {
            check_query_arg($arg, '_or_');
            if (is_array($arg)) {   // [$field, '>', $value] or [$field, 'IS NOT', 'NULL']
                $arg[1] = strtoupper(trim($arg[1]));

                if ($arg[1] === 'IS' || $arg[1] === 'IS NOT') {
                    $or[] = $arg[0] . ' ' . $arg[1] . ' ' . strtoupper(trim($arg[2]));
                } else {
                    $placeholder = ':' . preg_replace('/[^a-zA-Z0-9_]/', '_', $arg[0] . '_' . $k);
                    $params[$placeholder] = $arg[2];
                    $or[] = $arg[0] . ' ' . $arg[1] . ' ' . $placeholder;
                }
            } elseif ($arg instanceof \Nopis\Lib\Database\Params) {
                $params = array_merge($params, $arg->getParams());
                $or[] = '(' . $arg->getQuery() . ')';
            }
        }

        $query = implode(' OR ', $or);

        $newParams = new \Nopis\Lib\Database\Params();
        $newParams->setParams($params);
        $newParams->setQuery($query);

        return $newParams;
    }

    /**
     * 生成连接标准SQL的 WHERE 子句的 BETWEEN ... AND ... 部分
     *
     * @param string $field                 表字段名
     * @param int|string $min               最小值，只接受数字和英文单字符
     * @param int|string $max               最大值，只接受数字和英文单字符
     * @return \Nopis\Lib\Database\Params
     * @throws \InvalidArgumentException
     *
     * @api
     */
    function _between_($field, $min, $max)
    {
        if (!is_numeric($min) && !in_array(strtolower($min), range('a', 'z'))
                || !is_numeric($max) && !in_array(strtolower($max), range('a', 'z'))) {
            throw new \InvalidArgumentException('Arguments must be a number or a english single character, in function _between_()');
        }

        $query = $field . ' BETWEEN ' . (is_numeric($min) ? $min : "'" . $min . "'") . ' AND ' . (is_numeric($max) ? $max : "'" . $max . "'");

        $newParams = new \Nopis\Lib\Database\Params();
        $newParams->setQuery($query);

        return $newParams;
    }

    /**
     * 生成连接标准SQL的 WHERE 子句的 field IN (val, ...) 部分
     *
     * @param string $field                 表字段名
     * @param array $list                   所有值的集合，一个一维数组
     * @return \Nopis\Lib\Database\Params
     * @throws \InvalidArgumentException
     *
     * @api
     */
    function _in_($field, array $list)
    {
        $params = $in = [];
        foreach ($list as $key => $el) {
            if (!is_numeric($el) && !is_string($el)) {
                throw new \InvalidArgumentException(
                    sprintf('The elements of arguments "$list" must be number or string, but "%s" given in function _in_()', gettype($el))
                );
            }

            $placeholder = ':' . preg_replace('/[^a-zA-Z0-9_]/', '_', $field . '_in' . $key);
            $params[$placeholder] = $el;
            $in[] = $placeholder;
        }

        $query = $field . ' IN (' . implode(', ', $in) . ')';

        $newParams = new \Nopis\Lib\Database\Params();
        $newParams->setParams($params);
        $newParams->setQuery($query);

        return $newParams;
    }

    /**
     * 生成连接标准SQL的 WHERE 子句的 NOT BETWEEN ... AND ... 部分
     *
     * @param string $field                 表字段名
     * @param int|string $min               最小值，只接受数字和英文单字符
     * @param int|string $max               最大值，只接受数字和英文单字符
     * @return \Nopis\Lib\Database\Params
     * @throws \InvalidArgumentException
     *
     * @api
     */
    function _not_between_($field, $min, $max)
    {
        if (!is_numeric($min) && !in_array(strtolower($min), range('a', 'z'))
                || !is_numeric($max) && !in_array(strtolower($max), range('a', 'z'))) {
            throw new \InvalidArgumentException('Arguments must be a number or a english single character, in function _not_between_()');
        }

        $query = $field . ' NOT BETWEEN ' . (is_numeric($min) ? $min : "'" . $min . "'") . ' AND ' . (is_numeric($max) ? $max : "'" . $max . "'");

        $newParams = new \Nopis\Lib\Database\Params();
        $newParams->setQuery($query);

        return $newParams;
    }

    /**
     * 生成连接标准SQL的 WHERE 子句的 field NOT IN (val, ...) 部分
     *
     * @param string $field                 表字段名
     * @param array $list                   所有值的集合，一个一维数组
     * @return \Nopis\Lib\Database\Params
     * @throws \InvalidArgumentException
     *
     * @api
     */
    function _not_in_($field, array $list)
    {
        $params = $in = [];
        foreach ($list as $key => $el) {
            if (!is_numeric($el) && !is_string($el)) {
                throw new \InvalidArgumentException(
                    sprintf('The elements of arguments "$list" must be number or string, but "%s" given in function _not_in_()', gettype($el))
                );
            }

            $placeholder = ':' . preg_replace('/[^a-zA-Z0-9_]/', '_', $field . '_in' . $key);
            $params[$placeholder] = $el;
            $in[] = $placeholder;
        }

        $query = $field . ' NOT IN (' . implode(', ', $in) . ')';

        $newParams = new \Nopis\Lib\Database\Params();
        $newParams->setParams($params);
        $newParams->setQuery($query);

        return $newParams;
    }

    /**
     * 检查函数 _and() | _or() | DBInterface::where() | DBInterface::having() 参数的合法性
     *
     * @param array|\Nopis\Lib\Database\Params $arg
     * @param string $self
     * @throws \InvalidArgumentException
     */
    function check_query_arg($arg, $self)
    {
        if (null == $arg)
            return;

        if (is_array($arg)) {   // [$field, '>', $value] or [$field, 'IS NOT', 'NULL']
            // 检查数组的元素个数是否合法
            if (3 !== count($arg)) {
                throw new \InvalidArgumentException(
                    sprintf('Function array argument must has three elements, %d given', count($arg))
                );
            }
            // 检查数组第一个参数是否是一个合法的字段
            if (!is_string($arg[0])) {
                throw new \InvalidArgumentException(
                    sprintf('Function "%s" array argument first element is invalid field', $self)
                );
            }
            if (!is_string($arg[2]) && !is_integer($arg[2]) && !is_float($arg[2])) {
                throw new \InvalidArgumentException(
                    sprintf('Function "%s" array argument third element is invalid value', $self)
                );
            }
            // 检查数组的操作符是否合法
            $arg[1] = strtoupper(trim($arg[1]));
            if (!in_array($arg[1], ['=', '>', '<', '>=', '<=', '<>', '!=', 'IS', 'IS NOT', 'LIKE'])) {
                throw new \InvalidArgumentException(
                    sprintf('Function "%s" array argument operational character is invalid', $self)
                );
            }
            // 如果操作符是 IS 或 IS NOT 则值必须是 TRUE FALSE NULL UNKNOWN 中的一个
            if ($arg[1] === 'IS' || $arg[1] === 'IS NOT') {
                $arg[2] = strtoupper(trim($arg[2]));
                if (!in_array($arg[2], ['NULL', 'TRUE', 'FALSE', 'UNKNOWN'])) {
                    throw new \InvalidArgumentException(
                        sprintf('SQL operational character "%s" expect value is boolean or NULL or UNKNOWN, %s given ', $arg[1], $arg[2])
                    );
                }
            }
        } elseif (!$arg instanceof \Nopis\Lib\Database\Params) {
            throw new \InvalidArgumentException(
                sprintf('Function "%s" arguments must be an array or an object of class "Nopis\Lib\Database\Params", Type "%s" given', $self, gettype($arg))
            );
        }
    }
}
