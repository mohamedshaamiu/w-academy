<?php

namespace App\Services;

use App\Enums\StudentStatus;
use App\Models\AgreementSignature;
use App\Models\AgreementTemplate;
use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgreementService
{
    /**
     * Publish a template as the current one. Rejects publication unless
     * both language bodies/titles and every clause's both labels are filled.
     */
    public function publish(AgreementTemplate $template): void
    {
        if (! $this->isFullyTranslated($template)) {
            throw ValidationException::withMessages([
                'template' => __('admin.agreement.validation.both_languages_required'),
            ]);
        }

        DB::transaction(function () use ($template) {
            AgreementTemplate::where('is_current', true)->update(['is_current' => false]);
            $template->update(['is_current' => true]);
        });
    }

    public function isFullyTranslated(AgreementTemplate $template): bool
    {
        if (trim((string) $template->title_dv) === '' || trim((string) $template->title_en) === '') {
            return false;
        }

        if (trim((string) $template->body_dv) === '' || trim((string) $template->body_en) === '') {
            return false;
        }

        foreach ($template->consent_clauses as $clause) {
            if (trim((string) ($clause['label_dv'] ?? '')) === '' || trim((string) ($clause['label_en'] ?? '')) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Record a guardian's signature against the current template for one of
     * their linked students.
     *
     * @param  array<string, bool>  $consents
     */
    public function sign(
        Student $student,
        Guardian $guardian,
        string $signatoryName,
        array $consents,
        ?string $signatureImagePath,
        Request $request,
    ): AgreementSignature {
        $template = AgreementTemplate::current()->firstOrFail();
        $locale = app()->getLocale();

        $requiredClauses = collect($template->consent_clauses)->where('required', true)->pluck('key');
        foreach ($requiredClauses as $key) {
            if (empty($consents[$key])) {
                throw ValidationException::withMessages([
                    'consents' => __('agreement.validation.all_required_clauses'),
                ]);
            }
        }

        if (trim($signatoryName) === '' || mb_strlen(trim($signatoryName)) < 3) {
            throw ValidationException::withMessages([
                'signatory_name' => __('agreement.validation.signatory_name_min'),
            ]);
        }

        if (! $signatureImagePath && trim($signatoryName) === '') {
            throw ValidationException::withMessages([
                'signature' => __('agreement.validation.signature_or_name_required'),
            ]);
        }

        return DB::transaction(function () use ($student, $guardian, $signatoryName, $consents, $signatureImagePath, $request, $template, $locale) {
            $signature = AgreementSignature::create([
                'agreement_template_id' => $template->id,
                'student_id' => $student->id,
                'guardian_id' => $guardian->id,
                'signed_at' => now(),
                'signatory_name' => $signatoryName,
                'signature_image_path' => $signatureImagePath,
                'consents' => $consents,
                'signed_locale' => $locale,
                'template_snapshot' => $locale === 'dv' ? $template->body_dv : $template->body_en,
                'ip_address' => $request->ip(),
                'user_agent' => (string) substr((string) $request->userAgent(), 0, 255),
                'status' => 'signed',
            ]);

            $this->maybeActivateStudent($student->fresh());

            return $signature;
        });
    }

    /**
     * A pending student becomes active once they hold a valid signature
     * AND an active squad enrolment.
     */
    public function maybeActivateStudent(Student $student): void
    {
        if ($student->status !== StudentStatus::Pending) {
            return;
        }

        if ($student->has_signed_current_agreement && $student->activeEnrolment()->exists()) {
            $student->update(['status' => StudentStatus::Active]);
        }
    }
}
