<?php declare(strict_types=1);

namespace Arcanist\Tests;

use Generator;
use Mockery as m;
use Inertia\Inertia;
use Inertia\Response;
use Arcanist\WizardStep;
use Arcanist\AbstractWizard;
use Illuminate\Support\Facades\File;
use Illuminate\Testing\TestResponse;
use Arcanist\InertiaResponseRenderer;
use Illuminate\Support\Facades\Route;
use Arcanist\Contracts\ResponseRenderer;
use Arcanist\Tests\Fixtures\InertiaStep;
use Arcanist\Tests\Fixtures\InertiaWizard;
use Arcanist\Testing\ResponseRendererContractTests;
use Arcanist\Exception\StepTemplateNotFoundException;

class InertiaResponseRendererTest extends TestCase
{
    use ResponseRendererContractTests;

    private AbstractWizard $wizard;
    private WizardStep $step;

    protected function setUp(): void
    {
        parent::setUp();

        $this->wizard = m::mock(InertiaWizard::class);
        $this->wizard::$slug = 'inertia-wizard';
        $this->step = m::mock(InertiaStep::class);
        $this->step->slug = 'inertia-step';
    }

    /** @test */
    public function it_renders_the_correct_template_for_a_step(): void
    {
        File::shouldReceive('exists')->andReturnTrue();
        $this->wizard->allows('summary')->andReturns([]);

        Inertia::shouldReceive('render')
            ->once()
            ->with('Wizards/InertiaWizard/InertiaStep', m::any())
            ->andReturn($this->createMock(Response::class));

        $this->makeRenderer()->renderStep($this->step, $this->wizard);
    }

    /** @test */
    public function it_always_passes_the_wizard_summary_to_the_view(): void
    {
        File::shouldReceive('exists')->andReturnTrue();
        $this->wizard->allows('summary')->andReturns(['::summary::']);

        Inertia::shouldReceive('render')
            ->once()
            ->with(m::any(), [
                'arcanist' => [
                    'wizard' => ['::summary::'],
                ],
            ])
            ->andReturn($this->createMock(Response::class));

        $this->makeRenderer()->renderStep($this->step, $this->wizard);
    }

    /** @test */
    public function it_passes_the_view_data_to_the_view(): void
    {
        File::shouldReceive('exists')->andReturnTrue();
        $this->wizard->allows('summary')->andReturns(['::summary::']);

        Inertia::shouldReceive('render')
            ->once()
            ->with(m::any(), [
                'arcanist' => [
                    'wizard' => ['::summary::'],
                    'step' => ['::key::' => '::value::'],
                ],
            ])
            ->andReturn($this->createMock(Response::class));

        $this->makeRenderer()->renderStep($this->step, $this->wizard, ['::key::' => '::value::']);
    }

    /**
     * @test
     * @dataProvider redirectToStepProvider
     */
    public function it_redirects_to_a_steps_view(callable $callRenderer): void
    {
        Route::get('/wizard/inertia-wizard/{id}/{slug?}', fn () => 'ok')->name('wizard.inertia-wizard.show');
        $this->wizard->allows('exists')->andReturnTrue();
        $this->wizard->allows('getId')->andReturn('1');

        $response = new TestResponse($callRenderer($this->makeRenderer(), $this->wizard, $this->step));

        $response->assertRedirect(route('wizard.inertia-wizard.show', [
            'id' => '1',
            'slug' => 'inertia-step'
        ]));
    }

    /** @test */
    public function it_redirects_to_the_first_step_if_the_wizard_does_not_exist_yet(): void
    {
        Route::get('/wizard/inertia-wizard', fn () => 'ok')->name('wizard.inertia-wizard.create');

        $this->wizard->allows('exists')->andReturnFalse();
        $response = new TestResponse($this->makeRenderer()->redirect($this->step, $this->wizard));

        $response->assertRedirect(route('wizard.inertia-wizard.create'));
    }

    public function redirectToStepProvider(): Generator
    {
        yield from [
            'redirect' => [
                function (InertiaResponseRenderer $renderer, AbstractWizard $wizard, WizardStep $step) {
                    return $renderer->redirect($step, $wizard);
                },
            ],
            'redirectWithError' => [
                function (InertiaResponseRenderer $renderer, AbstractWizard $wizard, WizardStep $step) {
                    return $renderer->redirectWithError($step, $wizard);
                },
            ]
        ];
    }

    /** @test */
    public function it_redirects_with_an_error(): void
    {
        Route::get('/wizard/inertia-wizard/{id}/{slug?}', fn () => 'ok')->name('wizard.inertia-wizard.show');

        $this->wizard->allows('getId')->andReturn('1');

        $response = new TestResponse(
            $this->makeRenderer()->redirectWithError($this->step, $this->wizard, '::message::')
        );

        $response->assertSessionHasErrors('wizard');
    }

    /** @test */
    public function it_throws_an_exception_if_the_template_does_not_exist(): void
    {
        File::shouldReceive('exists')
            ->with(resource_path('js/Pages/Wizards/InertiaWizard/InertiaStep.vue'))
            ->andReturnFalse();

        $this->expectException(StepTemplateNotFoundException::class);
        $this->expectErrorMessage('No template found for step [inertia-step].');

        $this->makeRenderer()
            ->renderStep($this->step, $this->wizard, []);
    }

    protected function makeRenderer(): ResponseRenderer
    {
        return new InertiaResponseRenderer('Wizards');
    }
}
