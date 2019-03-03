<?php
namespace suda\framework\debug;

/**
 * 回溯调用者
 */
class Caller
{
    protected $ignorePath = [__FILE__];
    protected $backtrace;

    public function __construct(array $backtrace, array $ignorePath =[])
    {
        $this->ignorePath = \array_merge($this->ignorePath, $ignorePath);
        $this->backtrace = $backtrace;
    }

    public function getCallerTrace():?array
    {
        foreach ($this->backtrace as $trace) {
            if (array_key_exists('file', $trace)) {
                if (!$this->isIgnore($trace['file'])) {
                    return $trace;
                }
            }
        }
        return null;
    }

    protected function isIgnore(string $file):bool {
        foreach ($this->ignorePath as $path) {
            if (strpos($file, $path) === 0) {
                return true;
            }
        }
        return false;
    }
}
