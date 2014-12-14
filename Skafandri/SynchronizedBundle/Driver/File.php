<?php

namespace Skafandri\SynchronizedBundle\Driver;

class File extends AbstractDriver
{

    private $locks = array();
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    protected function lock($lockId)
    {
        $lockId = $this->path . '/' . $lockId;
        $this->locks[$lockId] = fopen($lockId, "w");
        if (flock($this->locks[$lockId], LOCK_EX | LOCK_NB)) {
            return true;
        }
        fclose($this->locks[$lockId]);
        return false;
    }

    protected function unlock($lockId)
    {
        $lockId = $this->path . '/' . $lockId;
        fclose($this->locks[$lockId]);
        unset($this->locks[$lockId]);
    }

}
