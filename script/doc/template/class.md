#  类 {{$:className }}

{{ $:classDoc('无说明信息') }}


## 变量


## 方法

@if (count($:methods([])))
| 可见性 | 方法名 | 说明 |
|--------|-------|------|
@foreach ($:methods as $name => $info )|{{$info['abstract'] .' '}} {{$info['visibility'].' ' }} {{$info['static']}}|[{{$name}}]({{$:className}}/{{$name}}.md) | {{$info['functionDoc']??'无'}} |
@endforeach @else
无方法
@endif