<?php

namespace Skafandri\SynchronizedBundle\Driver;

interface DriverInterface
{

    const DRIVER_FILE = 'file';
    const DRIVER_DOCTRINE = 'doctrine';
    const DRIVER_MEMCACHED = 'memcached';
    const DRIVER_REDIS = 'redis';

    public function setNameSpace($namespace);

    public function getLock($lockName);

    public function releaseLock($lockName);
}
