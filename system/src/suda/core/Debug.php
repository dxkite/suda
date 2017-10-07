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

use ZipArchive;

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
    private static $saved=false;

    public static function init()
    {
        $request=Request::getInstance();
        self::$hash=$hash=substr(md5(microtime().''.$request->ip()), 0, 6);
        self::$file=APP_LOG.'/tmps/'.self::$hash.'.tmp';
        Storage::mkdirs(dirname(self::$file));
        touch(self::$file);
        file_put_contents(self::$file, '====='.self::$hash.'====='.$request->ip().'====='.(conf('debug',defined('DEBUG') && DEBUG)?'debug':'normal')."=====\r\n",FILE_APPEND);
        file_put_contents(self::$file,self::printHead()."\r\n",FILE_APPEND);
    }

    public static function time(string $name,string $type='info')
    {
        self::$time[$name]=['time'=>microtime(true),'type'=>$type];
    }

    public static function timeEnd(string $name)
    {
        if (isset(self::$time[$name])) {
            $pass=microtime(true)-self::$time[$name]['time'];
            $backtrace=debug_backtrace();
            $offset=1;
            if (!isset($backtrace[$offset]['file'])) {
                $offset--;
            }
            $call=(isset($backtrace[$offset]['class'])?$backtrace[$offset]['class'].'#':'').$backtrace[$offset]['function'];
            self::_loginfo(self::$time[$name]['type'], $call, __('process %s %fs', $name, $pass), $backtrace[$offset]['file'] ??'unknown', $backtrace[$offset]['line'] ?? 0, $backtrace);
            return $pass;
        }
        return 0;
    }

    protected static function compareLevel($levela, $levelb)
    {
        $levela_num=is_numeric($levela)?$levela:self::$level[strtolower($levela)]??100;
        $levelb_num=is_numeric($levelb)?$levelb:self::$level[strtolower($levelb)]??100;
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

    protected static function printTrace(array $backtrace, bool $str=true,string $perfix='')
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
                $str.=$perfix.$trace_info."\r\n";
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
        $backtrace=$e->getBacktrace();
        return self::displayLog(['line'=>$line,'file'=>$file,'backtrace'=>$backtrace,'name'=>$e->getName(),'message'=>$e->getMessage()]);
    }
    
    protected static function displayLog(array $logarray){
        
        /* ---- 外部变量 ----- */
        $line=$logarray['line'];
        $file=$logarray['file'];
        $backtrace=$logarray['backtrace'];
        $name=$logarray['name'];
        $message=$logarray['message'];
 
        $pos_num = $line - 1;
        $code_file = file($file);
        $start = $line - 5 < 0 ? 0 : $line - 5;
        $lines = array_slice($code_file, $start, 10, true);
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
                if (conf('debug', false)) {
                    $this->template=$this->page('suda:error');
                } else {
                    $this->template=$this->page('suda:alert');
                }
            }
            public function render()
            {
                $stack=$this->template->getRenderStack();
                while ($name=array_pop($stack)) {
                    $get=ob_get_clean();
                    debug()->trace('free render', $name);
                }
                $this->template->render();
            }
        };
        $render->onRequest(Request::getInstance());
        $debug=self::getInfo();
        $render->template->assign([
                'erron'=>$name,
                'error'=>$message,
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
        self::_loginfo(Debug::ERROR, $e->getName(), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getBacktrace());
    }

    private static function printHead()
    {
        $request=Request::getInstance();
        return  $request->ip() . "\t" . date('Y-m-d H:i:s') . "\t" .$request->method()."\t".$request->virtualUrl();
    }

    protected static function save()
    {
        self::checkSize();
        $body=file_get_contents(self::$file);
        $time=number_format(microtime(true) - D_START, 10);
        $hash=self::$hash;
        $info=self::getInfo();
        $mem=self::memshow($info['memory'], 4);
        $peo=ceil(1/$time);
        $all=self::memshow($info['memory']*$peo,4);
        file_put_contents(self::$latest, $body, FILE_APPEND);
        file_put_contents(self::$latest,"====={$hash}====={$time}====={$mem}====={$peo}:{$all}=====\r\n\r\n",FILE_APPEND);
        unlink(self::$file);
        self::$saved=true;
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
        if(self::$saved || is_null(self::$file)){
            // 无法记录错误时直接显示错误
            return self::displayLog($log);
        }
        $str="\t[".number_format($log['time'], 10).'s:'.self::memshow($log['mem'], 2).']'."\t".$log['level'].'>In '.$log['file'].'#'.$log['line']."\t\t".$log['name']."\t".$log['message']."\r\n";
        // 添加调用栈 高级或者同级则记录
        if ((defined('LOG_FILE_APPEND') && LOG_FILE_APPEND) && self::compareLevel($log['level'], conf('debug-backtrace', Debug::ERROR)) >= 0) {
            $str.=self::printTrace($log['backtrace'],true,"\t\t=> ")."\r\n";
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


    public static function die(string $message) { 
        $backtrace=debug_backtrace();
        $offset=1;
        if (!isset($backtrace[$offset]['file'])) {
            $offset--;
        }
        $call=(isset($backtrace[$offset]['class'])?$backtrace[$offset]['class'].'#':'').$backtrace[$offset]['function'];
        self::_loginfo('die', $call, $message, $backtrace[$offset]['file'] ??'unknown', $backtrace[$offset]['line'] ?? 0, $backtrace);
        die($message);    
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
     * 检查日志文件大小
     *
     * @return void
     */
    private static function checkSize() {
        $file=self::$latest;
        if (file_exists($file)  && filesize($file) > self::MAX_LOG_SIZE) {
            $path=preg_replace('/[\\\\]+/', '/', Storage::path(APP_LOG).'/'.date('Y-m-d').'.zip');
            $zip = new ZipArchive;
            $res = $zip->open($path, ZipArchive::CREATE);
            if ($res === true) {
                $zip->addFile($file,date('Y-m-d'). '-'. ($zip->numFiles +1).'.log');
                $zip->close();
                unlink($file);
            } else {
                rename($file, APP_LOG . '/' . date('Y-m-d'). '-'. substr(md5_file($file), 0, 8).'.log');
            }
        }
    }
}

Debug::init();
