<?php
declare(strict_types=1);

namespace OCA\Postmag\Tests\Integration;

use OCP\AppFramework\App;
use Test\TestCase;


/**
 * This test shows how to make a small Integration Test. Query your class
 * directly from the container, only pass in mocks if needed and run your tests
 * against the database
 */
class AppTest extends TestCase {

    private $container;

    public function setUp(): void {
        parent::setUp();
        $app = new App('postmag');
        $this->container = $app->getContainer();
    }

    public function testAppInstalled() {
        $appManager = $this->container->query('OCP\App\IAppManager');
        $this->assertTrue($appManager->isInstalled('postmag'), 'Postmag is not installed in Nextcloud.');
    }

}
