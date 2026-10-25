<?php

namespace App\Services;

use App\Models\OrganizationForm;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ExcelFormGenerator
{
    /**
     * Generate structured Excel file for Requirement No. 9: Surat Pernyataan Formulir Isian Data Ormas.
     * File name: [Nama Organisasi].xlsx (sanitized for storage path).
     */
    public static function generate(OrganizationForm $form): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Formulir Isian Ormas');

        // Page Layout Configuration
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        // Column Widths
        $sheet->getColumnDimension('A')->setWidth(6);   // No
        $sheet->getColumnDimension('B')->setWidth(35);  // Nama Field
        $sheet->getColumnDimension('C')->setWidth(3);   // :
        $sheet->getColumnDimension('D')->setWidth(55);  // Isi Field

        // Default Font & Styling
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        // Header Title
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', 'BADAN KESATUAN BANGSA DAN POLITIK KOTA PALEMBANG');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', 'Persyaratan No. 8 — FORMULIR ISIAN DATA ORMAS');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A3', 'Peraturan Menteri Dalam Negeri Nomor 57 Tahun 2017');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Divider Line
        $sheet->getStyle('A4:D4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);

        // Table Header
        $row = 6;
        $sheet->setCellValue('A' . $row, 'NO.');
        $sheet->setCellValue('B' . $row, 'FIELD ISIAN ORMAS');
        $sheet->setCellValue('C' . $row, ':');
        $sheet->setCellValue('D' . $row, 'KETERANGAN / RINCIAN DATA');
        
        $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('E2EFDA');
        $sheet->getStyle('A' . $row . ':D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $row . ':D' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // 18 Fields List Definition
        $fields = [
            1 => ['label' => 'Nama Organisasi', 'val' => $form->nama_ormas],
            2 => ['label' => 'Bidang Kegiatan', 'val' => $form->bidang_kegiatan],
            3 => ['label' => 'Alamat Kantor/Sekretariat', 'val' => $form->alamat_sekretariat],
            4 => ['label' => 'No. AHU / Akta Notaris', 'val' => "Akta Notaris: " . ($form->nomor_akta_pendirian ?: '-') . "\nNotaris: " . ($form->nama_notaris ?: '-') . "\nSK Kemenkumham: " . ($form->nomor_sk_kemenkumham ?: '-')],
            5 => ['label' => 'Tempat dan Waktu Pendirian', 'val' => $form->tempat_waktu_pendirian ?: '-'],
            6 => ['label' => 'Asas Ciri Organisasi', 'val' => $form->asas_ciri_organisasi ?: '-'],
            7 => ['label' => 'Tujuan Organisasi', 'val' => $form->tujuan_organisasi ?: '-'],
            8 => ['label' => 'Nama Pendiri', 'val' => $form->nama_pendiri ?: '-'],
            9 => ['label' => 'Nama Pembina', 'val' => $form->nama_pembina ?: '-'],
            10 => ['label' => 'Nama Penasehat', 'val' => $form->nama_penasehat ?: '-'],
            11 => [
                'label' => 'Nama Pengurus',
                'is_group' => true,
                'sub' => [
                    'a. Ketua/Sekretaris' => $form->nama_ketua ?: '-',
                    'b. Sekretaris/Sederajat' => $form->nama_sekretaris ?: '-',
                    'c. Bendahara/Sederajat' => $form->nama_bendahara ?: '-',
                ]
            ],
            12 => ['label' => 'Masa Bhakti Kepengurusan', 'val' => $form->masa_bhakti ?: '-'],
            13 => ['label' => 'Keputusan Tertinggi Organisasi', 'val' => $form->keputusan_tertinggi ?: '-'],
            14 => ['label' => 'Unit/cabang/Sayap Otonom Organisasi', 'val' => $form->unit_sayap_otonom ?: '-'],
            15 => ['label' => 'Usaha Organisasi', 'val' => $form->usaha_organisasi ?: '-'],
            16 => ['label' => 'Sumber Keuangan', 'val' => $form->sumber_keuangan ?: '-'],
            17 => ['label' => 'Lambang/Logo Organisasi', 'val' => $form->logo_path ? 'Lihat Lampiran Gambar' : 'Belum diunggah', 'has_img' => 'logo'],
            18 => ['label' => 'Bendera Organisasi', 'val' => $form->bendera_path ? 'Lihat Lampiran Gambar' : 'Belum diunggah', 'has_img' => 'bendera'],
        ];

        $startRow = 7;
        $currentRow = $startRow;

        foreach ($fields as $no => $item) {
            if (isset($item['is_group']) && $item['is_group']) {
                $sheet->setCellValue('A' . $currentRow, $no . '.');
                $sheet->setCellValue('B' . $currentRow, $item['label']);
                $sheet->setCellValue('C' . $currentRow, ':');
                $sheet->getStyle('A' . $currentRow . ':D' . $currentRow)->getFont()->setBold(true);
                $sheet->getStyle('A' . $currentRow . ':D' . $currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $currentRow++;

                foreach ($item['sub'] as $subLabel => $subVal) {
                    $sheet->setCellValue('B' . $currentRow, '    ' . $subLabel);
                    $sheet->setCellValue('C' . $currentRow, ':');
                    $sheet->setCellValue('D' . $currentRow, $subVal);
                    $sheet->getStyle('D' . $currentRow)->getAlignment()->setWrapText(true);
                    $sheet->getStyle('A' . $currentRow . ':D' . $currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $currentRow++;
                }
            } else {
                $sheet->setCellValue('A' . $currentRow, $no . '.');
                $sheet->setCellValue('B' . $currentRow, $item['label']);
                $sheet->setCellValue('C' . $currentRow, ':');
                $sheet->setCellValue('D' . $currentRow, $item['val']);
                
                $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $currentRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('A' . $currentRow . ':D' . $currentRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // Embed Logo/Flag Images into Excel if available
                if (isset($item['has_img'])) {
                    $imgPath = null;
                    if ($item['has_img'] === 'logo' && $form->logo_path) $imgPath = Storage::disk('local')->path($form->logo_path);
                    if ($item['has_img'] === 'bendera' && $form->bendera_path) $imgPath = Storage::disk('local')->path($form->bendera_path);

                    if ($imgPath && file_exists($imgPath)) {
                        $sheet->getRowDimension($currentRow)->setRowHeight(50);
                        $drawing = new Drawing();
                        $drawing->setName($item['label']);
                        $drawing->setPath($imgPath);
                        $drawing->setHeight(45);
                        $drawing->setCoordinates('D' . $currentRow);
                        $drawing->setOffsetX(10);
                        $drawing->setOffsetY(2);
                        $drawing->setWorksheet($sheet);
                    }
                }

                $currentRow++;
            }
        }

        // Signature & Cap Box Section
        $currentRow += 2;
        $dateStr = $form->confirmed_at ? $form->confirmed_at->format('d F Y') : date('d F Y');
        
        $sheet->mergeCells('C' . $currentRow . ':D' . $currentRow);
        $sheet->setCellValue('C' . $currentRow, 'Palembang, ' . $dateStr);
        $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        $sheet->mergeCells('C' . $currentRow . ':D' . $currentRow);
        $sheet->setCellValue('C' . $currentRow, 'Ketua Ormas,');
        $sheet->getStyle('C' . $currentRow)->getFont()->setBold(true);
        $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        // Signature & Cap Box Area (Merge C & D for 5 rows height)
        $boxStartRow = $currentRow;
        $boxEndRow = $currentRow + 4;
        $sheet->mergeCells('C' . $boxStartRow . ':D' . $boxEndRow);
        $sheet->getStyle('C' . $boxStartRow . ':D' . $boxEndRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Embed Digital Signature Image
        if ($form->signature_path && Storage::disk('local')->exists($form->signature_path)) {
            $sigDrawing = new Drawing();
            $sigDrawing->setName('Digital Signature');
            $sigDrawing->setPath(Storage::disk('local')->path($form->signature_path));
            $sigDrawing->setHeight(70);
            $sigDrawing->setCoordinates('C' . $boxStartRow);
            $sigDrawing->setOffsetX(30);
            $sigDrawing->setOffsetY(10);
            $sigDrawing->setWorksheet($sheet);
        }

        // Embed Cap PNG Image Overlay
        if ($form->cap_path && Storage::disk('local')->exists($form->cap_path)) {
            $capDrawing = new Drawing();
            $capDrawing->setName('Cap Ormas');
            $capDrawing->setPath(Storage::disk('local')->path($form->cap_path));
            $capDrawing->setHeight(65);
            $capDrawing->setCoordinates('C' . $boxStartRow);
            $capDrawing->setOffsetX(120);
            $capDrawing->setOffsetY(10);
            $capDrawing->setWorksheet($sheet);
        }

        $currentRow = $boxEndRow + 1;
        $sheet->mergeCells('C' . $currentRow . ':D' . $currentRow);
        $sheet->setCellValue('C' . $currentRow, '( ' . ($form->nama_ketua ?: 'Nama Lengkap Ketua Ormas') . ' )');
        $sheet->getStyle('C' . $currentRow)->getFont()->setBold(true)->setUnderline(true);
        $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Save File to Storage
        $cleanOrgName = Str::slug($form->nama_ormas, ' ');
        if (empty($cleanOrgName)) $cleanOrgName = 'Formulir_Data_Ormas';
        $fileName = $cleanOrgName . '.xlsx';

        $relativeDirectory = 'forms/' . $form->user_id;
        $storageDir = storage_path('app/' . $relativeDirectory);
        if (!file_exists($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $fullPath = $storageDir . '/' . $fileName;
        $relativeFilePath = $relativeDirectory . '/' . $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($fullPath);

        // Save path to model
        $form->update(['excel_path' => $relativeFilePath]);

        return $relativeFilePath;
    }
}
