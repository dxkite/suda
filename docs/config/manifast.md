# Manifast 项目

- `namespace` 配置基础的空间名
- `version` 配置版本号
- `application` 配置应用启动时创建的实例，默认 （`suda\core\Application`）
- `modules` 配置启动时加载的模块
- `reachable` 配置后可以访问的模块
- `language` 配置语言包
- `url`
    - `mode` URL解析模式
    - `beautify` URL美化
    - `rewrite` URL 重写
- `import` 导入的文件夹（用于自动导入PHP）

    采用键值对的形式，为  "根命名空间":"相对文件夹"
