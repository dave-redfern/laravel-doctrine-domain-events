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

namespace Somnambulist\DomainEvents\Traits;

use Somnambulist\DomainEvents\Events\DomainEvent;

/**
 * Trait RaisesDomainEvents
 *
 * Based on the Gist by B. Eberlei https://gist.github.com/beberlei/53cd6580d87b1f5cd9ca
 *
 * @package    Somnambulist\DomainEvents\Traits
 * @subpackage Somnambulist\DomainEvents\Traits\RaisesDomainEvents
 */
trait RaisesDomainEvents
{

    /**
     * @var array|DomainEvent[]
     */
    private $events = [];

    /**
     * @param DomainEvent $event
     */
    public function raise(DomainEvent $event)
    {
        $this->events[] = $event;
    }

    /**
     * @return array
     */
    public function releaseAndResetEvents()
    {
        $pendingEvents = $this->events;

        $this->events = [];

        return $pendingEvents;
    }
}
