# Summary

* [内置函数](functions/README.md)
@foreach($:functions as $name => $info)    * [{{$name}}](functions/{{$name}}.md) 
@endforeach
* [核心类参考](classes/README.md)
@foreach($:classes as $name => $info)    * [{{$name}}](classes/{{ docme\Docme::realPath($name)}}.md)
@endforeach
