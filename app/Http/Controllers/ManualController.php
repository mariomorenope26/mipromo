<?php

namespace App\Http\Controllers;

use App\Services\UserManualService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ManualController extends Controller
{
    public function index(UserManualService $manual): View
    {
        $sections = $manual->sections();
        $bestPractices = $manual->bestPractices();

        return view('manual.index', compact('sections', 'bestPractices'));
    }

    public function pdf(UserManualService $manual): Response
    {
        @set_time_limit(120);

        $sections = collect($manual->sections())->map(function (array $section): array {
            $section['image_data_uri'] = $this->toDataUri($section['image'] ?? '');

            return $section;
        })->all();

        $bestPractices = $manual->bestPractices();
        $logoDataUri = $this->toDataUri('images/logo_jmsperu.png');
        $generatedAt = now()->format('d/m/Y H:i');

        $pdf = Pdf::loadView('manual.pdf', compact(
            'sections',
            'bestPractices',
            'logoDataUri',
            'generatedAt'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('MiPromo-Manual-de-Uso-Oficial-JMSPERU.pdf');
    }

    private function toDataUri(string $relativePath): ?string
    {
        $clean = ltrim(trim($relativePath), '/');
        if ($clean === '') {
            return null;
        }

        $fullPath = public_path($clean);
        if (! is_file($fullPath) || ! is_readable($fullPath)) {
            return null;
        }

        $mime = match (strtolower(pathinfo($fullPath, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        $contents = file_get_contents($fullPath);
        if ($contents === false) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
