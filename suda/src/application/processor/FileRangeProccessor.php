<?php

namespace suda\application\processor;

use Exception;
use function explode;
use function is_array;
use function md5;
use function rtrim;
use SplFileObject;
use function strpos;
use function substr;
use suda\framework\Request;
use suda\framework\Response;
use suda\application\Application;
use suda\framework\response\MimeType;
use suda\framework\http\stream\DataStream;
use function uniqid;

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
        $this->file = $file instanceof SplFileObject ? $file : new SplFileObject($file);
        $this->mime = MimeType::getMimeType($this->file->getExtension());
    }

    /**
     * 处理文件请求
     *
     * @param Application $application
     * @param Request $request
     * @param Response $response
     * @return void
     * @throws Exception
     */
    public function onRequest(Application $application, Request $request, Response $response)
    {
        $ranges = $this->getRanges($request);
        $response->setHeader('accept-ranges', 'bytes');
        if ($request->getMethod() !== 'GET' || $ranges === false) {
            $response->status(400);
        } else {
            $this->sendFileRanges($response, $ranges);
        }
    }

    /**
     * @param Response $response
     * @param array $ranges
     * @throws Exception
     */
    protected function sendFileRanges(Response $response, array $ranges)
    {
        if (count($ranges) === 0) {
            $response->setHeader('content-type', $this->mime);
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
     * @param Response $response
     * @param array $ranges
     * @return void
     */
    protected function sendMultipleFileByRange(Response $response, array $ranges)
    {
        $separates = 'multiple_range_' . base64_encode(md5(uniqid(), true));
        $response->setHeader('content-type', 'multipart/byteranges; boundary=' . $separates);
        foreach ($ranges as $range) {
            $response->write('--' . $separates . "\r\n");
            $this->sendMultipleRangePart($response, $range);
            $this->sendFileByRange($response, $range);
            $response->write("\r\n");
        }
    }


    /**
     * 发送范围数据
     *
     * @param Response $response
     * @param array $range
     * @return void
     */
    protected function sendFileByRange(Response $response, array $range)
    {
        // [start,end] = $end - $start + 1
        $response->write(new DataStream($this->file, $range['start'], $range['end'] - $range['start'] + 1));
    }

    /**
     * 获取Range描述
     * @param Request $request
     * @return array|bool
     */
    protected function getRanges(Request $request)
    {
        $ranges = $this->parseRangeHeader($request);
        if (count($ranges) > 0) {
            return $this->parseRanges($ranges);
        }
        return [];
    }

    /**
     * 写Range头
     *
     * @param Response $response
     * @param array $range
     * @return void
     */
    protected function sendMultipleRangePart(Response $response, array $range)
    {
        $response->write('Content-Type: ' . $this->mime . "\r\n");
        $response->write('Content-Range: ' . $this->getRangeHeader($range) . "\r\n\r\n");
    }

    /**
     * 生成Range头
     *
     * @param array $range
     * @return string
     */
    protected function getRangeHeader(array $range): string
    {
        return sprintf('bytes %d-%d/%d', $range['start'], $range['end'], $this->file->getSize());
    }

    /**
     * 获取Range描述
     * @param Request $request
     * @return array
     */
    protected function parseRangeHeader(Request $request)
    {
        $range = $request->getHeader('range', null);
        $range = trim($range);
        if (strpos($range, 'bytes=') === 0) {
            $rangesFrom = substr($range, strlen('bytes='));
            return explode(',', $rangesFrom);
        }
        return [];
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
        foreach ($ranges as $value) {
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
     * @param string $range
     * @return array|null
     */
    protected function parseRange(string $range): ?array
    {
        $range = trim($range);
        if (strrpos($range, '-') === strlen($range) - 1) {
            return [
                'start' => intval(rtrim($range, '-')),
                'end' => $this->file->getSize() - 1,
            ];
        } elseif (strpos($range, '-') !== false) {
            list($start, $end) = explode('-', $range, 2);
            $length = intval($end - $start);
            if ($length <= 0) {
                return ['start' => intval($start), 'end' => $this->file->getSize() - 1];
            }
            return ['start' => intval($start), 'end' => intval($end)];
        }
        return null;
    }
}
