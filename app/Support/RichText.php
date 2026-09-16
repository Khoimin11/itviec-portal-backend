<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class RichText
{
    public static function clean(?string $html): ?string
    {
        if (! $html) {
            return null;
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
            $body = $document->getElementsByTagName('body')->item(0);

            return $body ? self::children($body) : null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private static function children(DOMNode $parent): string
    {
        $html = '';
        foreach ($parent->childNodes as $node) {
            if ($node->nodeType === XML_TEXT_NODE) {
                $html .= htmlspecialchars($node->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

                continue;
            }
            if (! $node instanceof DOMElement) {
                continue;
            }
            $tag = strtolower($node->tagName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'svg', 'math', 'template'], true)) {
                continue;
            }
            $content = self::children($node);
            if (! in_array($tag, ['p', 'br', 'h1', 'h2', 'h3', 'ul', 'ol', 'li', 'strong', 'b', 'em', 'i', 'u', 'code', 'blockquote'], true)) {
                $html .= $content;

                continue;
            }
            $style = '';
            if (preg_match('/(?:^|;)\s*text-align\s*:\s*(left|right|center|justify)\s*(?:;|$)/i', $node->getAttribute('style'), $match)) {
                $style = ' style="text-align:'.strtolower($match[1]).'"';
            }
            $html .= $tag === 'br' ? '<br>' : '<'.$tag.$style.'>'.$content.'</'.$tag.'>';
        }

        return $html;
    }
}
