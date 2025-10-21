<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Constraints;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;
use Slothsoft\Farah\FarahUrl\FarahUrl;
use Slothsoft\Farah\Module\Module;
use Slothsoft\Farah\Schema\SchemaLocator;
use DOMDocument;
use SplFileInfo;

final class DOMDocumentIsValidAccordingToSchema extends Constraint {
    
    private ?string $schemaLocation;
    
    public function __construct(?string $schemaLocation = null) {
        $this->schemaLocation = $schemaLocation;
    }
    
    public function toString(): string {
        return 'is valid according to its XML schema';
    }
    
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool {
        $document = $this->coerceToDomDocument($other);
        
        if (! $document) {
            if ($returnResult) {
                return false;
            }
            $type = is_object($other) ? get_class($other) : gettype($other);
            throw new ExpectationFailedException(trim($description . "\n" . sprintf('Failed converting %s into DOMDocument.', $type)));
        }
        
        $schemaLocation = $this->schemaLocation;
        if (! $schemaLocation) {
            $locator = new SchemaLocator();
            $schemaLocation = $locator->findSchemaLocation($document);
        }
        
        if (! $schemaLocation) {
            return true;
        }
        
        $prev = libxml_use_internal_errors(true);
        libxml_clear_errors();
        
        $ok = @$document->schemaValidate($schemaLocation);
        $libxmlErrors = libxml_get_errors();
        
        libxml_use_internal_errors($prev);
        libxml_clear_errors();
        
        if ($ok or $returnResult) {
            return $ok;
        }
        
        $errorsText = $this->formatLibxmlErrors($libxmlErrors, $schemaLocation);
        
        $failure = new ComparisonFailure('XML is valid according to schema', "XML failed schema validation:\n" . $errorsText, 'valid', $errorsText);
        
        throw new ExpectationFailedException(trim($description . "\n" . sprintf('Failed asserting that %s.', $this->toString())), $failure);
    }
    
    private function coerceToDomDocument($other): ?DOMDocument {
        if ($other instanceof DOMDocument) {
            return $other;
        }
        if ($other instanceof FarahUrl) {
            return Module::resolveToDOMWriter($other)->toDocument();
        }
        if ($other instanceof SplFileInfo) {
            $doc = new DOMDocument();
            return @$doc->load($other->getPathname()) ? $doc : null;
        }
        if (is_string($other)) {
            $doc = new DOMDocument();
            return @$doc->loadXML($other) ? $doc : null;
        }
        return null;
    }
    
    private function formatLibxmlErrors(array $errs, string $schemaLocation): string {
        if (! $errs) {
            return 'Unknown validation error (no libxml errors captured).';
        }
        $lines = [
            "Schema: $schemaLocation"
        ];
        foreach ($errs as $e) {
            $level = [
                LIBXML_ERR_WARNING => 'Warning',
                LIBXML_ERR_ERROR => 'Error',
                LIBXML_ERR_FATAL => 'Fatal'
            ][$e->level] ?? 'Notice';
            
            $lines[] = sprintf('[%s] %s:%d:%d %s (code %d)', $level, $e->file ?: '(document)', $e->line, $e->column, trim($e->message), $e->code);
        }
        return implode("\n", $lines);
    }
}
