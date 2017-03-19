<?php
    $options=getopt('s::',['class:','share::']);
    $classname=$options['class'];
    $share= isset($options['share']) || isset($options['s']);
    if ($path=createClassFile($classname,$share)) {
        print 'class create at>'.$path."\r\n";
    } else {
        print 'class  format error!';
    }
    
function createClassFile(string $classname, bool $share=false)
{
    if (!preg_match('/^(.+?)@(.+?)$/', $classname, $matchs)) {
        return false;
    }
    list($all, $class_short, $module)=$matchs;
    $namespace=conf('app.namespace');
    $class=$namespace.'\\'.$class_short;
    $pos=strrpos($class, '\\');
    $class_namespace=substr($class, 0, $pos);
    $class_name=substr($class, $pos+1);
    if ($share) {
        $class_path=MODULES_DIR.'/'.$module.'/share/'.$class_namespace;
    } else {
        $class_path=MODULES_DIR.'/'.$module.'/src/'.$class_namespace;
    }

    $class_file=$class_path.'/'.$class_name.'.php';
    
    $class_template= Storage::get(SYS_RES. '/class_controller.php');
    $class_template=str_replace(
            ['__class_namespace__', '__class_name__', '__module__', ],
            [$class_namespace, $class_name, $module, ], $class_template);
    // 写入Class
    Storage::path($class_path);
    Storage::put($class_file, $class_template);
    return $class_file;
}
