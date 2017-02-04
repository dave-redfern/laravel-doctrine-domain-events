<?php

/**
 * Class EventListener
 *
 * @author Dave Redfern
 */
class EventListener
{

    public function onMyEntityCreated(\Somnambulist\DomainEvents\Events\DomainEvent $event)
    {
        printf(
            "New item created with id: %s, name: %s, another: %s\n",
            $event->getProperty('id'),
            $event->getProperty('name'),
            $event->getProperty('another')
        );
    }

    public function onMyEntityAddedAnotherEntity(\Somnambulist\DomainEvents\Events\DomainEvent $event)
    {
        printf(
            "Added related entity with name: %s, another: %s to entity id: %s\n",
            $event->getProperty('other')['name'],
            $event->getProperty('other')['another'],
            $event->getProperty('id')
        );
    }
}
