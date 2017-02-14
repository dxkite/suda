<?php
namespace archive;
use Query as XQuery;
/**
*   储存管理器
*/
class Manager
{
    protected $archive;
    protected $where=[];
    protected $wparam=[];
    protected $names=[];
    protected $sort;

    public function __construct(Archive $archive)
    {
        $this->archive=$archive;
    }

    public function insert():int
    {
        $values=$this->archive->_getVar();
        $bind='';
        $names='';
        foreach ($values as $name => $value) {
            $bind.=':'.$name.',';
            $names.='`'.$name.'`,';
            $param[$name]=$value;
        }
        $sql='INSERT INTO `'.$this->archive->getTableName().'` ('.trim($names, ',').') VALUES ('.trim($bind, ',').');';
        if ((new Query($sql, $values))->exec()) {
            return Query::lastInsertId();
        }
        return 0;
    }
    
    public function update(int $limit=0)
    {
        $values=$this->archive->_getVar();
        $wants=$this->archive->getWants();
        $param=[];
        $sets=[];
        $where=[];
        foreach ($values as $name=>$value) {
            $this->names[]=$name;
            $bname=$name.'_'.count($this->names);
            if (in_array($name, $wants)) {
                $sets[]="`{$name}`=:{$bname}";
            } else {
                $where[]="`{$name}`=:{$bname}";
            }
            $param[$bname]=$value;
        }
        $sql='UPDATE `'.$this->archive->getTableName().'` SET '.implode(',', $sets).' WHERE ' .implode(' AND ', $where).($limit?';':' LIMIT '.$limit.';');
        return (new Query($sql, $param))->exec();
    }

    public function where($where)
    {
        $wants=[];
        if (func_num_args()>1) {
            $wants=func_get_args();
        } elseif (is_array($where)) {
            $wants[]=$where;
        } else {
            $wants[]=[$where];
        }

        $or=[];
        $param=[];
        foreach ($wants as $want) {
            $and=[];
            if (!is_array($want)) {
                throw new \Exception('Unsupport Where Clouse:'.json_encode($where));
            }
            foreach ($want as $name => $value) {
                if (!$this->archive->_isField($name)) {
                    throw new \Exception("Unknown Field $name From Table {$this->archive->getTableName()}");
                }
                $this->names[]=$name;
                $bname=$name.'_'.count($this->names);
                if (is_array($value) && count($value)===2) {
                    $and[]="`{$name}` {$value[0]} :{$bname}";
                    $param[$bname]=$value[1];
                } else {
                    $and[]="`{$name}`=:{$bname}";
                    $param[$bname]=$value;
                }
            }
            $or[]='('.implode(' AND ', $and).')';
        }
        $this->where[]=implode(' OR ', $or);
        $this->wparam=array_merge($this->wparam, $param);
        return $this;
    }

    public function delete(int $limit=1):int
    {
        $param=[];
        $where=[];
        $values=$this->archive->_getVar();
        foreach ($values as $name=>$value) {
            $this->names[]=$name;
            $bname=$name.'_'.count($this->names);
            $where[]="`{$name}`=:{$bname}";
            $param[$bname]=$value;
        }
        $sql='DELETE FROM `'.$this->archive->getTableName().'` WHERE ' .implode(' AND ', $where).($limit?';':' LIMIT '.$limit.';');
        return (new Query($sql, $param))->exec();
    }

    public function sort(string $field, $sort=SORT_ASC)
    {
        if (!$this->archive->_isField($name)) {
            throw new \Exception("Unknown Field $name From Table {$this->archive->getTableName()}");
        }
        $order='ORDER BY `'.$field.'` ';
        if ($sort===SORT_ASC) {
            $order.=' ASC';
        } else {
            $order.=' DISC';
        }
        $this->sort=$order;
        return $this;
    }

    public function retrieve(array $wants=[], int $limit=1, int $offset=0)
    {
        $values=$this->archive->_getVar();
        if (count($wants)===0) {
            $fields='*';
        } else {
            $field=[];
            foreach ($wants as $want) {
                $field[]="`$want`";
            }
            $fields=implode(',', $field);
        }
        if (count($this->where)===0) {
            self::where($values);
        }
        $where=isset($this->where)?' WHERE '.implode(' OR ', $this->where):'';
        $sql='SELECT '.$fields.' FROM `'.$this->archive->getTableName()."` {$where} {$this->sort} LIMIT {$offset},{$limit};";
        return new Query($sql, $this->wparam);
    }

    public function find(array $wants=[], int $limit=1, int $offset=0)
    {
        return self::retrieve($wants,$limit,$offset)->fetch();
    }

    public function findAll(array $wants=[], int $limit=1, int $offset=0)
    {
        return self::retrieve($wants,$limit,$offset)->fetchAll();
    }
    public function count()
    {
        return XQuery::count($this->archive->getTableName());
    }
}
