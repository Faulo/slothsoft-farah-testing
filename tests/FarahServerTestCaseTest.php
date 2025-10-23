<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting;

use PHPUnit\Framework\Constraint\IsIdentical;

/**
 * FarahServerTestCaseTest
 *
 * @see FarahServerTestCase
 */
class FarahServerTestCaseTest extends FarahServerTestCase {
    
    private static array $values = [];
    
    protected static function setUpServer(): void {
        self::$values['server'] = self::$server;
    }
    
    protected function setUpClient(): void {
        self::$values['client'] = $this->client;
    }
    
    public function test_setUpServer() {
        $this->assertNotNull(self::$values['server']);
        
        $this->assertThat(self::$values['server'], new IsIdentical(self::$server));
    }
    
    public function test_setUpClient() {
        $this->assertNotNull(self::$values['client']);
        
        $this->assertThat(self::$values['client'], new IsIdentical($this->client));
    }
}
