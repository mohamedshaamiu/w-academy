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
