<?php


namespace suda\application;

use suda\framework\runnable\Runnable;
use Throwable;

class Event extends \suda\framework\Event
{
    /**
     * @var ApplicationContext|Application
     */
    protected $application;

    /**
     * Event constructor.
     * @param ApplicationContext|Application $application
     */
    public function __construct($application)
    {
        $this->application = $application;
    }

    /**
     * @param string $event
     * @param mixed $command
     * @param array $args
     * @return mixed
     */
    protected function call(string $event, $command, array &$args)
    {
        $runnable = new Runnable($command);
        $this->application->debug()->debug('invoke {event} event run {runnable}', [
            'runnable' => $runnable->getName(),
            'event' => $event,
        ]);
        try {
            return $runnable->apply($args);
        } catch (Throwable $e) {
            $this->application->debug()->error('invoke {event} event run {runnable} error', [
                'runnable' => $runnable->getName(),
                'event' => $event,
            ]);
            $this->application->debug()->addIgnorePath(__FILE__);
            $this->application->debug()->uncaughtException($e);
            if ($this->application instanceof  Application) {
                $this->application->dumpException($e);
            }
            return null;
        }
    }
}
