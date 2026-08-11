<?php

namespace Tests\Feature;

use App\Models\AgreementTemplate;
use App\Services\AgreementService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AgreementTemplateTest extends TestCase
{
    public function test_publish_blocked_when_either_language_incomplete(): void
    {
        $template = AgreementTemplate::factory()->create(['body_dv' => '']);

        $this->expectException(ValidationException::class);

        app(AgreementService::class)->publish($template);
    }

    public function test_publish_succeeds_when_both_languages_complete(): void
    {
        $template = AgreementTemplate::factory()->create(['is_current' => false]);

        app(AgreementService::class)->publish($template);

        $this->assertTrue($template->fresh()->is_current);
    }
}
