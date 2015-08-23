<?php

namespace Sms\SynchronizedBundle\Driver;

class File extends AbstractDriver
{

    private $locks = array();
    private $path;
    private $fileSystem;

    public function __construct($path)
    {
        $this->path = $path;
        $this->fileSystem = new \Symfony\Component\Filesystem\Filesystem();
    }

    protected function lock($lockId)
    {
        if (!is_dir($this->path)) {
            $this->fileSystem->mkdir($this->path);
        }
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
        flock($this->locks[$lockId], LOCK_UN);
        fclose($this->locks[$lockId]);
        unset($this->locks[$lockId]);
    }

    public function clearLocks()
    {
        $this->fileSystem->remove($this->path);
    }

}
