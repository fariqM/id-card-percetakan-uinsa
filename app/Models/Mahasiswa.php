<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa'; // Nama tabel yang sudah ada di database id_card
    protected $primaryKey = 'nim';   // Gunakan NIM sebagai primary key sesuai standar UINSA
    public $incrementing = false;    // NIM bukan auto-increment
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nim',
        'nama',
        'fakultas',
        'prodi',
        'file_foto',
        'status_foto',
        'foto_path'
    ];
}
