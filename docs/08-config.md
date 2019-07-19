# 配置文件说明

在suda中，配置文件默认使用 json 格式作为配置文件，但是也可以使用 `.php`、`.ini`、`.yml` 格式的文件做配置文件，一下提到的配置文件只说明文件名称，如 `manifest` 可以是 `manifest.json`，还可以是 `manifest.yml`，优先级：`.yml` > `.json` > `.php` > `.ini`

**使用YAML作为配置需要依赖第三方库，请自行包含，推荐使用PHP扩展或者使用symfony/yaml来解析**

资源位置描述说明：

- **@app** 表示app目录
- **@app-resouce** 表示app的资源目录，默认 `app/resource`

## @app/manifest 配置

| 键名 | 键值类型 | 默认值 | 说明 |
|---|----|-----|----|
| name | string | | 应用名称|
| version | string | |  应用版本号|
| locale | string | zh-cn | 应用采用的语言包 |
| style | string | default | 采用的样式  |
| resource | string | ./resource | 资源路径 |
| route-group | array | ['default'] | 启用的路由组 |
| import | array | null | 全局共享库 |
| module.load | array | 全部模块 | 默认加载的模块 |
| module.active | array | 全部模块 | 默认激活的模块 |
| module.reachable | array | 全部模块 | 默认可以访问的模块 |

