<?php
namespace suda\framework\runnable\target;

use Closure;
use function is_array;
use ReflectionClass;
use InvalidArgumentException;
use ReflectionException;
use function sprintf;
use function strrpos;
use suda\framework\runnable\exception\InvalidNameException;

/**
 * 目标构造器
 */
class TargetBuilder
{
    /**
     * 构建目标
     *
     * @param string|array|Closure $runnable
     * @param array $parameter
     * @return RunnableTarget
     */
    public static function build($runnable, array $parameter = []):RunnableTarget
    {
        if ($runnable instanceof Closure) {
            return new ClosureTarget($runnable, $parameter);
        }
        if (is_array($runnable)) {
            return new MethodTarget($runnable[0], null, $runnable[1], $parameter);
        }
        $target = self::buildWithString($runnable);
        if (count($parameter) > 0) {
            $target->setParameter($parameter);
        }
        return $target;
    }

    /**
     * 构建可执行对象
     *
     * @param string $command
     * @return RunnableTarget
     */
    protected static function buildWithString(string $command):RunnableTarget
    {
        $fileStart = strrpos($command, '@');
        if ($fileStart === 0) {
            return new FileTarget(substr($command, 1));
        }
        $requireFile = '';
        if ($fileStart > 0) {
            $requireFile = substr($command, $fileStart + 1);
            $command = substr($command, 0, $fileStart);
        }
        // for parameter list
        list($command, $parameter) = self::splitParameter($command);
        // for method
        $dynmicsMethod = strpos($command, '->');
        $splitLength = strpos($command, '#');
        $methodStart = $splitLength ?: strpos($command, '::') ?: $dynmicsMethod;
        $parameter = self::buildParameter($parameter);
        if ($methodStart > 0) {
            $splitLength = $splitLength > 0 ? 1:2;
            $methodName = substr($command, $methodStart + $splitLength);
            $command = substr($command, 0, $methodStart);
            list($className, $constructParameter) = self::splitParameter($command);
            $constructParameter = self::buildParameter($constructParameter);
            $target = new MethodTarget($className, $dynmicsMethod? $constructParameter :null, $methodName, $parameter);
        } else {
            $target = new FunctionTarget(self::buildName($command), $parameter);
        }
        if (strlen($requireFile)) {
            $target -> setRequireFile($requireFile);
        }
        return $target;
    }

    /**
     * @param string $class
     * @return object
     * @throws ReflectionException
     */
    public static function newClassInstance(string $class)
    {
        list($className, $parameter) = self::splitParameter($class);
        $classRelName = self::buildName($className);
        if (null === $parameter) {
            return new  $classRelName;
        }
        $parameters = self::buildParameter($parameter);
        $classRef = new ReflectionClass($classRelName);
        return $classRef->newInstanceArgs($parameters);
    }

    private static function buildName(string $name)
    {
        if (preg_match('/^[\w\\\\\/.]+$/', $name) !== 1) {
            throw new InvalidNameException(sprintf('invaild name: %s ', $name));
        }
        return  str_replace(['.','/'], '\\', $name);
    }

    private static function splitParameter(string $command):array
    {
        $parameter = null;
        if (strrpos($command, ')') === strlen($command) - 1) {
            $paramStart = strpos($command, '(');
            $parameter = substr($command, $paramStart + 1, strlen($command) - $paramStart - 2);
            $command = substr($command, 0, $paramStart);
        }
        return [$command,$parameter];
    }

    private static function buildParameter(?string $parameter)
    {
        if (null === $parameter) {
            return [];
        }
        return self::parseParameter($parameter);
    }

    protected static function parseParameter(string $param)
    {
        $param = trim($param);
        if (strpos($param, '=') === 0) {
            list($prefix, $value) = explode(':', $param, 2);
            if (strpos($value, ':') === 0) {
                $value = base64_decode(substr($value, 1));
            }
            if ($prefix === '=j' || $prefix === '=json') {
                $params = json_decode($value);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $params;
                }
                throw new InvalidArgumentException(sprintf('can not parse parameter %s', $param));
            } else {
                $params = unserialize($value);
                if (is_object($params)) {
                    return [$params];
                }
                return $params;
            }
        } else {
            $params = explode(',', trim($param, ','));
            foreach ($params as $index => $value) {
                $params[$index] = trim($value);
            }
            return $params;
        }
    }
}
