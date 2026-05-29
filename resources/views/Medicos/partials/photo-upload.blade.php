@php
    $photoUrl = $medico?->photo_url;
    $initials = $medico?->initials ?? 'DR';
@endphp

<section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm shadow-slate-200/60 sm:p-8" data-medico-photo-upload>
    <div class="grid gap-6 md:grid-cols-[auto_minmax(0,1fr)] md:items-center">
        <div class="flex justify-center md:block">
            <div class="relative h-32 w-32">
                <img src="{{ $photoUrl ?: '' }}" alt="Vista previa de foto" class="{{ $photoUrl ? '' : 'hidden' }} h-32 w-32 rounded-full object-cover shadow-xl shadow-violet-950/10 ring-4 ring-violet-100" data-photo-preview>
                <div class="{{ $photoUrl ? 'hidden' : '' }} flex h-32 w-32 items-center justify-center rounded-full bg-gradient-to-br from-violet-700 to-purple-600 text-4xl font-black text-white shadow-xl shadow-violet-700/20 ring-4 ring-violet-100" data-photo-fallback>
                    {{ $initials }}
                </div>
            </div>
        </div>

        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-violet-600">Foto de perfil</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Imagen profesional del médico</h2>
            <p class="mt-2 max-w-2xl text-sm font-medium leading-6 text-slate-500">Se mostrará en el portal público, especialistas destacados y listados internos. Usa JPG, JPEG, PNG o WEBP de máximo 2MB.</p>

            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
                <label for="photo" class="inline-flex cursor-pointer select-none items-center justify-center rounded-2xl bg-violet-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition hover:-translate-y-0.5 hover:bg-violet-700 focus-within:ring-4 focus-within:ring-violet-200">
                    Subir imagen
                    <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="sr-only" data-photo-input>
                </label>

                @if($medico?->photo_path)
                    <label class="inline-flex select-none items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-700 transition hover:bg-rose-100">
                        <input type="checkbox" name="remove_photo" value="1" class="rounded border-rose-300 text-rose-600 shadow-sm focus:ring-rose-500" data-photo-remove>
                        Eliminar imagen actual
                    </label>
                @endif
            </div>

            <p class="mt-3 text-xs font-semibold text-slate-400" data-photo-file-name>{{ $photoUrl ? 'Imagen actual cargada.' : 'No hay foto cargada. Se usará un avatar elegante.' }}</p>
            <x-input-error :messages="$errors->get('photo')" />
            <x-input-error :messages="$errors->get('remove_photo')" />
        </div>
    </div>
</section>

<script>
    function initMedicoPhotoUpload() {
        document.querySelectorAll('[data-medico-photo-upload]').forEach((section) => {
            if (section.dataset.enhanced === 'true') {
                return;
            }

            section.dataset.enhanced = 'true';

            const input = section.querySelector('[data-photo-input]');
            const preview = section.querySelector('[data-photo-preview]');
            const fallback = section.querySelector('[data-photo-fallback]');
            const fileName = section.querySelector('[data-photo-file-name]');
            const remove = section.querySelector('[data-photo-remove]');

            input?.addEventListener('change', () => {
                const file = input.files?.[0];

                if (! file) {
                    return;
                }

                const reader = new FileReader();

                reader.addEventListener('load', () => {
                    preview.src = reader.result;
                    preview.classList.remove('hidden');
                    fallback.classList.add('hidden');
                    fileName.textContent = file.name;

                    if (remove) {
                        remove.checked = false;
                    }
                });

                reader.readAsDataURL(file);
            });

            remove?.addEventListener('change', () => {
                if (! remove.checked) {
                    return;
                }

                input.value = '';
                preview.classList.add('hidden');
                fallback.classList.remove('hidden');
                fileName.textContent = 'La imagen actual se eliminará al guardar.';
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initMedicoPhotoUpload);
    document.addEventListener('livewire:navigated', initMedicoPhotoUpload);
</script>
