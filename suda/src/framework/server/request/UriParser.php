<?php
namespace suda\framework\server\request;

/**
 * HTTP URI处理
 */
class UriParser
{
    /**
     * 入口文件
     *
     * @var string
     */
    protected $indexFile;

    /**
     * 文本根目录
     *
     * @var string
     */
    protected $documentRoot;

    /**
     * URI
     *
     * @var string
     */
    protected $uri;

    /**
     * 请求参数
     *
     * @var array
     */
    protected $query;
    
    public function __construct(string $uri, string $indexFile)
    {
        $this->indexFile = $indexFile;
        $url = $this->clearIndex($uri, $indexFile);
        $query = parse_url($url, PHP_URL_QUERY);
        if (strlen($query) > 0) {
            parse_str($query, $this->query);
        }
        $url = parse_url($url, PHP_URL_PATH);
        $this->uri = '/' . trim($url, '\/');
    }

    public function getQuery():array
    {
        return $this->query;
    }

    public function getUri():string
    {
        return $this->uri;
    }

    private function clearIndex(string $url, string $indexFile)
    {
        if (\strpos($url, '/?/') === 0) {
            $url = substr($url, 2);
        }
        if (\strpos($url, $indexFile) === 0) {
            // for /index.php/
            $url = \substr($url, strlen($indexFile));// for /index.php?/
            if (\strpos($url, '?/') === 0) {
                $url = ltrim($url, '?');
            }
            // for /index.php
            elseif (\strpos($url, '/') !== 0) {
                $url = '/'.$url;
            }
        }
        return $url;
    }
}
