<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nopis\Lib\Database\Query;

/**
 * Description of FuncsList
 *
 * @author wb
 */
class FuncCollection
{
    /**
     * IF(expr1,expr2,expr3)
     * 用法说明：如果 expr1 是 TRUE (expr1 <> 0 and expr1 <> NULL)  ，则 IF()  的返回值为 expr2 ;
     * 否则返回值则为 expr3 。 IF()  的返回值为数字值或字符串值，具体情况视其所在语境而定
     */
    public $mysql_IF = 'IF';

    /**
     * IFNULL(expr1,expr2)
     * 用法说明：假如 expr1 不为 NULL ，则 IFNULL()  的返回值为 expr1 ;
     * 否则其返回值为 expr2 。 IFNULL()  的返回值是数字或是字符串，具体情况取决于其所使用的语境
     */
    public $mysql_IFNULL = 'IFNULL';

    // ---------------------------------------------------------
    // 字符串比较函数
    // ---------------------------------------------------------
    /**
     * ascii(str)
     * 用法说明：返回值为字符串 str 的最左字符的数值。假如 str 为空字符串，则返回值为 0 。
     * 假如 str 为 NULL ，则返回值为 NULL 。 ASCII()  用于带有从 0 到 255 的数值的字符
     */
    public $mysql_ASCII = 'ASCII';

    /**
     * BIN(N)
     * 用法说明：函数用法说明：返回值为 N 的二进制值的字符串表示，其中 N 为一个 longlong (BIGINT) 数字。
     * 这等同于 CONV(N ,10,2) 。假如 N 为 NULL ，则返回值为 NULL
     */
    public $mysql_BIN = 'BIN';

    /**
     * CHAR(N ,... [USING charset ])
     * 用法说明： CHAR()  将每个参数 N 理解为一个整数，其返回值为一个包含这些整数的代码值所给出的字符的字符串。 NULL 值被省略
     */
    public $mysql_CHAR = 'CHAR';

    /**
     * CHAR_LENGTH(str)
     * 使用说明：返回值为字符串 str 的长度，长度的单位为字符。一个多字节字符算作一个单字符。
     * 对于一个 包含五个二字节字符集 , LENGTH()  返回值为 10, 而 CHAR_LENGTH()  的返回值为 5
     */
    public $mysql_CHAR_LENGTH = 'CHAR_LENGTH';

    /**
     * CHARACTER_LENGTH(str)
     * 使用说明： CHARACTER_LENGTH()  ? CHAR_LENGTH()  的同义词
     */
    public $mysql_CHARACTER_LENGTH = 'CHARACTER_LENGTH';

    /**
     * COMPRESS(string_to_compress)
     * 使用说明： COMPRESS( 压缩一个字符串。这个函数要求 MySQL 已经用一个诸如 zlib 的压缩库压缩过。
     * 否则，返回值始终是 NULL 。 UNCOMPRESS()  可将压缩过的字符串进行解压缩 )
     */
    public $mysql_COMPRESS = 'COMPRESS';

    /**
     * CONCAT(str1, str2, ...)
     * 使用说明：返回结果为连接参数产生的字符串。如有任何一个参数为 NULL ，则返回值为 NULL 。
     * 或许有一个或多个参数。 如果所有参数均为非二进制字符串，则结果为非二进制字符串。
     * 如果自变量中含有任一二进制字符串，则结果为一个二进制字符串。一个数字参数被转化为与之相等的二进制字符串格式；
     * 若要避免这种情况，可使用显式类型 cast, 例如： SELECT CONCAT(CAST(int_col AS CHAR)  , char_col)
     */
    public $mysql_CONCAT = 'CONCAT';

    /**
     * CONCAT_WS(separator, str1, str2, ...)
     * 使用说明： CONCAT_WS()  代表 CONCAT With Separator ，是 CONCAT()  的特殊形式。
     * 第一个参数是其它参数的分隔符。分隔符的位置放在要连接的两个字符串之间。
     * 分隔符可以是一个字符串，也可以是其它参数。如果分隔符为 NULL ，则结果为 NULL 。
     * 函数会忽略任何分隔符参数后的 NULL 值
     */
    public $mysql_CONCAT_WS = 'CONCAT_WS';

    /**
     * CONV(N from_base, to_base)
     * 使用说明：不同数基间转换数字。返回值为数字的 N 字符串表示，由 from_base 基转化为 to_base 基。
     * 如有任意一个参数为 NULL ，则返回值为 NULL 。自变量 N 被理解为一个整数，但是可以被指定为一个整数或字符串。
     * 最小基数为 2 ，而最大基数则为 36 。 If to_base 是一个负数，则 N 被看作一个带符号数。
     * 否则， N 被看作无符号数。 CONV()  的运行精确度为 64 比特
     */
    public $mysql_CONV = 'CONV';

    /**
     * ELT(N ,str1 ,str2 ,str3 ,...)
     * 使用说明：若 N = 1 ，则返回值为   str1 ，若 N = 2 ，则返回值为 str2 ，以此类推。
     * 若 N 小于 1 或大于参数的数目，则返回值为 NULL 。 ELT()  ?  FIELD()  的补数
     */
    public $mysql_ELT = 'ELT';

    /**
     * EXPORT_SET(bits, on, off[,separator [,number_of_bits ]])
     * 使用说明： 返回值为一个字符串，其中对于 bits 值中的每个位组，可以得到一个 on 字符串，
     * 而对于每个清零比特位，可以得到一个 off 字符串。 bits 中的比特值按照从右到左的顺序
     * 接受检验 ( 由低位比特到高位比特 )  。字符串被分隔字符串分开 ( 默认为逗号 ‘,’)  ，
     * 按照从左到右的顺序被添加到结果中。 number_of_bits 会给出被检验的二进制位数 ( 默认为 64)
     */
    public $mysql_EXPORT_SET = 'EXPORT_SET';

    /**
     * FIELD(str, str1, str2, str3, …...)
     * 使用说明：返回值为 str1 , str2 , str3 ,…… 列表中的 str 指数。在找不到 str 的情况下，返回值为 0 。
     * 如果所有对于 FIELD()  的参数均为字符串，则所有参数均按照字符串进行比较。
     * 如果所有的参数均为数字，则按照数字进行比较。否则，参数按照双倍进行比较。
     * 如果 str 为 NULL ，则返回值为 0 ，原因是 NULL 不能同任何值进行同等比较。 FIELD()  ?ELT()  的补数
     */
    public $mysql_FIELD = 'FIELD';

    public $mysql_FIND_IN_SET = 'FIND_IN_SET';
    public $mysql_FORMAT = 'FORMAT';
    public $mysql_HEX = 'HEX';
    public $mysql_INSTR = 'INSTR';
    public $mysql_LCASE = 'LCASE';
    public $mysql_LEFT = 'LEFT';
    public $mysql_LENGTH = 'LENGTH';
    public $mysql_LOAD_FILE = 'LOAD_FILE';
    public $mysql_LOCATE = 'LOCATE';
    public $mysql_LOWER = 'LOWER';
    public $mysql_LPAD = 'LPAD';
    public $mysql_LTRIM = 'LTRIM';
    public $mysql_MAKE_SET = 'MAKE_SET';
    public $mysql_MID = 'MID';
    public $mysql_OCT = 'OCT';
    public $mysql_OCTET_LENGTH = 'OCTET_LENGTH';
    public $mysql_ORD = 'ORD';
    public $mysql_POSITION = 'POSITION';
    public $mysql_QUOTE = 'QUOTE';
    public $mysql_REPEAT = 'REPEAT';
    public $mysql_REPLACE = 'REPLACE';
    public $mysql_REVERSE = 'REVERSE';
    public $mysql_RIGHT = 'RIGHT';
    public $mysql_RPAD = 'RPAD';
    public $mysql_RTRIM = 'RTRIM';

    /**
     * SOUNDEX(str)
     * 从str返回一个soundex字符串。 两个具有几乎同样探测的字符串应该具有同样的 soundex 字符串。
     * 一个标准的soundex 字符串的长度为4个字符，然而SOUNDEX() 函数会返回一个人以长度的字符串。
     * 可使用结果中的SUBSTRING() 来得到一个标准 soundex 字符串。在str中，会忽略所有未按照字母顺序排列的字符。
     * 所有不在A-Z范围之内的国际字母符号被视为元音字母。
     * mysql> SELECT SOUNDEX('Hello');
     *     -> 'H400'
     */
    public $mysql_SOUNDEX = 'SOUNDEX';
    public $mysql_SPACE = 'SPACE';
    public $mysql_SUBSTRING = 'SUBSTRING';
    public $mysql_SUBSTRING_INDEX = 'SUBSTRING_INDEX';
    public $mysql_TRIM = 'TRIM';
    public $mysql_UCASE = 'UCASE';
    public $mysql_UNCOMPRESS = 'UNCOMPRESS';
    public $mysql_UNCOMPRESSED_LENGTH = 'UNCOMPRESSED_LENGTH';
    public $mysql_UNHEX = 'UNHEX';
    public $mysql_UPPER = 'UPPER';

    // ---------------------------------------------------------
    // 数学函数
    // ---------------------------------------------------------
    /**
     *
     */
    public $mysql_ABS = 'ABS';
    public $mysql_ACOS = 'ACOS';
    public $mysql_ASIN = 'ASIN';

    /**
     * ATAN(X)
     * 使用说明：返回 X 的反正切，即，正切为 X 的值。
     */
    public $mysql_ATAN = 'ATAN';

    /**
     * ATAN(Y, X), ATAN2(Y, X)
     * 使用说明：返回两个变量 X 及 Y 的反正切。 它类似于 Y 或 X 的反正切计算 ,
     * 除非两个参数的符号均用于确定结果所在象限
     */
    public $mysql_ATAN2 = 'ATAN2';
    public $mysql_CEILING = 'CEILING';
    public $mysql_COS = 'COS';
    public $mysql_COT = 'COT';
    public $mysql_CRC32 = 'CRC32';
    public $mysql_DEGREES = 'DEGREES';
    public $mysql_EXP = 'EXP';
    public $mysql_FLOOR = 'FLOOR';
    public $mysql_LN = 'LN';
    public $mysql_LOG = 'LOG';
    public $mysql_LOG2 = 'LOG2';
    public $mysql_LOG10 = 'LOG10';
    public $mysql_MOD = 'MOD';
    public $mysql_PI = 'PI';
    public $mysql_POW = 'POW';
    public $mysql_POWER = 'POWER';
    public $mysql_RADIANS = 'RADIANS';
    public $mysql_RAND = 'RAND';
    public $mysql_ROUND = 'ROUND';
    public $mysql_SIGN = 'SIGN';
    public $mysql_SIN = 'SIN';
    public $mysql_SQRT = 'SQRT';
    public $mysql_TAN = 'TAN';
    public $mysql_TRUNCATE = 'TRUNCATE';

    // ---------------------------------------------------------
    // 日期和时间函数
    // ---------------------------------------------------------
    public $mysql_ADDDATE = 'ADDDATE';
    public $mysql_ADDTIME = 'ADDTIME';
    public $mysql_CONVERT_TZ = 'CONVERT_TZ';
    public $mysql_CURDATE = 'CURDATE';
    public $mysql_CURRENT_DATE = 'CURRENT_DATE';
    public $mysql_CURTIME = 'CURTIME';
    public $mysql_CURRENT_TIME = 'CURRENT_TIME';
    public $mysql_CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';
    public $mysql_DATE = 'DATE';
    public $mysql_DATEDIFF = 'DATEDIFF';
    public $mysql_DATE_ADD = 'DATE_ADD';
    public $mysql_DATE_FORMAT = 'DATE_FORMAT';
    public $mysql_DAY = 'DAY';
    public $mysql_DAYNAME = 'DAYNAME';
    public $mysql_DAYOFMONTH = 'DAYOFMONTH';
    public $mysql_DAYOFWEEK = 'DAYOFWEEK';
    public $mysql_DAYOFYEAR = 'DAYOFYEAR';
    public $mysql_EXTRACT = 'EXTRACT';
    public $mysql_FROM_DAYS = 'FROM_DAYS';
    public $mysql_FROM_UNIXTIME = 'FROM_UNIXTIME';
    public $mysql_GET_FORMAT = 'GET_FORMAT';
    public $mysql_HOUR = 'HOUR';
    public $mysql_LAST_DAY = 'LAST_DAY';
    public $mysql_LOCALTIME = 'LOCALTIME';
    public $mysql_LOCALTIMESTAMP = 'LOCALTIMESTAMP';
    public $mysql_MAKEDATE = 'MAKEDATE';
    public $mysql_MAKETIME = 'MAKETIME';
    public $mysql_CROSECOND = 'CROSECOND';
    public $mysql_MINUTE = 'MINUTE';
    public $mysql_MONTH = 'MONTH';
    public $mysql_MONTHNAME = 'MONTHNAME';
    public $mysql_NOW = 'NOW';
    public $mysql_PERIOD_ADD = 'PERIOD_ADD';
    public $mysql_PERIOD_DIFF = 'PERIOD_DIFF';
    public $mysql_QUARTER = 'QUARTER';
    public $mysql_SECOND = 'SECOND';
    public $mysql_SEC_TO_TIME = 'SEC_TO_TIME';
    public $mysql_STR_TO_DATE = 'STR_TO_DATE';
    public $mysql_SUBDATE = 'SUBDATE';
    public $mysql_SUBTIME = 'SUBTIME';
    public $mysql_SYSDATE = 'SYSDATE';
    public $mysql_TIME = 'TIME';
    public $mysql_TIMEDIFF = 'TIMEDIFF';
    public $mysql_TIMESTAMPADD = 'TIMESTAMPADD';
    public $mysql_TIMESTAMPDIFF = 'TIMESTAMPDIFF';
    public $mysql_TIME_FORMAT = 'TIME_FORMAT';
    public $mysql_TIME_TO_SEC = 'TIME_TO_SEC';
    public $mysql_TO_DAYS = 'TO_DAYS';
    public $mysql_UNIX_TIMESTAMP = 'UNIX_TIMESTAMP';
    public $mysql_UTC_DATE = 'UTC_DATE';
    public $mysql_UTC_TIME = 'UTC_TIME';
    public $mysql_UTC_TIMESTAMP = 'UTC_TIMESTAMP';
    public $mysql_WEEK = 'WEEK';
    public $mysql_WEEKDAY = 'WEEKDAY';
    public $mysql_WEEKOFYEAR = 'WEEKOFYEAR';
    public $mysql_YEAR = 'YEAR';
    public $mysql_YEARWEEK = 'YEARWEEK';

    // ---------------------------------------------------------
    // 加密函数
    // ---------------------------------------------------------
    public $mysql_AES_ENCRYPT = 'AES_ENCRYPT';
    public $mysql_DECODE = 'DECODE';
    public $mysql_ENCODE = 'ENCODE';
    public $mysql_DES_DECRYPT = 'DES_DECRYPT';
    public $mysql_DES_ENCRYPT = 'DES_ENCRYPT';
    public $mysql_ENCRYPT = 'ENCRYPT';
    public $mysql_MD5 = 'MD5';
    public $mysql_PASSWORD = 'PASSWORD';

    // ---------------------------------------------------------
    // 加密函数
    // ---------------------------------------------------------
    public $mysql_AVG = 'AVG';
    public $mysql_BIT_AND = 'BIT_AND';
    public $mysql_BIT_OR = 'BIT_OR';
    public $mysql_BIT_XOR = 'BIT_XOR';
    public $mysql_COUNT = 'COUNT';
    public $mysql_GROUP_CONCAT = 'GROUP_CONCAT';
    public $mysql_MIN = 'MIN';
    public $mysql_MAX = 'MAX';
    public $mysql_STD = 'STD';
    public $mysql_STDDEV = 'STDDEV';
    public $mysql_STDDEV_POP = 'STDDEV_POP';
    public $mysql_STDDEV_SAMP = 'STDDEV_SAMP';
    public $mysql_SUM = 'SUM';
    public $mysql_VAR_POP = 'VAR_POP';
    public $mysql_VAR_SAMP = 'VAR_SAMP';
    public $mysql_VARIANCE = 'VARIANCE';

    // ---------------------------------------------------------
    // 信息函数
    // ---------------------------------------------------------
    /**
     * 函数使用说明：一条 SELECT 语句可能包括一个 LIMIT 子句，用来限制服务器返回客户端的行数。
     * 在有些情况下，需要不用再次运行该语句而得知在没有LIMIT 时到底该语句返回了多少行。
     * 为了知道这个行数, 包括在SELECT 语句中选择  SQL_CALC_FOUND_ROWS ，随后调用 FOUND_ROWS()
     *
     * mysql> SELECT SQL_CALC_FOUND_ROWS * FROM tbl_name
     *     -> WHERE id > 100 LIMIT 10;
     * mysql> SELECT FOUND_ROWS();
     * 第二个 SELECT返回一个数字，指示了在没有LIMIT子句的情况下，第一个SELECT返回了多少行 (若上述的 SELECT语句不包括 SQL_CALC_FOUND_ROWS 选项,则使用LIMIT 和不使用时，FOUND_ROWS() 可能会返回不同的结果）。
     * 通过 FOUND_ROWS()的有效行数是瞬时的，并且不用于越过SELECT SQL_CALC_FOUND_ROWS语句后面的语句。若你需要稍候参阅这个值，那么将其保存：
     * mysql> SELECT SQL_CALC_FOUND_ROWS * FROM ... ;
     * mysql> SET @rows = FOUND_ROWS();
     * 假如你正在使用 SELECT SQL_CALC_FOUND_ROWS, MySQL 必须计算出在全部结果集合中有所少行。然而， 这比不用LIMIT而再次运行问询要快，原因是结果集合不需要被送至客户端。
     * SQL_CALC_FOUND_ROWS 和 FOUND_ROWS() 在当你希望限制一个问询返回的行数时很有用，同时还能不需要再次运行问询而确定全部结果集合中的行数。一个例子就是提供页式显示的Web脚本，该显示包含显示搜索结果其它部分的页的连接。使用FOUND_ROWS() 使你确定剩下的结果需要多少其它的页。
     * SQL_CALC_FOUND_ROWS 和 FOUND_ROWS() 的应用对于UNION 问询比对于简单SELECT 语句更为复杂，原因是在UNION 中，LIMIT 可能会出现在多个位置。它可能适用于UNION中的个人 SELECT语句，或是总体上  到UNION 结果的全程。
     * SQL_CALC_FOUND_ROWS对于 UNION的意向是它应该不需要全程LIMIT而返回应返回的行数。SQL_CALC_FOUND_ROWS 和UNION 一同使用的条件是：
     * SQL_CALC_FOUND_ROWS 关键词必须出现在UNION的第一个 SELECT中。
     * FOUND_ROWS()的值只有在使用 UNION ALL时才是精确的。若使用不带ALL的UNION，则会发生两次删除， 而  FOUND_ROWS() 的指只需近似的。
     * 假若UNION 中没有出现  LIMIT ，则SQL_CALC_FOUND_ROWS 被忽略，返回临时表中的创建的用来处理UNION的行数。
     * LAST_INSERT_ID() LAST_INSERT_ID(expr)
     * 自动返回最后一个INSERT或 UPDATE 问询为 AUTO_INCREMENT列设置的第一个 发生的值
     *
     */
    public $mysql_FOUND_ROWS = 'FOUND_ROWS';

    /**
     * LAST_INSERT_ID() LAST_INSERT_ID(expr)
     * 自动返回最后一个INSERT或 UPDATE 问询为 AUTO_INCREMENT列设置的第一个 发生的值
     * 产生的ID 每次连接后保存在服务器中。这意味着函数向一个给定客户端返回的值是该客户端产生对影响AUTO_INCREMENT列的最新语句第一个 AUTO_INCREMENT值的。这个值不能被其它客户端影响，即使它们产生它们自己的 AUTO_INCREMENT值。这个行为保证了你能够找回自己的 ID 而不用担心其它客户端的活动，而且不需要加锁或处理。
     * 假如你使用一个非“magic”值来更新某一行的AUTO_INCREMENT 列，则LAST_INSERT_ID() 的值不会变化(换言之, 一个不是 NULL也不是 0的值)。
     * 重点: 假如你使用单INSERT语句插入多个行，  LAST_INSERT_ID() 只返回插入的第一行产生的值。其原因是这使依靠其它服务器复制同样的 INSERT语句变得简单
     */
    public $mysql_LAST_INSERT_ID = 'LAST_INSERT_ID';

    /**
     * ROW_COUNT()
     * 返回被前面语句升级的、插入的或删除的行数。 这个行数和 mysql 客户端显示的行数及 mysql_affected_rows() C API 函数返回的值相同
     */
    public $mysql_ROW_COUNT = 'ROW_COUNT';

    protected $mysql_info_funcs = [
        'BENCHMARK', 'CHARSET', 'COERCIBILITY',
        'COLLATION', 'CONNECTION_ID', 'CURRENT_USER', 'DATABASE',
        'SCHEMA', 'SESSION_USER', 'SYSTEM_USER', 'USER', 'VERSION'
    ];

    // ---------------------------------------------------------
    // 其他函数
    // ---------------------------------------------------------
    public $mysql_DEFAULT = 'DEFAULT';
    public $mysql_VALUES = 'VALUES';

    protected $mysql_other_funcs = [
        'GET_LOCK', 'INET_ATON', 'INET_NTOA', 'IS_FREE_LOCK', 'IS_USED_LOCK',
        'NAME_CONST', 'RELEASE_LOCK', 'SLEEP', 'UUID',

        /**
         * 该函数对于控制主从同步很有用处。它会持续封锁，直到从设备阅读和应用主机记录中所有补充资料到指定的位置。
         * 返回值是其为到达指定位置而必须等待的记录事件的数目。若从设备SQL线程没有被启动、从设备主机信息尚未初始化、
         * 参数不正确或出现任何错误，则该函数返回 NULL。若超时时间被超过，则返回-1。若在MASTER_POS_WAIT() 等待期间，
         * 从设备SQL线程中止，则该函数返回 NULL。若从设备由指定位置通过，则函数会立即返回结果。
         * 假如已经指定了一个超时时间值，当 超时时间 秒数经过后MASTER_POS_WAIT()会停止等待。
         * 超时时间 必须大于 0；一个为零或为负值的 超时时间 表示没有超市时间
         */
        'MASTER_POS_WAIT',
    ];

    /**
     * 返回mysql数据库的所有函数
     *
     * @return array
     */
    public static function getFuncs()
    {
        $self = new self();
        $refl = new \ReflectionObject($self);
        $prop = $refl->getProperties(\ReflectionProperty::IS_PUBLIC);

        $funcs = [];
        foreach ($prop as $p) {
            $funcs[] = $p->getValue($self);
        }

        return $funcs;
    }
}
