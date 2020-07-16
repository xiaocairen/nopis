<?php

/*
 * This file is part of the Nopis package.
 *
 * (c) wangbin <wbhazz@163.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace nPub\SPI\Persistence\Entity\Backend;

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
abstract class BackendMap extends ValueObject
{
    /**
     * 菜单ID
     *
     * @var int
     *
     * @db_type int(10)
     * @access private
     * @primary primary
     */
    protected $map_id;

    /**
     * 父菜单id
     *
     * @var int
     *
     * @db_type int(10)
     * @access protected
     */
    protected $pid = 0;

    /**
     * 菜单名称
     *
     * @var string
     *
     * @db_type varchar(20)
     * @access public
     */
    public $menu_name;

    /**
     * 菜单对应的action控制器
     *
     * @var string
     *
     * @db_type varchar(50)
     * @access public
     */
    public $menu_action;

    /**
     * 菜单排序
     *
     * @var int
     *
     * @db_type smallint(5)
     * @access public
     */
    public $menu_sort;

    /**
     * 是否显示到后台菜单，如果不显示到后台菜单，则仅作权限控制
     *
     * @var int
     *
     * @db_type tinyint(1)
     * @access public
     */
    public $if_show;

    /**
     * 菜单级别，只有1，2，3级
     *
     * @var int
     *
     * @db_type tinyint(3)
     * @access private
     */
    protected $menu_level = 1;

    /**
     * 菜单根路径
     *
     * @var string
     *
     * @db_type varchar(50)
     * @access private
     */
    protected $root_path = '/';

    /**
     * 数据表名
     *
     * @return string
     */
    public static function tableName()
    {
        return 'backend_map';
    }
}
