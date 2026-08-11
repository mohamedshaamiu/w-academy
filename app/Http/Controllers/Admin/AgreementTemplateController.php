<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAgreementTemplateRequest;
use App\Models\AgreementTemplate;
use App\Services\AgreementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AgreementTemplateController extends Controller
{
    public function __construct(private readonly AgreementService $agreements) {}

    public function index(): View
    {
        return view('admin.agreement-templates.index', [
            'templates' => AgreementTemplate::orderByDesc('version')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.agreement-templates.create');
    }

    public function store(StoreAgreementTemplateRequest $request): RedirectResponse
    {
        $template = AgreementTemplate::create($request->validated());

        return redirect()->route('admin.agreement-templates.show', $template)->with('status', __('admin.agreement.created'));
    }

    public function show(AgreementTemplate $agreementTemplate): View
    {
        return view('admin.agreement-templates.show', ['template' => $agreementTemplate]);
    }

    public function edit(AgreementTemplate $agreementTemplate): View
    {
        return view('admin.agreement-templates.edit', ['template' => $agreementTemplate]);
    }

    public function update(StoreAgreementTemplateRequest $request, AgreementTemplate $agreementTemplate): RedirectResponse
    {
        $agreementTemplate->update($request->validated());

        return redirect()->route('admin.agreement-templates.show', $agreementTemplate)->with('status', __('admin.agreement.updated'));
    }

    public function destroy(AgreementTemplate $agreementTemplate): RedirectResponse
    {
        abort_if($agreementTemplate->is_current, 422, __('admin.agreement.validation.cannot_delete_current'));

        $agreementTemplate->delete();

        return redirect()->route('admin.agreement-templates.index')->with('status', __('admin.agreement.deleted'));
    }

    public function publish(AgreementTemplate $agreementTemplate): RedirectResponse
    {
        $this->agreements->publish($agreementTemplate);

        return back()->with('status', __('admin.agreement.published'));
    }
}
