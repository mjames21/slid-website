<?php
// FILE: app/Controllers/DownloadsController.php
declare(strict_types=1);

final class DownloadsController extends BaseController
{
    /**
     * Hard-coded public PDF (safe, no path traversal).
     * You upload it here:
     *   public/assets/notices/convicted-drug-matters-as-at-29-jan-2026-public-redacted.pdf
     */
    private const PUBLIC_PDF_RELATIVE = '/assets/notices/convicted-drug-matters-as-at-29-jan-2026-public-redacted.pdf';

    /**
     * Route:
     *   /downloads/convicted-drug-matters-public        -> inline view
     *   /downloads/convicted-drug-matters-public?dl=1   -> forced download
     */
    public function convictedDrugMattersPublic(): void
    {
        $download = isset($_GET['dl']) && $_GET['dl'] === '1';

        $publicRoot = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        if ($publicRoot === '') {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Server misconfiguration: DOCUMENT_ROOT missing.';
            return;
        }

        $absolutePath = $publicRoot . self::PUBLIC_PDF_RELATIVE;

        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            http_response_code(404);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'PDF not found. Upload it into: public' . self::PUBLIC_PDF_RELATIVE;
            return;
        }

        $filename = basename($absolutePath);

        header('X-Content-Type-Options: nosniff');
        header('Content-Type: application/pdf');
        header('Content-Length: ' . (string)filesize($absolutePath));
        header('Cache-Control: public, max-age=86400');

        header(
            'Content-Disposition: ' .
            ($download ? 'attachment' : 'inline') .
            '; filename="' . $filename . '"'
        );

        $fp = fopen($absolutePath, 'rb');
        if ($fp === false) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Failed to open PDF.';
            return;
        }

        while (!feof($fp)) {
            $buf = fread($fp, 1024 * 256);
            if ($buf === false) break;
            echo $buf;
            flush();
        }

        fclose($fp);
    }
}
