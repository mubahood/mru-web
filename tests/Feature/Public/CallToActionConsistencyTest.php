<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A button must be the size of the words on it.
 *
 * The site used to stack two labels in one grid cell — a short resting one and
 * a longer one shown on hover — so every button was sized by the text nobody
 * could see. "Apply Now" measured 341px because "Apply on the E-Portal" was
 * hiding underneath it, while "WhatsApp Admissions", a genuinely longer label,
 * sat at 258px. Buttons of unrelated width, set by invisible strings.
 *
 * This pins the removal: no hidden second label in the markup, and no rule in
 * the stylesheet that could reintroduce one.
 */
class CallToActionConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_button_carries_a_hidden_second_label(): void
    {
        foreach ($this->bladeFiles() as $file) {
            $source = (string) file_get_contents($file);

            $this->assertStringNotContainsString('class="cta-a"', $source,
                basename($file).' still stacks a hidden label inside a button');
            $this->assertStringNotContainsString('class="cta-b"', $source,
                basename($file).' still stacks a hidden label inside a button');
        }
    }

    public function test_the_stylesheet_cannot_reintroduce_one(): void
    {
        $css = (string) file_get_contents(public_path('css/mru.css'));

        $this->assertDoesNotMatchRegularExpression('/(?:^|[,}])\s*\.cta\s*[,{]/m', $css,
            'the two-label button rule is gone and must stay gone');
        $this->assertStringNotContainsString('.cta-b', $css);
    }

    /**
     * Every button on a rendered page is as wide as its own label allows,
     * which is only true once nothing invisible is sizing it.
     */
    public function test_a_short_label_does_not_render_a_long_button(): void
    {
        $html = (string) $this->get(route('home'))->assertOk()->getContent();

        // The hidden labels were emitted as sibling spans; if any survived,
        // the markup would still carry aria-hidden text inside a button.
        $this->assertDoesNotMatchRegularExpression(
            '/<a[^>]*class="[^"]*\bbtn\b[^"]*"[^>]*>\s*<span[^>]*>[^<]*<\/span>\s*<span[^>]*aria-hidden/i',
            $html,
            'a button is still carrying a hidden label span'
        );
    }

    /** @return list<string> */
    private function bladeFiles(): array
    {
        $files = [];
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views')));
        foreach ($it as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
