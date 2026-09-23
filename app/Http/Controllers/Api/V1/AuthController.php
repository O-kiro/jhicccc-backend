<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\V1\AlumniResource;
use App\Http\Resources\V1\StudentResource;
use App\Http\Resources\V1\TeacherResource;
use App\Models\AlumniAccount;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Support\Peran;
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
     * Gerbang masuk tunggal untuk siswa, guru, dan admin.
     *
     * Surel dipakai admin dan guru; nomor induk dipakai siswa (NISN) dan guru
     * (NIP). Bentuk responsnya berbeda karena mekanisme sesinya berbeda:
     * siswa dan guru memakai token Sanctum, admin memakai sesi Filament.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $identifier = trim($request->string('identifier')->toString());
        $password = $request->string('password')->toString();
        $device = $request->string('device_name')->toString();

        return filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $this->loginEmail($identifier, $password, $device)
            : $this->loginNomorInduk($identifier, $password, $device);
    }

    /**
     * Surel dipakai tiga jenis akun. Urutannya admin, guru, lalu alumni; bila
     * sandinya salah, sandi tetap dicocokkan ke ketiganya supaya lama respons
     * tidak membocorkan tabel mana yang memuat surel itu.
     *
     * Orang yang punya lebih dari satu akun dengan surel serta sandi yang
     * sama selalu masuk sebagai yang paling awal; beri sandi berbeda bila
     * perlu keduanya.
     */
    private function loginEmail(string $email, string $password, string $device): JsonResponse
    {
        $user = User::query()->where('email', $email)->first();
        $adminCocok = $this->passwordMatches($password, $user?->password);

        if ($adminCocok && Peran::sah($user->role)) {
            return $this->loginAdmin($user);
        }

        $guru = Teacher::query()->where('email', $email)->first();

        if ($this->passwordMatches($password, $guru?->password)) {
            return $this->loginTeacher($guru, $device);
        }

        $alumni = AlumniAccount::query()->where('email', $email)->first();

        if ($this->passwordMatches($password, $alumni?->password)) {
            return $this->loginAlumni($alumni, $device);
        }

        // Diperiksa setelah sandi cocok, supaya jawaban ini tidak bisa dipakai
        // menebak alamat surel mana yang terdaftar.
        if ($adminCocok) {
            throw ValidationException::withMessages([
                'identifier' => ['Akun ini belum diberi peran. Hubungi Admin Utama.'],
            ]);
        }

        $this->rejectCredentials();
    }

    /**
     * NISN lebih dulu; NIP hanya dicari bila nomor itu bukan milik siswa mana
     * pun. Keduanya tidak bisa bertabrakan — NISN 10 digit, NIP 18 digit.
     */
    private function loginNomorInduk(string $nomor, string $password, string $device): JsonResponse
    {
        $akun = Student::query()->where('nisn', $nomor)->first()
            ?? Teacher::query()
                // NIP sering ditulis berkelompok ("19800101 200501 1 001").
                ->whereIn('nip', array_unique([$nomor, preg_replace('/\s+/', '', $nomor)]))
                ->first();

        if (! $this->passwordMatches($password, $akun?->password)) {
            $this->rejectCredentials();
        }

        return $akun instanceof Teacher
            ? $this->loginTeacher($akun, $device)
            : $this->loginStudent($akun, $device);
    }

    private function loginStudent(Student $student, string $deviceName): JsonResponse
    {
        if (! $student->is_active) {
            $this->rejectInactive();
        }

        $token = $student->createToken($deviceName !== '' ? $deviceName : 'portal-siswa');

        return response()->json([
            'role' => 'student',
            'token' => $token->plainTextToken,
            'student' => new StudentResource($student->load('classroom')),
        ]);
    }

    private function loginTeacher(Teacher $teacher, string $deviceName): JsonResponse
    {
        if (! $teacher->is_active) {
            $this->rejectInactive();
        }

        $token = $teacher->createToken($deviceName !== '' ? $deviceName : 'portal-guru');

        return response()->json([
            'role' => 'teacher',
            'token' => $token->plainTextToken,
            'teacher' => new TeacherResource($teacher->load('homeroomClassrooms')),
        ]);
    }

    private function loginAlumni(AlumniAccount $alumni, string $deviceName): JsonResponse
    {
        if (! $alumni->is_active) {
            $this->rejectInactive();
        }

        $token = $alumni->createToken($deviceName !== '' ? $deviceName : 'portal-alumni');

        return response()->json([
            'role' => 'alumni',
            'token' => $token->plainTextToken,
            'alumni' => new AlumniResource($alumni),
        ]);
    }

    /**
     * Admin tidak bisa memakai token Sanctum karena Filament berjalan di atas
     * sesi. Jadi yang dikembalikan adalah tautan serah-terima sekali pakai
     * berumur pendek, yang ditukar menjadi sesi oleh HandoffController.
     */
    private function loginAdmin(User $user): JsonResponse
    {
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
            'identifier' => ['NISN/NIP/Email atau kata sandi salah.'],
        ]);
    }

    private function rejectInactive(): never
    {
        throw ValidationException::withMessages([
            'identifier' => ['Akun ini tidak aktif. Hubungi admin madrasah.'],
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
     * Mengembalikan objek siswa tanpa pembungkus "data". Guru memakai
     * GuruController::me.
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
