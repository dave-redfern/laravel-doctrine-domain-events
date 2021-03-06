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

namespace Somnambulist\DomainEvents\Events;

use Doctrine\Common\EventArgs;
use Somnambulist\DomainEvents\Exceptions\InvalidPropertyException;
use Somnambulist\Collection\Immutable;

/**
 * Class DomainEvent
 *
 * Based on the Gist by B. Eberlei https://gist.github.com/beberlei/53cd6580d87b1f5cd9ca
 *
 * @package    Somnambulist\DomainEvents\Events
 * @subpackage Somnambulist\DomainEvents\Events\DomainEvent
 * @author     Dave Redfern
 */
abstract class DomainEvent extends EventArgs
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var Immutable
     */
    private $properties;

    /**
     * @var Immutable
     */
    private $context;

    /**
     * @var string
     */
    private $aggregateClass;

    /**
     * @var string
     */
    private $aggregateId;

    /**
     * @var int
     */
    private $version;

    /**
     * @var float
     */
    private $time;



    /**
     * Constructor.
     *
     * @param array $payload Array of specific state change data
     * @param array $context Array of additional data providing context e.g. user, ip etc
     * @param int   $version A version identifier for the payload format
     */
    public function __construct(array $payload = [], array $context = [], $version = 1)
    {
        $this->properties = new Immutable($payload);
        $this->context    = new Immutable($context);
        $this->time       = microtime(true);
        $this->version    = $version;
    }

    /**
     * @return float
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (is_null($this->name)) {
            $this->name = $this->parseName();
        }

        return $this->name;
    }

    /**
     * @return string
     */
    private function parseName()
    {
        $class = get_class($this);

        if (substr($class, -5) === "Event") {
            $class = substr($class, 0, -5);
        }
        if (strpos($class, "\\") === false) {
            return $class;
        }

        $parts = explode("\\", $class);

        return end($parts);
    }

    /**
     * @return Immutable
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return Immutable
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getAggregateClass()
    {
        return $this->aggregateClass;
    }

    /**
     * @return string
     */
    public function getAggregateId()
    {
        return $this->aggregateId;
    }



    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getProperty($name)
    {
        if (!$this->properties->has($name)) {
            throw InvalidPropertyException::propertyDoesNotExist($name);
        }

        return $this->properties->get($name);
    }

    /**
     * @param string $class
     * @param string $id
     */
    public function setAggregate($class, $id)
    {
        if (!$this->aggregateClass && !$this->aggregateId) {
            $this->aggregateClass = $class;
            $this->aggregateId    = $id;
        }
    }
}
