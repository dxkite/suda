<?php
namespace suda\application\processor;

use suda\framework\Config;
use suda\framework\Request;
use suda\framework\Response;
use suda\application\Resource;
use suda\application\Application;
use suda\framework\response\MimeType;
use suda\framework\filesystem\FileSystem;
use suda\framework\http\stream\DataStream;
use suda\application\template\TemplateUtil;
use suda\application\processor\RequestProcessor;

/**
 * 模块资源处理响应
 */
class TemplateAssetProccesser implements RequestProcessor
{
    /**
     * 处理文件请求
     *
     * @param \suda\application\Application $application
     * @param \suda\framework\Request $request
     * @param \suda\framework\Response $response
     * @return bool
     */
    public function onRequest(Application $application, Request $request, Response $response)
    {
        foreach ($application->getModule() as $name => $module) {
            $prefix = TemplateUtil::getAssetRoot($application, $name);
            $assetsPrefix = $prefix.'/'.$module->getUriSafeName().'/';
            if (strpos($request->getUri(), $assetsPrefix) === 0) {
                $assetPath = substr($request->getUri(), strlen($assetsPrefix));
                $parent = 'template/'.$application->getStyle();
                $resourcePath = $parent.'/'.$assetPath;
                $realPath = $module->getResource()->getResourcePath($resourcePath, $parent);
                if ($realPath) {
                    $file = new FileRangeProccessor($realPath);
                    $file->onRequest($application, $request, $response);
                    return true;
                }
            }
        }
        return false;
    }
}
