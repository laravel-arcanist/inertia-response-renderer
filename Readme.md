# Inertia Response Renderer

This package provides an Inertia.js response renderer for Arcanist.

> The real docs are still being written, but here’s a really quick
> introduction.

## Installation

Install the package through composer (you still need the main Arcanist package
installed).

```
composer require laravel-arcanist/inertia-response-renderer
```

Inside `config/arcanist.php`, 

1. Change the `renderers.renderer` key to `Arcanist\InertiaResponseRenderer::class`.

2. Add the following line `'inertia_framework' => '',` This can be either **vue** or **react**


That’s it.

## How it works

The response renderer will try and resolve step templates via the following
convention:

```
resources/js/Pages/Wizards/{wizard-slug}/{step-slug}.vue

resources/js/Pages/Wizards/{wizard-slug}/{step-slug}.js
```

You can configure the `Wizard` path prefix by changing the
`renderers.inertia.component_base_path` setting in the config.

## View Data

Arcanist passes a `step` and `wizard` prop to all views. These can be accessed
in the usual Inertia way.

```javascript
this.$page.props.arcanist.wizard

this.$page.props.arcanist.step
```

Please refer to the [main documentation](https://laravel-arcanist.com/getting-started#accessing-data-in-a-view) for a more detailed explanation of
these variables.

