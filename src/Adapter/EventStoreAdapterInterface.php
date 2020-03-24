<?php
/**
 * @Author: Angelo Bono
 * @Date:   2019-11-15 20:41:01
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2019-11-16 00:47:28
 */
declare(strict_types=1);

namespace Bono\EventSourcing\Api;

use Bono\EventSourcing\EventStream;

interface EventStoreAdapterInterface
{
    public function read(string $topic): EventStream;
    public function write(EventStream $eventStream);
    public function query(
        string $topic,
        array $criteria,
        int $limit = 1000,
        int $offset = 0
    ): array;
}
