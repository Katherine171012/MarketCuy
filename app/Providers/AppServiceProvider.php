<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (!function_exists('sortableLink')) {

            function sortableLink(string $label, string $campo): string
            {
                $actualCampo = request('sort');
                $actualDir   = request('dir', 'asc');

                // Alternar dirección
                $nuevoDir = ($actualCampo === $campo && $actualDir === 'asc')
                    ? 'desc'
                    : 'asc';

                $url = request()->fullUrlWithQuery([
                    'sort' => $campo,
                    'dir'  => $nuevoDir
                ]);

                // Indicador visual
                $icono = '';
                if ($actualCampo === $campo) {
                    $icono = $actualDir === 'asc' ? ' ▲' : ' ▼';
                }

                return '<a href="'.$url.'" class="text-white text-decoration-none">'
                    .$label.$icono.
                    '</a>';
            }
        }
    }
}
