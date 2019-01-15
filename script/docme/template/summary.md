# Summary

* [说明](README.md)
* [内置函数](functions/README.md)
@foreach($:functions as $name => $info)    * [{{$name}}]({{ $:docme->exportPath($info['export']) }}) 
@endforeach
* [核心类参考](classes/README.md)
@foreach($:classes as $name => $info)    * [{{$name}}]({{ $:docme->exportPath($info['export']) }})
@foreach($info['methods'] as $method => $path)        * [{{$method}}]({{ $:docme->exportPath($path['export']) }})
@endforeach
@endforeach
