<?php
namespace suda\core;

Hook::listen("system:init", "Debug::beforeSystemRun");
Hook::listen("system:display::after", "Debug::afterSystemRun");
Hook::listen("system:shutdown", "Debug::phpShutdown");
Hook::listen("system:debug:printf", "Debug::printf");

// TODO: 记录异常类型
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
    protected static $time=[];
    protected static $timer=[];
    protected static $traces=null;
    public static function setTrace(array $traces)
    {
        self::$traces=$traces;
    }
    public static function getTrace($offset_start, $offset_end)
    {
        if (self::$traces) {
            return self::$traces;
        }
        $trace = debug_backtrace();

        while ($offset_start--) {
            array_shift($trace);
        }
        while ($offset_end--) {
            array_pop($trace);
        }
        return $trace;
    }
    
    public static function time(string $name)
    {
        self::$time[$name]=microtime(true);
    }
    public static function timeEnd(string $name)
    {
        if (isset(self::$time[$name])) {
            $pass=microtime(true)-self::$time[$name];
            $timer[$name]=$pass;
            self::log('use '. number_format($pass, 10).' s', $name, self::T, 1);
        }
    }
    protected static function log(string $message, string $title='Log title', $level = self::E, int $offset_start=0, int $offset_end=0)
    {
        if (in_array(strtolower($level), ['debug', 'trace', 'info', 'notice', 'warning', 'error'])) {
            $level = strtolower($level);
        }
        $mark  = '';
        $loginfo=[];
        
        $trace=self::getTrace($offset_start, $offset_end);
        $trace_line = debug_backtrace();
        while ($offset_start--) {
            array_shift($trace_line);
        }
        while ($offset_end--) {
            array_pop($trace_line);
        }
        $loginfo['file']=$trace_line[0]['file'];
        $loginfo['line']=$trace_line[0]['line'];
        $loginfo['title']=$title;
        $loginfo['msg']=$message;
        $loginfo['level']=$level;
        $loginfo['time']=microtime(true)-D_START;
        $loginfo['mem']=memory_get_usage() - D_MEM;
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

        $pos_num = $line - 1;
        $code_file = file($file);
        $start = $line - 5 < 0 ? 0 : $line - 5;
        $lines = array_slice($code_file, $start, 10, true);
        $erron = $code;
        $error = $message;
        $traces = array();
        $traces_console=array();
        $start_trace=[];
        $end_trace=[];
        $backtrace=self::getTrace($offset_start, $offset_end);
        foreach ($backtrace as $trace) {
            $print = null;
            $print_d = null;
            if (isset($trace['file'])) {
                $print = '<a title="'.Storage::cut($trace['file']).'">'.basename($trace['file']).'</a>#'.$trace['line'];
                $print_d=basename($trace['file']).'#'.$trace['line'];
            }
            if (isset($trace['class'])) {
                $function = $trace['class'].$trace['type'].$trace['function'];
            } else {
                $function = $trace['function'];
            }
            $args = '';
            $args_d='';
            if (!empty($trace['args'])) {
                foreach ($trace['args'] as $arg) {
                    if (is_object($arg)) {
                        $args .= 'class '.get_class($arg).',';
                        $args_d .= 'class '.get_class($arg).',';
                    } else {
                        $args .=   var_export($arg, true).',';
                        $args_d.= (is_array($arg)?json_encode($arg):$arg) .',';
                    }
                }
                $args = rtrim($args, ',');
                $args_d = rtrim($args_d, ',');
            }
            $print .= ' '.$function.'('.$args.')';
            $print_d.=' '.$function.'('.$args_d.')';
            $traces[] = $print;
            $traces_console[]=$print_d;
        }
        $file=Storage::cut($file);
        $debug=self::getInfo();
        
        if (Config::get('console', false)) {
            print "\033[31m# Error>\033[33m $error\033[0m\r\n";
            print "\t\033[34mCause By $file:$line\033[0m\r\n";
            foreach ($traces_console as $trace_info) {
                print "\033[36m$trace_info\033[0m\r\n";
            }
        } else {
            $render=new class extends Response {
                public function onRequest(Request $request)
                {
                    $this->state(500);
                    if (\suda\template\Manager::compile('suda:error')) {
                        $this->display('suda:error');
                    } else {
                        $this->displayFile(SYS_RES.'/tpl/error.tpl');
                    }
                }
            };

            $render->assign([
                'erron'=>$erron,
                'error'=>$error,
                'file'=>$file,
                'line'=>$line,
                'debuginfo'=>$debuginfo="time:{$debug['time']}  memory:{$debug['memory']}",
                'lines'=>$lines,
                'pos_num'=>$pos_num,
                'traces'=>$traces,
            ]);
            \suda\template\Manager::loadCompile();
            $render->onRequest(Request::getInstance());
        }
        $loginfo['file']=$file;
        $loginfo['line']=$line;
        $loginfo['title']='Crash:'.$erron;
        $loginfo['msg']=$error;
        $loginfo['level']=self::E;
        $loginfo['time']=microtime(true)-D_START;
        $loginfo['mem']=memory_get_usage() - D_MEM;
        self::$log[]=$loginfo;
        exit($erron);
    }
    public static function printf()
    {
        return Request::ip() . "\t" . date('Y-m-d H:i:s') . "\t" .Request::method()."\t\t".Request::virtualUrl() . "\r\n";
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
            $str.="\t[".number_format($log['time'], 10).':'.$log['mem'].']'."\t".$log['level'].'>In '.$log['file'].'#'.$log['line']."\t\t".$log['title']."\t".$log['msg']."\r\n";
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
        self::save();
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
        $start=(isset($args[0]) && is_numeric($args[0]))?array_shift($args)+1:1;
        $end=(isset($args[0]) && is_numeric($args[0]))?array_shift($args)+1:1;
        self::log($message, $title, $level, $start+1, $end);
    }
    public function __call($method, $args)
    {
        self::aliasMethod($method, $args);
    }
}
