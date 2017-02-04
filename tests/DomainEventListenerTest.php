<?php

namespace Somnambulist\Tests\DomainEvents;

use Carbon\Carbon;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Somnambulist\DomainEvents\DomainEventListener;

/**
 * Class DomainEventListenerTest
 *
 * @package    Somnambulist\Tests\DomainEvents
 * @subpackage Somnambulist\Tests\DomainEvents\DomainEventListenerTest
 * @author     Dave Redfern
 */
class DomainEventListenerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var EntityManager
     */
    protected $em;

    protected function setUp()
    {
        $evm = new EventManager();
        $evm->addEventSubscriber(new DomainEventListener());
        $evm->addEventListener('onMyEntityCreated', new \EventListener());
        $evm->addEventListener('onMyEntityAddedAnotherEntity', new \EventListener());

        $conn = [
            'driver'   => $GLOBALS['DOCTRINE_DRIVER'],
            'memory'   => $GLOBALS['DOCTRINE_MEMORY'],
            'dbname'   => $GLOBALS['DOCTRINE_DATABASE'],
            'user'     => $GLOBALS['DOCTRINE_USER'],
            'password' => $GLOBALS['DOCTRINE_PASSWORD'],
            'host'     => $GLOBALS['DOCTRINE_HOST'],
        ];

        $driver = new YamlDriver(__DIR__ . '/_data/mappings');
        $config = new Configuration();
        $config->setMetadataCacheImpl(new ArrayCache());
        $config->setQueryCacheImpl(new ArrayCache());
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('Somnambulist\Tests\DomainEvents\Proxies');
        $config->setMetadataDriverImpl($driver);

        $em = EntityManager::create($conn, $config, $evm);

        $schemaTool = new SchemaTool($em);

        try {
            $schemaTool->createSchema([
                $em->getClassMetadata(\MyEntity::class),
                $em->getClassMetadata(\MyOtherEntity::class),
            ]);
        } catch (\Exception $e) {
            if (
                $GLOBALS['DOCTRINE_DRIVER'] != 'pdo_mysql' ||
                !($e instanceof \PDOException && strpos($e->getMessage(), 'Base table or view already exists') !== false)
            ) {
                throw $e;
            }
        }

        $this->em = $em;
    }

    protected function tearDown()
    {
        $this->em = null;
    }



    /**
     * @group listener
     * @group domain-events
     */
    public function testFiresEvents()
    {
        $entity = new \MyEntity('test-id', 'test', 'bob', Carbon::now());
        $this->em->persist($entity);
        $this->expectOutputString("New item created with id: test-id, name: test, another: bob\n");
        $this->em->flush();

        $this->assertCount(0, $entity->releaseAndResetEvents());
    }

    /**
     * @group listener
     * @group domain-events
     */
    public function testFiresEventsWhenRelatedEntitiesChangedButRootNot()
    {
        $entity = new \MyEntity('test-id', 'test', 'bob', Carbon::now());
        $this->em->persist($entity);
        $this->em->flush();

        $this->assertCount(0, $entity->releaseAndResetEvents());

        $this->getActualOutput();

        sleep(1);

        $entity->addRelated('example', 'test-test', Carbon::now());

        $this->em->flush();

        $expected  = "New item created with id: test-id, name: test, another: bob\n";
        $expected .= "Added related entity with name: example, another: test-test to entity id: test-id\n";

        $this->expectOutputString($expected);

        $this->assertCount(0, $entity->releaseAndResetEvents());
    }
}
