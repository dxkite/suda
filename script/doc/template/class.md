#  类 {{$:className }}

{{ $:classDoc('无说明信息') }}


## 变量


## 方法

@if (count($:methods([])))
@foreach ($:methods as $name => $info )- [{{$info['visibility']}} - {{$name}}]({{$:className}}/{{$name}}.md)
    {{$info['functionDoc']}}
@endforeach
@else
无方法
@endif