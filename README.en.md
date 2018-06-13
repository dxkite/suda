![Suda@PHP7](docs/imgs/logo.png)
---------------------------------

[![Latest Stable Version](https://poser.pugx.org/dxkite/suda/v/stable)](https://packagist.org/packages/dxkite/suda) 
[![Latest Unstable Version](https://poser.pugx.org/dxkite/suda/v/unstable)](https://packagist.org/packages/dxkite/suda) 
[![Total Downloads](https://poser.pugx.org/dxkite/suda/downloads)](https://packagist.org/packages/dxkite/suda) 
[![License](https://poser.pugx.org/dxkite/suda/license)](https://packagist.org/packages/dxkite/suda)

[中文](README.md)

This is a ligth fast web framework based on php7

## Features

- modularization
- fast url route
- sql query helper
- simple database tools
- page inner hooks
- smarty html template
- distributed routing

## Dokcer Install

### Simple

install docker env and create application at `~/app`

```bash
sudo docker-compose up -d
```

### Customized application path

```bash
sudo docker build -t suda-system .
sudo docker run -p 80:80 -v [abslute path]:/app suda-system 
```

## Legacy install

### Clone Framework

ues this bash command to clone this repo into your workspace

```bash
git init
git submodule add https://github.com/DXkite/suda
cp -R ./suda/system/resource/project/* .
```

### Confugure the website document root

confugiue the website document root to the `/public`


### Genarate Base Application

View page `host:port/dev.php` to get a demo application


> **For Linux** check the permission
> ```bash
> sudo usermod -aG service_group user_name
> sudo chmod g+rw application_directory
> sudo chmod g+rw document_directory
> ```

## Document

- [Auto Document](docs/README.md)    
- [Release](RELEASE.md)

## Historys Or Demos

- [DxSite](https://github.com/DXkite/DxSite)   
- [ATD_MINI](https://github.com/DXkite/atd_mini)   
- [ATD3CN](https://github.com/DXkite/atd3.cn)   

----------------
