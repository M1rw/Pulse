<?php
/**
 * View Engine.
 * 
 * Custom template engine supporting layout inheritance,
 * variable injection, section replacement, and caching.
 */
namespace App\Core;

class ViewEngine
{
    use Singleton;

    private string $layoutPath;
    private string $viewPath;

    // compiled view cache
    private array $cache = [];

    // ── init ─────────────────────────────────────────────────────

    private function __construct()
    {
        $this->viewPath   = PULSE_VIEWS;
        $this->layoutPath = PULSE_VIEWS . '/layouts';
    }

    /** the main method everyone calls */
    public function render(string $template, array $data = []): string
    {
        return $this->compile($template, $data);
    }

    /** find and include a view file with isolated scope */
    private function compile(string $template, array $data): string
    {
        $file = $this->resolveView($template);

        if (!$file || !file_exists($file)) {
            throw new \RuntimeException("View [{$template}] not found at {$file}");
        }

        // extract data into scope
        extract($data, EXTR_SKIP);

        // start output buffer to capture the view
        ob_start();

        // $__content is what child views fill in
        $__content = '';
        $__sections = [];

        // include the view file
        include $file;

        $childContent = ob_get_clean();

        // if the view extends a layout, render the layout with the content
        if (isset($__layout) && $__layout) {
            $layoutFile = $this->layoutPath . '/' . $this->ensureExtension($__layout);
            if (file_exists($layoutFile)) {
                $layoutContent = $childContent;
                $__content = '';
                
                ob_start();
                // re-extract for layout scope
                extract($data, EXTR_SKIP);
                include $layoutFile;
                $layoutRendered = ob_get_clean();

                // If the layout used section content tag, ensure it is replaced
                if (str_contains($layoutRendered, '<!-- @section(content) -->')) {
                    $layoutRendered = str_replace('<!-- @section(content) -->', $layoutContent, $layoutRendered);
                } elseif (!str_contains($layoutRendered, $layoutContent)) {
                    // Fallback if layout didn't echo $layoutContent
                    $layoutRendered = str_replace('</main>', $layoutContent . '</main>', $layoutRendered);
                }

                $childContent = $layoutRendered;
            }
        }

        // process any additional sections
        foreach ($__sections as $sectionName => $sectionContent) {
            $childContent = str_replace(
                "<!-- @section({$sectionName}) -->",
                $sectionContent,
                $childContent
            );
        }

        // Clean any remaining unreplaced section tags
        $childContent = preg_replace('/<!-- @section\([^)]+\) -->/', '', $childContent);

        return $childContent;
    }

    /** find a view file, trying different extensions */
    private function resolveView(string $template): string
    {
        $normalized = str_replace('.', '/', $template);
        $file = $this->viewPath . '/' . $normalized;

        foreach (['.php', '.phtml', ''] as $ext) {
            if (file_exists($file . $ext) && !is_dir($file . $ext)) {
                return $file . $ext;
            }
        }

        return $file . '.php'; // let it fail naturally
    }

    private function ensureExtension(string $layout): string
    {
        if (!str_ends_with($layout, '.php') && !str_ends_with($layout, '.phtml')) {
            return $layout . '.php';
        }
        return $layout;
    }
}
