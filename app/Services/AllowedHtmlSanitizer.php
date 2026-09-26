<?php

namespace App\Services;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;

class AllowedHtmlSanitizer
{
    /** @var array<string, array<int, string>> */
    private const ALLOWED = [
        'p' => ['class'], 'h2' => [], 'h3' => [], 'h4' => [], 'ul' => [], 'ol' => [], 'li' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'blockquote' => [], 'br' => [],
        'a' => ['href', 'title', 'target'],
    ];

    /** @var array<int, string> */
    private const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'svg', 'math'];

    public function sanitize(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="content-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('content-root');
        if (! $root) {
            return null;
        }

        $this->cleanChildren($root);
        $result = '';
        foreach ($root->childNodes as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result) ?: null;
    }

    private function cleanChildren(DOMNode $parent): void
    {
        for ($node = $parent->firstChild; $node !== null;) {
            $next = $node->nextSibling;

            if ($node instanceof DOMComment) {
                $parent->removeChild($node);
            } elseif ($node instanceof DOMElement) {
                $tag = strtolower($node->tagName);
                if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                    $parent->removeChild($node);
                } elseif (! array_key_exists($tag, self::ALLOWED)) {
                    $this->cleanChildren($node);
                    while ($node->firstChild) {
                        $parent->insertBefore($node->firstChild, $node);
                    }
                    $parent->removeChild($node);
                } else {
                    $this->cleanElement($node, $tag);
                    $this->cleanChildren($node);
                }
            }

            $node = $next;
        }
    }

    private function cleanElement(DOMElement $element, string $tag): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            if (! in_array(strtolower($attribute->name), self::ALLOWED[$tag], true)) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($tag === 'p' && $element->hasAttribute('class')) {
            $classes = array_intersect(preg_split('/\s+/', $element->getAttribute('class')) ?: [], ['lead', 'text-center']);
            if ($classes === []) {
                $element->removeAttribute('class');
            } else {
                $element->setAttribute('class', implode(' ', $classes));
            }
        }

        if ($tag === 'a') {
            $href = trim($element->getAttribute('href'));
            if (! $this->isSafeUrl($href)) {
                $element->removeAttribute('href');
            }
            if ($element->getAttribute('target') === '_blank') {
                $element->setAttribute('rel', 'noopener noreferrer');
            } else {
                $element->removeAttribute('target');
            }
        }
    }

    private function isSafeUrl(string $url): bool
    {
        if ($url === '') {
            return true;
        }

        if (preg_match('/[\\\\\x00-\x20\x7f]/', $url) || preg_match('/%(?:0[0-9a-f]|1[0-9a-f]|20|2f|5c|7f)/i', $url)) {
            return false;
        }

        if (str_starts_with($url, '#')) {
            return true;
        }

        if (str_starts_with($url, '/')) {
            return ! str_starts_with($url, '//') && ! preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $url);
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https', 'mailto', 'tel'], true);
    }
}
