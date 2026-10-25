<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationForm extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'nama_ormas',
        'bidang_kegiatan',
        'alamat_sekretariat',
        'nomor_akta_pendirian',
        'nama_notaris',
        'nomor_sk_kemenkumham',
        'tempat_waktu_pendirian',
        'asas_ciri_organisasi',
        'tujuan_organisasi',
        'nama_pendiri',
        'nama_pembina',
        'nama_penasehat',
        'nama_ketua',
        'nama_sekretaris',
        'nama_bendahara',
        'masa_bhakti',
        'keputusan_tertinggi',
        'unit_sayap_otonom',
        'usaha_organisasi',
        'sumber_keuangan',
        'logo_path',
        'bendera_path',
        'signature_path',
        'cap_path',
        'excel_path',
        'is_confirmed',
        'confirmed_at',
        'extra_fields',
        'completed_at',
    ];

    protected $casts = [
        'extra_fields' => 'array',
        'is_confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
