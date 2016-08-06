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
use Doctrine\ORM\Events;
use Somnambulist\DomainEvents\Contracts\RaisesDomainEvents;

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
     * @var array|RaisesDomainEvents[]
     */
    private $entities = [];

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::postPersist, Events::postUpdate, Events::postRemove, Events::postFlush];
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postPersist(LifecycleEventArgs $event)
    {
        $this->keepAggregateRoots($event);
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postUpdate(LifecycleEventArgs $event)
    {
        $this->keepAggregateRoots($event);
    }

    /**
     * @param LifecycleEventArgs $event
     */
    public function postRemove(LifecycleEventArgs $event)
    {
        $this->keepAggregateRoots($event);
    }

    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        $entityManager = $event->getEntityManager();
        $evm           = $entityManager->getEventManager();

        foreach ($this->entities as $entity) {
            $class = $entityManager->getClassMetadata(get_class($entity));
            foreach ($entity->releaseAndResetEvents() as $domainEvent) {
                $domainEvent->setAggregate($class->name, $class->getSingleIdReflectionProperty()->getValue($entity));
                $evm->dispatchEvent("on" . $domainEvent->getName(), $domainEvent);
            }
        }

        $this->entities = array();
    }

    /**
     * @param LifecycleEventArgs $event
     */
    private function keepAggregateRoots(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!($entity instanceof RaisesDomainEvents)) {
            return;
        }

        $this->entities[] = $entity;
    }
}
