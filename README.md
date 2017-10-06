#  Suda PHP Framework
suda is a simple web framework for **PHP7**.

## Functions

- [x] Module Based Application
- [x] Route Mapping
- [x] SQL Query Helper
- [x] Debugger & Logger
- [x] Page Hook 
- [x] Smarty Like Template
- [x] Response
- [x] Table Object


## Get Start

### Step1 get suda source code

#### clone from git 

```bash
git clone https://github.com/DXkite/suda  suda
```
#### clone as a git submodule

```bash
git submodule add https://github.com/DXkite/suda
```

### Step2 copy file necessary

```bash
cp -R ./suda/system/resource/project/* .
```
### Step3 change document root to `public` 

change web service configuration make `public` directory as the document root.

> **For Linux User** To make sure the web server has the permission to modify web appliction directorys.
> ```bash
> sudo usermod -aG service_group user_name
> sudo chmod g+rw application_directory
> sudo chmod g+rw document_directory
> ```

### Step4 make the template application

visit the localhost to help the framework to create a  template web application.

## Document

[Document](docs/readme.md)    
[Route](docs/tools/router.md)

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
