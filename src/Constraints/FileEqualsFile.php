<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Constraints;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\Factory as ComparatorFactory;
use Slothsoft\Core\IO\FileInfoFactory;
use Slothsoft\Core\StreamWrapper\StreamWrapperInterface;
use SplFileInfo;

/**
 * Compares two files byte-for-byte.
 *
 * The expected file is passed to the constructor. The actual value passed to
 * {@see Constraint::evaluate()} may be a {@see SplFileInfo} or a path string. Both files
 * must exist. If both values resolve to the same real path, the comparison
 * succeeds without reading the file contents.
 */
final class FileEqualsFile extends Constraint {
    
    public const DEFAULT_ROW_LENGTH = 128;
    
    private SplFileInfo $file;
    
    private int $rowLength;
    
    /**
     *
     * @param SplFileInfo|string $file
     * @param int $rowLength
     */
    public function __construct(SplFileInfo|string $file, int $rowLength = self::DEFAULT_ROW_LENGTH) {
        $this->file = $file instanceof SplFileInfo ? $file : FileInfoFactory::createFromPath($file);
        $this->rowLength = $rowLength;
    }
    
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool {
        if (! ($other instanceof SplFileInfo)) {
            $other = FileInfoFactory::createFromPath((string) $other);
        }
        
        try {
            try {
                if (! $this->file->isFile()) {
                    throw new ExpectationFailedException(sprintf("Expected file '%s' does not exist.", $this->file));
                }
                
                if (! $other->isFile()) {
                    throw new ExpectationFailedException(sprintf("Actual file '%s' does not exist.", $other));
                }
                
                if ($this->file->getRealPath() === $other->getRealPath()) {
                    return true;
                }
                
                $comparatorFactory = ComparatorFactory::getInstance();
                
                $expected = $this->file->openFile(StreamWrapperInterface::MODE_OPEN_READONLY);
                $actual = $other->openFile(StreamWrapperInterface::MODE_OPEN_READONLY);
                
                $expectedSize = $this->file->getSize();
                
                for ($i = 0; $i < $expectedSize; $i += $this->rowLength) {
                    $expectedRow = $expected->fread($this->rowLength);
                    $actualRow = $actual->fread($this->rowLength);
                    
                    $comparator = $comparatorFactory->getComparatorFor($expectedRow, $actualRow);
                    $comparator->assertEquals($expectedRow, $actualRow);
                }
                
                $actualSize = $actual->getSize();
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
