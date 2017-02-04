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
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
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
        return [Events::preFlush, Events::postFlush];
    }

    /**
     * @param PreFlushEventArgs $event
     */
    public function preFlush(PreFlushEventArgs $event)
    {
        $uow = $event->getEntityManager()->getUnitOfWork();

        foreach ($uow->getIdentityMap() as $class => $entities) {
            if (!in_array(RaisesDomainEvents::class, class_implements($class))) {
                continue;
            }

            foreach ($entities as $entity) {
                $this->entities[] = $entity;
            }
        }
    }

    /**
     * @param PostFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        $em  = $event->getEntityManager();
        $evm = $em->getEventManager();

        foreach ($this->entities as $entity) {
            $class = $em->getClassMetadata(get_class($entity));

            foreach ($entity->releaseAndResetEvents() as $event) {
                $event->setAggregate($class->name, $class->getSingleIdReflectionProperty()->getValue($entity));
                $evm->dispatchEvent("on" . $event->getName(), $event);
            }
        }

        $this->entities = [];
    }
}
