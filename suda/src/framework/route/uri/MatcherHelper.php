<?php

namespace suda\framework\route\uri;

use function array_key_exists;
use function explode;
use function in_array;
use InvalidArgumentException;
use function is_numeric;
use function str_replace;
use suda\framework\route\uri\parameter\IntParameter;
use suda\framework\route\uri\parameter\UrlParameter;
use suda\framework\route\uri\parameter\FloatParameter;
use suda\framework\route\uri\parameter\StringParameter;

/**
 * 匹配辅助类
 */
class MatcherHelper
{
    protected static $parameters = [
        'float' => FloatParameter::class,
        'int' => IntParameter::class,
        'string' => StringParameter::class,
        'url' => UrlParameter::class,
    ];

    public static function build(string $uri): UriMatcher
    {
        // 参数
        $parameters = [];
        // 转义正则
        $url = preg_replace('/([\/\.\\\\\+\(\^\)\$\!\<\>\-\?\*])/', '\\\\$1', $uri);
        // 添加忽略
        $url = preg_replace('/(\[)([^\[\]]+)(?(1)\])/', '(?:$2)?', $url);
        // 添加 * ? 匹配
        $url = str_replace(['\*', '\?'], ['[^/]*?', '[^/]'], $url);
        // 编译页面参数
        $url = preg_replace_callback('/\{(\w+)(?:\:([^}]+?))?\}/', function ($match) use (&$parameters) {
            $name = $match[1];
            $type = 'string';
            $extra = '';
            if (isset($match[2])) {
                if (strpos($match[2], '=') !== false) {
                    list($type, $extra) = explode('=', $match[2]);
                } else {
                    $type = $match[2];
                }
            }
            if (!in_array($type, array_keys(static::$parameters))) {
                throw new InvalidArgumentException(sprintf('unknown parameter type %s', $type), 1);
            }
            $index = count($parameters);
            $parameter = forward_static_call_array([static::$parameters[$type], 'build'], [$index, $name, $extra]);
            $parameters[] = $parameter;
            return $parameter->getMatch();
        }, $url);

        return new UriMatcher($uri, $url, $parameters);
    }

    /**
     * @param UriMatcher $matcher
     * @param array $parameter
     * @param bool $allowQuery
     * @return string
     */
    public static function buildUri(UriMatcher $matcher, array $parameter, bool $allowQuery = true): string
    {
        $uri = $matcher->getUri();
        // 拆分参数
        list($mapper, $query, $parameter) = static::analyseParameter($matcher, $parameter);
        // for * ?
        $url = str_replace(['*', '?'], ['', '-'], $uri);
        // for ignore value
        $url = static::parseIgnorableParameter($url, $matcher, $parameter, $mapper);
        $url = static::replaceParameter($url, $matcher, $parameter, $mapper);
        if (count($query) && $allowQuery) {
            return $url . '?' . http_build_query($query, 'v', '&', PHP_QUERY_RFC3986);
        }
        return $url;
    }

    /**
     * @param string $url
     * @param UriMatcher $matcher
     * @param array $parameter
     * @param array $mapper
     * @return string
     */
    protected static function parseIgnorableParameter(
        string $url,
        UriMatcher $matcher,
        array $parameter,
        array $mapper
    ): string {
        return preg_replace_callback('/\[(.+?)\]/', function ($match) use ($matcher, $parameter, $mapper) {
            if (preg_match('/\{(\w+).+?\}/', $match[1])) {
                $count = 0;
                $subUrl = static::replaceParameter($match[1], $matcher, $parameter, $mapper, true, $count);
                if ($count > 0) {
                    return $subUrl;
                }
            }
            return '';
        }, $url);
    }

    protected static function analyseParameter(UriMatcher $matcher, array $parameter): array
    {
        $query = [];
        $mapper = [];
        foreach ($parameter as $key => $value) {
            if (is_numeric($key)) {
                $mp = $matcher->getParameterByIndex($key);
                if ($mp  !== null) {
                    unset($parameter[$key]);
                    $key = $mp->getIndexName();
                    $parameter[$key] = $value;
                }
            } else {
                $mp = $matcher->getParameter($key);
            }
            // 多余参数
            if ($mp === null) {
                $query[$key] = $value;
            }
            $mapper[$key] = $mp;
        }
        return [$mapper, $query, $parameter];
    }

    /**
     * @param string $input
     * @param UriMatcher $matcher
     * @param array $parameter
     * @param array $mapper
     * @param bool $ignore
     * @param int|null $count
     * @return string|string[]|null
     */
    protected static function replaceParameter(
        string $input,
        UriMatcher $matcher,
        array $parameter,
        array $mapper,
        bool $ignore = false,
        ?int &$count = null
    ) {
        return preg_replace_callback(
            '/\{(\w+).+?\}/',
            function ($match) use ($matcher, $parameter, $mapper, $ignore, &$count) {
                if (array_key_exists($match[1], $mapper)) {
                    $count++;
                    return $mapper[$match[1]]->packValue($parameter[$match[1]]);
                }
                if ($default = $matcher->getParameter($match[1])) {
                    if ($default->hasDefault()) {
                        $count++;
                        return $default->getDefaultValue();
                    }
                }
                if ($ignore === false) {
                    throw new InvalidArgumentException(
                        sprintf('unknown parameter %s in %s', $match[1], $matcher->getUri()),
                        1
                    );
                }
                return '';
            },
            $input,
            -1,
            $count
        );
    }
}
