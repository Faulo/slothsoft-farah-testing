<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Constraints;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Constraint\IsEqual;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Farah\FarahUrl\FarahUrl;

/**
 * DOMDocumentIsValidAccordingToSchemaTest
 *
 * @see DOMDocumentIsValidAccordingToSchema
 */
final class DOMDocumentIsValidAccordingToSchemaTest extends TestCase {
    
    /**
     *
     * @dataProvider provideXml
     */
    public function test_matches($input, bool $expected): void {
        $sut = new DOMDocumentIsValidAccordingToSchema();
        
        $actual = $sut->evaluate($input, '', true);
        
        self::assertThat($actual, new IsEqual($expected));
    }
    
    public static function provideXml(): iterable {
        yield 'invalid xml' => [
            '<data xmlns="http://schema.slothsoft.net/farah/module"/>',
            false
        ];
        
        yield 'valid xml' => [
            '<assets xmlns="https://schema.slothsoft.net/farah/module"/>',
            true
        ];
        
        yield 'valid file' => [
            FileInfoFactory::createFromString('<assets xmlns="https://schema.slothsoft.net/farah/module"/>'),
            true
        ];
        
        yield 'valid manifest url' => [
            FarahUrl::createFromReference('farah://slothsoft@farah/'),
            true
        ];
        
        yield 'valid fragment url' => [
            FarahUrl::createFromReference('farah://slothsoft@farah/api'),
            true
        ];
    }
}
