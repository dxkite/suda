<?php
namespace suda\core;
use suda\archive\Query as AQuery;

class Query extends AQuery
{
    public static function insert(string $table, $values, array $binds=[]):int
    {
        $table=self::table($table);
        if (is_string($values)) {
            $sql=$sql='INSERT INTO `'.$table.'` '.trim($values, ';').' ;';
        } elseif (is_array($values)) {
            $bind='';
            $names='';
            foreach ($values as $name => $value) {
                $bind.=':'.$name.',';
                $names.='`'.$name.'`,';
                $param[$name]=$value;
            }
            $binds=$values;
            $sql='INSERT INTO `'.$table.'` ('.trim($names, ',').') VALUES ('.trim($bind, ',').');';
        }
        if (($count=(new AQuery($sql, $binds))->exec()) === 1) {
            return AQuery::lastInsertId();
        } else {
            return $count;
        }
        return -1;
    }

    public static function where(string $table, $wants='*', $condithon='1', array $binds=[], array $page=null, bool $scroll=false):AQuery
    {
        $where=self::prepareWhere($where,$binds);
        return self::select($table, $wants,$where, $binds, $page, $scroll);
    }

    public static function select(string $table, $wants ,  $conditions, array $binds=[], array $page=null, bool $scroll=false)
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
        $limit=is_null($page)?'': ' LIMIT '.self::page($page[0], $page[1]);
        return new AQuery('SELECT '.$fields.' FROM `'.$table.'` '.trim($conditions, ';').$limit.';', $binds, $scroll);
    }

    public static function update(string $table, $set_fields,  $where='1', array $binds=[]):int
    {
        $table=self::table($table);
        if (is_array($set_fields)) {
            $sets=[];
            foreach ($set_fields as $name=>$value) {
                $bname=$name.'_'.($count++);
                $sets[]="`{$name}`=:{$bname}";
                $binds[$bname]=$value;
            }
            $sql='UPDATE `'.$table.'` SET '.implode(',', $sets).' '.self::prepareWhere($where,$binds).';';
        } else {
            $sql='UPDATE `'.$table.'` SET '.$set_fields.' '.self::prepareWhere($where,$binds).';';
        }
        
        return (new Query($sql, array_merge($param, $binds)))->exec();
    }

    public static function delete(string $table, $where='1', array $binds=[]):int
    {
        $table=self::table($table);
        $sql='DELETE FROM `'.$table.'` '.self::prepareWhere($where,$binds).';';
        return (new AQuery($sql, $binds))->exec();
    }

    protected static function prepareIn(string $name, array $invalues, string $prefix='in_')
    {
        $count=0;
        $names=[];
        $param=[];
        foreach ($invalues as $key=>$value) {
            $bname=$prefix. preg_replace('/[_]+/','_',preg_replace('/[`.{}#]/','_',$name)).$key.($count++);
            $param[$bname]=$value;
            $names[]=':'.$bname;
        }
        $sql=$name.' IN ('.implode(',', $names).')';
        return ['sql'=>$sql,'param'=>$param];
    }

    protected static function prepareWhere($where,array &$bind){
        $param=[];
        $count=0;
        if (is_array($where)) {
            $count=0;
            $and=[];
            foreach ($where as $name => $value) {
                $bname=$name.'_'.($count++);
                // in cause
                if (is_array($value)){
                    $in=self::prepareIn($name,$value);
                    $and[]=$in['sql'];
                    $param=array_merge($param,$in['param']);
                }else{
                    $and[]="`{$name}`=:{$bname}";
                    $param[$bname]=$value;
                }
            }

            $where=implode(' AND ', $and);
        }
        $where='` WHERE '.rtrim($where, ';');
        $bind=array_merge($bind,$param);
        return $where;
    }
    public static function count(string $table, string $where='1', array $binds=[]):int
    {
        $table=self::table($table);
        $sql='SELECT count(*) as `count` FROM `'.$table.'` WHERE '.rtrim($where, ';').';';
        if ($query=(new AQuery($sql, $binds))->fetch()) {
            return intval($query['count']);
        }
        return 0;
    }
    
    public static function nextId(string $table,string $database=null)
    {
        $sql='SELECT `AUTO_INCREMENT` FROM `information_schema`.`TABLES` WHERE `TABLE_SCHEMA`=:database AND `TABLE_NAME`=:table LIMIT 1;';
        $table=self::table($table);
        if ($query=(new AQuery($sql,['database'=>is_null($database)?\Config::get('database.name'):$database,'table'=>$table]))->fetch()) {
            return intval($query['AUTO_INCREMENT']);
        }
        return 0;
    }

    protected static function table(string $name)
    {
        return \Config::get('database.prefix', '').$name;
    }

    protected static function page(int $page=0, int $percount=1)
    {
        if ($percount<1) {
            $percount=1;
        }
        if ($page < 1) {
            $page = 1;
        }
        return ((intval($page) - 1) * intval($percount)) . ', ' . intval($percount);
    }
}
