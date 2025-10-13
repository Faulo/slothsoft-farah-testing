<?php
declare(strict_types = 1);
namespace Slothsoft\FarahTesting\Module;

use Ds\Set;
use Slothsoft\Core\DOMHelper;
use DOMDocument;
use Slothsoft\Farah\Dictionary;

final class LinkCrawler {
    
    private const LINKING_ELEMENTS_HTML = [
        [
            DOMHelper::NS_HTML,
            'a',
            'href',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'link',
            'href',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'script',
            'src',
            false
        ],
        [
            DOMHelper::NS_HTML,
            'img',
            'src',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'video',
            'src',
            false
        ],
        [
            DOMHelper::NS_HTML,
            'audio',
            'src',
            false
        ],
        [
            DOMHelper::NS_HTML,
            'source',
            'src',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'track',
            'src',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'iframe',
            'src',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'embed',
            'src',
            true
        ],
        [
            DOMHelper::NS_HTML,
            'form',
            'action',
            false
        ]
    ];
    
    private const LINKING_ELEMENTS_XSLT = [
        [
            DOMHelper::NS_XSL,
            'include',
            'href',
            true
        ],
        [
            DOMHelper::NS_XSL,
            'import',
            'href',
            true
        ]
    ];
    
    private const LINKING_ELEMENTS_XSD = [
        [
            DOMHelper::NS_XSD,
            'include',
            'schemaLocation',
            true
        ],
        [
            DOMHelper::NS_XSD,
            'import',
            'schemaLocation',
            false
        ]
    ];
    
    private const LINKING_ELEMENTS_XINCLUDE = [
        [
            DOMHelper::NS_XINCLUDE,
            'include',
            'href',
            true
        ]
    ];
    
    private function getLinkingElements(string $namespace): iterable {
        switch ($namespace) {
            case DOMHelper::NS_HTML:
            case DOMHelper::NS_SVG:
                yield from self::LINKING_ELEMENTS_HTML;
                yield from self::LINKING_ELEMENTS_XINCLUDE;
                break;
            case DOMHelper::NS_XSL:
                yield from self::LINKING_ELEMENTS_XSLT;
                yield from self::LINKING_ELEMENTS_XINCLUDE;
                break;
            case DOMHelper::NS_XSD:
                yield from self::LINKING_ELEMENTS_XSD;
                yield from self::LINKING_ELEMENTS_XINCLUDE;
                break;
            default:
                yield from self::LINKING_ELEMENTS_XINCLUDE;
                break;
        }
    }
    
    private Set $whitelist;
    
    public function __construct(?Set $whitelist = null) {
        $this->whitelist = $whitelist ?? new Set();
    }
    
    public function crawlDocument(DOMDocument $document): iterable {
        if ($document->documentElement) {
            foreach ($this->getLinkingElements((string) $document->documentElement->namespaceURI) as $args) {
                [
                    $ns,
                    $tag,
                    $attribute,
                    $isRequired
                ] = $args;
                foreach ($document->getElementsByTagNameNS($ns, $tag) as $linkNode) {
                    if ($linkNode->hasAttribute(Dictionary::XPATH_DICT_ATTR_REPLACE)) {
                        continue;
                    }
                    
                    $link = (string) $linkNode->getAttribute($attribute);
                    
                    if ($link === '') {
                        // use fallback attribute
                        $link = (string) $linkNode->getAttribute('data-' . $attribute);
                    }
                    
                    if ($this->whitelist->contains($link)) {
                        continue;
                    }
                    
                    if ($link === '' and ! $isRequired) {
                        continue;
                    }
                    
                    $reference = implode(' ', [
                        $tag,
                        $attribute,
                        "'$link'"
                    ]);
                    
                    yield $reference => $link;
                }
            }
        }
    }
}

