<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswas'; // Nama tabel yang sudah ada di database id_card
    protected $primaryKey = 'nim';   // Gunakan NIM sebagai primary key sesuai standar UINSA
    public $incrementing = false;    // NIM bukan auto-increment
    protected $keyType = 'string';

    protected $fillable = ['nim', 'nama', 'fakultas', 'prodi', 'file_foto', 'status_foto'];
}
