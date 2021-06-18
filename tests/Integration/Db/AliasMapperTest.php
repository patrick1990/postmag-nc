<?php
declare(strict_types=1);

namespace OCA\Postmag\Tests\Integration\Db;

use Test\TestCase;
use OCP\AppFramework\App;
use OCA\Postmag\Db\Alias;

/**
 * @group DB
 */
class AliasMapperTest extends TestCase {
    
    private $mapper;
    private $userId = 'john';
    
    private $testAliases;
    private $insertedAliases;
    
    public function setUp(): void {
        parent::setUp();
        $app = new App('postmag');
        $container = $app->getContainer();
        
        // replace user id
        $container->registerService('UserId', function ($c) {
            return $this->userId;
        });
        
        $this->mapper = $container->get('OCA\Postmag\Db\AliasMapper');
        
        // Create test aliases
        $now = new \DateTime('now');
        $this->testAliases = [new Alias(), new Alias()];
        
        $this->testAliases[0]->setUserId('john');
        $this->testAliases[0]->setAliasId('1a2b');
        $this->testAliases[0]->setAliasName('alias');
        $this->testAliases[0]->setToMail('john@doe.com');
        $this->testAliases[0]->setComment('My Alias');
        $this->testAliases[0]->setEnabled(true);
        $this->testAliases[0]->setCreated($now->getTimestamp());
        $this->testAliases[0]->setLastModified($now->getTimestamp());
        
        $this->testAliases[1]->setUserId('jane');
        $this->testAliases[1]->setAliasId('2b3c');
        $this->testAliases[1]->setAliasName('important');
        $this->testAliases[1]->setToMail('jane@doe.com');
        $this->testAliases[1]->setComment('Very important');
        $this->testAliases[1]->setEnabled(true);
        $this->testAliases[1]->setCreated($now->getTimestamp());
        sleep(1);
        $this->testAliases[1]->setLastModified((new \DateTime('now'))->getTimestamp());
        
        // Insert aliases into database
        $this->insertedAliases = [];
        foreach ($this->testAliases as $alias) {
            $insert = $this->mapper->insert($alias);
            $this->insertedAliases[$insert->getId()] = $insert;
        }
    }
    
    public function tearDown(): void {
        // Clean up
        foreach (array_values($this->insertedAliases) as $alias) {
            $this->mapper->delete($alias);
        }
        
        parent::tearDown();
    }
    
    public function testFind(): void {
        $id = array_values($this->insertedAliases)[0]->getId();
        $ret = $this->mapper->find(
            $id,
            $this->testAliases[0]->getUserId()
            );
        
        $this->assertTrue($ret instanceof Alias, 'Result should be an Alias entity.');
        $this->assertSame($id, $ret->getId(), 'Did not return the expected id.');
        $this->assertSame($this->testAliases[0]->getUserId(), $ret->getUserId(), 'Did not return the expected user id.');
        $this->assertSame($this->testAliases[0]->getAliasId(), $ret->getAliasId(), 'Did not return the expected alias id.');
        $this->assertSame($this->testAliases[0]->getAliasName(), $ret->getAliasName(), 'Did not return the expected alias name.');
        $this->assertSame($this->testAliases[0]->getComment(), $ret->getComment(), 'Did not return the expected comment.');
        $this->assertSame($this->testAliases[0]->getEnabled(), $ret->getEnabled(), 'Did not return the expected enabled state.');
        $this->assertSame($this->testAliases[0]->getCreated(), $ret->getCreated(), 'Did not return the expected created timestamp.');
        $this->assertSame($this->testAliases[0]->getLastModified(), $ret->getLastModified(), 'Did not return the expected last modified timestamp.');
    }
    
    public function testFindAllPerUser(): void {
        $ret = $this->mapper->findAll(0, count($this->testAliases), $this->testAliases[0]->getUserId());
        
        $this->assertSame(1, count($ret), 'We expect only 1 result for this user.');
        $this->assertSame(array_values($this->insertedAliases)[0]->getId(), $ret[0]->getId(), 'Did not return the expected id.');
        $this->assertSame($this->testAliases[0]->getUserId(), $ret[0]->getUserId(), 'Did not return the expected user id.');
        $this->assertSame($this->testAliases[0]->getAliasId(), $ret[0]->getAliasId(), 'Did not return the expected alias id.');
        $this->assertSame($this->testAliases[0]->getAliasName(), $ret[0]->getAliasName(), 'Did not return the expected alias name.');
        $this->assertSame($this->testAliases[0]->getComment(), $ret[0]->getComment(), 'Did not return the expected comment.');
        $this->assertSame($this->testAliases[0]->getEnabled(), $ret[0]->getEnabled(), 'Did not return the expected enabled state.');
        $this->assertSame($this->testAliases[0]->getCreated(), $ret[0]->getCreated(), 'Did not return the expected created timestamp.');
        $this->assertSame($this->testAliases[0]->getLastModified(), $ret[0]->getLastModified(), 'Did not return the expected last modified timestamp.');
    }
    
    public function testFindAll(): void {
        $ret = $this->mapper->findAll(null, null, null);

        // Filter ret to get only test entries
        $testUserIds = [];
        foreach ($this->insertedAliases as $alias) {
            if(!in_array($alias->getUserId(), $testUserIds))
                array_push($testUserIds, $alias->getUserId());
        }

        // count test results
        $testResults = 0;
        foreach ($ret as $retAlias) {
            if(in_array($retAlias->getUserId(), $testUserIds)) {
                $this->assertSame($this->insertedAliases[$retAlias->getId()]->getUserId(), $retAlias->getUserId(), 'Did not return the expected user id.');
                $this->assertSame($this->insertedAliases[$retAlias->getId()]->getAliasId(), $retAlias->getAliasId(), 'Did not return the expected alias id.');
                $this->assertSame($this->insertedAliases[$retAlias->getId()]->getAliasName(), $retAlias->getAliasName(), 'Did not return the expected alias name.');
                $this->assertSame($this->insertedAliases[$retAlias->getId()]->getComment(), $retAlias->getComment(), 'Did not return the expected comment.');
                $this->assertSame($this->insertedAliases[$retAlias->getId()]->getEnabled(), $retAlias->getEnabled(), 'Did not return the expected enabled state.');
                $this->assertSame($this->insertedAliases[$retAlias->getId()]->getCreated(), $retAlias->getCreated(), 'Did not return the expected created timestamp.');
                $this->assertSame($this->insertedAliases[$retAlias->getId()]->getLastModified(), $retAlias->getLastModified(), 'Did not return the expected last modified timestamp.');
                $testResults = $testResults + 1;
            }
        }
        $this->assertSame(count($this->testAliases), $testResults, 'Did not get all results.');
    }
    
    public function testFindLastModifiedPerUser(): void {
        $ret = $this->mapper->findLastModified($this->testAliases[0]->getUserId());
        
        // get last modified alias of inserted aliases
        $expected = array_values($this->insertedAliases)[0];
        foreach (array_values($this->insertedAliases) as $alias) {
            if($alias->getUserId() === $expected->getUserId())
                if($alias->getLastModified() > $expected->getLastModified())
                    $expected = $alias;
        }
        
        $this->assertTrue($ret instanceof Alias, 'Result should be an Alias entity.');
        $this->assertSame($expected->getId(), $ret->getId(), 'Did not return the expected id.');
        $this->assertSame($expected->getUserId(), $ret->getUserId(), 'Did not return the expected user id.');
        $this->assertSame($expected->getAliasId(), $ret->getAliasId(), 'Did not return the expected alias id.');
        $this->assertSame($expected->getAliasName(), $ret->getAliasName(), 'Did not return the expected alias name.');
        $this->assertSame($expected->getComment(), $ret->getComment(), 'Did not return the expected comment.');
        $this->assertSame($expected->getEnabled(), $ret->getEnabled(), 'Did not return the expected enabled state.');
        $this->assertSame($expected->getCreated(), $ret->getCreated(), 'Did not return the expected created timestamp.');
        $this->assertSame($expected->getLastModified(), $ret->getLastModified(), 'Did not return the expected last modified timestamp.');
    }
    
    public function testFindLastModified(): void {
        $ret = $this->mapper->findLastModified(null);
        
        // get last modified alias of inserted aliases
        $expected = array_values($this->insertedAliases)[0];
        foreach (array_values($this->insertedAliases) as $alias) {
            if($alias->getLastModified() > $expected->getLastModified())
                $expected = $alias;
        }
        
        $this->assertTrue($ret instanceof Alias, 'Result should be an Alias entity.');
        $this->assertSame($expected->getId(), $ret->getId(), 'Did not return the expected id.');
        $this->assertSame($expected->getUserId(), $ret->getUserId(), 'Did not return the expected user id.');
        $this->assertSame($expected->getAliasId(), $ret->getAliasId(), 'Did not return the expected alias id.');
        $this->assertSame($expected->getAliasName(), $ret->getAliasName(), 'Did not return the expected alias name.');
        $this->assertSame($expected->getComment(), $ret->getComment(), 'Did not return the expected comment.');
        $this->assertSame($expected->getEnabled(), $ret->getEnabled(), 'Did not return the expected enabled state.');
        $this->assertSame($expected->getCreated(), $ret->getCreated(), 'Did not return the expected created timestamp.');
        $this->assertSame($expected->getLastModified(), $ret->getLastModified(), 'Did not return the expected last modified timestamp.');
    }
    
    public function testContainsAliasId(): void {
        $this->assertTrue(
            $this->mapper->containsAliasId(
                $this->testAliases[0]->getAliasId(),
                $this->testAliases[0]->getUserId(),
                $this->testAliases[0]->getAliasName()
                ),
            'Alias id was allready present but not found.'
            );
        $this->assertFalse(
            $this->mapper->containsAliasId(
                strrev($this->testAliases[0]->getAliasId()),
                $this->testAliases[0]->getUserId(),
                $this->testAliases[0]->getAliasName()
                ),
            'Alias id was not present but found.'
            );
    }
    
}