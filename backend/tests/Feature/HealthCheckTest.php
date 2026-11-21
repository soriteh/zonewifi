<?php
namespace Tests\Feature;
use Tests\TestCase;
class HealthCheckTest extends TestCase {
    public function test_home_page_is_available(): void { $this->get('/')->assertOk()->assertSee('ZoneWifi'); }
    public function test_api_status_endpoint_is_available(): void { $this->getJson('/api/status')->assertOk()->assertJsonPath('status','ok'); }
}
