# suda 应用程序 

## 目录结构

从上一步中运行后可以在项目目录得到一个 `app` 文件夹， 文件夹中即为项目运行文件内容
结构说明如下：

```
$ tree
.
├── data   数据日志记录
│   ├── extract-module .mod 模块解压目录
│   ├── logs
│   │   ├── dump  项目异常 Dump
│   │   ├── latest.log 项目运行日志
│   │   └── zip 项目运行日志打包
│   └── template 模板缓存目录
├── manifast.json 项目基础配置文件
├── modules 模块文件夹
│   ├── config.json 模块启用属性
│   └── welcome   模块内容文件夹
│       ├── module.json 模块属性配置文件
│       ├── resource  模块资源文件夹
│       │   ├── config   模块配置文件夹
│       │   │   └── route.json 模块运行路由
│       │   ├── locale  模块语言文件
│       │   │   └── zh-cn.json zh-cn 语言
│       │   └── template 模块模板
│       │       └── default 样式 default 模板目录
│       │           ├── helloworld.tpl.html   模板文件
│       │           ├── layout.tpl.html
│       │           ├── simple.tpl.html
│       │           ├── static  模板静态资源目录
│       │           │   └── style.css
│       │           └── welcome.tpl.html
│       ├── share 共享代码文件目录，该目录下的代码可以被其他模块访问
│       └── src 运行代码目录 该模块下代码只能当前模块使用，对其他模块不可见
│           └── SimpleResponse.php  运行的PHP代码文件
└── resource  App全局配置资源
    └── config   全局配置文件
        └── data-source.json  数据源配置文件，用于配置数据库
``` 


## 模块机制

Suda的基本功能都是通过模块实现的，一个基本的模块包括了如下几个部分，分别控制了基本的预定义功能：

- `resource` 模块的资源
  - `config`
     - `route*` 路由信息
  - `locale` 语言I18N
  - `template` 模板
- `share` 模块的共享代码
- `src` 模块的私有代码
- `module.json` 模块配置文件

模块的目录由 `manifast.json` 文件可以指定配置模块存放的目录，不仅限于一个目录，默认的模块目录为 `modules`，模块的启用由 `modules` 下面的 `config.json` 控制是否启用，模块的状态有四种

- `default`  - 模块不加载
- `loaded` - 模块加载到系统中，能够使用模块的资源和共享代码
- `reachable` - 模块开放访问请求，能够使用模块的资源和共享代码
- `active`  - 模块的路由被访问的时候可以使用私有代码

下面以Demo的模块做解释

目录： Demo的程序包含了一个模块 `suda/welcome`，目录位置为 `app/modules/welcome`
配置： `modules` 文件夹下的模块启用配置为如下

**文件：** `app/modules/config.json`

```json
{
    "loaded": [
        "welcome"
    ],
    "reachable": [
        "welcome"
    ]
}
```

即，加载并使模块可以访问，*如果模块目录文件夹下没有配置文件，默认全部启用。*

## 模块资源引用

模块的资源在Suda系统中使用特殊的资源标识符访问，具体说明如下：

`[namespace/]module-name[:version][@group]:`

资源基本由此指定，如：

- `welcome:1.0:simple` 使用 `welcome` 模块 `1.0` 版本的资源 `simple`
    suda 会尝试将不全的资源复原为完整的资源再引用
- `index` 根据环境自动推导所在模块以及版本等信息

