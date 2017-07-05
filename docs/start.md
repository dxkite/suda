# 快速开始使用
1. 下载框架
在网站根目录下打开控制台，执行命令
```
git clone https://github.com/DXkite/suda  suda
```
完成后，把`suda/resource`下的`public`**文件夹**复制到**当前目录**

2. 调整网站根目录     

把服务器的网站根目录调整为刚刚的`public`目录，把*public目录*作为*网站的根目录*。

**Linux用户注意** 在Linux上面安装的时候需要设置好权限组,来保证开发者和服务器都享有对应用的操作权限
```
sudo usermod -aG 服务器组名 开发者用户名
sudo chmod g+rw 应用目录（APP_DIR目录）
```
如：服务器设置的 Group ID 为：daemon 用户ID为：dxkite
则命令为:
```
sudo usermod -aG daemon dxkite
```
3. 访问网站 localhost