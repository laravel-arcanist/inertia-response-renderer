<?php declare(strict_types=1);

namespace Arcanist\Tests\Fixtures;

use Arcanist\AbstractWizard;

class InertiaWizard extends AbstractWizard
{
    public static string $slug = 'inertia-wizard';

    protected array $steps = [
        InertiaStep::class,
    ];
}
