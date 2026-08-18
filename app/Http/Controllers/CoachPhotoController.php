<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CoachPhotoController extends Controller
{
    /**
     * Coach photos are public-facing content (the homepage coach section),
     * so this route carries no auth — only the `signed` middleware, which
     * keeps the URL non-enumerable. Contrast StudentPhotoController, where
     * SPEC.md §8.6 additionally requires auth plus a policy check.
     */
    public function __invoke(Request $request, Coach $coach): StreamedResponse
    {
        abort_unless($coach->photo_path && Storage::disk('local')->exists($coach->photo_path), 404);

        return Storage::disk('local')->response($coach->photo_path);
    }
}
