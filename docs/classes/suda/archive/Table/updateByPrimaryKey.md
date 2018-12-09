# Table::updateByPrimaryKey
通过主键更新元素
> *文件信息* suda\archive\Table.php: 25~567
## 所属类 

[Table](../Table.md)

## 可见性

  public  
## 说明


用于提供对数据表的操作

## 参数

 
| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
 | value |  [type] | 无 |  主键 |
 | values |  [type] | 无 |  待更新的数据 |
## 返回值
 
类型：integer
 影响的行数
## 例子

主键的值为不定量，有时候有多个主键

#### 单主键操作
当用户表中只定义了一个主键的时候

```php
$table->updateByPrimaryKey($key,['name'=>$name,'value'=>$value]);
```

#### 多主键操作

当用户表中只定义了多个主键的时候

```php
$table->updateByPrimaryKey(['key1'=>$key1,'key2'=>$key2],['name'=>$name,'value'=>$value]);
```