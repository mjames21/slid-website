<?php declare(strict_types=1);

/**
 * MustacheLite - tiny Mustache-ish renderer (no Composer required).
 *
 * Supported:
 * - Variables: {{name}}
 * - Unescaped: {{{content}}}
 * - Sections: {{#items}}...{{/items}}
 * - Inverted sections: {{^items}}...{{/items}}
 *
 * Tip: Avoid dotted keys (dg.image_url). Keep VM keys "flat" in each section.
 */
final class MustacheLite {
    /** @param array<string,mixed> $ctx */
    public function render(string $tpl, array $ctx): string {
        $tpl = $this->renderUnescaped($tpl, $ctx);
        $tpl = $this->renderSections($tpl, $ctx, false);
        $tpl = $this->renderSections($tpl, $ctx, true);
        $tpl = $this->renderVars($tpl, $ctx);
        return $tpl;
    }

    /** @param array<string,mixed> $ctx */
    private function renderUnescaped(string $tpl, array $ctx): string {
        return preg_replace_callback('/\{\{\{\s*([a-zA-Z0-9_]+)\s*\}\}\}/', function ($m) use ($ctx) {
            return isset($ctx[$m[1]]) ? (string)$ctx[$m[1]] : '';
        }, $tpl) ?? $tpl;
    }

    /** @param array<string,mixed> $ctx */
    private function renderVars(string $tpl, array $ctx): string {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function ($m) use ($ctx) {
            $k = $m[1];
            if (!isset($ctx[$k])) return '';
            return htmlspecialchars((string)$ctx[$k], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }, $tpl) ?? $tpl;
    }

    /** @param array<string,mixed> $ctx */
    private function renderSections(string $tpl, array $ctx, bool $inverted): string {
        $tag = $inverted ? '\^' : '#';
        $re  = '/\{\{'.$tag.'\s*([a-zA-Z0-9_]+)\s*\}\}([\s\S]*?)\{\{\/\s*\1\s*\}\}/';

        return preg_replace_callback($re, function ($m) use ($ctx, $inverted) {
            $name = $m[1];
            $inner = $m[2];
            $val = $ctx[$name] ?? null;

            $truthy = false;
            if (is_array($val)) $truthy = count($val) > 0;
            elseif (is_bool($val)) $truthy = $val;
            elseif ($val !== null) $truthy = ((string)$val !== '');

            if ($inverted) {
                if ($truthy) return '';
                return $this->render($inner, $ctx);
            }

            if (!$truthy) return '';

            if (is_array($val)) {
                $out = '';
                foreach ($val as $item) {
                    if (is_array($item)) $out .= $this->render($inner, array_merge($ctx, $item));
                    else $out .= $this->render($inner, array_merge($ctx, ['.' => $item]));
                }
                return $out;
            }

            return $this->render($inner, $ctx);
        }, $tpl) ?? $tpl;
    }
}
