<!DOCTYPE html>
<html lang="{{ $signature->signed_locale->value }}" dir="{{ $signature->signed_locale->value === 'dv' ? 'rtl' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <title>{{ __('agreement.page.title') }} — {{ $student->full_name }}</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="font-thaana mx-auto max-w-3xl p-8 text-navy-900">
        <h1 class="text-xl font-bold text-navy">{{ __('agreement.page.title') }}</h1>
        <p class="mt-1 text-sm text-navy-500">
            {{ $student->full_name }} ({{ $student->index_number }})
        </p>
        <p class="mt-1 text-sm text-navy-500">
            {{ __('agreement.page.downloaded_on') }}: {{ \App\Support\FormatsDates::dateTime($signature->signed_at) }}
            &middot; {{ __('agreement.page.language_signed_in') }}: {{ $signature->signed_locale->label() }}
        </p>

        <div class="mt-6 whitespace-pre-line border-t border-navy-100 pt-6 leading-relaxed">
            {{ $signature->template_snapshot }}
        </div>

        <p class="mt-6 text-sm font-semibold text-navy">{{ $signature->signatory_name }}</p>

        @if ($signature->signature_image_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($signature->signature_image_path))
            <img src="data:image/png;base64,{{ base64_encode(\Illuminate\Support\Facades\Storage::disk('local')->get($signature->signature_image_path)) }}" alt="" class="mt-2 h-24">
        @endif
    </body>
</html>
