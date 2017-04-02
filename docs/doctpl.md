# {{ $:class.name }}
{{ $:class.doc }}

> 继承关系 @foreach ( $:class.extends)  {{ $:class.extend->name }} @endforeach
> 继承接口 @foreach ( $:class.extends)  {{ $:class.extend->name }} @endforeach