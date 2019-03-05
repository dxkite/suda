```php

$sourceset = new DataSourceSet();

// 读写分离
$sourceset->add(DataSource::connect('mysql', $config_master));
$sourceset->addWrite(DataSource::connect('mysql', $config_master));
$sourceset->addWrite(DataSource::connect('mysql', $config_master));
$sourceset->addRead(DataSource::connect('sqlite', $config_slave_one));
$sourceset->addRead(DataSource::connect('orcale', $config_slave_two));

// 表结构定义
$struct = new TableStruct;

$struct->fields(
    $struct->fields('id', 'bigint', 20)->auto()->primary(),
    $struct->fields('name', 'varchar', 80),
);

// 表访问对象
$table = new Table('user_table', $struct, $sourceset)
$middleware = new TableMiddleware;
// 中间件
$table->middleware($middleware); // 数据输入/输出之前处理

try {
    $table->begin();
    $table->read((new ReadStatement($table))->want('id','name')->where('id = ?', $id)->limit(1, 10));
    $table->write((new ReadStatement($table))->set('name', 'dxkite')->where('id = ?', $id));
    $table->commit();
} catch (OrmException $e) {
    $table->rollBack();
    var_dump($e);
}

```