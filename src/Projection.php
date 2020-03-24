<?php
/**
 * @Author: Angelo Bono
 * @Date:   2020-03-13 19:22:19
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2020-03-13 19:33:21
 */

namespace Bono\EventSourcing;

class Projection
{
    public function from(string $topic)
    {
    }

    public function onInit(callable $init)
    {
    }

    public function when(callable ...$handler)
    {
    }
}
