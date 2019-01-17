<?php
namespace suda\tool;

/**
 * 安全辅助工具
 */
class Security
{
    protected static $defaultCsp = [
        'default-src' => ['self'],
        'font-src'=> ['self'],
        'img-src' => ['self','data:'],
        'script-src' => ['self','unsafe-inline','unsafe-eval', 'nonce'],
        'style-src' => ['self','unsafe-inline','nonce'],
    ];

    /**
     * CSP 规则集合
     * @link https://www.w3.org/TR/CSP3
     * @var array
     */
    protected static $ruleSets = [
        'child-src',
        'connect-src',
        'default-src',
        'font-src',
        'frame-src',
        'img-src',
        'manifast-src',
        'media-src',
        'prefetch-src',
        'object-src',
        'script-src',
        'script-src-elem',
        'script-src-attr',
        'style-src',
        'style-src-elem',
        'style-src-attr',
        'worker-src',
    ];

    /**
     * CSP的关键字
     *
     * @link https://www.w3.org/TR/CSP3
     *
     * keyword-source = "'self'" / "'unsafe-inline'" / "'unsafe-eval'"
     *           / "'strict-dynamic'" / "'unsafe-hashes'" /
     *           / "'report-sample'" / "'unsafe-allow-redirects'"
     */
    protected static $keywords = ['self','unsafe-inline','unsafe-eval','unsafe-hashes','strict-dynamic','report-sample','unsafe-allow-redirects'];

    protected static $nonceSet =[];


    public static function getDefaultCsp():array
    {
        return static::$defaultCsp;
    }

    public static function getNonce(string $name='')
    {
        if (strlen($name) > 0) {
            $name = self::getName($name);
            $nonce = base64_encode(md5(\microtime(true), true));
            self::pushNonce($name,$nonce);
            return $nonce;
        }
        $nonce = base64_encode(md5(\microtime(true), true));
        self::pushNonce('style-src',$nonce);
        self::pushNonce('script-src',$nonce);
        return $nonce;
    }

    public static function getNonceSet(string $name='')
    {
        return self::$nonceSet;
    }

    protected static function pushNonce(string $name, string $hash) {
        if (!\array_key_exists($name, self::$nonceSet)) {
            self::$nonceSet[$name]  = [];
        }
        if (!in_array($hash, self::$nonceSet[$name])) {
            self::$nonceSet[$name] [] =$hash;
        }
    }

    public static function cspGeneretor($scpConfig):?string
    {
        if (\is_string($scpConfig)) {
            return $scpConfig;
        }
        if (is_null($scpConfig)) {
            $scpConfig = self::$defaultCsp;
        }
        
        $scpRules = '';
        foreach ($scpConfig as $ruleName => $ruleValue) {
            if (!\in_array($ruleName, self::$ruleSets)) {
                continue;
            }
            if (\array_key_exists($ruleName, self::$nonceSet)) {
                $nonce = self::$nonceSet[$ruleName];
            } else {
                $nonce = null;
            }
            $scpRules .= $ruleName.' ';
            if (\is_array($ruleValue)) {
                if (\is_null($nonce)) {
                    $ruleValue = \array_diff($ruleValue, ['nonce']);
                }
                foreach ($ruleValue as $ruleNameChild) {
                    if ($rule = self::getRule($ruleNameChild, $nonce)) {
                        $scpRules .= $rule.' ';
                    }
                }
            } elseif ($rule = self::getRule($ruleNameChild, $nonce)) {
                $scpRules .= $rule.' ';
            }
            $scpRules = trim($scpRules) .';';
        }
        return \rtrim($scpRules, ';');
    }

    protected static function getRule(string $ruleName, $nonce):?string
    {
        $lowerRuleName = strtolower($ruleName);
        $scpRules = null;
        if (\in_array($lowerRuleName, static::$keywords) || \preg_match('/^sha(256|384|512)\-/', $lowerRuleName) === 1) {
            $scpRules = "'{$lowerRuleName}'";
        } elseif ($lowerRuleName === 'nonce') {
            if ($nonce) {
                if (\is_string($nonce)) {
                    $scpRules = "'nonce-{$nonce}'";
                } elseif (is_array($nonce)) {
                    $nonceStr ='';
                    foreach ($nonce as $hash) {
                        $nonceStr.= "'nonce-{$hash}' ";
                    }
                    $scpRules = trim($nonceStr);
                }
            }
        } else {
            $scpRules = $ruleName;
        }
        return $scpRules;
    }

    protected static function getName(string $name):string
    {
        if (\strpos($name, '-') > 0) {
            list($name, $sub) = \explode('-', $name);
            $name = $name.'-src-'.$sub;
        } else {
            $name = $name.'-src';
        }
        return strtolower($name);
    }
}
