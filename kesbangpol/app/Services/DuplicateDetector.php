<?php

namespace App\Services;

use App\Models\Organization;

class DuplicateDetector
{
    /**
     * Check if an organization might be a duplicate of an existing record.
     * Returns array of potential duplicate organizations with match reasons.
     */
    public static function detectDuplicates(Organization $org): array
    {
        $matches = [];
        $cleanOrgName = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $org->name)));
        $nameSub = strlen($cleanOrgName) > 4 ? substr($cleanOrgName, 0, 4) : $cleanOrgName;

        $query = Organization::where('id', '!=', $org->id);

        $query->where(function ($q) use ($org, $nameSub) {
            if ($nameSub) {
                $q->orWhere('name', 'like', "%{$nameSub}%");
            }
            if (!empty($org->abbreviation)) {
                $q->orWhere('abbreviation', 'like', '%' . trim($org->abbreviation) . '%');
            }
            if (!empty($org->registration_number)) {
                $q->orWhere('registration_number', trim($org->registration_number));
            }
            if (!empty($org->leader_name)) {
                $q->orWhere('leader_name', 'like', '%' . trim($org->leader_name) . '%');
            }
        });

        $existingOrgs = $query->take(25)->get();

        foreach ($existingOrgs as $existing) {
            $reasons = [];

            // Check exact or partial name match
            $cleanExistingName = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $existing->name)));
            if ($cleanOrgName === $cleanExistingName) {
                $reasons[] = 'Nama organisasi persis sama';
            } else {
                similar_text($cleanOrgName, $cleanExistingName, $percent);
                if ($percent > 75) {
                    $reasons[] = 'Nama organisasi memiliki kemiripan ' . round($percent) . '%';
                }
            }

            // Check abbreviation match if available
            if (!empty($org->abbreviation) && !empty($existing->abbreviation)) {
                if (strcasecmp(trim($org->abbreviation), trim($existing->abbreviation)) === 0) {
                    $reasons[] = 'Singkatan nama organisasi sama (' . $org->abbreviation . ')';
                }
            }

            // Check registration number match
            if (!empty($org->registration_number) && !empty($existing->registration_number)) {
                if (trim($org->registration_number) === trim($existing->registration_number)) {
                    $reasons[] = 'Nomor registrasi sama (' . $org->registration_number . ')';
                }
            }

            // Check leader name match
            if (!empty($org->leader_name) && !empty($existing->leader_name)) {
                if (strcasecmp(trim($org->leader_name), trim($existing->leader_name)) === 0) {
                    $reasons[] = 'Nama Ketua sama (' . $org->leader_name . ')';
                }
            }

            // Check address match
            if (!empty($org->address) && !empty($existing->address)) {
                similar_text(strtolower($org->address), strtolower($existing->address), $addrPercent);
                if ($addrPercent > 80) {
                    $reasons[] = 'Alamat sekretariat memiliki kemiripan tinggi (' . round($addrPercent) . '%)';
                }
            }

            if (!empty($reasons)) {
                $matches[] = [
                    'organization' => $existing,
                    'reasons' => $reasons,
                ];
            }
        }

        return $matches;
    }
}
