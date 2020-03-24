<?php
/**
 * @Author: Angelo Bono
 * @Date:   2019-11-15 21:28:49
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2020-03-04 05:48:15
 */
declare(strict_types=1);

namespace Bono\EventSourcing;

class VersionableEventStreamTest extends EventStream
{
    /**
     * @return void
     */
    public function testRecordEvent()
    {
    }

    /**
     * @return void
     */
    protected function testApplyEvent(Api\AggregateRootInterface &$aggregate, Event $event)
    {
        $handler = 'when' . ucfirst($event->eventType());
        if (! method_exists($aggregate, $handler)) {
            throw new \BadMethodCallException(
                'Event handler "' . $event->eventType() . '" was not found in ' .
                get_class($aggregate)
            );
        }
        $aggregate->$handler($event);
    }

    /**
     * @return void
     */
    protected function testReplayEventStream(Api\AggregateRootInterface &$aggregate, EventStream $eventStream)
    {
        while ($event = $eventStream->pop()) {
            $this->version = $event->metadata()['aggregate_version'] ?? ($this->version() + 1);
            $aggregate->apply($event);
        }
    }
}
