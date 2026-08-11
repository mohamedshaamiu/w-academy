<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-md bg-navy px-4 py-2 text-sm font-semibold text-white transition hover:bg-navy-600 focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2 disabled:opacity-50']) }}>
    {{ $slot }}
</button>
