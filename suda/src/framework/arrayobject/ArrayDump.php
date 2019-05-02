<?php
namespace suda\framework\arrayobject;

use function file_put_contents;

/**
 * 数组导出
 */
class ArrayDump
{
    /**
     * 将数组美化导出成PHP代码
     *
     * @param string $name 导出的数组名
     * @param array $array 导出的数组
     * @return string 导出的PHP代码
     */
    public static function dump(string $name, array $array):string
    {
        $name = '$'.ltrim($name, '$');
        $exstr = '<?php'.PHP_EOL.$name.'=array();'.PHP_EOL;
        $exstr .= self::arr2string($name, $array);
        $exstr .= 'return '.$name.';';
        return $exstr;
    }

    /**
     * 将数组导出到PHP文件
     *
     * @param string $path
     * @param string $name
     * @param array $array
     * @return int
     */
    public static function export(string $path, string $name, array $array)
    {
        return file_put_contents($path, static::dump($name, $array));
    }

    protected static function arr2string($arrname, $array)
    {
        $exstr = '';
        foreach ($array as $key => $value) {
            $line = '';
            $current = $arrname."['".addslashes($key)."']";
            if (is_array($value)) {
                $line .= self::parserArraySub($current, $value);
            } else {
                $line = $current;
                if (is_string($value)) {
                    $line .= "='".addslashes($value).'\';'.PHP_EOL;
                } elseif (is_bool($value)) {
                    $line .= '='.($value ? 'true' : 'false').';'.PHP_EOL;
                } elseif (null === $value) {
                    $line .= '=null;'.PHP_EOL;
                } else {
                    $line .= '='.$value.';'.PHP_EOL;
                }
            }
            $exstr .= $line;
        }
        return $exstr;
    }

    protected static function parserArraySub(string $parent, array $array)
    {
        $line = '';
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $subpar = $parent."['".$key."']";
                $line .= self::parserArraySub($subpar, $value);
            } else {
                $line .= $parent."['".$key."']";
                if (is_string($value)) {
                    $line .= "='".addslashes($value).'\';'.PHP_EOL;
                } elseif (is_bool($value)) {
                    $line .= '='.($value ? 'true' : 'false').';'.PHP_EOL;
                } elseif (null === $value) {
                    $line .= '=null;'.PHP_EOL;
                } else {
                    $line .= '='.$value.';'.PHP_EOL;
                }
            }
        }
        return $line;
    }
}
