<?php
namespace suda\archive;

class DAO
{
    protected $fields=[];
    protected $primaryKey=null;
    protected $tableName;

    public function __construct(){

    }


    /**
     * 插入行
     * @param array $values 待插入的值
     * @return void
     */
    public function insert(array $values){

    }

    /**
     * 通过主键查找元素
     *
     * @param [type] $value 主键的值
     * @return array|false
     */
    public function getByPrimaryKey($value) {

    }


    /**
     * 通过主键更新元素
     *
     * @param [type] $value 待更新的数据
     * @param [type] $data 待更新的数据
     * @return counts 更新的行数
     */
    public function updataByPrimaryKey($value,$data) {

    }
    
    /**
     * 通过主键删除元素
     *
     * @param [type] $value 待更新的数据
     * @return void
     */
    public function deleteByPrimaryKey($value){

    }

    /**
     * 列出元素
     *
     * @param int $page  是否分页（页数）
     * @param int $rows 分页的元素个数
     * @return array|false
     */
    public function list(int $page=null,int $rows=10){

    }

    /**
     * 根据条件更新列
     *
     * @param [type] $data
     * @param [type] $where
     * @return int
     */
    function updata($data,$where){

    }

    /**
     * 根据条件删除列
     *
     * @param [type] $where
     * @return int
     */
    function delete($where) {

    }
    function setTableName(string $name){

    }
    function getTableName():string{

    }
    function setFields(array $fields){

    }
    function getFields():array{

    }
    function checkFields(array $values){

    }
    function count():int{

    }
}
