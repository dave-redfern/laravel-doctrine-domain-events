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
            "New item created with id: %s, name: %s, another: %s",
            $event->getProperty('id'),
            $event->getProperty('name'),
            $event->getProperty('another')
        );
    }
}
