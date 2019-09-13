<?php
namespace suda\application\processor;

use Exception;
use suda\framework\Request;
use suda\framework\Response;
use suda\application\Application;
use suda\application\template\TemplateUtil;

/**
 * 模块资源处理响应
 */
class TemplateAssetProcessor implements RequestProcessor
{
    /**
     * 处理文件请求
     *
     * @param Application $application
     * @param Request $request
     * @param Response $response
     * @throws Exception
     */
    public function onRequest(Application $application, Request $request, Response $response)
    {
        foreach ($application->getModules() as $name => $module) {
            $prefix = TemplateUtil::getAssetRoot($application, $name);
            $assetsPrefix = $prefix.'/'.$module->getUriSafeName().'/';
            if (strpos($request->getUri(), $assetsPrefix) === 0) {
                $assetPath = substr($request->getUri(), strlen($assetsPrefix));
                $parent = 'template/'.$application->getStyle();
                $resourcePath = $parent.'/'.$assetPath;
                $realPath = $module->getResource()->getResourcePath($resourcePath, $parent);
                if ($realPath) {
                    $file = new FileRangeProcessor($realPath);
                    $file->onRequest($application, $request, $response);
                }
            }
        }
    }
}
