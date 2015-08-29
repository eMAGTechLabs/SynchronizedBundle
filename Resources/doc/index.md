#SynchronizedBundle

SynchronizedBundle aims to let you decide how parts of your application should behave when running in a concurrent environment (multiple machines, multiple processes on the same machine..)
##Context
Consider the following example

````php
<?php

class ReservationService
{
    public function reserveProduct($orderId, $productId, $quantity) {
      if(!$this->checkProductAvailability($productId, $quantity)) {
          throw new QuantityNotAvailableException($productId, $quantity);
      }
      $this->doReservation($orderId, $productId, $quantity);
    }
}
````
`checkProductAvailability()` will check if the requested quantity is available in stock.  
`doReservation()` will reduce the product stock with **quantity** and insert a new row to the reservation table.

The previous code is not safe, and can have undesired behavior in some situations.

Suppose in product_stock table I have a record product_id:1 and quantity:10

Consider the following scenario

<table>
  <tr>
    <td>Request</td>
    <td>Code</td>
    <td>Quantity in product_stock</td>
  </tr>
  <tr>
    <td>Request1</td>
    <td>reserveProduct(122, 1, 7)</td>
    <td>10</td>
  </tr>
  <tr>
    <td>Request2</td>
    <td>reserveProduct(123, 1, 5)</td>
    <td>10</td>
  </tr>
  <tr>
    <td>Request1</td>
    <td>checkProductAvailability(1, 7) //return true</td>
    <td>10</td>
  </tr>
  <tr>
    <td>Request2</td>
    <td>checkProductAvailability(1, 5) //return true</td>
    <td>10</td>
  </tr>
  <tr>
    <td>Request1</td>
    <td>doReservation(1, 7)</td>
    <td>3</td>
  </tr>
  <tr>
    <td>Request2</td>
    <td>doReservation(1, 5)</td>
    <td>0 ? -2 ? Error??</td>
  </tr>
</table>

A common solution to this kind of problem is to acquire a lock before entering a critical section, and release it after finishing processing. If you choose to do it using MySQL `GET_LOCK` function, your code may look like

````php
<?php

class ReservationService
{
    public function reserveProduct($orderId, $productId, $quantity) {
      $lockName = sprintf('reserve_product_%d', $productId);
      //Try to acquire a lock
      if($this->connection->execute(sprintf('SELECT GET_LOCK("%s")', $lockName)) !== 1) {
          throw new CannotGetLockException();
      }
      if(!$this->checkProductAvailability($productId, $quantity)) {
          //Release the lock in case of failure
          $this->connection->execute(sprintf('SELECT RELEASE_LOCK("%s")', $lockName);
          throw new QuantityNotAvailableException($productId, $quantity);
      }
      $this->doReservation($orderId, $productId, $quantity);
      //Release the lock after persisting changes
      $this->connection->execute(sprintf('SELECT RELEASE_LOCK("%s")', $lockName);
    }
}
````

Introducing the previous locking technique will solve the problem. It has some drowback though.
- Is prone to code duplication, as you may want to achieve the same behavior in many places in your application.
- Violates the one abstraction level per method. `reserveProduct` contains logic about product reservation plus process management.
- You risk to use the same lock in two places that are not related (Imagine: One client cannot finalize a cart with ID 123 and productId 456 because another client wants to add productId 56 to his cart of ID 1234)
- Is hard to define different locking policies on different environments (dev, production)
- You must ensure very good exception handling when you use locking. To avoid retaining a lock that you don't need anymore (and prevent other processes access to that resource)

Things get more complicated when you have to use different timeouts for different actions. Or a different lock failing behaviour (queue a message somewhere when a lock fails..)

SynchronizedBundle will hide those implementation details from you. You may achieve the same functionality as the example above without altering your code. With as little as the following configuration

````
synchronized:
    locks:
        product_reservation:
            service: reservation_service #serviceId
            method: reserveProduct
            argument: 1 #Index of the argument in the method signature
            driver: mysql
````

##Planned functionalities (draft)

We intend to support the following configuration

````
synchronized:
    prefix: app
    # Lock prefix for all locks in this application.
    # Useful when sharing the locking storage between many applications.

    driver: driver_name
    # Choose one of the available drivers that will manage the locks persistence
    # The user can provide his service that implements DriverInterface

    timeout: # timeout in milliseconds before considering acquiring a lock failed

    events: # [true|false] dispatch or not events

    logging: # [true|false] activate or not logging

    locks:
        lock1:
            prefix: # overrides the global prefix
            driver: # overrides the global driver
            timeout: # overrides the global timeout

            service: # serviceId

            method: # methodName or RegEx

            arguments: [0, argumentName]
            # lock name will contain first argument (0) and argumentName argument

            onFail: # what happens when a lock fails?
                    # suggested options:
                      # silent: just return
                      # exception: throws an exception
                      # custom: callable provided by the user (serviceId+method?)

            group: group_name
            # If you have two methods that update the same table for example
            # you can set two locks with the same group
            # a call to method1 will block a call to method2 and so on
    file:
        path: # directory where to store locks when using file driver

    mysql,postgresql,mssql,mongodb,mariadb.. : # any Db you suggest
        connection: # connection to use when using a db driver
                    # if using Doctrine, could be @doctrine.dbal.default_connection
    memcached:
        service: # memcached service

    redis:
        client:
            type: predis
            dsn: redis://localhost
````

Each driver should be able to request extra configurations (name, type) to be provided.

[planned supported drivers](https://github.com/symfony-micro-services/SynchronizedBundle/blob/master/Resources/doc/drivers.md)

##Events
 The following events are dispatched

 <table>
 <tr>
    <td>Event name</td>
    <td>Class constant `Sms\SynchronizedBundle\Event`</td>
    <td>When</td>
 </tr>
 <tr>
    <td>synchronized.event.before_get_lock</td>
    <td>`EVENT_BEFORE_GET_LOCK`</td>
    <td>Before calling the lock driver to get a lock</td>
 </tr>
 <tr>
    <td>synchronized.event.success_get_lock</td>
    <td>`EVENT_SUCCESS_GET_LOCK`</td>
    <td>The lock driver returned `true`</td>
 </tr>
 <tr>
    <td>synchronized.event.failure_get_lock</td>
    <td>`EVENT_FAILURE_GET_LOCK`</td>
    <td>The lock driver returned `false`</td>
 </tr>
 <tr>
    <td>synchronized.event.before_release_lock</td>
    <td>`EVENT_BEFORE_RELEASE_LOCK`</td>
    <td>Before calling the lock driver to release a lock</td>
 </tr>
 <tr>
    <td>synchronized.event.after_release_lock</td>
    <td>`EVENT_AFTER_RELEASE_LOCK`</td>
    <td>After the call to lock driver to release a lock returned</td>
 </tr>
 </table>
<br/>

##Logging
 Intensive logging with *debug* level to help development. Comprehensive logging with *info* level that will be useful in production.

##Code quality
 SynchronizedBundle have (and will maintain) ![Coverage Status](https://coveralls.io/repos/symfony-micro-services/SynchronizedBundle/badge.svg?branch=master&service=github) and ![SensioLabsInsight](https://insight.sensiolabs.com/projects/ac3ccb67-8db2-49a3-92cb-be7730e7d5fd/big.png)
