<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;

class LocalisationTest extends TestCase
{
    public function test_dhivehi_locale_renders_rtl_direction(): void
    {
        app()->setLocale('dv');
        session(['locale' => 'dv']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('dir="rtl"', false);
    }

    public function test_english_locale_renders_ltr_direction(): void
    {
        $response = $this->withSession(['locale' => 'en'])->get('/');

        $response->assertOk();
        $response->assertSee('dir="ltr"', false);
    }

    public function test_switching_locale_persists_for_authenticated_user(): void
    {
        $user = User::factory()->create(['locale' => 'dv']);

        $this->actingAs($user)->get('/locale/en')->assertRedirect();

        $this->assertSame('en', $user->fresh()->locale->value);
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $response = $this->get('/locale/fr');

        $response->assertNotFound();
    }

    public function test_lang_dv_and_en_have_identical_key_sets(): void
    {
        $enPath = lang_path('en');
        $dvPath = lang_path('dv');

        $enFiles = collect(File::files($enPath))->map(fn ($f) => $f->getFilenameWithoutExtension())->sort()->values();
        $dvFiles = collect(File::files($dvPath))->map(fn ($f) => $f->getFilenameWithoutExtension())->sort()->values();

        $this->assertEquals($enFiles->all(), $dvFiles->all(), 'lang/en and lang/dv must contain the same set of files.');

        foreach ($enFiles as $file) {
            $enKeys = $this->flattenKeys(require "{$enPath}/{$file}.php");
            $dvKeys = $this->flattenKeys(require "{$dvPath}/{$file}.php");

            sort($enKeys);
            sort($dvKeys);

            $missingInDv = array_diff($enKeys, $dvKeys);
            $missingInEn = array_diff($dvKeys, $enKeys);

            $this->assertEmpty($missingInDv, "Keys missing in lang/dv/{$file}.php: ".implode(', ', $missingInDv));
            $this->assertEmpty($missingInEn, "Keys missing in lang/en/{$file}.php: ".implode(', ', $missingInEn));
        }
    }

    /**
     * @return array<int, string>
     */
    private function flattenKeys(array $array, string $prefix = ''): array
    {
        $keys = [];

        foreach ($array as $key => $value) {
            $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if (is_array($value)) {
                $keys = array_merge($keys, $this->flattenKeys($value, $path));
            } else {
                $keys[] = $path;
            }
        }

        return $keys;
    }

    public function test_no_untranslated_literals_in_blade_views(): void
    {
        $viewsPath = resource_path('views');
        $violations = [];

        /** @var SplFileInfo $file */
        foreach (File::allFiles($viewsPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = File::get($file->getPathname());
            $stripped = $this->stripTranslatedAndDynamicContent($contents);

            if (preg_match('/>\s*([A-Z][a-zA-Z]{2,}(?:\s+[a-zA-Z]{2,}){1,})\s*</', $stripped, $matches)) {
                $violations[] = $file->getRelativePathname().': "'.trim($matches[1]).'"';
            }
        }

        $this->assertEmpty($violations, "Possible untranslated literal text found:\n".implode("\n", $violations));
    }

    /**
     * SPEC.md §3.4: the Thaana face is "applied via a `.font-thaana` class and
     * set as the body font ONLY when locale is `dv`. It must never be set as
     * the global `sans` family."
     */
    public function test_thaana_font_class_applies_only_in_dv_locale(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Every layout: public, guest and app.
        $pages = [
            'public' => fn (string $locale) => $this->withSession(['locale' => $locale])->get('/'),
            'guest' => fn (string $locale) => $this->withSession(['locale' => $locale])->get('/login'),
            'app' => fn (string $locale) => $this->withSession(['locale' => $locale])->actingAs($admin)->get(route('admin.dashboard')),
        ];

        foreach ($pages as $layout => $request) {
            $dhivehi = $request('dv');
            $dhivehi->assertOk();
            $this->assertMatchesRegularExpression(
                '/<body[^>]*\bfont-thaana\b/',
                $dhivehi->getContent(),
                "The {$layout} layout must carry font-thaana on <body> in the dv locale."
            );

            $english = $request('en');
            $english->assertOk();
            $this->assertDoesNotMatchRegularExpression(
                '/<body[^>]*\bfont-thaana\b/',
                $english->getContent(),
                "The {$layout} layout must NOT carry font-thaana on <body> in the en locale."
            );
        }

        // No Blade file may pin the Thaana face unconditionally — every use
        // must be gated on a locale. This catches standalone documents such as
        // the agreement print view, which carry their own <html>/<body>.
        foreach (File::allFiles(resource_path('views')) as $file) {
            $contents = File::get($file->getPathname());

            foreach (explode("\n", $contents) as $number => $line) {
                if (! str_contains($line, 'font-thaana')) {
                    continue;
                }

                $this->assertMatchesRegularExpression(
                    "/locale|'dv'|\"dv\"/",
                    $line,
                    "{$file->getRelativePathname()}:".($number + 1)
                    .' applies font-thaana without a locale condition (SPEC.md §3.4).'
                );
            }
        }

        // ...and it must never be the global sans family.
        $tailwind = File::get(base_path('tailwind.config.js'));

        $this->assertMatchesRegularExpression(
            '/sans:\s*\[[^\]]*\]/',
            $tailwind,
            'tailwind.config.js must declare a sans stack.'
        );

        preg_match('/sans:\s*\[([^\]]*)\]/', $tailwind, $matches);

        $this->assertDoesNotMatchRegularExpression(
            '/thaana|faseyha|faruma|boli/i',
            $matches[1] ?? '',
            'SPEC.md §3.4: a Thaana face must never appear in the global `sans` family.'
        );
    }

    /**
     * SPEC.md §3.4: "Do not bundle a proprietary font without a licence
     * permitting web redistribution." §14 defers procurement of the licensed
     * Thaana webfont, tracked in FONT-LICENCE.md.
     */
    public function test_no_proprietary_font_binary_in_repository(): void
    {
        // Fonts that ship with an operating system and may not be redistributed.
        $proprietary = ['mvboli', 'segoeui', 'calibri', 'arial', 'tahoma', 'times'];

        $bundled = [];

        foreach ([resource_path('fonts'), public_path('build/assets')] as $directory) {
            if (! File::isDirectory($directory)) {
                continue;
            }

            foreach (File::files($directory) as $file) {
                if (! in_array(strtolower($file->getExtension()), ['ttf', 'otf', 'woff', 'woff2', 'eot'], true)) {
                    continue;
                }

                // Only source fonts are documented. Build output is derived and
                // carries a content hash that changes on every build.
                if ($directory === resource_path('fonts')) {
                    $bundled[] = $file;
                }

                // Strip Vite's -HASH suffix before matching the denylist.
                $stem = preg_replace('/-[A-Za-z0-9_]{8,}$/', '', $file->getFilenameWithoutExtension());

                $this->assertNotContains(
                    preg_replace('/[^a-z]/', '', strtolower($stem)),
                    $proprietary,
                    "SPEC.md §3.4: {$file->getFilename()} is a proprietary system font and must not be bundled."
                );
            }
        }

        $this->assertTrue(
            File::exists(base_path('FONT-LICENCE.md')),
            'SPEC.md §14: FONT-LICENCE.md must record the Thaana webfont licence position.'
        );

        // Whatever IS bundled has to be accounted for in that document.
        $licence = File::get(base_path('FONT-LICENCE.md'));

        foreach ($bundled as $file) {
            $this->assertStringContainsString(
                $file->getFilenameWithoutExtension(),
                $licence,
                "{$file->getFilename()} is bundled but is not documented in FONT-LICENCE.md."
            );
        }
    }

    private function stripTranslatedAndDynamicContent(string $contents): string
    {
        $contents = preg_replace('/\{\{--.*?--\}\}/s', '', $contents);
        $contents = preg_replace('/<!--.*?-->/s', '', $contents);
        $contents = preg_replace('/\{\{.*?\}\}/s', '', $contents);
        $contents = preg_replace('/\{!!.*?!!\}/s', '', $contents);
        $contents = preg_replace('/@\w+(\([^;]*?\))?/s', '', $contents);
        $contents = preg_replace_callback('/<[a-zA-Z][^>]*>/s', fn ($m) => preg_replace('/[^<>]/', ' ', $m[0]), $contents);

        return $contents;
    }
}
