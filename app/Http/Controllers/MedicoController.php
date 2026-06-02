<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MedicoController extends Controller
{
    private const PHOTO_DISK = 'public';

    private const PHOTO_DIRECTORY = 'medicos/fotos';

    private const PHOTO_MAX_DIMENSION = 900;

    public function index(Request $request): View
    {
        $filters = $request->only('q');
        $medicos = Medico::with(['user', 'servicios'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = trim((string) $request->query('q'));

                $query->where(function ($query) use ($search) {
                    $query->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('especialidad', 'like', "%{$search}%")
                        ->orWhereHas('servicios', fn ($query) => $query->where('nombre', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn ($query) => $query->where('email', 'like', "%{$search}%"));
                });
            })
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->paginate(15)
            ->withQueryString();

        return view('Medicos.index', compact('medicos', 'filters'));
    }

    public function create(): View
    {
        $usuariosMedicos = $this->usuariosConRol('medico');
        $servicios = Servicio::orderBy('nombre')->get();

        return view('Medicos.create', compact('usuariosMedicos', 'servicios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:medicos,email',
            'especialidad' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'user_id' => 'nullable|exists:users,id|unique:medicos,user_id',
            'servicio_ids' => ['required', 'array', 'min:1'],
            'servicio_ids.*' => ['integer', 'distinct', 'exists:servicios,id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $servicioIds = $validated['servicio_ids'];
        unset($validated['servicio_ids'], $validated['photo']);

        $validated['user_id'] = $validated['user_id'] ?? null;
        $this->ensureUserHasRole($validated['user_id'], 'medico');

        $medico = Medico::create($validated);
        $this->syncPhoto($medico, $request);
        $medico->servicios()->sync($servicioIds);

        return redirect()->route('medicos.index')->with('success', 'Médico registrado exitosamente.');
    }

    public function edit(int $id): View
    {
        $medico = Medico::with('servicios')->findOrFail($id);
        $usuariosMedicos = $this->usuariosConRol('medico');
        $servicios = Servicio::orderBy('nombre')->get();

        return view('Medicos.edit', compact('medico', 'usuariosMedicos', 'servicios'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $medico = Medico::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:medicos,email,'.$id,
            'especialidad' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'user_id' => 'nullable|exists:users,id|unique:medicos,user_id,'.$id,
            'servicio_ids' => ['required', 'array', 'min:1'],
            'servicio_ids.*' => ['integer', 'distinct', 'exists:servicios,id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        $servicioIds = $validated['servicio_ids'];
        unset($validated['servicio_ids'], $validated['photo'], $validated['remove_photo']);

        $validated['user_id'] = $validated['user_id'] ?? null;
        $this->ensureUserHasRole($validated['user_id'], 'medico');

        $medico->update($validated);
        $this->syncPhoto($medico, $request);
        $medico->servicios()->sync($servicioIds);

        return redirect()->route('medicos.index')->with('success', 'Datos del médico actualizados.');
    }

    public function profile(): View
    {
        $medico = $this->authenticatedMedico();
        $medico->load('servicios');

        return view('Medicos.profile', compact('medico'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $medico = $this->authenticatedMedico();

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:medicos,email,'.$medico->id,
            'especialidad' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ]);

        unset($validated['photo'], $validated['remove_photo']);

        $medico->update($validated);
        $this->syncPhoto($medico, $request);

        return redirect()->route('medicos.profile')->with('success', 'Perfil médico actualizado correctamente.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $medico = Medico::findOrFail($id);
        $this->deletePhoto($medico->photo_path);
        $medico->delete();

        return redirect()->route('medicos.index')->with('success', 'Médico eliminado del sistema.');
    }

    private function usuariosConRol(string $role)
    {
        return User::whereHas('roles', function ($query) use ($role) {
            $query->where('name', $role);
        })->orderBy('name')->get();
    }

    private function ensureUserHasRole(?int $userId, string $role): void
    {
        if (! $userId) {
            return;
        }

        $user = User::findOrFail($userId);

        if (! $user->hasRole($role)) {
            throw ValidationException::withMessages([
                'user_id' => "El usuario seleccionado debe tener rol {$role}.",
            ]);
        }
    }

    private function authenticatedMedico(): Medico
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole('medico')) {
            abort(403, 'No autorizado.');
        }

        $medico = Medico::where('user_id', $user->id)->first();

        if (! $medico) {
            abort(404, 'Tu usuario no está vinculado a un perfil médico.');
        }

        return $medico;
    }

    private function syncPhoto(Medico $medico, Request $request): void
    {
        if ($request->boolean('remove_photo') && $medico->photo_path) {
            $this->deletePhoto($medico->photo_path);
            $medico->forceFill(['photo_path' => null])->save();
        }

        if (! $request->hasFile('photo')) {
            return;
        }

        $oldPath = $medico->photo_path;
        $newPath = $this->storePhoto($request->file('photo'));

        $medico->forceFill(['photo_path' => $newPath])->save();

        if ($oldPath && $oldPath !== $newPath) {
            $this->deletePhoto($oldPath);
        }
    }

    private function storePhoto(UploadedFile $photo): string
    {
        $extension = strtolower($photo->getClientOriginalExtension());
        $extension = $extension === 'jpg' ? 'jpeg' : $extension;
        $originalPath = $photo->getRealPath();
        $size = $originalPath ? @getimagesize($originalPath) : false;

        if (! $size || ! $this->canResizePhoto($extension)) {
            return $photo->store(self::PHOTO_DIRECTORY, self::PHOTO_DISK);
        }

        [$width, $height] = $size;

        if ($width <= self::PHOTO_MAX_DIMENSION && $height <= self::PHOTO_MAX_DIMENSION) {
            return $photo->store(self::PHOTO_DIRECTORY, self::PHOTO_DISK);
        }

        $source = $this->createImageResource($originalPath, $extension);

        if (! $source) {
            return $photo->store(self::PHOTO_DIRECTORY, self::PHOTO_DISK);
        }

        $ratio = min(self::PHOTO_MAX_DIMENSION / $width, self::PHOTO_MAX_DIMENSION / $height);
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);

        if (in_array($extension, ['png', 'webp'], true)) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        Storage::disk(self::PHOTO_DISK)->makeDirectory(self::PHOTO_DIRECTORY);

        $path = self::PHOTO_DIRECTORY.'/'.Str::uuid().'.'.$extension;
        $absolutePath = Storage::disk(self::PHOTO_DISK)->path($path);

        $saved = $this->writeImageResource($resized, $absolutePath, $extension);

        imagedestroy($source);
        imagedestroy($resized);

        return $saved ? $path : $photo->store(self::PHOTO_DIRECTORY, self::PHOTO_DISK);
    }

    private function canResizePhoto(string $extension): bool
    {
        return function_exists('imagecreatetruecolor') && match ($extension) {
            'jpeg' => function_exists('imagecreatefromjpeg') && function_exists('imagejpeg'),
            'png' => function_exists('imagecreatefrompng') && function_exists('imagepng'),
            'webp' => function_exists('imagecreatefromwebp') && function_exists('imagewebp'),
            default => false,
        };
    }

    private function createImageResource(string $path, string $extension)
    {
        return match ($extension) {
            'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'webp' => @imagecreatefromwebp($path),
            default => false,
        };
    }

    private function writeImageResource($image, string $path, string $extension): bool
    {
        return match ($extension) {
            'jpeg' => imagejpeg($image, $path, 85),
            'png' => imagepng($image, $path, 8),
            'webp' => imagewebp($image, $path, 85),
            default => false,
        };
    }

    private function deletePhoto(?string $path): void
    {
        if ($path) {
            Storage::disk(self::PHOTO_DISK)->delete($path);
        }
    }
}
