![Suda@PHP7](docs/imgs/logo.png)
---------------------------------

[![Latest Stable Version](https://poser.pugx.org/dxkite/suda/v/stable)](https://packagist.org/packages/dxkite/suda) 
[![Latest Unstable Version](https://poser.pugx.org/dxkite/suda/v/unstable)](https://packagist.org/packages/dxkite/suda) 
[![Total Downloads](https://poser.pugx.org/dxkite/suda/downloads)](https://packagist.org/packages/dxkite/suda) 
[![License](https://poser.pugx.org/dxkite/suda/license)](https://packagist.org/packages/dxkite/suda)

Suda框架是一款基于PHP7开发的轻量级PHP框架。

## 特性

- 应用功能模块化开发
- 自动化应用构建
- URL路由美化
- SQL查询辅助
- 简单的日志和调试工具
- 内置页面插件机制 
- 可编译的模板语言
- 简化的数据表操作
- 分布式路由

## 基本使用

### 步骤一 下载框架代码

#### 从github克隆

```bash
git clone https://github.com/DXkite/suda  suda
```
#### 作为git子模块克隆

```bash
git submodule add https://github.com/DXkite/suda
```

### 步骤二 复制基本配置文件

```bash
cp -R ./suda/system/resource/project/* .
```
### 步骤三 调整根目录至 `public` 

> **Linux用户注意** 请保证目录的可读写性，参考配置：
> ```bash
> sudo usermod -aG service_group user_name
> sudo chmod g+rw application_directory
> sudo chmod g+rw document_directory
> ```

### 步骤四

访问public/dev.php文件，框架会自动创建应用

## 文档说明

[自动文档](docs/README.md)    

## Suggest Application Modules 
- function modules
    - a function module
        - admin router (*module functions admin*)
        - simple router (*user web interface*)
    - another function module
- install module (*for install this application*)
- admin module (*the admin panel*)
- suda base admin module *(admin suda `auto create when init this application`)*

## Historys Or Demos

- [DxSite](https://github.com/DXkite/DxSite)   
- [ATD_MINI](https://github.com/DXkite/atd_mini)   
- [ATD3CN](https://github.com/DXkite/atd3.cn)   

----------------
