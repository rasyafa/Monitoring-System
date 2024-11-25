<?php

namespace App\Http\Controllers;

use App\Models\Absen;
use App\Models\KegiatanHarian;
use App\Models\LaporanAkhir;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class AdminController extends Controller
{
    // Membuat array $data berisi informasi untuk dashboard
    public function dashboard()
    {
        $data = [
            // Menghitung total semua pengguna di tabel `users`
            'users_count' => User::count(),

            // Menghitung total pengguna dengan peran siswa
            'students_count' => User::where('role', 'siswa')->count(),

            // Menghitung total pembimbing
            'pembimbing_count' => User::where('role', 'pembimbing')->count(),

            // Menghitung total mentor
            'mentors_count' => User::where('role', 'mentor')->count(),
        ];

        // Mengarahkan data ke view
        return view('admin.dashboard', compact('data'));
    }

    // Menampilkan daftar pengguna
    public function manageUsers()
    {
        $users = User::whereIn('role', ['siswa', 'pembimbing', 'mentor'])->paginate(5);
        return view('admin.users.index', compact('users'));
    }

    // Menampilkan form untuk membuat pengguna baru
    public function createUser()
    {
        return view('admin.users.create');
    }

    // Menyimpan Pengguna Baru
    public function storeUser(Request $request)
    {
        // Validasi input untuk memastikan data yang diterima valid
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:siswa,pembimbing,mentor,admin',
            'email' => 'required|string|email|max:255|unique:users',
            'gender' => 'required|string',
            'city' => 'required|string|max:255',
        ]);

        // Membuat pengguna baru di database
        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email' => $request->email,
            'gender' => $request->gender,
            'city' => $request->city,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan');
    }

    // Tampilkan form untuk mengedit pengguna
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    // Update data pengguna
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:siswa,pembimbing,mentor',
            'gender' => 'required|in:male,female',
            'city' => 'required|string|max:255',
        ]);

        $data = $request->only('name', 'username', 'email', 'role', 'gender', 'city');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui');
    }

    // Hapus pengguna
    public function deleteUser(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus');
    }

    // CRUD ABSEN SISWA
    // Fungsi untuk menampilkan halaman absensi
    public function absenIndex()
    {
        // Mengambil data siswa yang berperan sebagai siswa
        $students = User::where('role', 'siswa')->get();

        // Mengambil semua data absensi dari tabel absens
        $attendances = Absen::all();

        // Tentukan tanggal awal dan akhir PKL
        $startDate = '2023-08-05';
        $endDate = '2023-12-05';

        // Dapatkan daftar hari kerja selama periode PKL
        $workingDays = $this->getWorkingDays($startDate, $endDate);

        // Hitung persentase kehadiran untuk setiap siswa
        foreach ($students as $student) {
            // Hitung total sesi berdasarkan hari kerja dalam periode
            $totalSessions = count($workingDays);
            $presentSessions = $attendances->where('user_id', $student->id)->where('status', 'Hadir')->count();

            // Persentase kehadiran berdasarkan status "Hadir"
            $student->attendance_percentage = $totalSessions > 0 ? ($presentSessions / $totalSessions) * 100 : 0;
            $student->total_sessions = $totalSessions;
            $student->present_sessions = $presentSessions;
        }

        // Kirim data ke view
        return view('admin.absen.index', compact('students', 'attendances'));
    }

    // Method privat untuk menghitung hari kerja
    private function getWorkingDays($startDate, $endDate)
    {
        // Mengubah tanggal awal dan akhir ke objek Carbon
        $start = Carbon::createFromFormat('Y-m-d', $startDate);
        $end = Carbon::createFromFormat('Y-m-d', $endDate);

        // Pastikan tanggal akhir lebih besar atau sama dengan tanggal awal
        if ($start->gt($end)) {
            return [];
        }

        $workingDays = [];

        // Iterasi setiap hari dari tanggal mulai hingga tanggal akhir
        while ($start->lte($end)) {
            // Cek apakah hari ini adalah Senin-Jumat (1=Senin, ..., 5=Jumat)
            if ($start->isWeekday()) {
                // Jika ya, tambahkan ke array hari kerja
                $workingDays[] = $start->toDateString();
            }
            // Tambahkan satu hari ke tanggal saat ini
            $start->addDay();
        }

        return $workingDays;
    }

    // Menampilkan daftar kegiatan semua siswa
    public function kegiatanIndex()
    {
        // Mendapatkan data siswa dengan role 'siswa'
        $students = User::where('role', 'siswa')->get(); // Mengambil data siswa dengan peran 'siswa'
        return view('admin.kegiatan.index', compact('students'));
    }

    // Menampilkan detail kegiatan/logbook siswa berdasarkan ID
    public function kegiatanShow($id)
    {
        // Cari siswa berdasarkan ID
        $students = User::findOrFail($id);

        // Ambil data kegiatan siswa berdasarkan ID siswa
        $kegiatans = KegiatanHarian::where('user_id', $id)->get();

        // Kirim data ke view
        return view('admin.kegiatan.show', compact('students', 'kegiatans'));
    }

    // Method untuk validasi kegiatan
    public function validasiKegiatan($id)
    {
        $kegiatan = KegiatanHarian::findOrFail($id);

        // Mengubah status menjadi 'acc' dan menambahkan catatan jika ada
        $kegiatan->status = 'acc';
        if (request()->has('catatan')) {
            $kegiatan->catatan = request('catatan');
        }

        $kegiatan->save();

        return redirect()->route('admin.kegiatan.show', $kegiatan->user_id)
        ->with('success', 'Kegiatan telah diterima (ACC).');
    }

    // LAPORAN AKHIR
    public function laporanAkhirIndex()
    {
        $students = User::where('role', 'siswa')->get();
        $laporans = LaporanAkhir::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return view('admin.laporan-akhir', compact('students', 'laporans'));
    }

    public function laporanAkhirShow($id)
    {
        // Cari siswa berdasarkan ID
        $students = User::findOrFail($id);

        // Ambil data laporan akhir berdasarkan ID siswa
        $laporans = LaporanAkhir::where('user_id', $id)->get();

        // Kirim data ke view
        return view('admin.laporan', compact('students', 'laporans'));
    }

    public function downloadAttendancePDF()
    {
        $students = User::where('role', 'siswa')->get(); // Ambil data siswa
        $attendances = Absen::all(); // Ambil data absensi

        // Render tampilan ke PDF
        $pdf = Pdf::loadView('admin.absen.attendance', compact('students', 'attendances'))
            ->setPaper('a4', 'landscape');

        // Unduh file PDF
        return $pdf->download('rekap-kehadiran.pdf');
    }

    public function downloadLogbookPdf($id)
    {
        // Ambil data mahasiswa berdasarkan ID
        $students = User::where('role', 'siswa')->findOrFail($id);

        // Ambil kegiatan yang terkait dengan mahasiswa ini
        $kegiatans = KegiatanHarian::where('user_id', $id)->get();

        // Generate PDF dari tampilan HTML
        $pdf = Pdf::loadView('admin.kegiatan.activity', compact('students', 'kegiatans'));

        // Download PDF
        return $pdf->download('laporan_harian_' . $students->name . '.pdf');
    }

     // Menampilkan form untuk memilih mentor untuk siswa
    public function assignMentorForm()
    {
        $students = User::where('role', 'siswa')->get(); // Mengambil semua siswa
        $mentors = User::where('role', 'mentor')->get(); // Mengambil semua mentor
        return view('admin.assign-mentor', compact('students', 'mentors'));
    }

    // Menangani penugasan mentor ke siswa
    public function assignMentor(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'mentor_id' => 'required|exists:users,id', // Pastikan mentor_id ada di users
        ]);

        // Cari siswa berdasarkan ID
        $students = User::findOrFail($id);

        // Assign mentor_id ke siswa
        $students->mentor_id = $request->mentor_id;
        $students->save(); // Simpan perubahan ke database

        // Kembali ke form dengan pesan sukses
        return redirect()->route('admin.assignMentorForm')->with('success', 'Mentor berhasil ditugaskan.');
    }

}
