<form method="POST" action="{{ route('admin.sessions.generate') }}" class="p-6">
    @csrf

    <h2 class="text-lg font-semibold text-navy">{{ __('session.generate.title') }}</h2>

    <div class="mt-4 space-y-4">
        <div>
            <x-input-label for="generate_squad_id" :value="__('session.fields.squad')" />
            <select id="generate_squad_id" name="squad_id" class="mt-1 block w-full rounded-md border-navy-200" required>
                <option value="">—</option>
                @foreach ($squads as $squad)
                    <option value="{{ $squad->id }}">{{ $squad->translated('name') }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('squad_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="generate_from" :value="__('session.generate.from')" />
            <x-text-input id="generate_from" type="date" name="from" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('from')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="generate_to" :value="__('session.generate.to')" />
            <x-text-input id="generate_to" type="date" name="to" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('to')" class="mt-2" />
        </div>
    </div>

    <div class="mt-6 flex justify-end gap-2">
        <button type="button" x-data @click="$dispatch('close-modal', 'generate-sessions')" class="rounded-md border border-navy-200 px-4 py-2 text-sm font-medium text-navy hover:bg-navy-50">
            {{ __('common.actions.cancel') }}
        </button>
        <x-primary-button>{{ __('admin.session.generate_button') }}</x-primary-button>
    </div>
</form>
