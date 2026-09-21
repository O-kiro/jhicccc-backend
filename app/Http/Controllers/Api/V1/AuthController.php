<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\V1\StudentResource;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Hash bcrypt sah atas nilai acak; tidak akan pernah cocok dengan kata
     * sandi mana pun, dan hanya dipakai untuk menyamakan waktu respons.
     */
    private const DUMMY_HASH = '$2y$12$.paFpCKbt.6A9bcasy4h1Oe3eV5CyuJoGorgU7XFGesYwM2VmxZkS';

    /**
     * Umur tautan serah-terima admin. Sengaja sangat pendek: tautan ini
     * langsung membuat sesi, jadi jendela penyalahgunaannya harus sempit.
     */
    public const HANDOFF_TTL_SECONDS = 60;

    /**
     * Gerbang masuk tunggal untuk siswa dan admin.
     *
     * Identitas yang berbentuk alamat surel diperlakukan sebagai admin,
     * selebihnya sebagai NISN siswa. Keduanya menghasilkan bentuk respons
     * berbeda karena mekanisme sesinya memang berbeda: siswa memakai token
     * Sanctum, admin memakai sesi Filament.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $identifier = trim($request->string('identifier')->toString());
        $password = $request->string('password')->toString();

        return filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $this->loginAdmin($identifier, $password)
            : $this->loginStudent($identifier, $password, $request->string('device_name')->toString());
    }

    private function loginStudent(string $nisn, string $password, string $deviceName): JsonResponse
    {
        $student = Student::query()->where('nisn', $nisn)->first();

        if (! $this->passwordMatches($password, $student?->password)) {
            $this->rejectCredentials();
        }

        if (! $student->is_active) {
            throw ValidationException::withMessages([
                'identifier' => ['Akun ini tidak aktif. Hubungi admin madrasah.'],
            ]);
        }

        $token = $student->createToken($deviceName !== '' ? $deviceName : 'portal-siswa');

        return response()->json([
            'role' => 'student',
            'token' => $token->plainTextToken,
            'student' => new StudentResource($student->load('classroom')),
        ]);
    }

    /**
     * Admin tidak bisa memakai token Sanctum karena Filament berjalan di atas
     * sesi. Jadi yang dikembalikan adalah tautan serah-terima sekali pakai
     * berumur pendek, yang ditukar menjadi sesi oleh HandoffController.
     */
    private function loginAdmin(string $email, string $password): JsonResponse
    {
        $user = User::query()->where('email', $email)->first();

        if (! $this->passwordMatches($password, $user?->password)) {
            $this->rejectCredentials();
        }

        $token = Str::random(64);

        Cache::put(
            self::handoffKey($token),
            $user->id,
            now()->addSeconds(self::HANDOFF_TTL_SECONDS),
        );

        // Jalur relatif lalu disambung ke APP_URL — BUKAN route() absolut.
        //
        // route() absolut memakai host permintaan. Ketika portal Next.js
        // berjalan di container lain, host itu adalah host.docker.internal:
        // nama yang hanya berarti di dalam container. Tautan ini dibuka oleh
        // browser pengguna, jadi harus memakai alamat publik di APP_URL.
        $path = route('admin.handoff', ['token' => $token], absolute: false);

        return response()->json([
            'role' => 'admin',
            'name' => $user->name,
            'redirect_url' => rtrim((string) config('app.url'), '/').$path,
        ]);
    }

    /**
     * Hash::check tetap dijalankan meski akun tidak ditemukan supaya waktu
     * responsnya tidak membocorkan identitas mana yang terdaftar. Nilai
     * cadangan harus bcrypt yang sah — hash asal-asalan membuat Hash::check
     * melempar RuntimeException dan endpoint balas 500, bukan 422.
     */
    private function passwordMatches(string $password, ?string $hash): bool
    {
        return Hash::check($password, $hash ?? self::DUMMY_HASH);
    }

    private function rejectCredentials(): never
    {
        throw ValidationException::withMessages([
            'identifier' => ['NISN/Email atau kata sandi salah.'],
        ]);
    }

    public static function handoffKey(string $token): string
    {
        return 'admin-handoff:'.hash('sha256', $token);
    }

    /**
     * Mencabut token yang sedang dipakai.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Berhasil keluar.']);
    }

    /**
     * Mengembalikan objek siswa tanpa pembungkus "data".
     *
     * Resource yang dikembalikan langsung dari controller otomatis dibungkus
     * Laravel; dibungkus response()->json() supaya bentuknya sama dengan
     * endpoint lain, yang semuanya mengirim objek polos.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(new StudentResource($request->user()->load('classroom')));
    }
}
