<?php
namespace suda\application\processor;

use SplFileObject;
use suda\framework\Request;
use suda\framework\Response;
use suda\application\Application;
use suda\framework\response\MimeType;
use suda\framework\http\stream\DataStream;
use suda\application\processor\RequestProcessor;

/**
 * 响应
 */
class FileRangeProccessor implements RequestProcessor
{
    /**
     * 文件路径
     *
     * @var SplFileObject
     */
    protected $file;

    /**
     * MIME
     *
     * @var string
     */
    protected $mime;

    public function __construct($file)
    {
        $this->file = $file instanceof SplFileObject? $file : new SplFileObject($file);
        $this->mime = MimeType::getMimeType($this->file->getExtension());
    }

    /**
     * 处理文件请求
     *
     * @param \suda\application\Application $application
     * @param \suda\framework\Request $request
     * @param \suda\framework\Response $response
     * @return void
     */
    public function onRequest(Application $application, Request $request, Response $response)
    {
        $ranges = $this->getRanges($request);
        $response->setHeader('accept-ranges', 'bytes');
        if ($ranges === false || $request->getMethod() !== 'GET') {
            $response->status(400);
        } elseif ($ranges === null) {
            $response->sendFile($this->file->getRealPath());
        } elseif (count($ranges) === 1) {
            $response->status(206);
            $range = $ranges[0];
            $response->setHeader('content-type', $this->mime);
            $response->setHeader('content-range', $this->getRangeHeader($range));
            $this->sendFileByRange($response, $range);
        } else {
            $response->status(206);
            $this->sendMultipleFileByRange($response, $ranges);
        }
    }

    /**
     * 发送多Range
     *
     * @param \suda\framework\Response $response
     * @param array $ranges
     * @return void
     */
    protected function sendMultipleFileByRange(Response $response, array $ranges)
    {
        $separates = 'multiple_range_'.base64_encode(\md5(\uniqid(), true));
        $response->setHeader('content-type', 'multipart/byteranges; boundary='.$separates);
        foreach ($ranges as $range) {
            $response->write('--'.$separates."\r\n");
            $this->sendMultipleRangePart($response, $range);
            $this->sendFileByRange($response, $range);
            $response->write("\r\n");
        }
    }


    /**
     * 发送范围数据
     *
     * @param \suda\framework\Response $response
     * @param array $range
     * @return void
     */
    protected function sendFileByRange(Response $response, array $range)
    {
        $response->write(new DataStream($this->file, $range['start'], $range['end'] -  $range['start'] + 1));
    }

    /**
     * 获取Range描述
     *
     * @param \suda\framework\Request $request
     * @return array|bool|null
     */
    protected function getRanges(Request $request)
    {
        $ranges = $this->parseRangeHeader($request);
        if (\is_array($ranges)) {
            return $this->parseRanges($ranges);
        } elseif ($ranges === false) {
            return false;
        }
        return null;
    }

    /**
     * 写Range头
     *
     * @param \suda\framework\Response $response
     * @param array $range
     * @return void
     */
    protected function sendMultipleRangePart(Response $response, array $range)
    {
        $response->write('Content-Type: '.$this->mime."\r\n");
        $response->write('Content-Range: '.$this->getRangeHeader($range) ."\r\n\r\n");
    }

    /**
     * 生成Range头
     *
     * @param array $range
     * @return string
     */
    protected function getRangeHeader(array $range):string
    {
        return sprintf('bytes %d-%d/%d', $range['start'], $range['end'], $this->file->getSize());
    }

    /**
     * 获取Range描述
     *
     * @param \suda\framework\Request $request
     * @return array|bool|null
     */
    protected function parseRangeHeader(Request $request)
    {
        $range = $request->getHeader('range', null);
        if (is_string($range)) {
            $range = trim($range);
            if (\strpos($range, 'bytes=') !== 0) {
                return false;
            }
            $rangesFrom = \substr($range, strlen('bytes='));
            return \explode(',', $rangesFrom);
        }
        return null;
    }
    
    /**
     * 处理范围
     *
     * @param array $ranges
     * @return array|bool
     */
    protected function parseRanges(array $ranges)
    {
        $range = [];
        foreach ($ranges as  $value) {
            if (($r = $this->parseRange($value)) !== null) {
                $range[] = $r;
            } else {
                return false;
            }
        }
        return $range;
    }

    /**
     * 处理Range
     *
     * @param string $range
     * @return array
     */
    protected function parseRange(string $range):?array
    {
        $range = trim($range);
        if (strrpos($range, '-') === strlen($range) - 1) {
            return [
                'start' => intval(\rtrim($range, '-')),
                'end' => $this->file->getSize() - 1,
            ];
        } elseif (\strpos($range, '-') !== false) {
            list($start, $end) = \explode('-', $range, 2);
            return ['start' => intval($start) , 'end' => intval($end) ];
        }
        return null;
    }
}
