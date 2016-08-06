## Domain Events for Laravel-Doctrine ORM

Adds support for Domain Events to Doctrine entities. This package is inspired by and based
on the Gist and blog posts by Benjamin Eberlei: 

 * [Doctrine and Domain Events](https://github.com/beberlei/whitewashing.de/blob/master/2013/07/24/doctrine_and_domainevents.rst)
 * [Decoupling applications with Domain Events](http://www.whitewashing.de/2012/08/25/decoupling_applications_with_domain_events.html)
 * [Gist for Doctrine Implementation](https://gist.github.com/beberlei/53cd6580d87b1f5cd9ca)

### Requirements

 * PHP 5.5+
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

 * _Note_: the DomainEventListener should be configured per entity manager instance.

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

Internally, after updating the entity state, simply call: `$this->raise(new NameOfEvent([]))`
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

### Creating a Listener

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
