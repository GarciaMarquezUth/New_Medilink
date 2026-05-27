<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-xl border border-transparent bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-violet-600/20 transition duration-200 hover:-translate-y-0.5 hover:bg-violet-700 hover:shadow-violet-700/25 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-60']) }}>
    {{ $slot }}
</button>
