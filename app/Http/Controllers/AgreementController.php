<?php

namespace App\Http\Controllers;

use App\Http\Requests\SignAgreementRequest;
use App\Models\AgreementTemplate;
use App\Models\Student;
use App\Services\AgreementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AgreementController extends Controller
{
    public function __construct(private readonly AgreementService $agreements) {}

    public function show(Student $student): View
    {
        $guardian = request()->user()->guardian;
        abort_unless($guardian && $student->guardians()->where('guardians.id', $guardian->id)->exists(), 403);

        return view('guardian.agreement-sign', [
            'student' => $student,
            'template' => AgreementTemplate::current()->firstOrFail(),
        ]);
    }

    public function sign(SignAgreementRequest $request, Student $student): RedirectResponse
    {
        $guardian = $request->user()->guardian;
        abort_unless($guardian && $student->guardians()->where('guardians.id', $guardian->id)->exists(), 403);

        $signaturePath = null;
        if ($request->filled('signature_image')) {
            $signaturePath = $this->storeSignature($request->string('signature_image')->toString(), $student);
        }

        $this->agreements->sign(
            student: $student,
            guardian: $guardian,
            signatoryName: $request->string('signatory_name')->toString(),
            consents: $request->array('consents'),
            signatureImagePath: $signaturePath,
            request: $request,
        );

        return redirect()->route('guardian.dashboard')->with('status', __('agreement.signed_successfully'));
    }

    public function download(Student $student): View
    {
        $user = request()->user();

        if ($user->isGuardian()) {
            abort_unless($user->guardian->students()->where('students.id', $student->id)->exists(), 403);
        } elseif ($user->isAdmin()) {
            // allowed
        } else {
            abort(403);
        }

        $signature = $student->agreementSignatures()->where('status', 'signed')->latest('signed_at')->firstOrFail();

        return view('guardian.agreement-download', ['signature' => $signature, 'student' => $student]);
    }

    private function storeSignature(string $dataUrl, Student $student): ?string
    {
        if (! preg_match('/^data:image\/png;base64,(.+)$/', $dataUrl, $matches)) {
            return null;
        }

        $contents = base64_decode($matches[1]);
        $path = 'signatures/'.$student->id.'/'.Str::uuid().'.png';
        Storage::disk('local')->put($path, $contents);

        return $path;
    }
}
