
namespace db;

use suda\core\Query;

class <?php echo htmlspecialchars($_SQL->name) ?>

{
	<?php 
		$primary_key=key($_SQL->primary);
		$primary_type=preg_match('/int/i', current($_SQL->primary))?'int':'string'; 
	?>
    <?php $set_update=[] ; $set_create=[] ;$create_params=[];$update_params=[]; ?>
    <?php foreach($_SQL->fields() as $name => $type): ?><?php  $type=preg_match('/int/i', $type)?'int':'string';$set_update[]= "'$name'=>\${$name}";?>
      <?php if($this->getAuto() == $name): ?>
        <?php $update_params[]=$type .' $'.$name; ?>
       <?php else: ?>
        <?php $set_create[]= "'$name'=>\${$name}"; $create_params[]=$type .' $'.$name; ?>
       <?php endif; ?>
    <?php endforeach; ?>
    <?php $set_update=implode(',',$set_update);  $set_create=implode(',',$set_create);  $update_params=array_merge($update_params, $create_params); $create_params=implode(',', $create_params);$update_params=implode(',', $update_params);?>
	
	/**
	* add A Item
	* @return  the id of item
	*/
    public static function add(<?php echo htmlspecialchars($create_params) ?>)
    {
        return Query::insert('<?php echo htmlspecialchars($this->getTableName()) ?>',[<?php echo($set_create) ?>]);
    }
	
	/**
	*  	Delete A Item By Primary Key
	*	@return  rows
	*/
	public static function delete(<?php echo htmlspecialchars($primary_type) ?> $<?php echo htmlspecialchars($primary_key) ?>){
        return Query::delete('<?php echo htmlspecialchars($this->getTableName()) ?>',['<?php echo htmlspecialchars($primary_key) ?>'=>$<?php echo htmlspecialchars($primary_key) ?>]);
    }
	
	/**
	*  	get A Item By Primary Key 
	* 	@return  item
	*/  
	public static function get(<?php echo htmlspecialchars($primary_type) ?> $<?php echo htmlspecialchars($primary_key) ?>)
    {
        return ($get=Query::where('<?php echo htmlspecialchars($this->getTableName()) ?>', <?php echo htmlspecialchars($this->getFieldsStr()) ?>,['<?php echo htmlspecialchars($primary_key) ?>'=>$<?php echo htmlspecialchars($primary_key) ?>])->fetch()) ? $get  : false;
    }
	
	/**
	*  	get A Item By Primary Key 
	* 	@return  item
	*/  
	public static function count()
    {
        return Query::count('<?php echo htmlspecialchars($this->getTableName()) ?>');
    }
	
	<?php foreach($_SQL->keys as $key => $type): ?> 
	
	/**
	* Get By <?php echo htmlspecialchars($key) ?> <?php echo htmlspecialchars($type) ?>  
	*/<?php $type=preg_match('/int/i', $type)?'int':'string'; ?> 
	public static function getBy<?php echo htmlspecialchars(ucfirst($key)) ?>(<?php echo htmlspecialchars($type) ?> $<?php echo htmlspecialchars($key) ?>)
    {
        return ($get=Query::where('<?php echo htmlspecialchars($this->getTableName()) ?>', <?php echo htmlspecialchars($this->getFieldsStr($key)) ?>,['<?php echo htmlspecialchars($key) ?>'=>$<?php echo htmlspecialchars($key) ?>])->fetch()) ? $get  : false;
    }
	
	<?php endforeach; ?> 
    public static function update(<?php echo htmlspecialchars($primary_type) ?> $<?php echo htmlspecialchars($primary_key) ?>,<?php echo htmlspecialchars($this->updataParams()) ?>){
	   $sets=[];
	   <?php foreach($_SQL->fields() as $name => $type): ?> 
	   if  (!is_null($<?php echo htmlspecialchars($name) ?>))
	   {
		   $sets['<?php echo htmlspecialchars($name) ?>']=$<?php echo htmlspecialchars($name) ?>;
	   }
       <?php endforeach; ?> 
       return Query::update('<?php echo htmlspecialchars($this->getTableName()) ?>',$sets,['<?php echo htmlspecialchars($primary_key) ?>'=>$<?php echo htmlspecialchars($primary_key) ?>]); 
    }
	 
    public static function set(<?php echo htmlspecialchars($primary_type) ?> $<?php echo htmlspecialchars($primary_key) ?>,array $data){
	   foreach($data as $name=>$value){
			if (!in_array($name,<?php echo htmlspecialchars($this->getFieldsStr( )) ?>)){
				return false;
			}
	   }
       return Query::update('<?php echo htmlspecialchars($this->getTableName()) ?>',$data,['<?php echo htmlspecialchars($primary_key) ?>'=>$<?php echo htmlspecialchars($primary_key) ?>]); 
    }
    public static function list(int $page=1, int $count=10)
    {
        return Query::where('<?php echo htmlspecialchars($this->getTableName()) ?>', <?php echo htmlspecialchars($this->getFieldsStr()) ?>, '1', [], [$page, $count])->fetchAll();
    }
	
 <?php foreach($_SQL->keys as $key => $type): ?> 
	/**
	* list By <?php echo htmlspecialchars($key) ?> <?php echo htmlspecialchars($type) ?>  
	*/<?php $type=preg_match('/int/i', $type)?'int':'string'; ?> 
	public static function listBy<?php echo htmlspecialchars(ucfirst($key)) ?>(<?php echo htmlspecialchars($type) ?> $<?php echo htmlspecialchars($key) ?>,int $page=1, int $count=10)
    {<?php if($type==='int'): ?> 
        return ($get=Query::where('<?php echo htmlspecialchars($this->getTableName()) ?>', <?php echo htmlspecialchars($this->getFieldsStr($key)) ?>,['<?php echo htmlspecialchars($key) ?>'=>$<?php echo htmlspecialchars($key) ?>],[],[$page, $count])->fetchAll()) ? $get  : false;<?php elseif ($type==='string'): ?> 
		return ($get=Query::where('<?php echo htmlspecialchars($this->getTableName()) ?>', <?php echo htmlspecialchars($this->getFieldsStr()) ?>, ' `<?php echo htmlspecialchars($key) ?>` LIKE CONCAT("%",:<?php echo htmlspecialchars($key) ?>,"%") ',['<?php echo htmlspecialchars($key) ?>'=>$<?php echo htmlspecialchars($key) ?>],[$page, $count])->fetchAll()) ? $get  : false;<?php endif; ?> 
	}
<?php endforeach; ?> 

 <?php foreach($_SQL->keys as $key => $type): ?> 
	/**
	* list By <?php echo htmlspecialchars($key) ?> <?php echo htmlspecialchars($type) ?>  
	*/<?php $type=preg_match('/int/i', $type)?'int':'string'; ?> 
	public static function countIf<?php echo htmlspecialchars(ucfirst($key)) ?>(<?php echo htmlspecialchars($type) ?> $<?php echo htmlspecialchars($key) ?>)
    {<?php if($type==='int'): ?> 
        return Query::count('<?php echo htmlspecialchars($this->getTableName()) ?>', ['<?php echo htmlspecialchars($key) ?>'=>$<?php echo htmlspecialchars($key) ?>] );<?php elseif ($type==='string'): ?> 
		return Query::count('<?php echo htmlspecialchars($this->getTableName()) ?>', ' `<?php echo htmlspecialchars($key) ?>` LIKE CONCAT("%",:<?php echo htmlspecialchars($key) ?>,"%") ',['<?php echo htmlspecialchars($key) ?>'=>$<?php echo htmlspecialchars($key) ?>] );<?php endif; ?> 
	}
<?php endforeach; ?> 
}