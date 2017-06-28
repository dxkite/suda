# 快速开始

1. 下载框架

  ```
  git clone https://github.com/DXkite/suda 
  ```
  并复制public文件夹的路径

2. 设置网站根目录     
  把服务器的网站根目录调整为刚刚下载的框架的 public 目录，把public目录作为网站的根目录。

**2.1 Linux用户注意** 在Linux上面安装的时候需要设置好权限组,来保证开发者和服务器都享有对应用的操作权限
```
sudo usermod -aG 服务器组名 开发者用户名
sudo chmod g+rw 应用目录（APP_DIR目录）
```
如：服务器设置的 Group ID 为：daemon 用户ID为：dxkite
则命令为:
```
sudo usermod -aG daemon dxkite
```

3. 访问网站