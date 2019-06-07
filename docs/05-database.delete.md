# 删除记录

需要删除，使用 delete 函数
例如

```php
$table = new HelloTable();
try {
    $effectRows = $table->delete(['id' => new \ArrayObject([1, 2, 3])])->rows();
} catch (\ReflectionException $e) {
} catch (SQLException $e) {
}
```

说明：`delete` 函数的参数与 `where` 解析方式一致，如果不输入则删除全部