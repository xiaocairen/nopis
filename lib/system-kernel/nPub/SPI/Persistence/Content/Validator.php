<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Content;

/**
 * 数据字段验证类，xxxx 表示验证未通过时的提示信息<br>
 * <br>
 * 参数 $field :   数据表字段名<br>
 * 参数 $value :   值<br>
 * 参数 $param :   @xxxx 后面的参数字符串, 多个参数之间用英文逗号分割<br>
 * 参数 $db_type : 字段在数据库里的字段类型<br>
 * 对 @minlength、@maxlength、@min、@max、@range 必须标注 @db_type xxxx 才能生效
 *
 * @required xxxxxx
 * @type (email|url|date|number|digits|id_card|phone), xxxxxxxx
 * @minlength 长度, xxxxxx
 * @maxlength 长度, xxxxxx
 * @min 数值, xxxxxx
 * @max 数值, xxxxxx
 * @range 最小值, 最大值, xxxxxx
 *
 * @author wb
 */
class Validator
{
    private $string = ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext'];
    private $int = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double'];

    // @required xxxxxx
    public function required($field, $value, $param, $db_type = null)
    {
        if (!$value && $value !== 0 && $value !== '0')
            throw new ValidateException($param);
    }

    // ********************************************************************
    // @type (email|url|date|number|digits|id_card|phone), xxxxxxxx
    // 可选类型有
    // email    邮箱
    // url      url地址
    // date     时间日期格式
    // number   数字字符串
    // digits   整数
    // id_card  身份证号码
    // phone    电话
    // ********************************************************************
    public function type($field, $value, $param, $db_type = null)
    {
        if (!$value)
            return;

        list($type, $tooltip) = array_map('trim', explode(',', $param));
        switch (strtolower($type)) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new ValidateException($tooltip);
                }
                break;

            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    throw new ValidateException($tooltip);
                }
                break;

            case 'date':
                if (false === strtotime($value)) {
                    throw new ValidateException($tooltip);
                }
                break;

            case 'number':
                if (!is_numeric($value)) {
                    throw new ValidateException($tooltip);
                }
                break;

            case 'digits':
                if (!preg_match('/^[1-9]{1}[\d]*$/', $value)) {
                    throw new ValidateException($tooltip);
                }
                break;

            case 'id_card':
                if (!is_ID_card($value)) {
                    throw new ValidateException($tooltip);
                }
                break;

            case 'phone':
                if (!is_mobile($value)) {
                    throw new ValidateException($tooltip);
                }
                break;

            default:
                throw new ValidateException(
                    sprintf('Field %s @type Unsupport type %s', $field, $type)
                );
        }
    }

    // @minlength 长度, xxxxxx
    // 仅对 db_type 为 char varchar 的类型有效
    public function minlength($field, $value, $param, $db_type = null)
    {
        if (!$value || !$db_type)
            return;

        $db_type = preg_replace('/\(.*\)/', '', strtolower($db_type));
        if (!in_array($db_type, $this->string))
            return;

        list($minlength, $tooltip) = array_map('trim', explode(',', $param));
        if (mb_strlen($value) < $minlength)
            throw new ValidateException($tooltip);
    }

    // @maxlength 长度, xxxxxx
    // 仅对 db_type 为 char varchar 的类型有效
    public function maxlength($field, $value, $param, $db_type = null)
    {
        if (!$value || !$db_type)
            return;

        $db_type = preg_replace('/\(.*\)/', '', strtolower($db_type));
        if (!in_array($db_type, $this->string))
            return;

        list($maxlength, $tooltip) = array_map('trim', explode(',', $param));
        if (mb_strlen($value) > $maxlength)
            throw new ValidateException($tooltip);
    }

    // @min 数值, xxxxxx
    // 仅对 db_type 为 tinyint int 等类型有效
    public function min($field, $value, $param, $db_type = null)
    {
        if (!$value && $value !== 0 && $value !== '0')
            return;
        if (!$db_type)
            return;

        $db_type = preg_replace('/\(.*\)/', '', strtolower($db_type));
        if (!in_array($db_type, $this->int))
            return;

        list($min, $tooltip) = array_map('trim', explode(',', $param));
        if ($value < $min)
            throw new ValidateException($tooltip);
    }

    // @max 数值, xxxxxx
    // 仅对 db_type 为 tinyint int 等类型有效
    public function max($field, $value, $param, $db_type = null)
    {
        if (!$value && $value !== 0 && $value !== '0')
            return;
        if (!$db_type)
            return;

        $db_type = preg_replace('/\(.*\)/', '', strtolower($db_type));
        if (!in_array($db_type, $this->int))
            return;

        list($max, $tooltip) = array_map('trim', explode(',', $param));
        if ($value > $max)
            throw new ValidateException($tooltip);
    }

    // @range 最小值, 最大值, xxxxxx
    // 仅对 db_type 为 tinyint int 等类型有效
    public function range($field, $value, $param, $db_type = null)
    {
        if (!$value && $value !== 0 && $value !== '0')
            return;
        if (!$db_type)
            return;

        $db_type = preg_replace('/\(.*\)/', '', strtolower($db_type));
        if (!in_array($db_type, $this->int))
            return;

        list($min, $max, $tooltip) = array_map('trim', explode(',', $param));
        if ($value < $min || $value > $max)
            throw new ValidateException($tooltip);
    }
}
