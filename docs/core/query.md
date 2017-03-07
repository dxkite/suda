# Query 类 [继承自 archive\\Query ]

封装了数据查询，其中，#{数据表名} 会给数据表名加上前缀,默认全程使用PDO，不用担心SQL注入问题。


## 插入数据

插入数据，返回插入的ID（如果存在自增值的化）

```php
public static function insert(string $table, $values, array $binds=[]):int
```
### 说明
| 参数 | 说明 |
|------|------|
|table |为数据表名，会自动添加数据表前缀。|
|values| 为插入的值，可以为字符串或者MAP数组。|
|bind |为values中出现的模板控制待绑定字符。|

###  使用实例
```php
// 建议使用
$id = Query::insert('user',[
            'name'=>$name,
            'password'=>  $password ,
            'group'=>$group,
            'available'=>false,
            'email'=>$email,
            'ip'=>$ip,
            ]);
// 或
$id = Query::insert('user',"(`name`,`password`,`group`,`available`,`email`,`ip`)", [
            'name'=>$name,
            'password'=>  $password ,
            'group'=>$group,
            'available'=>false,
            'email'=>$email,
            'ip'=>$ip,
            ]);
```

以上调用会编译成类似如下模板

```sql
INSERT INTO `mc_user` (`name`,`password`,`group`,`available`,`email`,`ip`) VALUES (:name,:password,:group,:available,:email,:ip); 
```

## where查询数据
查询后会返回一个 `archive\Query ` 的实例对象，通过 fetch 或者 fetchAll 获取查询结果。

```php
public static function where(string $table, $wants='*', $condition='1', array $binds=[], array $page=null, bool $scroll=false)
```
### 参数说明

| 参数 | 说明 |
|------|------|
|table | 数据表名 | 
|wants | 为查询的字段，可以为字符串如`"field1,field2"` 或者数组 `[ "field1","field2" ]`； 建议使用数组模式。|
|condition | 为查询的条件 ，可以为字符串 或者数组 ， 建议使用数组模式。会自动添加WHERE 条件 |
| bind | 查询字符串中绑定的数据|
| page | 分页查询，接受数组 ，格式为： [为分页的页数,每页长度]  |
| scroll | 滚动查询，一次取出一条记录 |


### 例子

```php
$fetch=Query::where('user', ['id', 'name', 'email', 'available', 'avatar', 'ip'], '1', [], [$page, $count])->fetchAll();
```

```sql
SELECT `id`, `name`,`email`,`available`, `avatar`, `ip` FROM mc_user WHERE 1; 
```



## select查询数据
查询后会返回一个 `archive\Query ` 的实例对象，通过 fetch 或者 fetchAll 获取查询结果。

```php
public static function select(string $table, $wants ,  $conditions, array $binds=[], array $page=null, bool $scroll=false)
```
### 参数说明

| 参数 | 说明 |
|------|------|
|table | 数据表名 | 
|wants | 为查询的字段，可以为字符串如`"field1,field2"` 或者数组 `[ "field1","field2" ]`； 建议使用数组模式。|
|condition | 为查询的条件 ，可以为字符串 或者数组 ， 建议使用数组模式。|
| bind | 查询字符串中绑定的数据|
| page | 分页查询，接受数组 ，格式为： [为分页的页数,每页长度]  |
| scroll | 滚动查询，一次取出一条记录 |

### 例子
```php
$fetch=Query::select('user_group', 'auths', ' JOIN `#{user}` ON `#{user}`.`id` = :id  WHERE `user` = :id  or `#{user_group}`.`id` =`#{user}`.`group` LIMIT 1;', ['id'=>$uid])->fetch()
```

```sql
SELECT auths FROM `mc_user_group`  JOIN `mc_user` ON `mc_user`.`id` = :id  WHERE `user` = :id  or `mc_user_group`.`id` =`mc_user`.`group` LIMIT 1; 
```


## 更新数据
返回影响的函数

```php
public static function update(string $table, $set_fields,  $where='1', array $binds=[]):int
```

### 参数说明

| 参数 | 说明 |
|------|------|
|table | 数据表名 | 
|set_fields | 为设置的字段，使用键值数组式设置值。|
| where | 为更新的条件 ，可以为字符串 或者数组 ， 建议使用数组模式。|
| bind | 查询字符串中绑定的数据|

### 例子
```php
Query::update('user_token', 'expire = :time , token=:new_token,value=:refresh', 'id=:id AND UNIX_TIMESTAMP() < `time` + :alive AND value = :value ', ['id'=>$id, 'value'=>$value, 'new_token'=>$new, 'refresh'=>$refresh, 'time'=>time() + $get['beat'], 'alive'=>$get['alive']]);
```
```sql
UPDATE `mc_user_token` SET expire = :time , token=:new_token,value=:refresh  WHERE id=:id AND UNIX_TIMESTAMP() < `time` + :alive AND value = :value ; 
```

## 删除数据 
返回影响的函数

```php
public static function delete(string $table, $where='1', array $binds=[]):int
```

### 参数说明

| 参数 | 说明 |
|------|------|
|table | 数据表名 | 
| where | 为删除的条件 ，可以为字符串 或者数组 ， 建议使用数组模式。|
| bind | 查询字符串中绑定的数据|