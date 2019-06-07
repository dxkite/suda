# 数据表

在框架中，在进行数据表操作之前先创建数据表描述类，数据表描述类负责的功能为描述数据表结构，为了保证性能以及减少不必要的查询，数据表的创建工作为手动创建，当框架提供了一个基于数据表描述类的 MySQL表创建类。

## 定义数据表

数据表类的创建继承类 `suda\application\database\Table`，同时需要实现 `onCreateStruct` 方法，用来描述连接的数据表结构，实例如下：


```php
namespace suda\welcome\table;

use suda\database\struct\TableStruct;
use suda\application\database\Table;

class HelloTable extends Table
{
    public function __construct()
    {
        parent::__construct('hello');
    }

    public function onCreateStruct(TableStruct $struct): TableStruct
    {
        return $struct->fields([
            $struct->field('id', 'bigint', 20)->auto()->primary(),
            $struct->field('name', 'varchar', 80),
        ]);
    }
}

```

在使用如上类之前，需要创建数据表 `hello`，其MySQL的DDL为如下（说明，为了保证utf-8正常的存储，请使用 `utf8mb4` 作为数据表的编码：

```SQL
CREATE TABLE `dx_hello` (
	`id` BIGINT(20) NULL AUTO_INCREMENT,
	`name` VARCHAR(80) NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

注意，`dx_` 为数据表前缀，在连接配置时设置 `(data-source文件)`。

## 字段支持的DDL方法

**index()**

设置为索引

**key()**

设置为键索引

**auto()**

设置自增索引

**primary()**

设置为主键

**unique()**

设置为唯一键

**default($value)**

设置 value 为默认值

**null(bool)**

设置是否可空

## 实例

```php
$struct->field('id', 'bigint', 20)->auto()->primary() // 设置自增主键
```

