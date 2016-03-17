<?php

namespace Emag\SynchronizedBundle\Driver;

interface DriverInterface
{

    public function getLock($lockName);

    public function releaseLock($lockName);

    public function clearLocks();
}
