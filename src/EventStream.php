<?php
/**
 * @Author: Angelo Bono
 * @Date:   2019-11-15 20:41:01
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2019-11-16 01:51:13
 */
declare(strict_types=1);

namespace Bono\EventSourcing;

class EventStream
{
    private $topic;
    private $events;

    public function __construct(string $topic, array $events = [])
    {
        $this->events = new \SplQueue();
        $this->topic = $topic;
        $this->push($events);
    }

    public function push(array $events)
    {
        foreach ($events as $event) {
            $this->events->push($event);
        }
    }

    protected function pop()
    {
        return $this->events->pop();
    }

    public function topic(): string
    {
        return $this->topic;
    }

    public function events(): \SplQueue
    {
        return $this->events;
    }
}
