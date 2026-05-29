<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-violet-600">Administración</p>
                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Permisos</h1>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:border-violet-100 hover:bg-violet-50 hover:text-violet-700">
                Volver al dashboard
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-3xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <section class="rounded-3xl border border-slate-200 bg-white shadow-sm shadow-slate-200/60">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-extrabold text-slate-950">Permisos por rol</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Activa o desactiva las acciones disponibles para cada rol administrativo.</p>
            </div>

            <form action="{{ route('permisos.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Módulo</th>
                                <th class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-slate-500">Rol</th>
                                @foreach($actions as $actionLabel)
                                    <th class="px-6 py-4 text-center text-xs font-extrabold uppercase tracking-wider text-slate-500">{{ $actionLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($modules as $module => $moduleLabel)
                                @foreach($roles as $role => $roleLabel)
                                    <tr class="transition hover:bg-violet-50/40">
                                        @if($loop->first)
                                            <td rowspan="{{ count($roles) }}" class="whitespace-nowrap px-6 py-4 align-top font-extrabold text-slate-950">
                                                {{ $moduleLabel }}
                                            </td>
                                        @endif
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-slate-700">{{ $roleLabel }}</td>
                                        @foreach($actions as $action => $actionLabel)
                                            <td class="px-6 py-4 text-center">
                                                <label class="inline-flex cursor-pointer items-center justify-center">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[{{ $role }}][{{ $module }}][{{ $action }}]"
                                                        value="1"
                                                        class="h-5 w-5 rounded border-slate-300 text-violet-600 shadow-sm focus:ring-violet-500"
                                                        @checked($matrix[$role][$module][$action] ?? false)
                                                    >
                                                    <span class="sr-only">{{ $actionLabel }} {{ $moduleLabel }} para {{ $roleLabel }}</span>
                                                </label>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-medium text-slate-500">El módulo Permisos queda reservado para administradores por seguridad.</p>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700">
                        Guardar permisos
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
