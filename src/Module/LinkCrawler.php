<?php
declare(strict_types = 1);

namespace Slothsoft\FarahTesting\Module;

use DOMDocument;
use Ds\Set;
use Slothsoft\Core\DOMHelper;
use Slothsoft\Farah\Dictionary;

final class LinkCrawler {
    
    private const LINKING_ELEMENTS_HTML = [
        [
            DOMHelper::NS_HTML,
            'a',
            'href',
            true,
            false
        ],
        [
            DOMHelper::NS_HTML,
            'link',
            'href',
            true,
            false
        ],
        [
            DOMHelper::NS_HTML,
            'script',
            'src',
            false,
            false
        ],
        [
            DOMHelper::NS_HTML,
            'img',
            'src',
            true,
            false
        ],
        [
            DOMHelper::NS_HTML,
            'video',
            'src',
            false,
            false
        ],
        [
            DOMHelper::NS_HTML,
            'audio',
            'src',
            false,
            false
        ],
        [
            DOMHelper::NS_HTML,
            'source',
            'src',
            true,
            false
        ],
        [
            DOMHelper::NS_HTML,
            'track',
            'src',
            true,
            false
        ],
        [
            DOMHelper::NS_HTML,
            'iframe',
            'src',
            true,
            false
        ],
        [
            DOMHelper::NS_HTML,
            'embed',
            'src',
            true,
            false
        ],
        [
            DOMHelper::NS_HTML,
            'form',
            'action',
            false,
            false
        ]
    ];
    
    private const LINKING_ELEMENTS_SVG = [
        [
            DOMHelper::NS_SVG,
            'a',
            'href',
            true,
            false
        ],
        [
            DOMHelper::NS_SVG,
            'use',
            'href',
            false,
            false
        ],
        [
            DOMHelper::NS_SVG,
            'script',
            'href',
            false,
            false
        ],
        [
            DOMHelper::NS_SVG,
            'image',
            'href',
            true,
            false
        ],
        [
            DOMHelper::NS_SVG,
            'feImage',
            'href',
            true,
            false
        ],
        [
            DOMHelper::NS_HTML,
            'link',
            'href',
            true,
            true
        ]
    ];
    
    private const LINKING_ELEMENTS_XSLT = [
        [
            DOMHelper::NS_XSL,
            'include',
            'href',
            true,
            false
        ],
        [
            DOMHelper::NS_XSL,
            'import',
            'href',
            true,
            false
        ]
    ];
    
    private const LINKING_ELEMENTS_XSD = [
        [
            DOMHelper::NS_XSD,
            'include',
            'schemaLocation',
            true,
            false
        ],
        [
            DOMHelper::NS_XSD,
            'import',
            'schemaLocation',
            false,
            false
        ]
    ];
    
    private const LINKING_ELEMENTS_XINCLUDE = [
        [
            DOMHelper::NS_XINCLUDE,
            'include',
            'href',
            true,
            true
        ]
    ];
    
    private function getLinkingElements(string $namespace): iterable {
        switch ($namespace) {
            case DOMHelper::NS_HTML:
                yield from self::LINKING_ELEMENTS_HTML;
                break;
            case DOMHelper::NS_SVG:
                yield from self::LINKING_ELEMENTS_SVG;
                break;
            case DOMHelper::NS_XSL:
                yield from self::LINKING_ELEMENTS_XSLT;
                break;
            case DOMHelper::NS_XSD:
                yield from self::LINKING_ELEMENTS_XSD;
                break;
            default:
                break;
        }
        
        yield from self::LINKING_ELEMENTS_XINCLUDE;
    }
    
    private Set $whitelist;
    
    public function __construct(?Set $whitelist = null) {
        $this->whitelist = $whitelist ?? new Set();
    }
    
    public function crawlDocument(DOMDocument $document): iterable {
        if ($document->documentElement) {
            $xpath = DOMHelper::loadXPath($document, 0);
            foreach ($this->getLinkingElements((string) $document->documentElement->namespaceURI) as $args) {
                [
                    $ns,
                    $tag,
                    $attribute,
                    $isRequired,
                    $canAppearAlone
                ] = $args;
                $xpath->registerNamespace('search', $ns);
                $query = $canAppearAlone ? sprintf('//search:%s', $tag) : sprintf('//search:%s[count(ancestor::*) = count(ancestor::search:*)]', $tag);
                foreach ($xpath->query($query) as $linkNode) {
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

