<?php
/**
 * @Author: Angelo Bono
 * @Date:   2019-11-15 22:06:54
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2019-11-16 00:47:24
 */
declare(strict_types=1);

namespace Bono\EventSourcing\Api;

use Bono\EventSourcing\EventStream;

interface AggregateRootInterface
{
    public function popEventStream(): EventStream;
    public function aggregateId(): string;
}
