# Storage 类

文件储存类。

| 方法  | 说明 |
|-------|-------|
| public static function mkdirs(string $dir, int $mode=0777):bool | 递归创建文件夹 |
| public static function path(string $path) | 获取路径的绝对路径并返回，如果不存在则创建 |
| public static function readDirFiles(string $dirs,  bool $repeat=false, string $preg='/^.+$/', bool $cut=false):array | 读取文件夹下面的文件 |
| public static function cut(string $path, string $basepath=ROOT_PATH) |   截取路径 |
| public static function readDirs(string $dirs, bool $repeat=false, string $preg='/^.+$/'):array| 读取文件夹下面的内容 |
| public static function rmdirs(string $dir) | 递归删除文件夹 |
| public static function copydir(string $src, string $dest) | 复制文件夹 |
| public static function copy(string $source, string $dest):bool | 复制文件|
| public static function move(string $src, string $dest):bool | 移动文件  |
| public static function mkdir(string $dirname, int $mode=0777):bool | 传建文件夹 |
| public static function rmdir(string $dirname):bool | 删除文件夹 |
| public static function put(string $name, $content, int $flags = 0):bool | 写入文件 |
| public static function get(string $name):string | 读取文件 |
| public static function remove(string $name) : bool | 删除文件 |
| public static function isFile(string $name):bool | 判断是否为文件 |
| public static function isDir(string $name):bool | 判断是否为文件夹|
| public static function isReadable(string $name):bool | 目录是否可读 |
| public static function isWritable(string $name):bool | 目录是否可写 |
| public static function size(string $name):int | 获取文件大小 |
| public static function download(string $url, string $save):int | 下载文件 |
| public static function type(string $name):int | 获取文件类型 |
| public static function exist(string $name, array $charset=[]) | 判断文件是否存在（中英文皆可）  |