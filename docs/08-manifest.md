# manifest 配置参数

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
