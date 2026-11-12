<?php

declare(strict_types=1);

use App\SiteTexts\RichTextCleaner;

it('keeps the toolbar tags', function (): void {
    $html = '<p>A <strong>b</strong> <em>c</em> <a href="https://cetronic.be">l</a></p><ul><li>x</li></ul><ol><li>y</li></ol><p>d<br>e</p>';

    expect(app(RichTextCleaner::class)->clean($html))->toBe($html);
});

it('removes headings, styles, scripts and event handlers', function (): void {
    $clean = app(RichTextCleaner::class)->clean(
        '<h1>Titre</h1><p style="color:red">A <script>alert(1)</script><a href="https://x.be" onclick="y()">l</a><img src="x.png"></p>',
    );

    expect($clean)->not->toContain('<h1')
        ->not->toContain('style=')
        ->not->toContain('script')
        ->not->toContain('onclick')
        ->not->toContain('<img')
        ->toContain('Titre')
        ->toContain('<a href="https://x.be">l</a>');
});

it('drops javascript links', function (): void {
    expect(app(RichTextCleaner::class)->clean('<p><a href="javascript:alert(1)">x</a></p>'))->not->toContain('javascript');
});

it('keeps mailto links', function (): void {
    expect(html_entity_decode(app(RichTextCleaner::class)->clean('<p><a href="mailto:info@cetronicbenelux.com">mail</a></p>')))
        ->toContain('href="mailto:info@cetronicbenelux.com"');
});

it('turns double line breaks into paragraphs', function (): void {
    expect(app(RichTextCleaner::class)->toEditor("Bonjour,<br><br>Merci pour votre commande.<br /><br/>L'équipe"))
        ->toBe("<p>Bonjour,</p><p>Merci pour votre commande.</p><p>L'équipe</p>");
});

it('leaves texts that already have paragraphs alone', function (): void {
    expect(app(RichTextCleaner::class)->toEditor('<p>A</p><p>B</p>'))->toBe('<p>A</p><p>B</p>');
});

it('keeps an empty text empty', function (): void {
    expect(app(RichTextCleaner::class)->toEditor(''))->toBe('')
        ->and(app(RichTextCleaner::class)->clean(''))->toBe('');
});

it('unwraps a text made of a single paragraph, shown inside a <p> by the site', function (): void {
    expect(app(RichTextCleaner::class)->clean('<div>Merci pour votre <strong>commande</strong>.<br>À bientôt</div>'))
        ->toBe('Merci pour votre <strong>commande</strong>.<br>À bientôt');
});
