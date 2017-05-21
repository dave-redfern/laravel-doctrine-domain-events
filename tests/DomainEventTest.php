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

namespace Somnambulist\Tests\DomainEvents;

use Events\NamespacedEvent;
use Somnambulist\Collection\Immutable;
use Somnambulist\DomainEvents\Events\DomainEvent;
use Somnambulist\DomainEvents\Exceptions\InvalidPropertyException;

/**
 * Class DomainEventTest
 *
 * @package    Somnambulist\Tests\DomainEvents
 * @subpackage Somnambulist\Tests\DomainEvents\DomainEventTest
 * @author     Dave Redfern
 */
class DomainEventTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @group domain-event
     */
    public function testCanSetAggregateRoot()
    {
        $event = $this->getMockForAbstractClass(DomainEvent::class);
        $event->setAggregate(\MyEntity::class, 1234);

        $this->assertEquals(\MyEntity::class, $event->getAggregateClass());
        $this->assertEquals(1234, $event->getAggregateId());
    }

    /**
     * @group domain-event
     */
    public function testCanGetEvetName()
    {
        $event = new NamespacedEvent();

        $this->assertEquals('Namespaced', $event->getName());
    }

    /**
     * @group domain-event
     */
    public function testCanGetVersion()
    {
        $event = $this->getMockForAbstractClass(DomainEvent::class);

        $this->assertEquals(1, $event->getVersion());
    }

    /**
     * @group domain-event
     */
    public function testCanGetContext()
    {
        $event = $this->getMockForAbstractClass(DomainEvent::class);

        $this->assertInstanceOf(Immutable::class, $event->getContext());
    }

    /**
     * @group domain-event
     */
    public function testCanGetProperties()
    {
        $event = $this->getMockForAbstractClass(DomainEvent::class);

        $this->assertInstanceOf(Immutable::class, $event->getProperties());
    }

    /**
     * @group domain-event
     */
    public function testCanGetProperty()
    {
        $event = $this->getMockForAbstractClass(DomainEvent::class, [
            [
                'foo' => 'bar',
            ]
        ]);

        $this->assertEquals('bar', $event->getProperty('foo'));
    }

    /**
     * @group domain-event
     */
    public function testGetPropertyRaisesExceptionIfNotFound()
    {
        $event = $this->getMockForAbstractClass(DomainEvent::class, [
            [
                'foo' => 'bar',
            ]
        ]);

        $this->expectException(InvalidPropertyException::class);
        $event->getProperty('baz');
    }
}