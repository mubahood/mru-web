<?php

namespace Tests\Feature\Admin;

use App\Console\Commands\ImportLegacyContent;
use ReflectionClass;
use Tests\TestCase;

/**
 * The title cleaner used when bringing content over from the old WordPress
 * site. Two earlier versions of it were wrong on real data, and both mistakes
 * are pinned here so they cannot come back:
 *
 *  · a whole-title uppercase test left "SWEARING-IN OF THE 16th GUILD COUNCIL"
 *    untouched, because of one stray lowercase pair;
 *  · folding word by word then turned EACOP into Eacop, IUEA into Iuea and
 *    "Eid : A Message" into "a Message".
 */
class LegacyTitleCleanerTest extends TestCase
{
    private function clean(string $raw): string
    {
        $class = new ReflectionClass(ImportLegacyContent::class);
        $method = $class->getMethod('cleanTitle');
        $method->setAccessible(true);

        return $method->invoke($class->newInstanceWithoutConstructor(), $raw);
    }

    /** @dataProvider titles */
    public function test_it_cleans_a_title(string $raw, string $expected): void
    {
        $this->assertSame($expected, $this->clean($raw));
    }

    public static function titles(): array
    {
        return [
            'shouting is sentence-cased' => [
                'MUTEESA I ROYAL UNIVERSITY STRATEGIC PLAN LAUNCH',
                'Muteesa I Royal University Strategic Plan Launch',
            ],
            'ordinals keep their number' => ['13TH GRADUATION CEREMONY', '13th Graduation Ceremony'],
            'stray lowercase does not defeat folding' => [
                'SWEARING-IN OF THE 16th GUILD COUNCIL',
                'Swearing-in of the 16th Guild Council',
            ],
            'acronyms survive a shouting title' => [
                'MRU Vs YMCA (UNIVERSITY LEAGUE FINALS 2026)',
                'MRU vs YMCA (University League Finals 2026)',
            ],
            'an all-acronym title is left alone' => ['MRU Vs MUBS', 'MRU vs MUBS'],
            'a typo the old site carried' => ['BUGANDA SPORTS GALLA', 'Buganda Sports Gala'],
            'entities are decoded' => ['ADMISSION LIST 2024 &#8211; 2025', 'Admission List 2024 – 2025'],
            'apostrophe entities decode' => [
                'Vice Chancellor&#8217;s Inauguration Ceremony',
                'Vice Chancellor’s Inauguration Ceremony',
            ],
            // The regressions.
            'an acronym inside a normal title is not folded' => [
                'EACOP Officials Recognized for Their Visit to MRU Kirumba Campus',
                'EACOP Officials Recognized for Their Visit to MRU Kirumba Campus',
            ],
            'another one' => [
                'Royal Lions Triumph Over IUEA in a Thrilling Match',
                'Royal Lions Triumph Over IUEA in a Thrilling Match',
            ],
            'a capital after punctuation is not lowercased' => [
                'Celebrating Eid : A Message of Unity, Service and Community',
                'Celebrating Eid : A Message of Unity, Service and Community',
            ],
            'a quoted Luganda word keeps its styling' => [
                'Muteesa I Royal University Hosts the Inaugural “OMMANYI” Themed Buganda Institutions Games',
                'Muteesa I Royal University Hosts the Inaugural “OMMANYI” Themed Buganda Institutions Games',
            ],
        ];
    }
}
