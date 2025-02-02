@extends('layouts.pembimbing')

@section('title', 'Data Logbook Siswa')

@section('page-title', 'Data Logbook Siswa')

@section('content')

<style>
    :root {
            --main-bg-color: #03d703;
            --main-text-color: #03d703;
            --second-text-color: #686868;
            --second-bg-color: #fff;
            --toggle-color: #03d703;
            --heading-color: #03d703;
        }


    h2 {
        color: #2e7d32;
        /* Hijau */
    }

    .table-striped>tbody>tr:nth-of-type(odd) {
        background-color: #e8f5e9;
        /* Baris hijau sangat terang */
    }

    .table-bordered thead {
        background-color: #ffffff;
        /* Header tabel putih */
        color: #2e7d32;
        /* Hijau tua untuk teks */
        border-bottom: 2px solid #2e7d32;
        /* Garis bawah header */
    }

    .btn-light-green {
        background-color: #66bb6a;
        /* Hijau cerah */
        color: white;
        border: none;
    }

    .btn-light-green:hover {
        background-color: #4caf50;
        /* Hijau lebih gelap saat hover */
    }

    .btn-danger {
        background-color: #ef5350;
        /* Merah */
        color: white;
        border: none;
    }

    .btn-danger:hover {
        background-color: #d32f2f;
        /* Merah lebih gelap saat hover */
    }
</style>

<div class="container mt-5">
    <div class="card">
        <div class="card-header text-center">
            <h2 class="mb-0">Data Logbook Siswa</h2>
        </div>
        <div class="card-body">
            <!-- Tabel Kegiatan Siswa -->
            <table class="table table-striped table-hover table-bordered">
                <thead class="text-center">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $index => $siswa)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $siswa->name }}</td>
                        <td class="text-center">
                            <a href="{{ route('pembimbing.show', $siswa->id) }}" class="btn btn-light-green btn-sm">
                                Lihat laporan harian
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
