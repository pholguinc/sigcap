<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BlockUploadAssets
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $blacklistedExtensions = [
            'php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'pht', 'phps',
            'asp', 'aspx', 'jsp', 'cfm', 'pl', 'py', 'sh', 'bash',
            'exe', 'bat', 'cmd', 'htaccess', 'config', 'html', 'htm', 'js'
        ];

        $blacklistedMimeTypes = [
            'text/html',
            'text/javascript',
            'application/x-javascript',
            'application/x-php',
            'application/x-httpd-php',
            'application/x-httpd-php-source',
            'application/x-msdownload', // .exe
        ];

        foreach ($request->allFiles() as $key => $file) {
            if ($file instanceof UploadedFile) {
                $extension = strtolower($file->getClientOriginalExtension());
                $mimeType = strtolower($file->getMimeType());

                // If extension is empty, try to guess from client name
                if (empty($extension)) {
                    $parts = explode('.', $file->getClientOriginalName());
                    $extension = strtolower(end($parts));
                }

                // Check extension
                if (in_array($extension, $blacklistedExtensions)) {
                    return response()->json([
                        'error' => 'El tipo de archivo cargado no está permitido por razones de seguridad.'
                    ], 400);
                }

                // Check MIME type (prevent mime spoofing for php/html/javascript)
                foreach ($blacklistedMimeTypes as $blacklistedMime) {
                    if (str_contains($mimeType, $blacklistedMime)) {
                        return response()->json([
                            'error' => 'El contenido del archivo no es seguro para cargar.'
                        ], 400);
                    }
                }
                
                // Additional protection against path traversal in filenames
                $originalName = $file->getClientOriginalName();
                if (str_contains($originalName, '..') || str_contains($originalName, '/') || str_contains($originalName, '\\')) {
                    return response()->json([
                        'error' => 'Nombre de archivo no válido.'
                    ], 400);
                }
            }
        }

        return $next($request);
    }
}
