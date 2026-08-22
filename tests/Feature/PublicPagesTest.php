<?php

namespace Tests\Feature;

use App\Models\FrameworkPillar;
use Database\Seeders\FrameworkPillarSeeder;
use Database\Seeders\StrikeLevelSeeder;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The public surface (SPEC.md §9 "Public", plus the /about page added on top
 * of §7's table).
 *
 * Every request here is made as a guest, before any authentication, so a
 * leaked guard cannot make a page look reachable that is not.
 */
class PublicPagesTest extends TestCase
{
    /** @var array<int, string> */
    private const PUBLIC_URIS = ['/', '/about', '/framework', '/contact'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(FrameworkPillarSeeder::class);
        $this->seed(StrikeLevelSeeder::class);
    }

    /**
     * SPEC.md §3.4: direction is locale-driven on every layout, and §3.3: the
     * copy itself comes from the lang files rather than the template.
     */
    public function test_every_public_page_renders_its_own_locale_and_direction(): void
    {
        foreach (self::PUBLIC_URIS as $uri) {
            $dhivehi = $this->withSession(['locale' => 'dv'])->get($uri);
            $dhivehi->assertOk();
            $dhivehi->assertSee('dir="rtl"', false);
            $dhivehi->assertSee(trans('nav.about', [], 'dv'));
            $dhivehi->assertDontSee(trans('nav.about', [], 'en'));

            $english = $this->withSession(['locale' => 'en'])->get($uri);
            $english->assertOk();
            $english->assertSee('dir="ltr"', false);
            $english->assertSee(trans('nav.about', [], 'en'));
            $english->assertDontSee(trans('nav.about', [], 'dv'));
        }
    }

    /**
     * The hero is driven entirely by `public.hero.slides`, so every slide the
     * lang file defines must reach the page — in the reader's language.
     */
    public function test_home_hero_renders_every_slide_in_the_active_locale(): void
    {
        foreach (['dv', 'en'] as $locale) {
            $slides = trans('public.hero.slides', [], $locale);

            $this->assertNotEmpty($slides, "public.hero.slides is empty for '{$locale}'.");

            $response = $this->withSession(['locale' => $locale])->get('/');
            $response->assertOk();

            foreach ($slides as $slide) {
                $response->assertSee($slide['eyebrow']);
                $response->assertSee($slide['title']);
                $response->assertSee($slide['body']);
            }
        }
    }

    /**
     * SPEC.md §8.8: the pillar set is table-driven. The home, about and
     * framework pages must render whatever the table holds rather than a
     * hardcoded list.
     */
    public function test_pillar_content_is_read_from_the_database_on_every_page_that_shows_it(): void
    {
        // A sentinel name, so "must not appear" cannot be satisfied by accident
        // through a word that also occurs in the surrounding copy.
        $hidden = FrameworkPillar::query()->where('code', 'sport')->firstOrFail();
        $hidden->update(['is_active' => false, 'name_en' => 'ZzDeactivatedPillar']);

        $active = FrameworkPillar::active()->get();

        $this->assertCount(3, $active, 'Deactivating a pillar must reduce the active set.');

        foreach (['/', '/about', '/framework'] as $uri) {
            $response = $this->withSession(['locale' => 'en'])->get($uri);
            $response->assertOk();

            foreach ($active as $pillar) {
                $response->assertSee($pillar->name_en);
            }

            $response->assertDontSee($hidden->name_en);
        }
    }

    /**
     * SPEC.md §9 "Public 1" and §10: the public site carries the enrolment
     * notice and NO registration or password-reset affordance of any kind.
     */
    public function test_public_pages_offer_no_registration_or_password_reset_link(): void
    {
        $forbidden = ['/register', 'forgot-password', 'reset-password', 'password/request'];

        foreach (array_merge(self::PUBLIC_URIS, ['/login']) as $uri) {
            $content = $this->get($uri)->assertOk()->getContent();

            preg_match_all('/href="([^"]*)"/', $content, $matches);

            foreach ($matches[1] as $href) {
                foreach ($forbidden as $needle) {
                    $this->assertStringNotContainsString(
                        $needle,
                        $href,
                        "{$uri} links to '{$href}', which is a self-service auth affordance (SPEC.md §10)."
                    );
                }
            }
        }

        $this->withSession(['locale' => 'en'])
            ->get('/')
            ->assertSee(trans('public.home.enrolment_notice', [], 'en'));
    }

    /**
     * The W-ACADEMY acronym (customer content, 21 Aug 2026 — see
     * "customer documents/WACADEMY-ACRONYM.md"). Every entry the lang file
     * defines must reach /about in the reader's own language, letter
     * included.
     */
    public function test_about_renders_every_acronym_value_in_the_active_locale(): void
    {
        foreach (['dv', 'en'] as $locale) {
            $values = trans('public.values.items', [], $locale);

            $this->assertNotEmpty($values, "public.values.items is empty for '{$locale}'.");

            $response = $this->withSession(['locale' => $locale])->get('/about');
            $response->assertOk();

            foreach ($values as $key => $value) {
                foreach (['letter', 'title', 'body'] as $part) {
                    $this->assertArrayHasKey($part, $value, "public.values.items.{$key} has no '{$part}' in '{$locale}'.");
                    $response->assertSee($value[$part]);
                }
            }
        }
    }

    /**
     * The eight values are the letters of the academy's name, in order. The
     * customer's own English message carried a stray ninth entry
     * ("E - Enable") that breaks the acronym and is absent from their
     * Dhivehi; this is what stops it — or any dropped letter — coming back
     * unnoticed.
     */
    public function test_the_english_value_letters_spell_the_academy_name(): void
    {
        $letters = array_column(trans('public.values.items', [], 'en'), 'letter');

        $this->assertSame('WACADEMY', implode('', $letters));

        $this->assertCount(
            count($letters),
            trans('public.values.items', [], 'dv'),
            'The Dhivehi acronym must hold exactly the same number of entries as the English.'
        );
    }

    /**
     * The academy's own contact details (supplied 22 Aug 2026). These were the
     * last `[CONTENT PENDING]` markers on the public site, and they render in
     * two places each — the contact block and the footer — so this walks every
     * public page rather than just /contact.
     *
     * The marker assertion is deliberately scoped to Dhivehi: the strike
     * ladder's `*_en` columns are still genuinely pending (BACKLOG-NEW.md
     * NEW-7), so /framework in English is *expected* to carry one.
     */
    public function test_the_academy_contact_details_reach_every_public_page_with_no_pending_markers(): void
    {
        foreach (['dv', 'en'] as $locale) {
            foreach (['address_value', 'phone_value', 'email_value'] as $key) {
                $value = trans("public.contact.{$key}", [], $locale);

                $this->assertStringNotContainsString(
                    'CONTENT PENDING',
                    $value,
                    "public.contact.{$key} is still pending in '{$locale}'."
                );

                foreach (['/contact', '/'] as $uri) {
                    $this->app['auth']->forgetGuards();

                    $response = $this->withSession(['locale' => $locale])->get($uri);
                    $response->assertOk();
                    $response->assertSee($value);
                }
            }
        }
    }

    /**
     * CLAUDE.md's deploy verification greps the served homepage for
     * `CONTENT PENDING` — this is the same check, run before the deploy
     * rather than after it.
     */
    public function test_the_dhivehi_public_site_carries_no_pending_markers(): void
    {
        foreach (self::PUBLIC_URIS as $uri) {
            $this->app['auth']->forgetGuards();

            $response = $this->withSession(['locale' => 'dv'])->get($uri);
            $response->assertOk();
            $response->assertDontSee('CONTENT PENDING');
        }
    }

    /**
     * SPEC.md §12: no directional Tailwind utility may appear in mirrored
     * layout — logical properties only, so the whole app mirrors under RTL.
     */
    public function test_no_directional_tailwind_utilities_in_any_view(): void
    {
        $pattern = '/\b(?:ml|mr|pl|pr)-(?:\d|px|auto|\[)|\btext-(?:left|right)\b/';
        $violations = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            foreach (explode("\n", File::get($file->getPathname())) as $number => $line) {
                if (preg_match($pattern, $line, $match)) {
                    $violations[] = $file->getRelativePathname().':'.($number + 1).' — '.$match[0];
                }
            }
        }

        $this->assertSame(
            [],
            $violations,
            "SPEC.md §12: use logical properties (ms/me/ps/pe/text-start/text-end):\n".implode("\n", $violations)
        );
    }
}
