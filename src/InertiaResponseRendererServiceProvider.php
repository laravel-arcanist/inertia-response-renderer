<?php declare(strict_types=1);

namespace Arcanist;

use Illuminate\Support\ServiceProvider;

class InertiaResponseRendererServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(InertiaResponseRenderer::class, function () {
            return new InertiaResponseRenderer(
                config('arcanist.renderers.inertia.component_base_path', 'Wizards')
            );
        });
    }
}
