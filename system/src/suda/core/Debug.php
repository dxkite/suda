<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 * 
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\core;

// 监听事件
Hook::listen('system:init', 'suda\core\Debug::beforeSystemRun');
Hook::listen('system:shutdown', 'suda\core\Debug::phpShutdown');
Hook::listen('system:debug:printf', 'suda\core\Debug::printf');
defined('APP_LOG') or define('APP_LOG', APP_DIR.'/data/logs');
// TODO: 记录异常类型
class Debug
{
    const MAX_LOG_SIZE=2097152;
    const TRACE = 'trace'; // 运行跟踪
    const DEBUG = 'debug'; // 调试记录
    const INFO = 'info'; // 普通消息
    const NOTICE = 'notice'; // 注意的消息
    const WARNING = 'warning'; // 警告消息
    const ERROR = 'error'; // 错误消息
    protected static $level=[
        Debug::TRACE=>1,
        Debug::DEBUG=>2,
        Debug::INFO=>3,
        Debug::NOTICE=>4,
        Debug::WARNING=>5,
        Debug::ERROR=>6,
    ];
    protected static $run_info;
    protected static $log=[];
    protected static $time=[];
    protected static $hash;
    private static $file;
    private static $latest=APP_LOG.'/latest.log';
    
    public static function init() {
        self::$hash=substr(md5(microtime().''.Request::ip()), 0, 8);
        self::$file=APP_LOG.'/'.self::$hash.'.tmp';
        Storage::mkdirs(dirname(self::$file));
        touch(self::$file);
    }

    public static function time(string $name)
    {
        self::$time[$name]=microtime(true);
    }

    public static function timeEnd(string $name)
    {
        if (isset(self::$time[$name])) {
            $pass=microtime(true)-self::$time[$name];
            $backtrace=debug_backtrace();
            $offset=1;
            if (!isset($backtrace[$offset]['file'])) {
                $offset--;
            }
            $call=(isset($backtrace[$offset]['class'])?$backtrace[$offset]['class'].'#':'').$backtrace[$offset]['function'];
            self::_loginfo('info', $call, __('process %s %fs', $name, $pass), $backtrace[$offset]['file'] ??'unknown', $backtrace[$offset]['line'] ?? 0, $backtrace);
            return $pass;
        }
        return 0;
    }

    protected static function compareLevel($levela, $levelb)
    {
        $levela_num=is_numeric($levela)?$levela:self::$level[strtolower($levela)];
        $levelb_num=is_numeric($levelb)?$levelb:self::$level[strtolower($levelb)];
        return $levela_num - $levelb_num;
    }

    protected static function _loginfo(string $level, string $name, string $message, string $file, int $line, array $backtrace=null)
    {
        if (defined('LOG_LEVEL')) {
            if (self::compareLevel(LOG_LEVEL, $level)>0) {
                return;
            }
        }
        $loginfo['file']=$file;
        $loginfo['line']=$line;
        $loginfo['message']=$message;
        $loginfo['name']=$name;
        $loginfo['level']=$level;
        $loginfo['backtrace']=$backtrace;
        $loginfo['time']=microtime(true)-D_START;
        $loginfo['mem']=memory_get_usage() - D_MEM;
        self::writeLine($loginfo);
        if (defined('LOG_JSON') && LOG_JSON) {
            self::$log[]=$loginfo;
        }
    }

    public static function displayException(Exception $e)
    {
        self::logException($e);
        if (Config::get('console', false)) {
            return self::printConsole($e);
        } else {
            return self::printHTML($e);
        }
    }

    protected static function printTrace(array $backtrace, bool $str=true)
    {
        $traces_console=[];
        foreach ($backtrace as $trace) {
            $print_d = null;
            if (isset($trace['file'])) {
                $print_d=$trace['file'].'#'.$trace['line'];
            }
            if (isset($trace['class'])) {
                $function = $trace['class'].$trace['type'].$trace['function'];
            } else {
                $function = $trace['function'];
            }
            $args_d='';
            if (!empty($trace['args'])) {
                foreach ($trace['args'] as $arg) {
                    if (is_object($arg)) {
                        $args_d .= 'class '.get_class($arg).',';
                    } else {
                        $args_d.= (is_array($arg)?json_encode($arg):$arg) .',';
                    }
                }
                $args_d = rtrim($args_d, ',');
            }
            $print_d.=' '.$function.'('.$args_d.')';
            $traces_console[]=$print_d;
        }
        if ($str) {
            $str='';
            foreach ($traces_console as $trace_info) {
                $str.=$trace_info."\r\n";
            }
            return $str;
        }
        return  $traces_console;
    }

    protected static function printConsole(Exception $e)
    {
        $line=$e->getLine();
        $file=$e->getFile();
        $error=$e->getMessage();
        $backtrace=$e->getBacktrace();
        $traces_console=self::printTrace($backtrace, false);
        print "\033[31m# Error>\033[33m $error\033[0m\r\n";
        print "\t\033[34mCause By $file:$line\033[0m\r\n";
        foreach ($traces_console as $trace_info) {
            print "\033[36m$trace_info\033[0m\r\n";
        }
    }

    protected static function printHTML(Exception $e)
    {
        // // 非致命错误
        if ($e->getSeverity()!==E_ERROR) {
            echo "<div class=\"suda-error\" style=\"color:red\"><b>{$e->getName()}</b>: {$e->getMessage()} at {$e->getFile()}#{$e->getLine()}</div>";
            return;
        }
        // echo "<div class=\"suda-error\"><b>{$e->getName()}</b>: {$e->getMessage()} at {$e->getFile()}#{$e->getLine()}</div>";
        $line=$e->getLine();
        $file=$e->getFile();

        $pos_num = $line - 1;
        $code_file = file($file);
        $start = $line - 5 < 0 ? 0 : $line - 5;
        $lines = array_slice($code_file, $start, 10, true);
        $backtrace=$e->getBacktrace();
        
        foreach ($backtrace as $trace) {
            $print = null;
            if (isset($trace['file'])) {
                $print = '<a title="'.Storage::cut($trace['file']).'">'.basename($trace['file']).'</a>#'.$trace['line'];
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
                        $args .=   var_export($arg, true).',';
                    }
                }
                $args = rtrim($args, ',');
            }
            $print .= ' '.$function.'('.$args.')';
            $traces[] = $print;
        }

        $render=new class extends Response {
            public $template=null;
            public function onRequest(Request $request)
            {
                $this->state(500);
                if (\suda\template\Manager::compile('suda:error')) {
                    if (conf('debug', false)) {
                        $this->template=$this->page('suda:error');
                    } else {
                        $this->template=$this->page('suda:alert');
                    }
                } else {
                    $this->template=$this->pagefile(SYSTEM_RESOURCE.'/tpl/error.tpl', 'suda:error');
                }
            }
            public function render()
            {
                $stack=$this->template->getRenderStack();
                while ($name=array_pop($stack)) {
                    $get=ob_get_clean();
                    _D()->trace('free render', $name);
                }
                $this->template->render();
            }
        };
        $render->onRequest(Request::getInstance());
        $debug=self::getInfo();
        $render->template->assign([
                'erron'=>$e->getName(),
                'error'=>$e->getMessage(),
                'file'=>$file,
                'line'=>$line,
                'time'=> $debug['time'].'S',
                'mem'=> self::memshow($debug['memory'], 2),
                'lines'=>$lines,
                'pos_num'=>$pos_num,
                'traces'=>$traces,
            ]);
        \suda\template\Manager::loadCompile();
        $render->render();
        exit;
    }

    public static function logException(\Exception $e)
    {
        if (!$e instanceof Exception) {
            $e=new Exception($e);
        }
        self::_loginfo(Debug::ERROR,$e->getName(),$e->getMessage(),$e->getFile(), $e->getLine(),$e->getBacktrace());
    }

    public static function printf()
    {
        $info=self::getInfo();
        $time=number_format($info['time'], 10);
        $mem=self::memshow($info['memory'], 2);
        return Request::ip(). "\t" .(conf('debug')?'debug':'normal') . "\t" . date('Y-m-d H:i:s') . "\t" .Request::method()."\t".Request::virtualUrl() ."\t".$time.'s '.$mem.' '.self::$hash;
    }

    protected static function save()
    {
        $file=self::$latest;
        if (file_exists($file)  && filesize($file) > self::MAX_LOG_SIZE) {
            rename($file, dirname($file) . '/' . date('Y-m-d'). '-'. substr(md5_file($file), 0, 8).'.log');
        }
        $head=Hook::execTail("system:debug:printf")."\r\n";
        $body=file_get_contents(self::$file);
        file_put_contents($file,$head.$body,FILE_APPEND);
        unlink(self::$file);
        self::$file=null;
        if (defined('LOG_JSON') && LOG_JSON) {
            $loginfo=self::getInfo();
            $loginfo['request']=[
                'ip'=>Request::ip(),
                'method'=>Request::method(),
                'url'=>Request::virtualUrl(),
            ];
            $loginfo['logs']=self::$log;
            $filejson=RUNTIME_DIR.'/log-json/'.self::$hash.'.json';
            Storage::mkdirs(dirname($filejson));
            file_put_contents($filejson, json_encode($loginfo));
        }
    }

    private static function writeLine(array $log)
    {
        if (is_null(self::$file)){
            return;
        }
        $str="\t[".number_format($log['time'], 10).'S:'.self::memshow($log['mem'], 2).']'."\t".$log['level'].'>In '.$log['file'].'#'.$log['line']."\t\t".$log['name']."\t".$log['message']."\r\n";
        // 添加调用栈 高级或者同级则记录
        if ((defined('LOG_FILE_APPEND') && LOG_FILE_APPEND) && self::compareLevel($log['level'], conf('debug-backtrace', Debug::ERROR)) >= 0) {
            $str.=self::printTrace($log['backtrace'])."\r\n";
        }
        return file_put_contents(self::$file, $str, FILE_APPEND);
    }

    public static function memshow(int $mem, int $dec)
    {
        $human= ['B', 'KB', 'MB', 'GB', 'TB'];
        $pos= 0;
        while ($mem >= 1024) {
            $mem /= 1024;
            $pos++;
        }
        return round($mem, $dec) . $human[$pos];
    }

    public static function beforeSystemRun()
    {
        self::time('system');
        Hook::exec('system:debug::start');
        self::$run_info['start_time']=D_START;
        self::$run_info['start_memory']=D_MEM;
    }

    public static function getInfo()
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
        Hook::exec('system:debug::end', $info);
        self::timeEnd('system');
    }

    public static function phpShutdown()
    {
        self::afterSystemRun();
        self::save();
    }

    public static function __callStatic($method, $args)
    {
        self::aliasMethod($method, $args);
    }
    
    private static function aliasMethod($method, $args)
    {
        static $mpk=['d','t','i','n','w','e','u'];
        static $map=['d'=>'debug','t'=>'trace','i'=>'info','n'=>'notice','w'=>'warning','e'=>'error','u'=>'user'];

        if (preg_match('/([dtinweu]|debug|trace|info|notice|warning|error|user)/i', $method)) {
            if (in_array($method, $mpk)) {
                $level=$map[strtolower($method)];
            } else {
                $level=strtolower($method);
            }
        }
        $backtrace=debug_backtrace();
        
        $name=(isset($backtrace[2]['class'])?$backtrace[2]['class'].'#':'').$backtrace[2]['function'];
        self::_loginfo($level, self::strify(isset($args[1])?$args[0]:$name), self::strify($args[1]??$args[0]), $backtrace[1]['file'], $backtrace[1]['line'], $backtrace);
    }

    protected static function strify($object)
    {
        if (is_null($object)) {
            return '[NULL]';
        } elseif (is_object($object)) {
            return serialize($object);
        } elseif (is_array($object)) {
            return json_encode($object);
        }
        return $object;
    }

    public function __call($method, $args)
    {
        self::aliasMethod($method, $args);
    }

    /**
     * 压缩日志文件
     *
     * @return void
     */
    protected function compress() {

    }
}

Debug::init();