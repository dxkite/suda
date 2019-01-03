![Suda@PHP7](docs/imgs/logo.png)
---------------------------------

[![Latest Stable Version](https://poser.pugx.org/dxkite/suda/v/stable)](https://packagist.org/packages/dxkite/suda)
[![PHP >= 7.2](https://img.shields.io/badge/php-%3E%3D7.2-8892BF.svg)](https://php.net/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/dxkite/suda/badges/quality-score.png)](https://scrutinizer-ci.com/g/dxkite/suda)
[![Total Downloads](https://poser.pugx.org/dxkite/suda/downloads)](https://packagist.org/packages/dxkite/suda) 
[![License](https://poser.pugx.org/dxkite/suda/license)](https://packagist.org/packages/dxkite/suda)


 
[中文](README.zh.md)

a ligth and fast web framework based on php7

## Features

- modularization
- fast url route
- sql query helper
- simple database tools
- page inner hooks
- smarty html template
- distributed routing

## Install

use composer to install stable version:

```bash
composer global require 'dxkite/suda:2.*'
```

use composer to install develop version:

```bash
composer global require 'dxkite/suda:dev-dev'
```

then you can use `suda-cli` in your console.

## Create Project

in you console, type `suda-cli new` command to create a project with suda

```bash
suda-cli new /path/to/project
```

## Document

- [Classes Document](docs/README.md)  
- [Performance Test](docs/test.en.md)
- [Releases Logs](RELEASE.md)
