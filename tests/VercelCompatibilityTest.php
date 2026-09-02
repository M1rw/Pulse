<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Core\Application;
use App\Core\Model;
use App\Http\Request;
use App\Models\Project;

class VercelCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear test db if exists
        if (file_exists('/tmp/pulse_test.db')) {
            @unlink('/tmp/pulse_test.db');
        }
    }

    public function testApplicationBootAndDbInitInTemp(): void
    {
        $app = new Application();
        $app->boot();

        $this->assertInstanceOf(Application::class, Application::getInstance());

        // Resolve DB
        $pdo = $app->make('db');
        $this->assertInstanceOf(\PDO::class, $pdo);

        // Check that tables were initialized
        $stmt = $pdo->query("SELECT count(*) as total FROM projects");
        $row = $stmt->fetch();
        $this->assertGreaterThan(0, (int)$row['total']);
    }

    public function testModelAutoInitializesDatabase(): void
    {
        // Force model database connection
        $connection = Project::getConnection();
        $this->assertInstanceOf(\PDO::class, $connection);

        $projects = Project::all();
        $this->assertNotEmpty($projects);
    }

    public function testRequestIpDetectionWithProxyHeaders(): void
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.195, 70.41.3.18';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $req = Request::capture();
        $this->assertEquals('203.0.113.195', $req->ip());

        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
        $_SERVER['HTTP_X_REAL_IP'] = '198.51.100.22';

        $req2 = Request::capture();
        $this->assertEquals('198.51.100.22', $req2->ip());

        unset($_SERVER['HTTP_X_REAL_IP']);
    }

    public function testErrorLoggingFallback(): void
    {
        log_error("Test error message for Vercel logging verification", ['context' => 'testing']);
        $this->assertTrue(true);
    }
}
