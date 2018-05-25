#  Cache 

> *文件信息* suda\core\Cache.php: 23~148
文件缓存
## 描述

由于访问数据库的效率远远低于访问文件的效率，所以我添加了一个文件缓存类，
你可以把常用的数据和更改很少的数据查询数据库以后缓存到文件里面，用来加快页面加载速度。
## 常量列表
| 常量名  |  值|
|--------|----|
|CACHE_DEFAULT | 86400 | 


## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| public  static  | cache | | 
| public  static  | storage | | 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  static|[set](Cache/set.md) | 设置 |
|  public  static|[get](Cache/get.md) | 获取值 |
|  public  static|[delete](Cache/delete.md) | 删除值 |
|  public  static|[has](Cache/has.md) | 检测是否存在 |
|  public  static|[gc](Cache/gc.md) | 垃圾回收 |
|  public  static|[clear](Cache/clear.md) |  |
|  public  static|[enable](Cache/enable.md) |  |
|  public  static|[disable](Cache/disable.md) |  |
 

## 例子

example