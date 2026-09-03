<?php

namespace Tests\Feature\Shell;

use Tests\TestCase;

/**
 * Guards one specific mistake.
 *
 * The public design system lives in public/css/mru.css. A terminal cursor
 * was styled as a bare `.caret`, and the navigation chevron and the account-menu
 * chevron were both already using that class name, so both header icons turned
 * into a blinking gold block.
 *
 * There is no general rule to assert here. Plenty of classes in this sheet are
 * deliberately styled globally and then refined inside a component (.btn,
 * .eyebrow, .lead, .ph), which is ordinary cascade rather than a collision, and
 * the two are not reliably distinguishable by pattern. So this pins the actual
 * bug: the cursor keeps a name of its own, and the chevron stays scoped.
 */
class StylesheetScopeTest extends TestCase
{
    public function test_the_terminal_cursor_does_not_reuse_the_chevron_class(): void
    {
        $css = $this->layoutCss();

        $this->assertStringContainsString('.term-caret{', $css, 'the terminal cursor must have its own class');
        $this->assertDoesNotMatchRegularExpression(
            '/(?:^|[,}])\s*\.caret\s*\{/m',
            $css,
            '.caret must never be styled without a parent, the nav and account chevrons both use it'
        );
    }

    public function test_both_chevrons_are_styled_only_in_context(): void
    {
        $css = $this->layoutCss();

        $this->assertStringContainsString('.nav-link .caret{', $css);
        $this->assertStringContainsString('.acct-trigger .caret{', $css);
    }

    public function test_the_page_header_is_defined_once(): void
    {
        /* It had fragmented into three rules in different parts of the sheet,
           and the last one set `padding:26px 0 0`, so every page header on the
           site sat with its content pressed against whatever came next. Rules
           for one component belong in one place, or the last edit silently
           wins. */
        $css = $this->refreshCss();

        $this->assertSame(1, preg_match_all('/(?:^|[,}])\s*\.page-hero\s*\{/m', $css),
            '.page-hero must be defined in exactly one rule in the current design layer');
    }

    private function layoutCss(): string
    {
        return (string) file_get_contents(public_path('css/mru.css'));
    }

    /**
     * The refresh layer: everything after the banner that marks it.
     *
     * The sheet is deliberately two layers — an inherited base, and the
     * current design language that settles it. A component may therefore be
     * touched twice in the file as a whole, but only once in the layer being
     * edited, which is where the original "last edit silently wins" bug
     * would happen again.
     */
    private function refreshCss(): string
    {
        $css = $this->layoutCss();
        $at = strpos($css, 'MRU visual system');

        $this->assertNotFalse($at, 'the refresh layer banner must stay: the tests below scope to it');

        return substr($css, $at);
    }
}
