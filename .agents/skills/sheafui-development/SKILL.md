---
name: sheafui-development
description: "Use this skill for Sheaf UI development in Laravel applications. Trigger when working with <x-ui.*> Blade components, resources/views/components/ui/**, Sheaf CLI commands, sheaf.json/sheaf-lock.json, or when building and customizing Livewire/Blade/Alpine/Tailwind interfaces with Sheaf UI. Covers component discovery and installation, source-owned component customization, forms, modals, selects, layouts, icons, Livewire bindings, responsive design, accessibility, theming, and verification. Do not use Flux UI conventions or assume React/shadcn APIs."
license: MIT
metadata:
  author: project
---

# Sheaf UI Development

## Scope

Use this skill for UI work based on Sheaf UI in Laravel, Blade, Livewire, Alpine.js, and Tailwind CSS.

Sheaf UI is inspired by shadcn/ui's copy-and-own model, but its API is Laravel Blade based. Components are installed into the application and become project source code.

Never translate a shadcn/React example literally. Never use Flux tags such as `<flux:button>` in a Sheaf UI codebase.

## Documentation

For Sheaf UI component-specific behavior, prefer these sources in this order:

1. The component source already installed in `resources/views/components/ui/**`
2. Existing usages in the application
3. The installed Sheaf CLI and its help output
4. Official Sheaf UI documentation: `https://sheafui.dev/docs`
5. Laravel Boost `search-docs` for Laravel, Livewire, Alpine/Tailwind ecosystem behavior when applicable

Do not guess component names, props, slots, icon names, Alpine hooks, or JavaScript imports.

When framework or package behavior is version-sensitive, follow the project's Laravel Boost rules and confirm installed versions before relying on an API.

## Core Philosophy

Sheaf UI components are source-owned.

Installed components normally live under:

```text
resources/views/components/ui/
```

Treat these local files as the project's source of truth. A component may have been customized after installation.

Prefer, in order:

1. Reuse an existing Sheaf UI component already installed
2. Compose existing Sheaf UI components
3. Install a missing Sheaf UI component when appropriate
4. Extend/customize the local Sheaf component
5. Fall back to normal Blade + Tailwind when no suitable component exists

Do not recreate a component from scratch before checking whether the project already has an equivalent.

## Required Preflight

Before creating or editing UI files:

1. Follow the project's `AGENTS.md` / Laravel Boost rules.
2. If `.ai/rules` exists, read `.ai/rules/index.md`, then every matching rule file for the paths in scope.
3. Search `.ai/rules` for relevant terms when required by the project guidelines.
4. Confirm relevant installed versions rather than assuming them.
5. Inspect the current Sheaf UI setup and installed components.
6. Inspect sibling files and existing component usage before introducing a new pattern.

Useful checks:

```bash
composer show sheaf/cli
composer show livewire/livewire
php artisan list
php artisan sheaf:list
```

Also inspect when present:

```text
sheaf.json
sheaf-lock.json
resources/views/components/ui/
resources/css/app.css
resources/css/theme.css
resources/js/app.js
```

Do not change dependencies without approval.

## Sheaf CLI

The current Sheaf CLI package is installed with Composer and initialized once per project.

Initial setup, only when the project has not already been initialized:

```bash
composer require sheaf/cli
php artisan sheaf:init
```

Do not rerun `sheaf:init` casually on an existing project.

List available components:

```bash
php artisan sheaf:list
```

Install a component:

```bash
php artisan sheaf:install button
```

Install multiple components:

```bash
php artisan sheaf:install button input select textarea switch radio
```

Preview an install before touching files:

```bash
php artisan sheaf:install button --dry-run
```

Update a component:

```bash
php artisan sheaf:update button
```

Remove a component:

```bash
php artisan sheaf:remove button
```

### Critical Source-Ownership Rule

Because Sheaf UI copies component source into the project, an update or forced install can overwrite project customizations.

Never use `--force` or replace an existing component without first inspecting the local component and determining whether it has project-specific changes.

If command syntax differs in the installed version, use:

```bash
php artisan sheaf:install --help
php artisan sheaf:update --help
php artisan sheaf:remove --help
```

Use the installed CLI's behavior rather than relying on memory.

## Blade Component Conventions

Sheaf UI components use the `x-ui` Blade namespace.

### Button

```blade
<x-ui.button variant="primary">
    Save
</x-ui.button>
```

### Form Field

Prefer Sheaf's field composition when those components are installed:

```blade
<x-ui.field>
    <x-ui.label>Email</x-ui.label>

    <x-ui.input
        type="email"
        wire:model="email"
    />

    <x-ui.error name="email" />
</x-ui.field>
```

Keep validation on the Livewire/server side. Display validation feedback through the project's established Sheaf field/error pattern.

### Select

When the Sheaf select is installed, prefer it over a custom dropdown:

```blade
<x-ui.select
    wire:model="categoryId"
    placeholder="Choose a category..."
    searchable
    clearable
>
    @foreach ($categories as $category)
        <x-ui.select.option
            wire:key="category-{{ $category->id }}"
            value="{{ $category->id }}"
        >
            {{ $category->name }}
        </x-ui.select.option>
    @endforeach
</x-ui.select>
```

For dependent or dynamically re-rendered selects, use stable `wire:key` values on the select and options when necessary.

For large datasets, prefer server-side search rather than rendering an unbounded option list.

### Modal

Use the installed modal API instead of inventing a custom overlay:

```blade
<x-ui.modal id="edit-product" heading="Edit product">
    <div class="space-y-4">
        {{-- Form content --}}
    </div>
</x-ui.modal>
```

Confirm the installed modal's opening/closing API before wiring triggers, because local component behavior may differ from current documentation.

### Empty States

Prefer the Sheaf empty-state composition when installed:

```blade
<x-ui.empty>
    <x-ui.empty.media>
        <x-ui.icon name="inbox" class="size-10" />
    </x-ui.empty.media>

    <x-ui.empty.contents>
        <x-ui.heading>No products yet</x-ui.heading>
        <x-ui.text>Create the first product to get started.</x-ui.text>
    </x-ui.empty.contents>
</x-ui.empty>
```

## Livewire Integration

Keep application state server-side unless the interaction is purely client-side.

Use Livewire bindings intentionally:

- `wire:model` for normal state synchronization
- `wire:model.live` only when immediate server synchronization is actually needed
- `wire:click` for server actions
- stable `wire:key` values for loops and dynamic component trees
- server-side validation and authorization for mutations

Use Alpine.js for client-only interactions such as small disclosure states, visual toggles, and UI behavior that does not need a server round trip.

Avoid maintaining the same authoritative state independently in both Livewire and Alpine.

When a Sheaf component includes its own Alpine behavior, compose with it rather than replacing its internals unless customization is necessary.

## JavaScript-Backed Components

Some Sheaf components require generated JavaScript or CSS imports.

After installing an interactive component:

1. Inspect the installation output
2. Inspect its generated files
3. Check `resources/js/app.js`
4. Check `resources/css/app.css`
5. Follow the component documentation for required imports

Do not assume every Sheaf component is dependency-free.

Examples such as advanced selects and date pickers may require additional generated assets or primitives.

## Layouts and Navigation

Sheaf UI provides layout primitives for application shells, including layout, sidebar, navlist, header, and navbar components.

Before building a dashboard shell manually:

1. Check whether these components are already installed
2. Check existing application layouts
3. Reuse the existing navigation structure
4. Install missing layout components only when needed

Typical installation:

```bash
php artisan sheaf:install layout sidebar navlist navbar
```

Preserve existing route names, authorization rules, active-state conventions, responsive behavior, and sidebar collapse behavior.

## Icons

Use the installed Sheaf icon component when available:

```blade
<x-ui.icon name="academic-cap" />
```

Sheaf supports Heroicons and can optionally support Phosphor Icons.

For Phosphor Icons, use the configured Sheaf prefix:

```blade
<x-ui.icon name="ps:package" />
```

Do not assume Phosphor support is installed. Check the project configuration/dependencies first.

Do not invent icon names. Verify the icon exists in the configured provider.

Do not install another icon library merely because one icon is missing without approval.

## Tables and Data-Dense Interfaces

Do not assume a Sheaf data-table component is available locally.

For tables:

1. Check installed Sheaf components
2. Check whether the application already uses a table/datatable package
3. Reuse the established solution
4. Use plain semantic Blade tables only when appropriate

Do not recreate pagination, sorting, searching, selection, or bulk-action infrastructure in ad-hoc JavaScript when the project already has a server-driven solution.

If a Sheaf Pro component is referenced by documentation but is not installed/licensed in the project, do not pretend it is available.

## Styling and Theming

Use Tailwind CSS according to the project's installed version and conventions.

Prefer customization at the narrowest appropriate level:

1. Component props/variants
2. Classes passed to the component
3. Existing design tokens/theme variables
4. Local Sheaf component source
5. New custom CSS only when necessary

Because the project owns installed Sheaf components, reusable visual changes may belong in the local component implementation rather than repeated call-site classes.

Before changing a shared component, inspect all usages so the change does not unintentionally alter unrelated screens.

Preserve the project's established:

- color tokens
- border radii
- typography
- spacing scale
- dark-mode behavior
- focus styles
- responsive breakpoints

Avoid hard-coded inline styles unless there is a strong reason.

## Accessibility

Preserve or improve:

- semantic HTML
- `<label>` associations
- keyboard navigation
- visible focus states
- disabled states
- ARIA attributes when needed
- modal focus management
- meaningful button/link semantics

Do not replace an accessible Sheaf primitive with a visually similar but behaviorally weaker custom implementation.

## Responsive Design

Every new interface must be checked at mobile, tablet, and desktop widths.

Pay special attention to:

- modal width and overflow
- select/dropdown positioning
- table overflow
- sidebar collapse behavior
- action-button wrapping
- form grid collapse
- touch target size

Prefer responsive Tailwind utilities over JavaScript layout branching.

## Component Customization Rules

Since Sheaf UI is shadcn-like and source-owned, local customization is expected, but it must be deliberate.

When modifying `resources/views/components/ui/**`:

1. Read the entire component first
2. Search for all usages
3. Preserve its public props/slots unless a breaking change is intentional
4. Preserve Alpine/Livewire hooks
5. Preserve accessibility behavior
6. Keep variants consistent
7. Add or update tests when behavior changes

If a one-off screen needs a small style change, prefer call-site classes over changing the shared component.

If the same change is repeated across the application, consider adding a reusable variant to the local component.

## Testing and Verification

Every functional change must be programmatically tested according to the project guidelines.

For Livewire behavior, prefer feature/Livewire tests that cover the action or state transition.

Run the minimum relevant test set, for example:

```bash
php artisan test --compact --filter=Product
```

If PHP files were modified, run the project's required formatter:

```bash
vendor/bin/pint --dirty --format agent
```

If frontend assets or generated component assets changed, ensure the application builds:

```bash
npm run build
```

Also verify:

1. The component renders without Blade errors
2. Livewire interactions work
3. Validation states render correctly
4. Loading/disabled states behave correctly
5. Keyboard interaction works
6. Mobile layout is usable
7. Browser console has no relevant errors
8. Dark mode still works if the project supports it

Do not create throwaway verification scripts when existing tests can prove the behavior.

## Common Pitfalls

- Using `<flux:*>` tags in a Sheaf UI project
- Copying React/shadcn code instead of using Blade components
- Assuming a component exists without checking local files
- Guessing props, variants, slots, or icon names
- Running `sheaf:init` again on an initialized project
- Using `--force` and overwriting customized UI source
- Updating a Sheaf component without reviewing local modifications
- Installing dependencies without approval
- Forgetting required JS/CSS imports for interactive components
- Duplicating component Alpine state in page-level Alpine code
- Using unstable or missing `wire:key` values in dynamic lists
- Using `wire:model.live` everywhere without considering request cost
- Rebuilding an existing Sheaf primitive with custom HTML
- Assuming Pro-only functionality is available
- Changing a shared UI component without checking all usages

## Decision Rule

When implementing UI, ask:

> "Can this be expressed cleanly using the Sheaf components this project already owns?"

If yes, reuse and compose them.

If no, check the Sheaf component catalog and project conventions before creating a custom implementation.
