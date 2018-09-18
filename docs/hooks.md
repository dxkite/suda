| 2.0.1 | 2.0.2 | 说明 |
|-----|----|-----|
| system:debug::start | suda:system:debug::start | Suda系统开启Debug |
| system init | suda:system:init | 系统初始化 |
| core:loadManifast | suda:system:loadManifast |  系统加载manifast |
| Manager:loadCompile::before | suda:template:loadCompile::before | 加载模板编译器 **之前** |
| template:theme::change | suda:template:changeTheme | 模板切换样式 |
| Application:init | suda:application:init | 系统应用初始化 |
| Router:prepareRouterInfo | suda:route:prepare | 系统准备路由 |
| Router:dispatch::before | suda:route:dispatch::before | 系统分配路由 **之前** |
| Router:filter | suda:route:dispatch::filter | 系统分配路由 **过滤** |
| Router:runRouter::before | suda:route:run::before | 系统运行路由 **之前** |
| Router:extra | suda:route:run::extra | 系统运行额外路由 |
| system:404 | suda:system:error::404 | 系统404错误 |
| Application:active | suda:application:active | 系统激活路由 |
| template:SudaCompiler:init | suda:template:compiler:init | 系统编译器初始化 |
| Manager:prepareResource::before | suda:template:resource:prepare::before | 系统准备资源之前 |
| Template:render::before | suda:template:render::before | 系统模板渲染之前 |
| Router:runRouter::after | suda:route:run::after | 系统运行路由之后 |
| system:shutdown::before | suda:system:shutdown::before | 系统关闭之前 |
| system:shutdown | suda:system:shutdown | 系统关闭时 |
| system:debug::end | suda:system:debug::stop | 系统停止Debug |