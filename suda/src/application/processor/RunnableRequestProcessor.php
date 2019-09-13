<?php


namespace suda\application\processor;


use suda\application\Application;
use suda\framework\Request;
use suda\framework\Response;
use suda\framework\runnable\Runnable;

class RunnableRequestProcessor implements RequestProcessor
{
    /**
     * @var string[]|null
     */
    protected $runnable = null;

    /**
     * @param Application $application
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function onRequest(Application $application, Request $request, Response $response)
    {
        $runnable = $this->getNextRunnable($application, $request);
        if ($runnable === null) {
            return null;
        }
        return $runnable($application, $request, $response, $this);
    }

    /**
     * @param Application $application
     * @param Request $request
     * @return Runnable|null
     */
    protected function getNextRunnable(Application $application, Request $request)
    {
        $runnable = $this->getNextRunnableFromChan($application, $request);
        if ($runnable === null) {
            return null;
        }
        return new Runnable($runnable);
    }

    /**
     * @param Application $application
     * @param Request $request
     * @return string|null
     */
    protected function getNextRunnableFromChan(Application $application, Request $request)
    {
        if ($this->runnable === null) {
            $this->runnable = $this->createRunnable($application, $request);
        }
        if (count($this->runnable) === 0) {
            return null;
        }
        return array_shift($this->runnable);
    }

    /**
     * @param Application $application
     * @param Request $request
     * @return array
     */
    protected function createRunnable(Application $application, Request $request)
    {
        $class = $application->getConfig()->get('processor', []);
        $processor = $this->formatClassAsRunnable($class);
        $runnable = $this->createChanFromRequest($request);
        return array_merge($processor, $runnable);
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function createChanFromRequest(Request $request)
    {
        $runnable = $request->getAttribute('runnable');
        if (is_string($runnable)) {
            return [$runnable];
        }
        if (is_array($runnable)) {
            return $runnable;
        }
        $class = $request->getAttribute('class', []);
        return $this->formatClassAsRunnable($class);
    }

    /**
     * @param $class
     * @return array
     */
    private function formatClassAsRunnable($class)
    {
        if (is_string($class)) {
            $class = [$class];
        }
        foreach ($class as $index => $className) {
            $class[$index] = $this->className($className) . '->onRequest';
        }
        return $class;
    }

    /**
     * 转换类名
     *
     * @param string $name
     * @return string
     */
    private function className(string $name)
    {
        return trim(str_replace(['.', '/'], '\\', $name), '\\');
    }
}