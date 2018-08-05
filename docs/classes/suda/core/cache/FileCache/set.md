# FileCache::set
设置
> *文件信息* suda\core\cache\FileCache.php: 28~168
## 所属类 

[FileCache](../FileCache.md)

## 可见性

  public  
## 说明


由于访问数据库的效率远远低于访问文件的效率，所以我添加了一个文件缓存类，
你可以把常用的数据和更改很少的数据查询数据库以后缓存到文件里面，用来加快页面加载速度。

## 参数

| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
| name |  string | 无 |  名 |
| expire |  int | null |  过期时间 |

## 返回值
类型：bool
无

## 例子

example