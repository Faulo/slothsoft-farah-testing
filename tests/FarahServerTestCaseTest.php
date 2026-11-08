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
    
    /** @var array<string, FarahServer|\Symfony\Component\Panther\Client> */
    private static array $values = [];
    
    protected static function setUpServer(): void {
        self::$values['server'] = self::$server;
    }
    
    protected function setUpClient(): void {
        self::$values['client'] = $this->client;
    }
    
    /**
     * @test
     */
    public function test_setUpServer(): void {
        $this->assertThat(self::$values['server'], new IsIdentical(self::$server));
    }
    
    /**
     * @test
     */
    public function test_setUpClient(): void {
        $this->assertThat(self::$values['client'], new IsIdentical($this->client));
    }
}
