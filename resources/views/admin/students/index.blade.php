<x-app-layout portal="admin" :title="__('admin.student.index.title')" :header="__('admin.student.index.title')">
    <div class="mb-4 flex items-center justify-between">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('admin.student.index.search_placeholder') }}" class="rounded-md border-navy-200 text-sm">

            <select name="status" class="rounded-md border-navy-200 text-sm">
                <option value="">{{ __('admin.student.index.filter_status') }}</option>
                @foreach (\App\Enums\StudentStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>

            <select name="squad" class="rounded-md border-navy-200 text-sm">
                <option value="">{{ __('admin.student.index.filter_squad') }}</option>
                @foreach ($squads as $squad)
                    <option value="{{ $squad->id }}" @selected((string) request('squad') === (string) $squad->id)>{{ $squad->translated('name') }}</option>
                @endforeach
            </select>

            <select name="agreement" class="rounded-md border-navy-200 text-sm">
                <option value="">{{ __('admin.student.index.filter_agreement') }}</option>
                <option value="signed" @selected(request('agreement') === 'signed')>{{ __('admin.student.index.agreement_signed') }}</option>
                <option value="unsigned" @selected(request('agreement') === 'unsigned')>{{ __('admin.student.index.agreement_unsigned') }}</option>
            </select>

            <select name="login" class="rounded-md border-navy-200 text-sm">
                <option value="">{{ __('admin.student.index.filter_login') }}</option>
                <option value="issued" @selected(request('login') === 'issued')>{{ __('admin.student.index.login_issued') }}</option>
                <option value="not_issued" @selected(request('login') === 'not_issued')>{{ __('admin.student.index.login_not_issued') }}</option>
            </select>

            <button type="submit" class="rounded-md bg-navy px-4 py-2 text-sm font-medium text-white hover:bg-navy-600">
                {{ __('common.actions.filter') }}
            </button>
        </form>

        <a href="{{ route('admin.students.create') }}" class="shrink-0 rounded-md bg-gold px-4 py-2 text-sm font-semibold text-navy-900 hover:bg-gold-400">
            + {{ __('common.actions.create') }}
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-navy-100 bg-white">
        <table class="min-w-full divide-y divide-navy-100 text-start text-sm">
            <thead class="bg-navy-50">
                <tr>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('student.fields.index_number') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('student.fields.full_name') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('student.dashboard.squad') }}</th>
                    <th class="px-4 py-3 text-start font-semibold text-navy">{{ __('common.labels.status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-100">
                @forelse ($students as $student)
                    <tr class="cursor-pointer hover:bg-navy-50" onclick="window.location='{{ route('admin.students.show', $student) }}'">
                        <td class="px-4 py-3 font-mono">{{ $student->index_number }}</td>
                        <td class="px-4 py-3">{{ $student->full_name }}</td>
                        <td class="px-4 py-3">{{ $student->activeEnrolment?->squad?->translated('name') ?? __('common.na') }}</td>
                        <td class="px-4 py-3"><x-status-badge :status="$student->status->value" :label="$student->status->label()" /></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-navy-400">{{ __('common.none') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $students->links() }}</div>
</x-app-layout>
