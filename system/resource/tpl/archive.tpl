
<?php if($_SQL->namespace): ?>namespace <?php echo htmlspecialchars($_SQL->namespace) ?>; 
<?php endif; ?>

use archive\Archive;
use archive\Condition;
use archive\Statement;

class <?php echo htmlspecialchars($_SQL->name) ?> extends Archive {
    protected static $_fields=<?php echo htmlspecialchars($this->getFieldsStr()) ?>;
    // 是否为可用字段
    protected function _isField($name){
        return in_array($name,self::$_fields);
    }
    public function getTableName():string
    {
        return '<?php echo htmlspecialchars($this->getTableName()) ?>';
    }

}