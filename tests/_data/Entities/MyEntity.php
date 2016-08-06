<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Class MyEntity
 *
 * @ORM\Entity
 */
class MyEntity implements \Somnambulist\DomainEvents\Contracts\RaisesDomainEvents
{

    use \Somnambulist\DomainEvents\Traits\RaisesDomainEvents;

    /** @ORM\Id @ORM\Column(type="string", name="id") */
    protected $id;

    /** @ORM\Column(type="string", name="name") */
    protected $name;

    /** @ORM\Column(type="string", name="another") */
    protected $another;

    /** @ORM\Column(type="datetime", name="created_at") */
    protected $createdAt;

    /**
     * Constructor.
     *
     * @param $id
     * @param $name
     * @param $another
     * @param $createdAt
     */
    public function __construct($id, $name, $another, $createdAt)
    {
        $this->id        = $id;
        $this->name      = $name;
        $this->another   = $another;
        $this->createdAt = $createdAt;

        $this->raise(new MyEntityCreatedEvent(['id' => $id, 'name' => $name, 'another' => $another]));
    }

    public function updateName($name)
    {
        $this->name = $name;

        $this->raise(new MyEntityNameUpdatedEvent(['id' => $this->id, 'new' => $name, 'previous' => $this->name]));
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getAnother()
    {
        return $this->another;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
