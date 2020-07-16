<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Entity\User;

use Nopis\Lib\Entity\ValueObject;

/**
 * 数据对象使用约定：
 *
 * 1. 文档注释docComment的解释说明，请参考类 nPub\SPI\Persistence\Entity\FieldValidator 的每个方法； <br />
 * 2. 映射到数据表字段的属性所使用的修饰符只能是 protected 和 public 两种；private属性只提供给类本身使用； <br />
 * 3. protected 表示只读属性字段，在数据表业务逻辑中体现为只有在创建数据时可以被赋值，更新数据时则不能修改； <br />
 * 4. public 表示可读可写属性字段，在数据表业务逻辑中体现为在创建是可以被赋值，更新数据时也可以被修改； <br />
 * 5. 属性字段可以有合理的默认值；
 *
 * @author wangbin
 */
abstract class User extends ValueObject
{
    /**
     * 用户ID
     *
     * @var int
     *
     * @db_type int(10)
     * @access private
     * @primary primary
     */
    public $user_id;

    /**
     * 用户名
     *
     * @var string
     *
     * @db_type varchar(20)
     * @access protected
     * @primary unique
     */
    public $username;

    /**
     * 移动电话
     *
     * @var string
     *
     * @db_type char(12)
     * @access protected
     * @primary unique
     */
    public $phone;

    /**
     * uuid
     *
     * @var string
     *
     * @access public
     * @db_type varchar(64)
     */
    public $uuid = '';

    /**
     * 密码
     *
     * @var string
     *
     * @db_type char(32)
     * @access protected
     * @encrypt password
     */
    protected $password;

    /**
     * 真实姓名
     *
     * @var string
     *
     * @db_type varchar(20)
     * @access public
     */
    public $realname = '';

    /**
     * 性别
     *
     * @var string
     *
     * @db_type enum('man', 'woman', 'secret')
     * @access public
     */
    public $sex = 'secret';

    /**
     * 生日
     *
     * @var string
     *
     * @db_type varchar(10)
     * @access public
     */
    public $birthday = '';

    /**
     * 邮箱
     *
     * @var string
     *
     * @db_type varchar(100)
     * @access public
     */
    public $email = '';

    /**
     * 身份证号
     *
     * @var string
     *
     * @db_type char(18)
     * @access public
     */
    public $id_card = '';

    /**
     * 头像
     *
     * @var string
     *
     * @db_type varchar(100)
     * @access public
     */
    public $avatar = '';

    /**
     * 省份id
     *
     * @var int
     *
     * @db_type int(10)
     * @access public
     */
    public $prov_code = 0;

    /**
     * 城市id
     *
     * @var int
     *
     * @db_type int(10)
     * @access public
     */
    public $city_code = 0;

    /**
     * 城市名称
     *
     * @var string
     *
     * @db_type varchar(20)
     * @access public
     */
    public $city_name = '';

    /**
     * 注册时间 unix时间戳
     *
     * @var int
     *
     * @db_type int(10)
     * @access private
     */
    protected $reg_time = SYS_TIMESTAMP;

    /**
     * 最后登陆时间 unix时间戳
     *
     * @var int
     *
     * @db_type int(10)
     * @access public
     */
    public $last_login_time = SYS_TIMESTAMP;

    /**
     * 角色
     *
     * @var string
     *
     * @db_type tinyint(3)
     * @access protected
     */
    public $roler = 0;

    /**
     * 是否禁用
     *
     * @var int
     *
     * @db_type tinyint(1)
     * @access public
     */
    public $is_forbid = 0;

    /**
     * 是否删除
     *
     * @var int
     *
     * @db_type tinyint(1)
     * @access protected
     */
    public $is_del = 0;

    /**
     * 数据表名
     *
     * @return string
     */
    public static function tableName()
    {
        return 'user';
    }
}
