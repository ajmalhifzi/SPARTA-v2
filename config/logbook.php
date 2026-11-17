<?php
return [
    'demo' => env('LOGBOOK_DEMO', false),
    'sections' => [1 => 'Pengenalan Projek', 'Pernyataan Masalah', 'Objektif Projek', 'Tujuan Projek', 'Skop Projek', 'Literatur', 'Metodologi', 'Anggaran Kos', 'Perancangan Pelaksanaan Projek', 'Carta Gantt Pelaksanaan Projek', 'Jangkaan Hasil', 'Rujukan', 'Lampiran'],
    'components' => ['Laporan Kertas Cadangan' => 60, 'Buku Log PTA 1' => 5, 'Pembentangan PTA 1' => 25, 'Kemajuan Projek' => 10],
    'pages' => [

        'profile' => ['Maklumat Pelajar', 'id', '01'],
        'proposal' => ['Kertas Cadangan', 'file-pencil', '02'],
        'schedule' => ['Jadual Perjumpaan', 'calendar', '03'],
        'meeting' => ['Rekod Perjumpaan', 'notes', '04'],
        'evaluation' => ['Borang Penilaian PTA 1', 'clipboard-check', '05'],
        'report' => ['Laporan', 'file-description', '06'],
        'slides' => ['Slaid', 'book-2', '07'],
    ],
];
