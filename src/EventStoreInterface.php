<?php
/**
 * @Author: Angelo Bonos
 * @Date:   2019-11-15 20:41:01
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2020-03-13 19:24:07
 */
declare(strict_types=1);

namespace Bono\EventSourcing;

interface EventStoreInterface
{
    public function load(string $topic): EventStream;
    public function append(EventStream $eventStream);
    public function flush();
}
