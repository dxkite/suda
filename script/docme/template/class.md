#  类 {{$:className }}

> *文件信息* {{$:fileName('未知文件')}}: {{$:lineStart('未知')}}~{{$:lineEnd('未知')}}

{{ $:classDoc('无说明信息') }}

## 描述

{{! $:document('该类暂时无说明') }}


## 变量


## 方法

@if (count($:methods([])))
| 可见性 | 方法名 | 说明 |
|--------|-------|------|
@foreach ($:methods as $name => $info )|{{$info['abstract'] .' '}} {{$info['visibility'].' ' }} {{$info['static']}}|[{{$name}}]({{$:className}}/{{$name}}.md) | {{$info['functionDoc']??'无'}} |
@endforeach @else
无方法
@endif