<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 * 
 * Copyright (c)  2017-2018 DXkite
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

defined('APP_LOG') or define('APP_LOG', DATA_DIR.'/logs');
defined('LOG_FORMAT') or define('LOG_FORMAT', '  $time_format - $memoery_format [$level] $file:$line $name $message');
defined('LOG_FORMAT_HEADER') or define('LOG_FORMAT_HEADER', '  $addr $time_format $method $url');
defined('LOG_TAG') or define('LOG_TAG', ['{%','%}']);

/**
 * 异常日志类
 * 用于记录运行日志和运行信息以及提供错误显示
 */
class Debug
{
    const MAX_LOG_SIZE=2097152;
    const TRACE = 'trace'; // 运行跟踪
    const DEBUG = 'debug'; // 调试记录
    const INFO = 'info'; // 普通消息
    const NOTICE = 'notice'; // 注意的消息
    const WARNING = 'warning'; // 警告消息
    const ERROR = 'error'; // 错误消息
    const LOG_PACK = LOG_TAG;
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
    private static $tempname;
    private static $latest=false;
    private static $saved=false;
    private static $shutdown=false;
    protected static $dump=[];

    public static function init()
    {
        if (defined('DEFAULT_TIMEZONE')) {
            date_default_timezone_set(DEFAULT_TIMEZONE);
        }
        $request=Request::getInstance();
        self::$hash=$hash=substr(md5(microtime().''.$request->ip()), 0, 6);
        $file = tmpfile();
        if ($file === false) {
            self::$tempname =  APP_LOG .'/.suda.tmp';
            storage()->path(dirname(static::$tempname));
            $file = fopen(static::$tempname, 'w+');
        }
        self::$file= $file;
        Config::set('request', self::$hash);
        $mode = conf('debug', defined('DEBUG') && DEBUG)?'debug':'normal';
        fwrite(self::$file, self::LOG_PACK[0].PHP_EOL.'request-'.self::$hash.'-begin from '.$request->ip().' at '.microtime(true).' with mode '. $mode .PHP_EOL);
        fwrite(self::$file, self::printHead().PHP_EOL);
        if (defined('APP_LOG') && Storage::path(APP_LOG) && is_writable(APP_LOG)) {
            self::$latest =APP_LOG.'/latest.log';
        }
        if (DEBUG) {
            cookie()->set(conf('log.cookie', '__debug'), conf('log.secret', base64_encode('dxkite')))->set();
        }
    }

    public static function time(string $name, string $type='info')
    {
        self::$time[$name]=['time'=>microtime(true),'type'=>$type];
    }

    public static function timeEnd(string $name)
    {
        if (isset(self::$time[$name])) {
            $pass=microtime(true)-self::$time[$name]['time'];
            self::$time[$name]['pass'] = $pass;
            $backtrace=debug_backtrace();
            foreach ($backtrace as $trace) {
                if (array_key_exists('file', $trace) && $trace['file'] != __FILE__) {
                    $traceInfo = $trace;
                    break;
                }
            }
            $call=(isset($traceInfo['class'])?$traceInfo['class'].'#':'').$traceInfo['function'];
            self::_loginfo(self::$time[$name]['type'], $call, __('process $0 $1s', $name,  number_format($pass,5)), $traceInfo['file'] ??'unknown', $traceInfo['line'] ?? 0, $backtrace);
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
        $loginfo['time']=microtime(true)-SUDA_START_TIME;
        $loginfo['mem']=memory_get_usage() - SUDA_START_MEMORY;
        self::writeLine($loginfo);
        $dump_log = defined('DEBUG_DUMP_LOG') && DEBUG_DUMP_LOG;
        if ($dump_log) {
            self::$log[]=$loginfo;
        }
    }

    public static function displayException(Exception $e)
    {
        self::logException($e);
        if (IS_CONSOLE) {
            return self::printConsole($e);
        } else {
            return self::printHTML($e);
        }
    }

    protected static function printTrace(array $backtrace, bool $str=true, string $perfix='')
    {
        $traces_console=[];
        foreach ($backtrace as $trace) {
            $print_d = null;
            if (isset($trace['file'])) {
                $print_d=$trace['file'].':'.$trace['line'];
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
                $str.=$perfix.preg_replace('/\n/', "\n".$perfix."\t", $trace_info).PHP_EOL;
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
        if (IS_LINUX) {
            print "\033[31m# Error>\033[33m $error\033[0m\r\n";
            print "  \033[34mCause By $file:$line\033[0m\r\n";
            if (is_array($traces_console)) {
                foreach ($traces_console as $trace_info) {
                    print "\033[36m    => $trace_info\033[0m\r\n";
                }
            }
        } else {
            print "# Error> $error\r\n";
            print "  Cause By $file:$line\r\n";
            if (is_array($traces_console)) {
                foreach ($traces_console as $trace_info) {
                    print "    => $trace_info\r\n";
                }
            }
        }
    }

    protected static function printHTML(Exception $e)
    {
        // // 非致命错误
        if ($e->getSeverity()!==E_ERROR) {
            if (cookie()->get(conf('log.cookie', '__debug')) == conf('debugSecret', base64_encode('dxkite'))) {
                echo "<div class=\"suda-error\" style=\"color:red\"><b>{$e->getName()}[{$e->getLevel()}]</b>: {$e->getMessage()} at {$e->getFile()}#{$e->getLine()}</div>";
            }
            return;
        }
        $line=$e->getLine();
        $file=$e->getFile();
        $backtrace=$e->getBacktrace();
        $ex= substr(md5($e->getName().'#'.$e->getMessage().'#'.$e->getFile().'#'.$e->getLine()), 0, 8);
        Config::set('request', $ex.'_'.self::$hash);
        self::$hash = $ex.'-'.self::$hash;
        $dump = ['Exception'=>$e,'Dump'=>self::dumpArray()];
        storage()->path(APP_LOG.'/dump');
        storage()->put(APP_LOG.'/dump/'.self::$hash.'.json', json_encode($dump, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
        return self::displayLog(['line'=>$line,'file'=>$file,'backtrace'=>$backtrace,'name'=>$e->getName(),'level'=>$e->getLevel(),'message'=>$e->getMessage()]);
    }
    
    protected static function displayLog(array $logarray)
    {
        /* ---- 外部变量 ----- */
        $line=$logarray['line'];
        $file=$logarray['file'];
        $backtrace=$logarray['backtrace'];
        $name=$logarray['name'];
        $message=$logarray['message'];
        $level = $logarray['level'];

        $pos_num = $line - 1;
        $code_file = file($file);
        $start = $line - 5 < 0 ? 0 : $line - 5;
        $lines = array_slice($code_file, $start, 10, true);
        foreach ($backtrace as $trace) {
            $print = null;
            if (isset($trace['file'])) {
                if (preg_match('/^'.preg_quote(SYSTEM_DIR, '/').'/', $trace['file'])) {
                    $print = '<a class="trace-file" title="';
                } else {
                    $print = '<a class="trace-user-file" title="';
                }
                $print .= Storage::cut($trace['file']).'">'.basename($trace['file']).'#'.$trace['line'].'</a>';
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
                        $args .=   print_r($arg, true).',';
                    }
                }
                $args = rtrim($args, ',');
            }
            $args = str_replace(',', '<span class="trace-separator">,</span>', $args);
            $print .= '<span class="trace-function">'.$function.'</span> (<span class="trace-args">'.$args.'</span>)';
            $traces[] = $print;
        }

        $render=new class extends Response {
            public $template=null;
            public function onRequest(Request $request)
            {
                $this->state(500);
                $this->template=$this->page('suda:error');
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
                'error_type'=>$name,
                'error_message'=>$message,
                'file'=>$file,
                'line'=>$line,
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
        self::_loginfo($e->getLevel(), $e->getName(), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getBacktrace());
    }

    private static function printHead()
    {
        $request=Request::getInstance();
        return  str_replace([
            '$addr','$time_format','$time','$method','$url'
        ], [
            $request->ip(),
            date('Y-m-d H:i:s'),
            time(),
            $request->method(),
            $request->virtualUrl()
        ], conf('log.header-format', LOG_FORMAT_HEADER));
    }

    protected static function save()
    {
        self::checkSize();
        // 获取日志信息
        $time=number_format(microtime(true) - SUDA_START_TIME, 10);
        $hash=self::$hash;
        $info=self::getInfo();
        $mem=self::memshow($info['memory'], 4);
        $requests=ceil(1/$time);
        $peak=self::memshow(memory_get_peak_usage(), 4);
        // 写入最终日志
        fwrite(self::$file, "request-{$hash}-end finished in {$time}s with memory {$mem} and max {$peak} - {$requests} req/s".PHP_EOL.self::LOG_PACK[1].PHP_EOL);
        $size=ftell(self::$file);
        fseek(self::$file, 0);
        $body=fread(self::$file, $size);
        fclose(self::$file);
        if (self::$tempname) {
            Storage::delete(self::$tempname);
        }
        // 是否可以写入
        if (self::$latest) {
            file_put_contents(self::$latest, $body, FILE_APPEND);
            self::$saved=true;
        }
    }

    private static function writeLine(array $log)
    {
        if (self::$saved || is_null(self::$file)) {
            if (self::$shutdown) {
                // 无法记录错误时跳过
                return;
            }
            return self::displayLog($log);
        }
        $str=str_replace(['$time_format','$time','$memoery_format','$memoery','$level','$file','$line','$name','$message'], [
            number_format($log['time'], 10),
            $log['time'],
            self::memshow($log['mem'], 2),
            $log['mem'],
            $log['level'],
            $log['file'],
            $log['line'],
            $log['name'],
            $log['message'],
        ], conf('log.format', LOG_FORMAT)) .PHP_EOL;
        // 添加调用栈 高级或者同级则记录
        if ((defined('LOG_FILE_APPEND') && LOG_FILE_APPEND) && self::compareLevel($log['level'], conf('log.backtrace', Debug::ERROR)) >= 0) {
            $str.=self::printTrace($log['backtrace'], true, "\t\t=> ").PHP_EOL;
        }
        return fwrite(self::$file, $str);
    }

    public static function memshow(int $mem, int $dec)
    {
        $human= ['B', 'KB', 'MB', 'GB', 'TB'];
        $pos= 0;
        while ($mem >= 1024) {
            $mem /= 1024;
            $pos++;
        }
        return round($mem, $dec).' '.$human[$pos];
    }

    public static function beforeSystemRun()
    {
        self::time('system');
        Hook::exec('suda:system:debug::start');
        self::$run_info['start_time']=SUDA_START_TIME;
        self::$run_info['start_memory']=SUDA_START_MEMORY;
    }

    public static function getInfo()
    {
        self::$run_info['end_time']=microtime(true);
        self::$run_info['time']=microtime(true) - SUDA_START_TIME;
        self::$run_info['memory']=memory_get_usage() - SUDA_START_MEMORY;
        self::$run_info['enSUDA_START_MEMORYory']=memory_get_usage();
        self::$run_info['peak_memory']=memory_get_peak_usage();
        self::$run_info['included_files']=get_included_files();
        return self::$run_info;
    }


    public static function afterSystemRun()
    {
        $info=self::getInfo();
        Hook::exec('suda:system:debug::stop', $info);
        self::timeEnd('system');
    }

    public static function phpShutdown()
    {
        self::$shutdown =true;
        self::afterSystemRun();
        self::save();
    }


    public static function die(string $message)
    {
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
    
    private static function aliasMethod(string $method, array $args)
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
        $backtrace =null;
        foreach ($args as $e) {
            if ($e instanceof \Exception) {
                $backtrace=$e->getTrace();
            }
        }
        if (is_null($backtrace)) {
            $backtrace=debug_backtrace();
        }

        $name=(isset($backtrace[2]['class'])?$backtrace[2]['class'].'#':'').$backtrace[2]['function'];
        $traceInfo = null;

        // 获取第一个带位置的信息
        foreach ($backtrace as $trace) {
            if (array_key_exists('file', $trace) && $trace['file'] != __FILE__) {
                $traceInfo = $trace;
                break;
            }
        }

        self::_loginfo(
            $level,
            self::strify(isset($args[1])?$args[0]:$name),
            self::strify($args[1]??$args[0]??null),
            $traceInfo['file'],
            $traceInfo['line'],
            $backtrace
        );
    }

    protected static function strify($object)
    {
        if (is_null($object)) {
            return '[NULL]';
        } elseif (is_object($object)) {
            return serialize($object);
        } elseif (is_array($object)) {
            return json_encode($object, JSON_UNESCAPED_UNICODE);
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
    private static function checkSize()
    {
        $logFile=self::$latest;
        if (file_exists($logFile)) {
            if (filesize($logFile) > self::MAX_LOG_SIZE) {
                $path=preg_replace('/[\\\\]+/', '/', Storage::path(APP_LOG.'/zip').'/'.date('Y-m-d').'.zip');
                $zip = new ZipArchive;
                $res = $zip->open($path, ZipArchive::CREATE);
                $rm =[];
                if ($res === true) {
                    if ($zip->addFile($logFile, date('Y-m-d'). '-'. $zip->numFiles .'.log')) {
                        $rm[]=$logFile;
                    }
                    if ($jsonLogs=storage()->readDirFiles(APP_LOG.'/dump')) {
                        foreach ($jsonLogs as $json) {
                            if ($zip->addFile($json, 'dump/'.basename($json))) {
                                $rm[]=$json;
                            }
                        }
                    }
                    $zip->close();
                    foreach ($rm as $rmFile) {
                        if (file_exists($rmFile) && is_file($rmFile)) {
                            unlink($rmFile);
                        }
                    }
                } else {
                    if (is_file($logFile) && file_exists($logFile)) {
                        rename($logFile, APP_LOG . '/' . date('Y-m-d'). '-'. substr(md5_file($logFile), 0, 8).'.log');
                    }
                }
            }
        }
    }

    public static function addDump(string $key, $values)
    {
        self::$dump[$key] = $values;
    }

    protected static function assginDebugInfo($page)
    {
        $page->set('request_id', self::$hash);
        $page->set('memory_usage', self::memshow(memory_get_usage() - SUDA_START_MEMORY, 4));
        $page->set('memory_peak_usage', self::memshow(memory_get_peak_usage(), 4));
        $page->set('time_spend', number_format(microtime(true) - SUDA_START_TIME, 4));
    }

    protected static function dumpArray()
    {
        $dump=  [
            '_ENV' => [
                'PHP' =>  PHP_VERSION,
                'SERVER' => $_SERVER['SERVER_SOFTWARE'],
                'SUDA' => SUDA_VERSION,
                'ROUTER' => \suda\core\route\Mapping::current(),
                'ROUTERS'=> router()->getRouters(),
            ],
            '_PHP' => [
                '_GET' => $_GET,
                '_POST'=> $_POST,
                '_FILES' => $_FILES,
                '_SERVER' => $_SERVER,
                '_COOKIE'=> $_COOKIE,
                '_SESSION'=> $_SESSION ?? null,
            ],
            '_CONST'=> get_defined_constants(true)['user'],
            '_LOG' => self::$log,
            '_TIME' => self::$time,
            '_COOKIE' => Cookie::$values,
            '_INCLUDE' => Autoloader::getIncludePath(),
            '_LANG_PATH' => Locale::getLocalePaths(),
            '_LANG_STR' => Locale::getLangs(),
            '_CONFIG'=> Config::get(),
        ];
        return array_merge($dump, self::$dump);
    }
}

Debug::init();
