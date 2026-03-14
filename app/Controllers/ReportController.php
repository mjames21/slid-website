<?php
// FILE: app/Controllers/ReportController.php
declare(strict_types=1);

final class ReportController extends BaseController
{
    private const MAX_FILE_BYTES = 5_000_000; // 5MB

    public function index(): void
    {
        $vm = array_merge(SiteVm::base($this->config), [
            'page_title' => 'Report an Issue',
            'success' => '',
            'ref' => '',
            'errors' => [],
            'old' => [
                'full_name' => '',
                'email' => '',
                'phone' => '',
                'category' => '',
                'location' => '',
                'details' => '',
            ],
            'categories' => $this->categories(''),
        ]);

        if (strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            $this->view->page('layout.html', 'report.html', $vm);
            return;
        }

        $errors = [];
        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $category = trim((string)($_POST['category'] ?? ''));
        $location = trim((string)($_POST['location'] ?? ''));
        $details = trim((string)($_POST['details'] ?? ''));

        if ($fullName === '') $errors[] = 'Full name is required.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
        if ($category === '') $errors[] = 'Please select a category.';
        if ($details === '' || mb_strlen($details) < 20) $errors[] = 'Please provide details (at least 20 characters).';

        $uploadMeta = null;
        if (isset($_FILES['attachment']) && is_array($_FILES['attachment'])) {
            $uploadMeta = $this->handleUpload($_FILES['attachment'], $errors);
        }

        $vm['old'] = [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'category' => $category,
            'location' => $location,
            'details' => $details,
        ];
        $vm['errors'] = array_map(static fn(string $e): array => ['text' => $e], $errors);
        $vm['categories'] = $this->categories($category);

        if ($errors) {
            $this->view->page('layout.html', 'report.html', $vm);
            return;
        }

        $ref = $this->persistReport([
            'ref' => '',
            'created_at' => gmdate('c'),
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'category' => $category,
            'location' => $location,
            'details' => $details,
            'attachment' => $uploadMeta,
            'ip' => (string)($_SERVER['REMOTE_ADDR'] ?? ''),
            'user_agent' => (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
        ]);

        $vm['success'] = '1';
        $vm['ref'] = $ref;
        $vm['errors'] = [];
        $vm['categories'] = $this->categories('');
        $vm['old'] = [
            'full_name' => '',
            'email' => '',
            'phone' => '',
            'category' => '',
            'location' => '',
            'details' => '',
        ];

        $this->view->page('layout.html', 'report.html', $vm);
    }

    /** @param array<string,mixed> $file @param array<int,string> &$errors */
    private function handleUpload(array $file, array &$errors): ?array
    {
        $err = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err === UPLOAD_ERR_NO_FILE) return null;
        if ($err !== UPLOAD_ERR_OK) {
            $errors[] = 'Attachment upload failed.';
            return null;
        }

        $tmp = (string)($file['tmp_name'] ?? '');
        $size = (int)($file['size'] ?? 0);
        $name = (string)($file['name'] ?? 'attachment');
        if (!is_file($tmp)) {
            $errors[] = 'Attachment upload failed.';
            return null;
        }
        if ($size <= 0 || $size > self::MAX_FILE_BYTES) {
            $errors[] = 'Attachment must be smaller than 5MB.';
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string)($finfo->file($tmp) ?: '');
        $allowed = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($allowed[$mime])) {
            $errors[] = 'Attachment must be a PDF or image (JPG/PNG/WebP).';
            return null;
        }

        $safeBase = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $name) ?: 'attachment';
        $ext = $allowed[$mime];
        if (!str_ends_with(strtolower($safeBase), '.' . $ext)) {
            $safeBase .= '.' . $ext;
        }

        $dir = $this->storageDir() . '/attachments';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            $errors[] = 'Server storage is not available for attachments.';
            return null;
        }

        $id = bin2hex(random_bytes(8));
        $dest = $dir . '/' . $id . '-' . $safeBase;

        if (!move_uploaded_file($tmp, $dest)) {
            $errors[] = 'Attachment upload failed.';
            return null;
        }

        return [
            'original_name' => $name,
            'stored_name' => basename($dest),
            'mime' => $mime,
            'bytes' => $size,
        ];
    }

    /** @param array<string,mixed> $payload */
    private function persistReport(array $payload): string
    {
        $dir = $this->storageDir() . '/reports';
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Server storage is not available.');
        }

        $ref = 'SLID-' . gmdate('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $payload['ref'] = $ref;

        $file = $dir . '/' . $ref . '.json';
        file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $ref;
    }

    
    /** @return array<int,array<string,mixed>> */
    private function categories(string $selected): array
    {
        $cats = [
            ['value' => 'passport', 'label' => 'Passport Application / Renewal'],
            ['value' => 'visa', 'label' => 'Visa / eVisa'],
            ['value' => 'permit', 'label' => 'Permits / Residency'],
            ['value' => 'border', 'label' => 'Border / Entry-Exit Issue'],
            ['value' => 'complaint', 'label' => 'Service Complaint'],
            ['value' => 'other', 'label' => 'Other'],
        ];

        return array_map(static function (array $c) use ($selected): array {
            $c['is_selected'] = ((string)($c['value'] ?? '') === $selected) ? '1' : '';
            return $c;
        }, $cats);
    }

private function storageDir(): string
    {
        $root = dirname(__DIR__, 2);
        return $root . '/storage';
    }
}
