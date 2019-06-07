# 更新记录

需要更新记录，使用 write 函数与 where 函数配合即可
例如

```php
$table = new HelloTable();
try {
    $effectRows = $table->write([
        'name' => 'update',
    ])->where(['id' => new \ArrayObject([1,2,3])])->rows();
} catch (\ReflectionException $e) {
} catch (SQLException $e) {
}
```

