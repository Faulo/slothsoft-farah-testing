<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Constraints;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Constraint\Constraint;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Comparator\ScalarComparator;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use DOMNode;

final class DOMNodeEqualTo extends Constraint {
    
    private DOMNode $expected;
    
    private string $expectedText;
    
    public function __construct(DOMNode $expected) {
        $this->expected = $expected;
        $this->expectedText = self::stringify($expected);
    }
    
    public function toString(): string {
        return 'is XML-equal to the expected node';
    }
    
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool {
        if ($this->expected === $other) {
            return true;
        }
        
        try {
            $otherText = $other instanceof DOMNode ? self::stringify($other) : '';
            
            $comparator = new ScalarComparator();
            
            $comparator->assertEquals($this->expectedText, $otherText);
        } catch (ComparisonFailure $f) {
            if ($returnResult) {
                return false;
            }
            
            throw new ExpectationFailedException(trim($description . "\n" . sprintf('Failed asserting that %s.%s%s', $this->failureDescription($other), "\n", $this->additionalFailureDescription($other))), $f);
        }
        
        return true;
    }
    
    protected function failureDescription($other): string {
        return 'the provided DOMNode ' . $this->toString();
    }
    
    protected function additionalFailureDescription($other): string {
        $otherText = self::stringify($other);
        
        return (new Differ(new UnifiedDiffOutputBuilder("--- Expected\n+++ Actual\n")))->diff($this->expectedText, $otherText);
    }
    
    public static function stringify(DOMNode $node): string {
        return implode("\n", [
            ...self::stringifyIterator($node)
        ]);
    }
    
    private static function stringifyIterator(DOMNode $node, int $depth = 0): iterable {
        switch ($node->nodeType) {
            case XML_DOCUMENT_NODE:
            case XML_HTML_DOCUMENT_NODE:
                if ($node->documentElement) {
                    yield from self::stringifyIterator($node->documentElement, $depth);
                }
                break;
            case XML_DOCUMENT_FRAG_NODE:
                foreach ($node->childNodes as $child) {
                    yield from self::stringifyIterator($child, $depth);
                }
                break;
            case XML_ELEMENT_NODE:
                yield sprintf('%s<%s', self::printDepth($depth), self::printName($node));
                $depth ++;
                
                $attributes = [];
                foreach ($node->attributes as $child) {
                    $attributes[self::printName($child)] = $child;
                }
                ksort($attributes);
                foreach ($attributes as $child) {
                    yield from self::stringifyIterator($child, $depth);
                }
                
                yield sprintf('%s>', self::printDepth($depth));
                
                $buffer = '';
                
                foreach ($node->childNodes as $child) {
                    switch ($child->nodeType) {
                        case XML_TEXT_NODE:
                        case XML_CDATA_SECTION_NODE:
                        case XML_ENTITY_REF_NODE:
                            $buffer .= $child->nodeValue;
                            break;
                        default:
                            $buffer = self::normalizeSpace($buffer);
                            if ($buffer !== '') {
                                yield sprintf('%s%s', self::printDepth($depth), json_encode($buffer));
                                $buffer = '';
                            }
                            
                            yield from self::stringifyIterator($child, $depth);
                    }
                }
                
                $buffer = self::normalizeSpace($buffer);
                if ($buffer !== '') {
                    yield sprintf('%s%s', self::printDepth($depth), json_encode($buffer));
                }
                break;
            case XML_ATTRIBUTE_NODE:
                yield sprintf('%s%s=%s', self::printDepth($depth), self::printName($node), json_encode($node->nodeValue));
                break;
            case XML_TEXT_NODE:
            case XML_CDATA_SECTION_NODE:
            case XML_ENTITY_REF_NODE:
                $text = self::normalizeSpace($node->nodeValue);
                if ($text !== '') {
                    yield sprintf('%s%s', self::printDepth($depth), json_encode($text));
                }
                break;
            case XML_PI_NODE:
                yield sprintf('%s<?%s %s?>', self::printDepth($depth), $node->target, $node->data);
                break;
            case XML_COMMENT_NODE:
            case XML_DOCUMENT_TYPE_NODE:
            case XML_NOTATION_NODE:
            case XML_ENTITY_DECL_NODE:
            case XML_ENTITY_NODE:
            default:
                break;
        }
    }
    
    private static function printName(DOMNode $node): string {
        return $node->namespaceURI ? "$node->namespaceURI $node->localName" : $node->localName;
    }
    
    private static function printDepth(int $depth): string {
        return str_pad('', $depth, '  ');
    }
    
    private static function normalizeSpace(?string $text): string {
        $text = (string) $text;
        $text = preg_replace('~[ \t\r\n]+~', ' ', $text);
        $text = trim($text, ' ');
        return $text;
    }
}
