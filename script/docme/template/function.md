# {{$:functionName}}

{{! $:functionDoc('该函数暂时无注释文档') }}

> *文件信息* {{$:fileName('未知文件')}}: {{$:lineStart('未知')}}~{{$:lineEnd('未知')}}

{{! $:document('该函数暂时无说明') }}

## 参数

@if (count($:params([])))
| 参数名 | 类型 | 默认值 | 说明 |
|--------|-----|-------|-------|
@foreach ($:params as $name => $param)| @{$name} |  @{$param['type'] ??['无']} | @{$param['default']??'无'} | @{$param['description']??'无'} |
@endforeach
@else
无参数
@endif

## 返回值
@if (count($:return([])))
类型：{{ $:return['type'] }}

@{$:return['description']}

@else
返回值类型不定
@endif

## 例子

{{! $:example }}