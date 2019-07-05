# Replaced with https://github.com/dave-redfern/somnambulist-domain

This project has been replaced with a generic domain library that combines several packages into one
for easier maintenace.

## Domain Events for Laravel-Doctrine ORM

Adds support for Domain Events to Doctrine entities. This package is inspired by and based
on the Gist and blog posts by Benjamin Eberlei: 

 * [Doctrine and Domain Events](https://github.com/beberlei/whitewashing.de/blob/master/2013/07/24/doctrine_and_domainevents.rst)
 * [Decoupling applications with Domain Events](http://www.whitewashing.de/2012/08/25/decoupling_applications_with_domain_events.html)
 * [Gist for Doctrine Implementation](https://gist.github.com/beberlei/53cd6580d87b1f5cd9ca)

### Requirements

 * PHP 5.6+
 * bcmath extension
 * Laravel 5.2+
 * laravel-doctrine/orm

### Installation

Install using composer, or checkout / pull the files from github.com.

 * composer install somnambulist/laravel-doctrine-domain-events
 * add the event listener to your entity manager configuration in config/doctrine.php
 * add the interface to your aggregate roots (main entities that should raise events)
 * add the trait to provide an implementation of the interface (or roll your own)
 * add some domain events that extend the provided Domain Event
 * create some listeners and attach to the domain events in the doctrine config

A service provider is included that adds compiles allowing the main classes to be
added to the main bootstrap.

 * __Note__: the DomainEventListener should be configured per entity manager instance.

### Raising Events

To raise an event, decide which actions should result in a domain event. These should
coincide with state changes in the domain objects and the events should originate from
your main entities (aggregate roots).

For example: you may want to raise an event when a new User entity is created or that
a role was added to the user.

This does necessitate some changes to how you typically work with entities and Doctrine
in that you should remove setters and nullable constructor arguments. Instead you will
need to manage changes to you entity through specific methods, for example:

 * completeOrder()
 * updatePermissions()
 * revokePermissions()
 * publishStory()

Internally, after updating the entity state, call: `$this->raise(new NameOfEvent([]))`
and pass any specific parameters into the event that you want to make available to the
listener. This could be the old vs new or the entire entity reference, it is entirely
up to you.

    public function __construct($id, $name, $another, $createdAt)
    {
        $this->id        = $id;
        $this->name      = $name;
        $this->another   = $another;
        $this->createdAt = $createdAt;
        $this->raise(new MyEntityCreatedEvent(['id' => $id, 'name' => $name, 'another' => $another]));
    }

Generally it is better to not raise events in the constructor but instead to use named
constructors for primary object creation:

    private function __construct($id, $name, $another, $createdAt)
    {
        $this->id        = $id;
        $this->name      = $name;
        $this->another   = $another;
        $this->createdAt = $createdAt;
        $this->raise(new MyEntityCreatedEvent(['id' => $id, 'name' => $name, 'another' => $another]));
    }
    
    public static function create($id, $name, $another)
    {
        $entity = new static($id, $name, $another, new DateTime());
        $entity->raise(new MyEntityCreatedEvent(['id' => $id, 'name' => $name, 'another' => $another]));
        
        return $entity;
    }

### Firing Domain Events

This implementation includes a Doctrine subscriber that will listen for entities that
implement the RaisesDomainEvent interface and then ensures that `releaseAndResetEvents()`
is called.

 * __Note:__ before v. 0.6 the subscriber listened for LifeCycle events and could miss
   events if the Aggregate root was not modified at the same time as the child entities.
 
 * __Note:__ it is not required to use the `DomainEventListener` subscriber. You can
   implement your own event dispatcher, use another dispatcher entirely (the frameworks)
   and then manually trigger the domain events by flushing the changes and then manually
   calling `releaseAndResetEvents` and dispatching the events.
   
   If you do this note that the aggregate root class and primary identifier (if used)
   will not be set automatically. You will need to update your code to set these if
   you intend to use them.

To use the included listener, add it to your list of event subscribers in the Doctrine
configuration. This is per entity manager.

 * __Note:__ to use listeners with domain events that rely on Doctrine repositories
   it is necessary to defer loading those subscribers until after Doctrine has been
   resolved. Within Laravel, this can be done during a service provider `boot` method.
   See [Laravel Doctrine Project](https://github.com/dave-redfern/laravel-doctrine-project)
   for an example deferred loader.

### Creating a Domain Event Listener

Listeners can have their own dependencies (constructor is not defined), and are called
after the onFlush Unit of Work event. The listener can perform any post processing as
necessary, even triggering more events.

The listener should add methods that are named:

 * onNameOfTheEvent
 * without "event" suffixed
 * method will receive the Domain event instance
 * Domain event will have the class and id of the aggregate available

The example from the unit test:

    class EventListener
    {    
        public function onMyEntityCreated(MyEntityCreatedEvent $event)
        {
            printf(
                "New item created with id: %s, name: %s, another: %s",
                $event->getProperty('id'),
                $event->getProperty('name'),
                $event->getProperty('another')
            );
        }
    }

The unit test shows how it can be implemented.

Be sure to read the posts by Benjamin Eberlei mentioned earlier and check out his
[Assertion library](https://github.com/beberlei/assert) for low dependency entity
validation.

Alternatively, for validation that hooks into the main Laravel validator see:
[Entity Validation for Laravel Doctrine](https://github.com/dave-redfern/laravel-doctrine-entity-validation).

## Links

 * [Entity Auditing (port of SimpleThings: EntityAudit)](https://github.com/dave-redfern/laravel-doctrine-entity-audit)
 * [Entity Validation](https://github.com/dave-redfern/laravel-doctrine-entity-validation)
 * [Multi-Tenancy for Laravel with Doctrine](https://github.com/dave-redfern/laravel-doctrine-tenancy)
 * [Laravel Doctrine](http://laraveldoctrine.org)
 * [Laravel](http://laravel.com)
 * [Doctrine](http://doctrine-project.org)
