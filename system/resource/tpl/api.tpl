
namespace db;

use Query;

class <?php echo htmlspecialchars($_SQL->name) ?>

{
    <?php $set_update=[] ; $set_create=[] ;$create_params=[];$update_params=[]; ?>
    <?php foreach($_SQL->fields() as $name => $type): ?><?php  $type=preg_match('/int/', $type)?'int':'string';$set_update[]= "'$name'=>\${$name}";?>
      <?php if($this->getAuto() == $name): ?>
        <?php $update_params[]=$type .' $'.$name; ?>
       <?php else: ?>
        <?php $set_create[]= "'$name'=>\${$name}"; $create_params[]=$type .' $'.$name; ?>
       <?php endif; ?>
    <?php endforeach; ?>
    <?php $set_update=implode(',',$set_update);  $set_create=implode(',',$set_create);  $update_params=array_merge($update_params, $create_params); $create_params=implode(',', $create_params);$update_params=implode(',', $update_params);?>

    public function create(<?php echo htmlspecialchars($create_params) ?>)
    {
        return Query::insert('<?php echo htmlspecialchars($this->getTableName()) ?>',[<?php echo($set_create) ?>]);
    }
    public function delete(int $id){
        return Query::delete('<?php echo htmlspecialchars($this->getTableName()) ?>',['id'=>$id]);
    }
	
	   
	public function get(int $id)
    {
        // 获取储存的验证数据
        return ($get=Query::where('<?php echo htmlspecialchars($this->getTableName()) ?>', <?php echo htmlspecialchars($this->getFieldsStr()) ?>,['id'=>$id])->fetch()) ? $get  : false;
    }
    
    public function update(<?php echo htmlspecialchars($update_params) ?>){
       return Query::update('<?php echo htmlspecialchars($this->getTableName()) ?>',[<?php echo($set_update) ?>]); 
    }
    public function list(int $page=1, int $count=10)
    {
        return Query::where('<?php echo htmlspecialchars($this->getTableName()) ?>', <?php echo htmlspecialchars($this->getFieldsStr()) ?>, '1', [], [$page, $count])->fetchAll();
    }
}