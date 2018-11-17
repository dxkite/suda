![Suda@PHP7](docs/imgs/logo.png)
---------------------------------

[![Latest Stable Version](https://poser.pugx.org/dxkite/suda/v/stable)](https://packagist.org/packages/dxkite/suda) 
[![Latest Unstable Version](https://poser.pugx.org/dxkite/suda/v/unstable)](https://packagist.org/packages/dxkite/suda) 
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
