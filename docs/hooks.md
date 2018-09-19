# 钩子列表

| 钩子名称 | 参数 |行为描述 |
|--------|-----|-----|
| suda:application:active | 激活的模块名 | 当系统激活某个模块时调用 |
| suda:application:init | 无 | 系统初始化时调用 |
| suda:database:connect::before | 无 | 系统连接数据库时调用 | 
| suda:module:load | 无 | 系统载入模块时调用 |
| suda:module:load:on:: | 无 |系统载入某模块时调用 |
| suda:route:dispatch::before | Requst 请求类  | 处理路由之前调用，过滤路由 |
| suda:route:dispatch::error | 无 | 路过滤时调用 |
| suda:route:dispatch::extra | 无 | 路由匹配找不到时调用|
| suda:route:dispatch::filter | 模块名,Mapping对象 | 匹配URL时调用 |
| suda:route:prepare | 无 | 在载入路由配置时调用 |
| suda:route:run::after |  Mapping对象 | 在路由响应运行之后调用 | 
| suda:route:run::before |  Mapping对象 | 在路由响应运行之前调用 | 
| suda:system:debug::start | 无 | 调试初始化时调用 |
| suda:system:debug::stop | 无 | 调试停止时调用 |
| suda:system:error::404 | 无 | 404错误时调用 |
| suda:system:exception::display | 异常 | 显示异常时调用，用于自定义异常显示 |
| suda:system:init | 无 | 系统初始化时调用 |
| suda:system:load-manifast | 无 | 系统加载App的 manifast 时调用 |
| suda:system:shutdown | 无 | PHP关闭时调用 |
| suda:system:shutdown::before | 无 | 在PHP将要关闭时调用 |
| suda:system:display:json | JSON | 在JSON发送到浏览器之前调用 |
| suda:template:change-theme | 模板名 | 在切换模板时调用 |
| suda:template:compile::before | 模板编译类 | 编译器编译模板时调用 |
| suda:template:compile::init | 模板编译类 | 编译器懒初始化时调用 |
| suda:template:load-compile::before | 无 | 加载编译器时调用 |
| suda:template:render::before | HTML | 模板渲染发送到浏览器之前调用 |
| suda:template:resource-prepare::before | 模块名 | 系统准备模板资源前调用 |
