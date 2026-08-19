<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * The public site's photography (CLAUDE.md § Photography).
 *
 * <x-site-photo> is the single construction site for these URLs, the marketing
 * counterpart of Student::photoUrl(). The component carries no `onerror`
 * fallback on purpose, so a rendition that is referenced but missing would
 * reach a visitor as a broken image rather than an exception. These tests are
 * what turns that into a build failure instead.
 */
class SitePhotoTest extends TestCase
{
    /** Every rendition the component knows how to build a URL for. */
    private const WIDTHS = [800, 1600];

    /** @var array<int, string> */
    private const FORMATS = ['webp', 'jpg'];

    /**
     * @return array<int, string>
     */
    private function declaredPhotos(): array
    {
        $component = File::get(resource_path('views/components/site-photo.blade.php'));

        preg_match_all("/^\s+'([a-z-]+)' => \[\d+, \d+\],$/m", $component, $matches);

        $this->assertNotEmpty($matches[1], 'No photo renditions were declared in the component.');

        return $matches[1];
    }

    public function test_every_declared_photo_has_all_of_its_renditions_on_disk(): void
    {
        foreach ($this->declaredPhotos() as $photo) {
            foreach (self::WIDTHS as $width) {
                foreach (self::FORMATS as $format) {
                    $path = public_path("images/photos/{$photo}-{$width}.{$format}");

                    $this->assertFileExists($path, "Missing rendition for [{$photo}].");
                    $this->assertGreaterThan(0, filesize($path), "Empty rendition for [{$photo}].");
                }
            }
        }
    }

    /**
     * The intrinsic size declared on the <img> is what stops the page shifting
     * as photos arrive, so a wrong number is a real defect rather than a typo.
     */
    public function test_declared_intrinsic_sizes_match_the_files_on_disk(): void
    {
        $component = File::get(resource_path('views/components/site-photo.blade.php'));

        preg_match_all("/^\s+'([a-z-]+)' => \[(\d+), (\d+)\],$/m", $component, $matches, PREG_SET_ORDER);

        foreach ($matches as [, $photo, $width, $height]) {
            [$actualWidth, $actualHeight] = getimagesize(public_path("images/photos/{$photo}-1600.jpg"));

            $this->assertSame((int) $width, $actualWidth, "Declared width is wrong for [{$photo}].");
            $this->assertSame((int) $height, $actualHeight, "Declared height is wrong for [{$photo}].");
        }
    }

    /**
     * Nothing may reach into images/photos directly — the component is the only
     * place these paths are built, the same discipline Student::photoUrl() and
     * Coach::photoUrl() enforce for the private disk.
     */
    public function test_no_view_builds_a_photo_path_outside_the_component(): void
    {
        $offenders = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if ($file->getFilename() === 'site-photo.blade.php') {
                continue;
            }

            if (str_contains(File::get($file->getRealPath()), 'images/photos')) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $offenders, 'Photo paths must be built by <x-site-photo>.');
    }

    /**
     * Every name passed to the component must resolve, or the page throws. A
     * typo in a template is otherwise only found by loading that page.
     */
    public function test_every_photo_named_in_a_view_is_declared(): void
    {
        $declared = $this->declaredPhotos();
        $unknown = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            $contents = File::get($file->getRealPath());

            preg_match_all('/<x-site-photo\s[^>]*\bname="([a-z-]+)"/s', $contents, $matches);

            foreach ($matches[1] as $name) {
                if (! in_array($name, $declared, true)) {
                    $unknown[] = "{$file->getRelativePathname()}: {$name}";
                }
            }
        }

        $this->assertSame([], $unknown, 'A view names a photo the component cannot build.');
    }
}
