<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PendaftaranOsisResource;
use App\Models\PendaftaranOsis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PendaftaranOsisController extends Controller
{
    /**
     * Menampilkan daftar seluruh pendaftaran OSIS.
     */
    public function index(): AnonymousResourceCollection
    {
        // TODO LATIHAN L1:
        // GET daftar pendaftaran seharusnya menyertakan data kelas dan jenis kelamin melalui relasi siswa.
        // Jalankan GET /api/pendaftaran-osis di Postman dan periksa object siswa pada setiap item.
        // Mengapa data kelas dan jenis kelamin belum muncul pada daftar, padahal pada GET detail sudah lengkap?
        $pendaftaran = PendaftaranOsis::with(
            'siswa.kelas',
            'siswa.jenisKelamin'
        )->get();
        return PendaftaranOsisResource::collection($pendaftaran);
    }

    /**
     * Menampilkan detail satu pendaftaran OSIS berdasarkan ID.
     */
    public function show(string|int $id): JsonResponse|PendaftaranOsisResource
    {
        $pendaftaran = PendaftaranOsis::with(['siswa.kelas', 'siswa.jenisKelamin'])->find($id);

        if (! $pendaftaran) {
            return response()->json([
                'message' => 'Pendaftaran tidak ditemukan.',
            ], 404);
        }

        return new PendaftaranOsisResource($pendaftaran);
    }

    /**
     * Menyimpan data pendaftaran OSIS baru.
     */
    public function store(Request $request): JsonResponse
    {
        
        $validated = $request->validate([
            // TODO LATIHAN L2:
            // Validasi POST seharusnya memastikan siswa_id benar-benar terdaftar pada tabel siswa.
            // Kirim POST /api/pendaftaran-osis dengan siswa_id yang tidak ada (misal: 9999).
            // Amati apakah request ditolak dengan status 422 atau justru menyebabkan error database.
            // Aturan validasi apa yang harus ditambahkan pada siswa_id? DONE
            
            
            // TODO LATIHAN L3:
            // Satu siswa hanya boleh memiliki satu pendaftaran OSIS.
            // Kirim dua kali POST /api/pendaftaran-osis valid dengan siswa_id yang sama.
            // Bandingkan respons request kedua dan periksa apakah jumlah record di database bertambah.
            // Di mana dan bagaimana pencegahan pendaftaran ganda ini seharusnya divalidasi?
            'siswa_id' => ['required', 'integer', 'exists:siswa,id', 'unique:pendaftaran_osis,siswa_id'],

            // TODO LATIHAN L4:
            // Pilihan bidang pendaftaran wajib dibatasi hanya pada: kepemimpinan, keagamaan, olahraga, atau seni.
            // Kirim POST / PUT dengan nilai pilihan_bidang di luar keempat opsi tersebut (misal: "robotik").
            // Periksa apakah request tersebut lolos atau ditolak dengan HTTP 422.
            // Bagaimana membatasi nilai input agar hanya menerima opsi yang diizinkan?
            'pilihan_bidang' => ['required', 'string', 'in:kepemimpinan,keagamaan,olahraga,seni'],

            // TODO LATIHAN L5:
            // Motivasi wajib memiliki panjang antara 20 sampai 300 karakter.
            // Uji dengan mengirim teks motivasi yang terlalu pendek (19 karakter) atau terlalu panjang (301 karakter).
            // Amati apakah validasi menolak input tersebut dengan status 422.
            // Batasan panjang karakter apa yang belum diterapkan pada validasi ini?
            'motivasi' => ['required', 'string', 'min:20', 'max:300'],
        ]);

        $pendaftaran = PendaftaranOsis::create([
            'siswa_id' => $validated['siswa_id'],
            'pilihan_bidang' => $validated['pilihan_bidang'],
            'motivasi' => $validated['motivasi'],
        ]);

        $pendaftaran->load(['siswa.kelas', 'siswa.jenisKelamin']);

        // TODO LATIHAN L6:
        // Berdasarkan spesifikasi REST API, pembuatan resource baru yang berhasil harus mengembalikan status HTTP 201.
        // Kirim request POST valid di Postman dan periksa status HTTP pada respons yang diterima.
        // Mengapa status yang diterima saat ini adalah 200 OK dan bagaimana mengubahnya menjadi 201 Created?
        return response()->json([
            'message' => 'Pendaftaran berhasil dibuat.',
            'data' => new PendaftaranOsisResource($pendaftaran),
        ], 201);
    }

    /**
     * Memperbarui data pendaftaran OSIS yang sudah ada.
     */
    public function update(Request $request, string|int $id): JsonResponse
    {
        $pendaftaran = PendaftaranOsis::find($id);

        if (! $pendaftaran) {
            return response()->json([
                'message' => 'Pendaftaran tidak ditemukan.',
            ], 404);
        }

        $validated = $request->validate([
            // TODO LATIHAN L7:
            // Pada method PUT, siswa_id milik pendaftaran yang sedang diedit seharusnya boleh tetap digunakan.
            // Kirim PUT /api/pendaftaran-osis/{id} menggunakan siswa_id yang sama dengan data aslinya.
            // Perhatikan bahwa request ditolak dengan pesan duplikat (422) padahal itu adalah data milik sendiri.
            // Bagaimana cara mengecualikan ID pendaftaran yang sedang diperbarui dari pemeriksaan keunikan?
            'siswa_id' => [
                'required',
                'integer',
                'exists:siswa,id',
                'unique:pendaftaran_osis,siswa_id,' . $pendaftaran->id,
            ],

            // TODO LATIHAN L4:
            // Pilihan bidang pendaftaran wajib dibatasi hanya pada: kepemimpinan, keagamaan, olahraga, atau seni.
            // Kirim POST / PUT dengan nilai pilihan_bidang di luar keempat opsi tersebut (misal: "robotik").
            // Periksa apakah request tersebut lolos atau ditolak dengan HTTP 422.
            // Bagaimana membatasi nilai input agar hanya menerima opsi yang diizinkan?
            'pilihan_bidang' => ['required', 'string' , 'in:kepemimpinan,keagamaan,olahraga,seni'],

            // TODO LATIHAN L5:
            // Motivasi wajib memiliki panjang antara 20 sampai 300 karakter.
            // Uji dengan mengirim teks motivasi yang terlalu pendek (19 karakter) atau terlalu panjang (301 karakter).
            // Amati apakah validasi menolak input tersebut dengan status 422.
            // Batasan panjang karakter apa yang belum diterapkan pada validasi ini?
            'motivasi' => ['required', 'string', 'min:20', 'max:300'],
        ]);

        // TODO LATIHAN L8:
        // Request PUT yang valid seharusnya memperbarui semua data pendaftaran (termasuk pilihan_bidang dan motivasi) ke database.
        // Kirim PUT valid dengan mengubah pilihan_bidang dan motivasi, lalu periksa isi tabel di database atau lakukan GET detail.
        // Mengapa nilai pilihan_bidang dan motivasi di database tidak berubah meskipun API mengembalikan pesan berhasil?
        // Field apa saja yang diteruskan pada proses update model?
        $pendaftaran->update([
            'siswa_id' => $validated['siswa_id'],
            'pilihan_bidang' => $validated['pilihan_bidang'],
            'motivasi' => $validated['motivasi'],
        ]);

        $pendaftaran->load(['siswa.kelas', 'siswa.jenisKelamin']);

        return response()->json([
            'message' => 'Pendaftaran berhasil diperbarui.',
            'data' => new PendaftaranOsisResource($pendaftaran),
        ], 200);
    }

    /**
     * Menghapus data pendaftaran OSIS.
     */
    public function destroy(string|int $id): JsonResponse|Response
    {
        $pendaftaran = PendaftaranOsis::find($id);
        if (!$pendaftaran) {
            return response()->json([
                'message' => 'Pendaftaran not found!',
            ], 404);
        }

        $pendaftaran->delete();
        return response()->json([
            'message' => 'Pendaftaran deleted successfully!',
        ], 200);

        // TODO LATIHAN L9:
        // Request DELETE yang berhasil seharusnya menghapus data pendaftaran dari database dan mengembalikan HTTP 204 tanpa body.
        // Kirim DELETE /api/pendaftaran-osis/{id} pada ID yang tersedia.
        // Respons mengembalikan 204 No Content, tetapi lakukan GET detail atau cek database: datanya ternyata masih ada!
        // Operasi apa yang belum dipanggil pada instance model pendaftaran sebelum mengembalikan respons?

        return response()->noContent();
    }
}
