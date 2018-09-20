<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 * 
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\core;

use suda\archive\SQLQuery;
use suda\exception\SQLException;

/**
 * 数据库查询类
 * 提供了数据库的查询方式
 *
 */
class Query extends SQLQuery
{
    /**
     * 向数据表中插入一行
     *
     * @example
     * ```php
     * // 建议使用
     * $id = Query::insert('user',['name'=>$name,'password'=>  $password ,'group'=>$group, 'available'=>false,'email'=>$email,'ip'=>$ip,]);
     * // 或
     * $id = Query::insert('user',"(`name`,`password`,`group`,`available`,`email`,`ip`)", ['name'=>$name, 'password'=>  $password ,'group'=>$group,'available'=>false,'email'=>$email,'ip'=>$ip,]
     * ```
     * 以上调用会编译成类似如下模板
     * ```sql
     * INSERT INTO `mc_user` (`name`,`password`,`group`,`available`,`email`,`ip`) VALUES (:name,:password,:group,:available,:email,:ip);
     * ```
     * @param string $table 为数据表名，会自动添加数据表前缀。
     * @param [type] $values  为插入的值，可以为字符串或者MAP数组。
     * @param array $binds 为values中出现的模板控制待绑定字符。
     * @param [type] $object 数据库回调对象
     * @return integer 成功时返回ID, 失败时返回0，无自动增ID时返回影响行数（负数表示）
     */
    public static function insert(string $table, $values, array $binds=[], $object=null):int
    {
        $table=self::table($table);
        if (is_string($values)) {
            $sql=$sql='INSERT INTO `'.$table.'` '.trim($values, ';').' ;';
        } elseif (is_array($values)) {
            $bind='';
            $names='';
            foreach ($values as $name => $value) {
                $bindName=$name.count($binds);
                $bind.=':'.$bindName.',';
                $names.='`'.$name.'`,';
                $binds[$bindName]=static::value($name, $value);
            }
            $sql='INSERT INTO `'.$table.'` ('.trim($names, ',').') VALUES ('.trim($bind, ',').');';
        }
        $count=($q=new SQLQuery($sql, $binds))->object($object)->exec();
        if ($count) {
            $id=$q->lastInsertId();
            if ($id>0) {
                return $id;
            } else {
                return -$count;
            }
        }
        return 0;
    }

    public static function that($object)
    {
        return (new Query)->object($object);
    }
    
    /**
     * 在数据表总搜索
     *
     * 查询后会返回一个 `archive\Query ` 的实例对象，通过 fetch 或者 fetchAll 获取查询结果。
     *
     * @example
     * 如下语句
     * ```php
     * $fetch=Query::where('user', ['id', 'name', 'email', 'available', 'avatar', 'ip'], '1', [], [$page, $count])->fetchAll();
     * ```
     * 与如下SQL语句等价
     * ```sql
     * SELECT `id`, `name`,`email`,`available`, `avatar`, `ip` FROM mc_user WHERE 1;
     * ```
     * @param string $table 表名
     * @param string|array $wants 提取的列
     * @param string|array $condithon 提取的条件
     * @param array $binds 模板绑定的值
     * @param array $page 分页获取
     * @param boolean $scroll 滚动获取
     * @return SQLQuery
     */
    public static function where(string $table, $wants='*', $condithon='1', array $binds=[], array $page=null, bool $scroll=false):SQLQuery
    {
        $where=self::prepareWhere($condithon, $binds);
        return self::select($table, $wants, $where, $binds, $page, $scroll);
    }


    /**
     * 搜索列
     *
     * @param string $table 表名
     * @param string|array $wants 提取的列
     * @param [type] $field 搜索的列，支持对一列或者多列搜索
     * @param string $search 搜索的值
     * @param array $page 分页获取
     * @param boolean $scroll 滚动获取
     * @return SQLQuery
     */
    public static function search(string $table, $wants='*', $field, string $search, array $page=null, bool $scroll=false):SQLQuery
    {
        list($search_str, $bind)=self::prepareSearch($field, $search);
        return self::where($table, $wants, $search_str, $bind, $page, $scroll);
    }

    /**
     * 选择列
     * 查询后会返回一个 `archive\Query ` 的实例对象，通过 fetch 或者 fetchAll 获取查询结果。
     * @example
     *
     * ```php
     * $fetch=Query::select('user_group', 'auths', ' JOIN `#{user}` ON `#{user}`.`id` = :id  WHERE `user` = :id  or `#{user_group}`.`id` =`#{user}`.`group` LIMIT 1;', ['id'=>$uid])->fetch()
     * ```
     * 等价于
     * ```sql
     * SELECT auths FROM `mc_user_group`  JOIN `mc_user` ON `mc_user`.`id` = :id  WHERE `user` = :id  or `mc_user_group`.`id` =`mc_user`.`group` LIMIT 1;
     * ```
     * @param string $table 数据表名
     * @param [type] $wants 为查询的字段，可以为字符串如`"field1,field2"` 或者数组 `[ "field1","field2" ]`； 建议使用数组模式。
     * @param [type] $conditions 为查询的条件 ，可以为字符串 或者数组 ， 建议使用数组模式。
     * @param array $binds 查询字符串中绑定的数据
     * @param array $page 分页查询，接受数组 ，格式为： [为分页的页数,每页长度,是否为OFFSET]
     * @param boolean $scroll 滚动查询，一次取出一条记录
     * @return SQLQuery
     */
    public static function select(string $table, $wants, $conditions, array $binds=[], array $page=null, bool $scroll=false):SQLQuery
    {
        $table=self::table($table);
        if (is_string($wants)) {
            $fields=$wants;
        } else {
            $field=[];
            foreach ($wants as $want) {
                $field[]="`$want`";
            }
            $fields=implode(',', $field);
        }
        $limit=is_null($page)?'': ' LIMIT '.self::page($page);
        return new SQLQuery('SELECT '.$fields.' FROM `'.$table.'` '.trim($conditions, ';').$limit.';', $binds, $scroll);
    }

    /**
     * 更新列
     *
     * 返回影响的记录数
     *
     * @example
     *
     * ```php
     * Query::update('user_token', 'expire = :time , token=:new_token,value=:refresh', 'id=:id AND UNIX_TIMESTAMP() < `time` + :alive AND value = :value ', ['id'=>$id, 'value'=>$value, 'new_token'=>$new, 'refresh'=>$refresh, 'time'=>time() + $get['beat'], 'alive'=>$get['alive']]);
     * ```
     * 等价于
     * ```sql
     * UPDATE `mc_user_token` SET expire = :time , token=:new_token,value=:refresh  WHERE id=:id AND UNIX_TIMESTAMP() < `time` + :alive AND value = :value ;
     * ```
     * @param string $table 数据表名
     * @param [type] $set_fields 为设置的字段，使用键值数组式设置值。
     * @param string $where 为更新的条件 ，可以为字符串 或者数组 ， 建议使用数组模式。
     * @param array $binds 查询字符串中绑定的数据
     * @param [type] $object 数据库回调对象
     * @return integer
     */
    public static function update(string $table, $set_fields, $where='1', array $binds=[], $object=null):int
    {
        $table=self::table($table);
        $count=0;
        if (is_array($set_fields)) {
            $sets=[];
            foreach ($set_fields as $name=>$value) {
                $bname= $name.($count++);
                $binds[$bname]=static::value($name, $value);
                $sets[]="`{$name}`=:{$bname}";
            }
            $sql='UPDATE `'.$table.'` SET '.implode(',', $sets).' '.self::prepareWhere($where, $binds, $count).';';
        } else {
            $sql='UPDATE `'.$table.'` SET '.$set_fields.' '.self::prepareWhere($where, $binds, $count).';';
        }
        return (new Query($sql, $binds))->object($object)->exec();
    }

    /**
     * 删除列
     *
     * @param string $table 数据表名
     * @param string $where 为删除的条件 ，可以为字符串 或者数组 ， 建议使用数组模式。
     * @param array $binds 查询字符串中绑定的数据
     * @param [type] $object
     * @return integer
     */
    public static function delete(string $table, $where='1', array $binds=[], $object=null):int
    {
        $table=self::table($table);
        $sql='DELETE FROM `'.$table.'` '.self::prepareWhere($where, $binds).';';
        return (new SQLQuery($sql, $binds))->object($object)->exec();
    }


    public static function prepareIn(string $name, array $invalues, string $prefix='in_', int $count=0)
    {
        if (count($invalues)<=0) {
            throw new SQLException('on field '.$name.' value can\'t be empty array');
        }
        $names=[];
        $param=[];
        foreach ($invalues as $key=>$value) {
            $bname=$prefix. preg_replace('/[_]+/', '_', preg_replace('/[`.{}#]/', '_', $name)).$key.($count++);
            $param[$bname]= static::value($name, $value);
            $names[]=':'.$bname;
        }
        $sql=$name.' IN ('.implode(',', $names).')';
        return [$sql,$param];
    }


    public static function prepareSearch($field, string $search)
    {
        $search=preg_replace('/([%_])/', '\\\\$1', $search);
        $search=preg_replace('/\s+/', '%', $search);
        if (is_array($field)) {
            $search_str=[];
            foreach ($field as $item=>$want) {
                $search_str[]="`{$want}` LIKE CONCAT('%',:search,'%')";
                $bind['search']=$search;
            }
            $search_str=implode(' OR ', $search_str);
        } else {
            $search_str='`'.$field.'` LIKE CONCAT(\'%\',:search,\'%\')';
            $bind['search']=$search;
        }
        return [$search_str,$bind];
    }

    public static function prepareWhere($where, array &$bind, string $prefix='where_', int $count=1)
    {
        $param=[];
        if (is_array($where)) {
            $and=[];
            foreach ($where as $name => $value) {
                $bname= $prefix.$name.($count++);
                // in cause
                if (is_array($value)) {
                    list($sql, $in_param)=self::prepareIn($name, $value, 'where_in_', $count);
                    $and[]=$sql;
                    $param=array_merge($param, $in_param);
                } else {
                    $and[]="`{$name}`=:{$bname}";
                    $param[$bname]=static::value($name, $value);
                }
            }
            $where=implode(' AND ', $and);
        }
        $where=' WHERE '.rtrim($where, ';');
        $bind=array_merge($bind, $param);
        return $where;
    }

    
    public static function count(string $table, $where='1', array $binds=[], $object=null):int
    {
        $table=self::table($table);
        $where=self::prepareWhere($where, $binds);
        $sql='SELECT count(*) as `count` FROM `'.$table.'` '.$where.';';
        if ($query=(new SQLQuery($sql, $binds))->object($object)->fetch()) {
            return intval($query['count']);
        }
        return 0;
    }
    
    public static function nextId(string $table, string $database=null)
    {
        $sql='SELECT `AUTO_INCREMENT` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`=:database AND `TABLE_NAME`=:table LIMIT 1;';
        $table=self::table($table);
        if ($query=(new SQLQuery($sql, ['database'=>is_null($database)?Config::get('database.name'):$database,'table'=>$table]))->fetch()) {
            return intval($query['AUTO_INCREMENT']);
        }
        return 0;
    }

    protected static function table(string $name)
    {
        return Config::get('database.prefix', '').$name;
    }

    protected static function page(array $page)
    {
        if (count($page)>2) {
            list($page, $row, $offset)=$page;
        } else {
            list($page, $row)=$page;
            $offset=false;
        }
        if ($row<1) {
            $row=1;
        }
        if ($page < 1) {
            $page = 1;
        }
        if ($offset) {
            return $page .',' .$row;
        } else {
            return ((intval($page) - 1) * intval($row)) . ', ' . intval($row);
        }
    }
}
