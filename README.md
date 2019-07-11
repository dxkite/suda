# suda: nebula

[![Latest Stable Version](https://poser.pugx.org/dxkite/suda/v/stable)](https://packagist.org/packages/dxkite/suda)
[![PHP >= 7.2](https://img.shields.io/badge/php-%3E%3D7.2-8892BF.svg)](https://php.net/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dxkite/suda/badges/quality-score.png)](https://scrutinizer-ci.com/g/dxkite/suda)
[![Total Downloads](https://poser.pugx.org/dxkite/suda/downloads)](https://packagist.org/packages/dxkite/suda)
[![License](https://poser.pugx.org/dxkite/suda/license)](https://packagist.org/packages/dxkite/suda)

高性能、轻量化Web框架，文档 [dxkite.github.io/suda](https://dxkite.github.io/suda/)

## 特性

- 标准化请求以及响应处理
- 响应包装器
- 事件监控器
- 读写分离与ORM
- 多缓存支持
- 模块化支持
- 标准化日志接口

## 运行

### PHP 运行

```
git clone https://github.com/dxkite/suda .
php -S 127.0.0.1:8080 -t ./public 
```

### PHP+Swoole 运行 (推荐)

```
git clone https://github.com/dxkite/suda .
php server.php 127.0.0.1:8080
```

### Web服务器运行

将WEB更目录调整到 `public`，如果需要URL重写，`nginx` 配置需包含 `nginx.conf`, `Apache` 无需配置