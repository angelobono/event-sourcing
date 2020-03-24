<?php
/**
 * @Author: Angelo Bono
 * @Date:   2020-03-04 05:27:05
 * @Last Modified by:   Angelo Bono
 * @Last Modified time: 2020-03-07 12:07:47
 */
declare (strict_types=1);

namespace Bono\EventSourcing;

class ConfigProvider
{
    /**
     * @return array The Module Configuration
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencies(),
            'event-store' => $this->getEventStoreConfig(),
        ];
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return [
            
        ];
    }

    /**
     * @return array
     */
    public function getEventStoreConfig()
    {
        return [
            
        ];
    }
}
