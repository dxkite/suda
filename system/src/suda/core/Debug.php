<?php
namespace suda\core;

class Debug
{
    const T = 'trace'; // 运行跟踪
    const D = 'debug'; // 调试记录
    const I = 'info'; // 普通消息
    const N = 'notice'; // 注意的消息
    const W = 'warning'; // 警告消息
    const E = 'error'; // 错误消息

    protected static $run_info;
    protected static $log=[];
    protected static $count=[];
    protected static $max = 2097152; // 2M

    public static function init()
    {
        Hook::listen("system:init", "Debug::beforeSystemRun");
        Hook::listen("system:display::after", "Debug::afterSystemRun");
        Hook::listen("system:shutdown", "Debug::phpShutdown");
        Hook::listen("system:debug:printf", "Debug::printf");
    }

    protected static function log(string $message, string $title='Log title', $level = self::E, int $offset_start=0, int $offset_end=0)
    {
        if (in_array(strtolower($level), ['debug', 'trace', 'info', 'notice', 'warning', 'error'])) {
            $level = strtolower($level);
        }
        $mark  = '';
        $loginfo=[];
        $trace = debug_backtrace();

        while ($offset_start--) {
            array_shift($trace);
        }
        while ($offset_end--) {
            array_pop($trace);
        }
        
        $loginfo['file']=$trace[0]['file'];
        $loginfo['line']=$trace[0]['line'];
        $loginfo['title']=$title;
        $loginfo['msg']=$message;
        $loginfo['level']=$level;
        if (isset(self::$count[$level])) {
            self::$count[$level]++ ;
        } else {
            self::$count[$level]=1;
        }
        self::$log[]=$loginfo;
    }



    protected static function error($message, $code=":(", $offset=0, $offend=0)
    {
        $backtrace = debug_backtrace();
        $file = $backtrace[$offset]['file'];
        $line = $backtrace[$offset]['line'];
        self::printError($message, $code, $file, $line, $offset+2, $offend);
    }


    public static function printError($message, $code, $file, $line, $offset_start=0, $offset_end=0)
    {
        $backtrace = debug_backtrace();
        $pos_num = $line - 1;
        $code_file = file($file);
        $start = $line - 5 < 0 ? 0 : $line - 5;
        $lines = array_slice($code_file, $start, 10, true);
        $erron = $code;
        $error = $message;
        $traces = array();
        while ($offset_start--) {
            array_shift($backtrace);
        }
        while ($offset_end--) {
            array_pop($backtrace);
        }
        foreach ($backtrace as $trace) {
            $print = null;
            if (isset($trace['file'])) {
                $print = '<a title="'.Storage::cutPath($trace['file']).'">'.basename($trace['file']).'</a>#'.$trace['line'];
            }
            if (isset($trace['class'])) {
                $function = $trace['class'].$trace['type'].$trace['function'];
            } else {
                $function = $trace['function'];
            }
            $args = '';
            if (!empty($trace['args'])) {
                foreach ($trace['args'] as $arg) {
                    if (is_object($arg)) {
                        $args .= 'class '.get_class($arg).',';
                    } else {
                        $args .= stripcslashes(var_export($arg, true)).',';
                    }
                }
                $args = rtrim($args, ',');
            }
            $print .= ' '.$function.'('.$args.')';
            $traces[] = $print;
        }
        $file=Storage::cutPath($file);
        $debug=self::getInfo();
        Debug::e($erron.':'.$error);
        
        if (Config::get('console',false)) {
            echo 'console';
        } else {
            $render=new Response;
            $render->state(404);
            $render->display('suda:error',[
                'erron'=>$erron,
                'error'=>$error,
                'file'=>$file,
                'line'=>$line,
                'debuginfo'=>$debuginfo="time:{$debug['time']}  memory:{$debug['memory']}",
                'lines'=>$lines,
                'pos_num'=>$pos_num,
                'traces'=>$traces,
            ]);
        }
        exit($erron);
    }
    public static function printf()
    {
        return Request::ip() . "\t" . date('Y-m-d H:i:s') . "\t" .Request::method()."\t".Request::url() . "\r\n";
    }
    protected static function save($file = 'debug.log')
    {
        if (!is_dir(dirname($file))) {
            mkdir_r(dirname($file));
        }

        if (is_file($file) && filesize($file) > self::$max) {
            rename($file, dirname($file) . '/' . time() . '-' . basename($file));
        }

        $str=Hook::execTail("system:debug:printf");
        foreach (self::$log as $log) {
            $str.="\t\\-".$log['level'].'>In '.$log['file'].'#'.$log['line']."\t\t".$log['title']."\t".$log['msg']."\r\n";
        }

        return file_put_contents(LOG_DIR.'/'.$file, $str, FILE_APPEND);
    }



    public static function beforeSystemRun()
    {
        Hook::listen('system:debug::start');
        self::$run_info['start_time']=D_START;
        self::$run_info['start_memory']=D_MEM;
    }

    protected static function getInfo()
    {
        self::$run_info['end_time']=microtime(true);
        self::$run_info['time']=microtime(true) - D_START;
        self::$run_info['memory']=memory_get_usage() - D_MEM;
        self::$run_info['end_memory']=memory_get_usage();
        self::$run_info['included_files']=get_included_files();
        return self::$run_info;
    }


    public static function afterSystemRun()
    {
        $info=self::getInfo();
        Hook::listen('system:debug::end', $info);
    }

    public static function phpShutdown()
    {
        if (\Config::get('debug')) {
            self::save();
        }
    }

    public static function __callStatic($method, $args)
    {
        self::aliasMethod($method, $args);
    }
    private static function aliasMethod($method, $args)
    {
        // var_dump($args);
        // string $message, string $title='Log title',int $offset_start=0,int $offset_end=0
        static $mpk=['d','t','i','n','w','e','u'];
        static $map=['d'=>'debug','t'=>'trace','i'=>'info','n'=>'notice','w'=>'warning','e'=>'error','u'=>'user'];

        if (preg_match('/([dtinweu]|debug|trace|info|notice|warning|error|user)/i', $method)) {
            if (in_array($method, $mpk)) {
                $level=$map[strtolower($method)];
            } else {
                $level=strtolower($method);
            }
            $title=$method;
        } else {
            $level='user';
            $title=$method;
        }

        $message=(isset($args[0]) && is_string($args[0]))?array_shift($args):'NO MESSAGE';
        $title=(isset($args[0]) && is_string($args[0]))?array_shift($args):$title;
        $start=(isset($args[0]) && is_numeric($args[0]))?array_shift($args):0;
        $end=(isset($args[0]) && is_numeric($args[0]))?array_shift($args):0;
        self::log($message, $title, $level, $start+1, $end);
    }
    public function __call($method, $args)
    {
        self::aliasMethod($method, $args);
    }
}
