<?php declare(strict_types=1);

final class View {
    private MustacheLite $mustache;
    private string $viewsDir;

    public function __construct(string $viewsDir) {
        $this->mustache = new MustacheLite();
        $this->viewsDir = rtrim($viewsDir, '/');
    }

    /** @param array<string,mixed> $vm */
    public function render(string $template, array $vm): string {
        $path = $this->viewsDir . '/' . ltrim($template, '/');
        if (!is_file($path)) throw new RuntimeException("View not found: {$template}");
        $tpl = (string)file_get_contents($path);
        return $this->mustache->render($tpl, $vm);
    }

    /** @param array<string,mixed> $vm */
    public function page(string $layout, string $view, array $vm): void {
        $content = $this->render($view, $vm);
        $html = $this->render($layout, array_merge($vm, ['content' => $content]));
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
    }
}
