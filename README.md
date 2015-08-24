#SynchronizedBundle
##Instalation
via composer
````
require: {"symfony-micro-services/synchronized-bundle": "dev-master"}
````
and load the bundle in your AppKernel.php
````
new Sms\SynchronizedBundle()
````
##Usage
SynchronizedBundle lets you easily manage access to critical resources in a distributed environment.

Suppose you have the following service

````PHP
class Processor
{
    public function critical()
    {
        //do stuff
    }
}
````

You need to guarantee that only one process can execute the `critical` method. There are several locking techniques to achieve it. We try to group most of them in SynchronizedBundle and provide a **configuration only** easy to use interface.

For instance, the minimum configuration you need to achieve the previous lock is

````
synchronized:
    driver: "file"
    locks:
        - {service: my_processor, method: critical, driver: file}
````

##Drivers
<table>
    <tr>
      <th>driver name</th>
      <th>details</th>
    </tr>
    <tr>
      <td>debug</td>
      <td>Does no locking, useful for dev mode</td>
    </tr>
    <tr>
      <td>file</td>
      <td>uses <a href="http://php.net/manual/en/function.flock.php">flock</a><br>If intended to use in a production environment, make sure the path you use is shared across all your servers (ie. network mount)</td>
    </tr>
    <tr>
      <td>mysql</td>
      <td>Not implemented yet</td>
    </tr>
    <tr>
      <td>memcache</td>
      <td>Not implemented yet</td>
    </tr>
    <tr>
      <td>redis</td>
      <td>Not implemented yet</td>
    </tr>
    <tr>
      <td>mongodb</td>
      <td>Not implemented yet</td>
    </tr>
    <tr>
      <td>mariadb</td>
      <td>Not implemented yet</td>
    </tr>
    <tr>
      <td>postgresql</td>
      <td>Not implemented yet</td>
    </tr>
    <tr>
      <td>mssql</td>
      <td>Not implemented yet</td>
    </tr>
</table>

##Contributing
All contributions are welcome.
- Fork
- Do the magic
- Pull request

##Tests
To run the tests simply run `phpunit`. SynchronizedBundle has **100%** code coverage.
