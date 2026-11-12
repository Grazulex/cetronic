<?php

declare(strict_types=1);

namespace App\SiteTexts;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * HTML of the long site texts: only what the rich editor toolbar can produce
 * is kept (paragraphs, line breaks, bold, italic, links and lists), so the
 * texts keep the layout of the site and cannot carry scripts or styles.
 */
final class RichTextCleaner
{
    private const BLOCKED = ['div', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'b', 'i', 'u', 'font', 'blockquote', 'pre', 'del', 'figure', 'figcaption'];

    private readonly HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig())
            ->allowElement('p')
            ->allowElement('br')
            ->allowElement('strong')
            ->allowElement('em')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('a', ['href'])
            ->allowLinkSchemes(['http', 'https', 'mailto']);

        // Blocked elements disappear but their text stays.
        foreach (self::BLOCKED as $element) {
            $config = $config->blockElement($element);
        }

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function clean(string $html): string
    {
        // The editor (Trix) writes blocks as <div>: they are paragraphs here.
        $html = (string) preg_replace(['#<div(\s[^>]*)?>#i', '#</div>#i'], ['<p>', '</p>'], $html);
        $html = (string) preg_replace(['#<b(\s[^>]*)?>#i', '#</b>#i', '#<i(\s[^>]*)?>#i', '#</i>#i'], ['<strong>', '</strong>', '<em>', '</em>'], $html);

        $clean = $this->sanitizer->sanitize($html);

        $clean = (string) preg_replace(['#<br\s*/?>#i', '#<p>\s*</p>#'], ['<br>', ''], $clean);

        return $this->unwrapSingleParagraph($clean);
    }

    /**
     * Old texts (e-mails) separate paragraphs with <br><br>: turn them into
     * real paragraphs before editing.
     */
    public function toEditor(string $html): string
    {
        if ('' === $html || 1 === preg_match('#<p[\s>]#i', $html)) {
            return $html;
        }

        $parts = preg_split('#(?:<br\s*/?>\s*){2,}#i', $html) ?: [$html];

        return implode('', array_map(fn(string $part): string => '<p>' . $part . '</p>', $parts));
    }

    /**
     * Some texts are printed inside a <p> by the site (order.thanks…): a text
     * of one paragraph is stored without it, to avoid <p><p>…</p></p>.
     */
    private function unwrapSingleParagraph(string $html): string
    {
        if (1 === preg_match('#^<p>(.*)</p>$#s', $html, $match) && ! str_contains($match[1], '<p>')) {
            return $match[1];
        }

        return $html;
    }
}
