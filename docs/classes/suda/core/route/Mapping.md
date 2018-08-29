#  Mapping 

> *文件信息* suda\core\route\Mapping.php: 23~584





## 描述



该类暂时无说明
## 常量列表
| 常量名  |  值|
|--------|----|
|ROLE_ADMIN | 0 | 
|ROLE_SIMPLE | 1 | 


## 变量列表
| 可见性 |  变量名   | 说明 |
|--------|----|------|
| protected    | method | | 
| protected    | url | | 
| protected    | mapping | | 
| protected    | callback | | 
| protected    | template | | 
| protected    | source | | 
| protected    | module | | 
| protected    | name | | 
| protected    | role | | 
| protected    | types | | 
| protected    | param | | 
| protected    | value | | 
| protected    | buffer | | 
| protected    | host | | 
| protected    | port | | 
| protected    | scheme | | 
| protected    | antiPrefix | | 
| protected    | hidden | | 
| protected    | dynamic | | 
| protected  static  | urlType | | 
| public  static  | current | | 

## 方法

| 可见性 | 方法名 | 说明 |
|--------|-------|------|
|  public  |[__construct](Mapping/__construct.md) |  |
|  public  |[match](Mapping/match.md) |  |
|  public  |[matchUrlValue](Mapping/matchUrlValue.md) |  |
|  public  |[run](Mapping/run.md) |  |
|  protected  |[getResponseObStatus](Mapping/getResponseObStatus.md) |  |
|  public  |[build](Mapping/build.md) |  |
|  public  |[is](Mapping/is.md) | 判断路由是否为指定路由 |
|  public  |[inModule](Mapping/inModule.md) | 判断路由是否为指定模块的路由 |
|  public  |[getFullName](Mapping/getFullName.md) |  |
|  public  |[getSortName](Mapping/getSortName.md) |  |
|  public  |[getName](Mapping/getName.md) |  |
|  public  |[setParam](Mapping/setParam.md) |  |
|  public  |[getParam](Mapping/getParam.md) |  |
|  public  |[setValue](Mapping/setValue.md) |  |
|  public  |[getValue](Mapping/getValue.md) |  |
|  public  |[setCallback](Mapping/setCallback.md) |  |
|  public  |[setModule](Mapping/setModule.md) |  |
|  public  |[setMethod](Mapping/setMethod.md) |  |
|  public  |[isDynamic](Mapping/isDynamic.md) |  |
|  public  |[isHidden](Mapping/isHidden.md) |  |
|  public  |[getRole](Mapping/getRole.md) |  |
|  public  |[getModule](Mapping/getModule.md) |  |
|  public  |[getTypes](Mapping/getTypes.md) |  |
|  public  |[setAntiPrefix](Mapping/setAntiPrefix.md) |  |
|  public  |[setDynamic](Mapping/setDynamic.md) |  |
|  public  |[setHidden](Mapping/setHidden.md) |  |
|  public  |[setMapping](Mapping/setMapping.md) |  |
|  public  |[setTemplate](Mapping/setTemplate.md) |  |
|  public  |[setSource](Mapping/setSource.md) |  |
|  public  |[getSource](Mapping/getSource.md) |  |
|  public  |[setUrl](Mapping/setUrl.md) |  |
|  public  |[getUrl](Mapping/getUrl.md) |  |
|  public  |[getTemplate](Mapping/getTemplate.md) |  |
|  public  |[getHost](Mapping/getHost.md) |  |
|  public  |[setHost](Mapping/setHost.md) |  |
|  public  |[setPort](Mapping/setPort.md) |  |
|  public  |[getUrlTemplate](Mapping/getUrlTemplate.md) |  |
|  public  |[createUrl](Mapping/createUrl.md) | 创建URL |
|  public  |[getBaseUrl](Mapping/getBaseUrl.md) |  |
|  public  |[getPrefix](Mapping/getPrefix.md) |  |
|  protected  |[buildMatch](Mapping/buildMatch.md) |  |
|  public  static|[createFromRouteArray](Mapping/createFromRouteArray.md) |  |
|  public  static|[current](Mapping/current.md) |  |
|  protected  static|[emptyResponse](Mapping/emptyResponse.md) |  |
|  protected  static|[sourceResponse](Mapping/sourceResponse.md) |  |
|  public  |[jsonSerialize](Mapping/jsonSerialize.md) |  |
 

## 例子

example