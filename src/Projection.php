<?php
/**
 * @Author: Angelo Bono
 * @Date:   2020-03-13 19:22:19
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2020-06-25 23:13:57
 */

namespace Bono\EventSourcing;

class Projection
{
    private $eventStore;
    private $topic = '';
    private $data = [];

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    public function from(string $topic)
    {
        $this->topic = $topic;
        return $this;
    }

    public function onInit(callable $init)
    {
        $this->data = $init;
        return $this;
    }

    public function when(callable ...$handlers)
    {
        foreach ($this->eventStore->read($topic) as $event) {
            foreach ($handlers as $handler) {
                $handler($event, $this->data);
            }
        }
    }
}
