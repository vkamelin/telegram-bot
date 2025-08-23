<?php
declare(strict_types=1);

namespace App\Helpers;

use Psr\Http\Message\ResponseInterface as Res;

/**
 * Minimalistic view renderer for templates.
 */
final class View
{
    /**
     * Renders template with optional layout.
     *
     * @param Res    $res      HTTP response to write into
     * @param string $template Template path relative to templates/
     * @param array  $params   Variables for template
     * @param string|null $layout Layout path relative to templates/
     */
    public static function render(Res $res, string $template, array $params = [], ?string $layout = null): Res
    {
        $basePath    = dirname(__DIR__, 2) . '/templates/';

        $csrfName  = env('CSRF_TOKEN_NAME', '_csrf_token');
        $csrfToken = $_COOKIE[$csrfName] ?? bin2hex(random_bytes(16));
        if (!isset($_COOKIE[$csrfName])) {
            setcookie($csrfName, $csrfToken, ['path' => '/']);
        }

        $currentPath = strtok($_SERVER['REQUEST_URI'] ?? '/', '?') ?: '/';
        $menu        = require $basePath . 'menu.php';
        foreach ($menu as &$item) {
            $url = rtrim($item['url'], '/');
            if ($url === '/dashboard') {
                $item['class'] = $currentPath === '/dashboard' ? 'active' : '';
                continue;
            }
            $item['class'] = ($currentPath === $url || str_starts_with($currentPath, $url . '/')) ? 'active' : '';
        }
        unset($item);

        $vars = array_merge([
            'csrfToken'   => $csrfToken,
            'currentPath' => $currentPath,
            'menu'        => $menu,
        ], $params);

        $templatePath = $basePath . ltrim($template, '/');
        ob_start();
        extract($vars, EXTR_SKIP);
        require $templatePath;
        $content = ob_get_clean();

        if ($layout !== null) {
            $layoutPath = $basePath . ltrim($layout, '/');
            ob_start();
            $title = $vars['title'] ?? '';
            require $layoutPath;
            $content = ob_get_clean();
        }

        $res->getBody()->write($content);
        return $res->withHeader('Content-Type', 'text/html');
    }
}

