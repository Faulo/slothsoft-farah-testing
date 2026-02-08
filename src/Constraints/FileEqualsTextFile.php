<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Constraints;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use Slothsoft\Core\IO\FileInfoFactory;
use SplFileInfo;

final class FileEqualsTextFile extends Constraint {
    
    private SplFileInfo $file;
    
    private int $options;
    
    /**
     *
     * @param SplFileInfo|string $file
     * @param int $options
     *            same options as PHP's file() function
     */
    public function __construct($file, int $options = 0) {
        $this->file = $file instanceof SplFileInfo ? $file : FileInfoFactory::createFromPath((string) $file);
        $this->options = $options;
    }
    
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool {
        if ($this->file === $other) {
            return true;
        }
        
        if (! ($other instanceof SplFileInfo)) {
            $other = FileInfoFactory::createFromPath((string) $other);
        }
        
        $comparatorFactory = ComparatorFactory::getInstance();
        try {
            try {
                if (! $this->file->isFile()) {
                    throw new ExpectationFailedException(sprintf("Expected file '%s' does not exist.", $this->file));
                }
                
                if (! $other->isFile()) {
                    throw new ExpectationFailedException(sprintf("Actual file '%s' does not exist.", $this->file));
                }
                
                $expected = file((string) $this->file, $this->options);
                $actual = file((string) $other, $this->options);
                
                $expectedSize = count($expected);
                
                for ($i = 0; $i < $expectedSize; $i ++) {
                    $expectedRow = $expected[$i];
                    $actualRow = $actual[$i] ?? '';
                    
                    $comparator = $comparatorFactory->getComparatorFor($expectedRow, $actualRow);
                    $comparator->assertEquals($expectedRow, $actualRow);
                }
                
                $actualSize = count($actual);
                $comparator = $comparatorFactory->getComparatorFor($expectedSize, $actualSize);
                $comparator->assertEquals($expectedSize, $actualSize);
            } catch (ComparisonFailure $f) {
                throw new ExpectationFailedException(trim($description . "\n" . $f->getMessage()), $f);
            }
        } catch (ExpectationFailedException $e) {
            if ($returnResult) {
                return false;
            }
            
            throw $e;
        }
        
        return true;
    }
    
    public function toString(): string {
        return sprintf("is equal to '%s'", $this->file);
    }
}
