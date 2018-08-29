#  Storage 

> *文件信息* suda\core\storage\Storage.php: 22~226


存储系统


## 描述



该类暂时无说明



## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|abstract  public  |[mkdirs](Storage/mkdirs.md) | 递归创建文件夹 |
|abstract  public  |[path](Storage/path.md) | 判断路径是否存在，不存在则创建，返回路径绝对地址 |
|abstract  public  |[abspath](Storage/abspath.md) | 返回路径绝对地址 |
|abstract  public  |[readDirFiles](Storage/readDirFiles.md) | 读取路径下面所有的文件 |
|abstract  public  |[cut](Storage/cut.md) | 截断路径的前部分 |
|abstract  public  |[readDirs](Storage/readDirs.md) | 读取路径下面的所有文件或者目录 |
|abstract  public  |[delete](Storage/delete.md) | 删除文件或者目录 |
|abstract  public  |[rmdirs](Storage/rmdirs.md) | 递归删除文件夹 |
|abstract  public  |[isEmpty](Storage/isEmpty.md) | 判断文件夹是否为空 |
|abstract  public  |[copydir](Storage/copydir.md) | 复制目录 |
|abstract  public  |[movedir](Storage/movedir.md) | 移动目录 |
|abstract  public  |[copy](Storage/copy.md) | 复制文件 |
|abstract  public  |[move](Storage/move.md) | 移动文件 |
|abstract  public  |[mkdir](Storage/mkdir.md) | 创建文件夹 |
|abstract  public  |[rmdir](Storage/rmdir.md) | 删除文件夹 |
|abstract  public  |[put](Storage/put.md) | 创建文件 |
|abstract  public  |[get](Storage/get.md) | 获取文件内容 |
|abstract  public  |[remove](Storage/remove.md) | 删除文件 |
|abstract  public  |[isFile](Storage/isFile.md) | 判断是否为文件 |
|abstract  public  |[isDir](Storage/isDir.md) | 判断是否为目录 |
|abstract  public  |[isReadable](Storage/isReadable.md) | 判断是否可读 |
|abstract  public  |[isWritable](Storage/isWritable.md) | 判断是否可写 |
|abstract  public  |[size](Storage/size.md) | 获取文件大小 |
|abstract  public  |[type](Storage/type.md) | 取得文件类型 |
|abstract  public  |[exist](Storage/exist.md) | 判断文件是否存在 |
|abstract  public  |[temp](Storage/temp.md) | 创建一个临时文件 |
|abstract  public  static|[getInstance](Storage/getInstance.md) |  |
 

## 例子

example