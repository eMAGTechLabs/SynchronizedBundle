#SynchronizedS
##Instalation

##Usage

The most common use case is to synchronize a service method.

````PHP
class Processor
{
    public function process()
    {
        //do stuff
    }
}
````

Suppose you need to make the **process** method from the **Processor** service atomic.
If the service id is **my_processor**


````
synchronized:
    driver: "file"
    path: "%kernel.root_dir%/synchronized"
    services:
        my_processor:
            method: "process"
````