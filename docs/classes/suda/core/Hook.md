#  Hook 

> *文件信息* suda\core\Hook.php: 23~228


系统钩子，监听系统内部一些操作并载入一些自定义行为


## 描述



该类暂时无说明


## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected  static  | hooks | | 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  static|[loadConfig](Hook/loadConfig.md) |  |
|  public  static|[load](Hook/load.md) |  |
|  public  static|[listen](Hook/listen.md) | 注册一条命令 |
|  public  static|[register](Hook/register.md) | 注册一条命令 |
|  public  static|[add](Hook/add.md) | 添加命令到底部 |
|  public  static|[addTop](Hook/addTop.md) | 添加命令到顶部 |
|  public  static|[remove](Hook/remove.md) | 移除一条命令 |
|  public  static|[exec](Hook/exec.md) | 运行所有命令 |
|  public  static|[execIf](Hook/execIf.md) | 运行，遇到返回指定条件则停止并返回true |
|  public  static|[execNotNull](Hook/execNotNull.md) | 运行所有命令返回第一个非空值 |
|  public  static|[execTop](Hook/execTop.md) | 运行最先注入的命令 |
|  public  static|[execTail](Hook/execTail.md) | 运行最后一个注入的命令 |
|  protected  static|[call](Hook/call.md) |  |
 

## 例子

example