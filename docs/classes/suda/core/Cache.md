#  Cache 

> *文件信息* suda\core\Cache.php: 24~42


缓存系统


## 描述




由于访问数据库的效率远远低于访问文件的效率，所以我添加了一个文件缓存类，
你可以把常用的数据和更改很少的数据查询数据库以后缓存到文件里面，用来加快页面加载速度。


## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected  static  | cache | | 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  static|[newInstance](Cache/newInstance.md) |  |
|  public  static|[__callStatic](Cache/__callStatic.md) |  |
 

## 例子

example