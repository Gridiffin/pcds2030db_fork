<?php

use PHPUnit\Framework\TestCase;

class GetReportingPeriodsTest extends TestCase
{
    private string $endpoint;

    protected function setUp(): void
    {
        if (!defined('PROJECT_ROOT_PATH')) {
            define('PROJECT_ROOT_PATH', dirname(dirname(__DIR__)));
        }
        $this->endpoint = PROJECT_ROOT_PATH . '/app/ajax/get_reporting_periods.php';
        $_GET = $_POST = $_REQUEST = [];
        $_SERVER['REQUEST_METHOD'] = 'GET';
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
    }

    private function runAjax(array $get = [], array $session = [], string $method = 'GET'): array
    {
        $_GET = $get;
        $_REQUEST = $get;
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SESSION = $session;

        ob_start();
        include $this->endpoint;
        $body = ob_get_clean();
        $code = http_response_code();
        $json = json_decode($body, true);
        return [$code, $json, $body];
    }

    public function test_requires_auth(): void
    {
        [$code, $json] = $this->runAjax(['program_id' => 1], []);
        $this->assertSame(403, $code, 'Should return 403 when not authenticated/authorized');
        $this->assertIsArray($json);
        $this->assertFalse($json['success']);
    }

    public function test_missing_program_id(): void
    {
        [$code, $json] = $this->runAjax([], ['user_id' => 10, 'role' => 'agency']);
        $this->assertSame(400, $code);
        $this->assertIsArray($json);
        $this->assertFalse($json['success']);
        $this->assertStringContainsString('Missing required parameter', $json['error']);
    }
}


