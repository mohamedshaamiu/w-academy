<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgreementSignature;
use Illuminate\View\View;

class AgreementSignatureController extends Controller
{
    public function index(): View
    {
        return view('admin.agreement-signatures.index', [
            'signatures' => AgreementSignature::with(['student', 'guardian.user', 'agreementTemplate'])
                ->latest('signed_at')
                ->paginate(20),
        ]);
    }
}
