<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PendaftaranOsisResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $siswaData = null;

        if ($this->siswa) {
            $siswaData = [
                'id' => $this->siswa->id,
                'nis' => $this->siswa->nis,
                'nama' => $this->siswa->nama,
            ];

            if ($this->siswa->relationLoaded('kelas') && $this->siswa->kelas) {
                $siswaData['kelas'] = [
                    'id' => $this->siswa->kelas->id,
                    'nama_kelas' => $this->siswa->kelas->nama_kelas,
                ];
            }

            if ($this->siswa->relationLoaded('jenisKelamin') && $this->siswa->jenisKelamin) {
                $siswaData['jenis_kelamin'] = [
                    'id' => $this->siswa->jenisKelamin->id,
                    'nama' => $this->siswa->jenisKelamin->nama,
                ];
            }
        }

        return [
            'id' => $this->id,
            'siswa' => $siswaData,
            'pilihan_bidang' => $this->pilihan_bidang,
            'motivasi' => $this->motivasi,
            
        ];
    }
}
