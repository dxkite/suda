![Suda@PHP7](docs/imgs/logo.png)
---------------------------------

[![Latest Stable Version](https://poser.pugx.org/dxkite/suda/v/stable)](https://packagist.org/packages/dxkite/suda)
[![PHP >= 7.2](https://img.shields.io/badge/php-%3E%3D7.2-8892BF.svg)](https://php.net/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dxkite/suda/badges/quality-score.png)](https://scrutinizer-ci.com/g/dxkite/suda)
[![Total Downloads](https://poser.pugx.org/dxkite/suda/downloads)](https://packagist.org/packages/dxkite/suda) 
[![License](https://poser.pugx.org/dxkite/suda/license)](https://packagist.org/packages/dxkite/suda)

[English](README.md)

Suda框架是一款基于PHP7开发的轻量级，高性能，模块化Web框架。

## 特性

- 应用功能模块化开发
- URL路由美化
- SQL查询辅助
- 简单的日志和调试工具
- 内置页面插件机制 
- 可编译的模板语言
- 简化的数据表操作
- 分布式路由

## 安装

使用Composer安装稳定版本

```bash
composer global require 'dxkite/suda:2.*'
```

也可以安装开发版本，**推荐**

```bash
composer global require 'dxkite/suda:dev-dev'
```

## 创建项目

使用命令，然后将网站根目录设置在 public 文件夹。

```bash
suda-cli new /path/to/project
```

## 文档说明

- [类文档](docs/README.md)    
- [性能测试](docs/test.md)
- [版本历史](RELEASE.md)

##  PHP版本特性说明

项目内已经使用的特性说明

| 特性 |  版本 | 项目使用情况 | 备注 | 
|-----|------|----|---|
| 允许重写抽象方法 | 7.2 | × | 可能会使用 |
| PDOStatement::debugDumpParams() | 7.2 | × | 可能会使用 |
| 可为空（Nullable）类型 | 7.1  | √ | |
| Symmetric array destructuring | 7.1 | √ | |
| list() 支持键名 | 7.1 | √ |  |
| 短数组声明 | 7.0 | √ |  |
| 返回值类型声明 |7.0 | √ |  |
| null合并运算符 |7.0 | √ |  |
| 匿名类 | 7.0 | √ |  |
