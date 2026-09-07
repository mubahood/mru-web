<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * The ODEL section's own navigation.
 *
 * Separate from SiteNav on purpose. The main menu is organised around a
 * prospective school-leaver's questions; this one is organised around the
 * question an adult with a job and a family actually arrives with — how would
 * this fit my life — so "Study modes" leads, where the main site would lead
 * with the institution.
 *
 * One list feeds the header, the mobile sheet and the footer columns, so they
 * cannot drift apart.
 */
class OdelNav
{
    /** @return list<array{label:string,url:string,match:list<string>}> */
    public static function items(): array
    {
        return [
            ['label' => 'Study modes', 'url' => route('odel.modes'), 'match' => ['odel.modes', 'odel.mode']],
            ['label' => 'How it works', 'url' => route('odel.how-it-works'), 'match' => ['odel.how-it-works']],
            ['label' => 'What you get', 'url' => route('odel.what-you-get'), 'match' => ['odel.what-you-get']],
            ['label' => 'Credit', 'url' => route('odel.credit'), 'match' => ['odel.credit']],
            ['label' => 'Programmes', 'url' => route('odel.programmes'), 'match' => ['odel.programmes']],
            ['label' => 'Quality', 'url' => route('odel.quality'), 'match' => ['odel.quality']],
            ['label' => 'Support', 'url' => route('odel.support'), 'match' => ['odel.support', 'odel.faqs']],
        ];
    }

    /** @param  array{match:list<string>}  $item */
    public static function isOn(array $item): bool
    {
        foreach ($item['match'] as $name) {
            if (Route::currentRouteNamed($name)) {
                return true;
            }
        }

        return false;
    }

    /** The footer's grouped view of the same destinations. @return list<array<string,mixed>> */
    public static function columns(): array
    {
        return [
            [
                'label' => 'Studying this way',
                'links' => [
                    ['label' => 'The six study modes', 'url' => route('odel.modes')],
                    ['label' => 'How ODEL works', 'url' => route('odel.how-it-works')],
                    ['label' => 'What you are entitled to', 'url' => route('odel.what-you-get')],
                    ['label' => 'Credit for what you know', 'url' => route('odel.credit')],
                ],
            ],
            [
                'label' => 'Before you commit',
                'links' => [
                    ['label' => 'Programmes', 'url' => route('odel.programmes')],
                    ['label' => 'Quality and assessment', 'url' => route('odel.quality')],
                    ['label' => 'Who runs ODEL', 'url' => route('odel.governance')],
                    ['label' => 'Dates', 'url' => route('odel.calendar')],
                ],
            ],
            [
                'label' => 'Getting started',
                'links' => [
                    ['label' => 'Start here', 'url' => route('odel.apply')],
                    ['label' => 'Support and contacts', 'url' => route('odel.support')],
                    ['label' => 'Questions', 'url' => route('odel.faqs')],
                ],
            ],
        ];
    }
}
