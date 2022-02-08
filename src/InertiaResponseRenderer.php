<?php declare(strict_types=1);

namespace Arcanist;

use Inertia\Inertia;
use Illuminate\Support\Str;
use Arcanist\Contracts\ResponseRenderer;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class InertiaResponseRenderer implements ResponseRenderer
{
    public function __construct(private string $componentBasePath)
    {
    }

    public function renderStep(
        WizardStep $step,
        AbstractWizard $wizard,
        array $data = []
    ): Response | Responsable | Renderable {
        $component = $this->componentBasePath . '/' . Str::studly($wizard::$slug) . '/' . Str::studly($step->slug);

        $viewData = [
            'arcanist' => array_filter([
                'wizard' => $wizard->summary(),
                'step' => $data,
            ])
        ];

        return Inertia::render($component, $viewData);
    }

    public function redirect(WizardStep $step, AbstractWizard $wizard): Response | Responsable | Renderable
    {
        if (!$wizard->exists()) {
            return redirect()->route('wizard.' . $wizard::$slug . '.create');
        }

        return redirect()->route(
            'wizard.' . $wizard::$slug . '.show',
            [$wizard->getId(), $step->slug]
        );
    }

    public function redirectWithError(
        WizardStep $step,
        AbstractWizard $wizard,
        ?string $error = null
    ): Response | Responsable | Renderable {
        return redirect()->route(
            'wizard.' . $wizard::$slug . '.show',
            [$wizard->getId(), $step->slug]
        )->withErrors(['wizard' => $error]);
    }
}
