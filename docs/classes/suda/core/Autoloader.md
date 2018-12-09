#  Autoloader 

> *文件信息* suda\core\Autoloader.php: 22~215


自动加载控制器


## 描述



控制按照一定的规则自动加载文件或者类
 
## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
 | protected  static  | namespace | 默认命名空间| 
 | protected  static  | includePath | 包含路径| 
## 方法

 
| 可见性 | 方法名 | 说明 |
|--------|-------|------|
 |  public  static|[realName](Autoloader/realName.md) | 将JAVA，路径分割转换为PHP分割符 |
 |  public  static|[realPath](Autoloader/realPath.md) | 获取真实或者虚拟存在的地址 |
 |  public  static|[formatSeparator](Autoloader/formatSeparator.md) |  |
 |  public  static|[register](Autoloader/register.md) |  |
 |  public  static|[import](Autoloader/import.md) |  |
 |  public  static|[classLoader](Autoloader/classLoader.md) |  |
 |  public  static|[getClassPath](Autoloader/getClassPath.md) |  |
 |  public  static|[addIncludePath](Autoloader/addIncludePath.md) |  |
 |  public  static|[getIncludePath](Autoloader/getIncludePath.md) |  |
 |  public  static|[getNamespace](Autoloader/getNamespace.md) |  |
 |  public  static|[setNamespace](Autoloader/setNamespace.md) |  |
 |  public  static|[parsePath](Autoloader/parsePath.md) | 将路径转换成绝对路径 |
## 例子

example