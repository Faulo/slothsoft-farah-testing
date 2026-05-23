<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Constraints;

use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Farah\FarahUrl\FarahUrl;

/**
 * DOMDocumentIsValidAccordingToSchemaTest
 *
 * @see DOMDocumentIsValidAccordingToSchema
 */
final class DOMDocumentIsValidAccordingToSchemaTest extends TestCase {
    
    /**
     * @test
     * @dataProvider provideXml
     */
    public function test_matches($input, bool $expected): void {
        $sut = new DOMDocumentIsValidAccordingToSchema();
        
        $actual = $sut->evaluate($input, '', true);
        
        self::assertThat($actual, new IsEqual($expected));
    }
    
    /**
     * @test
     */
    public function test_evaluate_acceptsXmlWithoutSchemaLocation(): void {
        $sut = new DOMDocumentIsValidAccordingToSchema();
        
        $actual = $sut->evaluate('<data/>', '', true);
        
        $this->assertTrue($actual);
    }
    
    /**
     * @test
     */
    public function test_evaluate_throwsWhenInputCannotBeConvertedToDomDocument(): void {
        $sut = new DOMDocumentIsValidAccordingToSchema();
        
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed converting string into DOMDocument.');
        
        $sut->evaluate('not xml');
    }
    
    /**
     * @test
     */
    public function test_evaluate_throwsSchemaValidationFailure(): void {
        $sut = new DOMDocumentIsValidAccordingToSchema();
        
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed asserting that is valid according to its XML schema.');
        
        $sut->evaluate('<data xmlns="http://schema.slothsoft.net/farah/module"/>');
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
