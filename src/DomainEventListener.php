<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace Somnambulist\DomainEvents;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Somnambulist\Collection\Collection;
use Somnambulist\DomainEvents\Contracts\RaisesDomainEvents;
use Somnambulist\DomainEvents\Events\DomainEvent;

/**
 * Class DomainEventListener
 *
 * Based on the Gist by B. Eberlei https://gist.github.com/beberlei/53cd6580d87b1f5cd9ca
 *
 * @package    Somnambulist\DomainEvents
 * @subpackage Somnambulist\DomainEvents\DomainEventListener
 * @author     Dave Redfern
 */
class DomainEventListener implements EventSubscriber
{

    /**
     * @var Collection|RaisesDomainEvents[]
     */
    private $entities;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->entities = new Collection();
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::prePersist, Events::preFlush, Events::postFlush];
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function prePersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof RaisesDomainEvents) {
            $this->entities->add($entity);
        }
    }

    /**
     * @param PreFlushEventArgs $event
     */
    public function preFlush(PreFlushEventArgs $event)
    {
        $uow = $event->getEntityManager()->getUnitOfWork();

        foreach ($uow->getIdentityMap() as $class => $entities) {
            if (!in_array(RaisesDomainEvents::class, class_implements($class))) {
                continue; // @codeCoverageIgnore
            }

            foreach ($entities as $entity) {
                $this->entities->add($entity);
            }
        }
    }

    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        $em     = $event->getEntityManager();
        $evm    = $em->getEventManager();
        $events = new Collection();

        /*
         * Capture all domain events in this UoW and re-order for dispatch
         */
        foreach ($this->entities as $entity) {
            $class = $em->getClassMetadata(get_class($entity));

            foreach ($entity->releaseAndResetEvents() as $domainEvent) {
                /** @var DomainEvent $domainEvent */
                $domainEvent->setAggregate($class->name, $class->getSingleIdReflectionProperty()->getValue($entity));

                $events->add($domainEvent);
            }
        }

        $events->sortUsing(function ($a, $b) {
            /** @var DomainEvent $a */
            /** @var DomainEvent $b */
            return bccomp($a->getTime(), $b->getTime(), 6);
        });

        /*
         * Events should now be in created order so they can be dispatched / published.
         * If overriding this subscriber, fire messages to rabbitmq, beanstalk etc here
         * or replace doctrine event manager with another event dispatcher.
         */
        $events->call(function ($event) use ($em, $evm) {
            /** @var DomainEvent $event */
            $evm->dispatchEvent('on' . $event->getName(), $event);
        });

        $this->entities->reset();
    }
}
