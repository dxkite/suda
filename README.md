![Suda@PHP7](docs/imgs/logo.png)
---------------------------------

[![Latest Stable Version](https://poser.pugx.org/dxkite/suda/v/stable)](https://packagist.org/packages/dxkite/suda) 
[![Latest Unstable Version](https://poser.pugx.org/dxkite/suda/v/unstable)](https://packagist.org/packages/dxkite/suda) 
[![Total Downloads](https://poser.pugx.org/dxkite/suda/downloads)](https://packagist.org/packages/dxkite/suda) 
[![License](https://poser.pugx.org/dxkite/suda/license)](https://packagist.org/packages/dxkite/suda)

[English](README.en.md)

Suda框架是一款基于PHP7开发的轻量级PHP框架。

## 特性

- 应用功能模块化开发
- URL路由美化
- SQL查询辅助
- 简单的日志和调试工具
- 内置页面插件机制 
- 可编译的模板语言
- 简化的数据表操作
- 分布式路由


## Dokcer 安装

### 一键使用
一键安装环境，并在 `~/app` 创建可执行的应用 App

```bash
sudo docker-compose up -d
```

### 自定义应用位置

```bash
sudo docker build -t suda-system .
sudo docker run -p 80:80 -v [应用目录，绝对路径]:/app suda-system 
```

## 手动安装

### 步骤一：安装框架
选择工作目录，打开命令窗口,输入以下命令。

```bash
git init
git submodule add https://github.com/DXkite/suda
cp -R ./suda/system/resource/project/* .
```

### 步骤二：配置服务器

讲网站的更目录指定到 `public` 目录

### 步骤三

访问public/dev.php文件，框架会自动创建应用

> **Linux用户注意** 请保证目录的可读写性，参考配置：
> ```bash
> sudo usermod -aG service_group user_name
> sudo chmod g+rw application_directory
> sudo chmod g+rw document_directory
> ```

## 文档说明

- [自动文档](docs/README.md)    
- [Release](RELEASE.md)

## Historys Or Demos

- [DxSite](https://github.com/DXkite/DxSite)   
- [ATD_MINI](https://github.com/DXkite/atd_mini)   
- [ATD3CN](https://github.com/DXkite/atd3.cn)   

----------------
