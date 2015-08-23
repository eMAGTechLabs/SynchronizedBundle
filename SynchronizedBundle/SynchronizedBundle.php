<?php

namespace Sms\SynchronizedBundle;

use Sms\SynchronizedBundle\DependencyInjection\Compiler\DriversPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SynchronizedBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DriversPass());
    }

}
