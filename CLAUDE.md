# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

SolidShift — a Symfony 7.3 / PHP 8.4 application for shift / schedule management (organisations, sites, positions, schedules, shifts, shift templates, users with site-scoped access). Exposes an API via API Platform 4 and a server-rendered UI built with Twig + Stimulus + Symfony UX Live Components, styled with Tabler/Bootstrap 5, bundled by Webpack Encore.

## Common commands

PHP / backend:
- Install deps: `composer install`
- Run app (Symfony CLI + FrankenPHP via Caddyfile): `symfony server:start` (or run via the included `compose.yaml` Postgres + `Caddyfile`)
- Console: `bin/console <command>` (e.g. `bin/console doctrine:migrations:migrate`)
- Create test DB / schema: `bin/console --env=test doctrine:database:create && bin/console --env=test doctrine:schema:create`
- Run tests: `vendor/bin/phpunit`
- Run a single test: `vendor/bin/phpunit --filter MyTest tests/Path/To/MyTest.php`
- Static analysis: `vendor/bin/phpstan analyse` (level max, Symfony container loaded from `var/cache/dev/...`, so run `bin/console cache:clear` first if container is stale)
- Coding standards: `vendor/bin/ecs check` / `vendor/bin/ecs check --fix`
- Rector (dry run is what CI runs): `vendor/bin/rector --dry-run`
- Composer normalize check: `composer normalize --dry-run --diff`

Frontend (uses bun, see `bun.lockb` and `.nvmrc`):
- Install: `bun install`
- Dev build (watch): `bun run watch`
- Dev server on :7002: `bun run dev-server`
- Production build: `bun run build`

CI (`.github/workflows/`) runs ECS, Rector dry-run, composer-normalize, super-linter, and PHPUnit on PHP 8.2 + 8.3 even though `composer.json` requires `php: >=8.4` — be aware of this skew; new syntax must still parse on older PHP for CI.

## Architecture

Standard Symfony MicroKernel layout (`src/Kernel.php`) with `App\` PSR-4 → `src/`, `App\Tests\` → `tests/`. Bundles registered in `config/bundles.php`; per-bundle config in `config/packages/*.yaml`. Services use autowire + autoconfigure with `App\:` resource `../src/` (see `config/services.yaml`).

Domain model (`src/Entity/`) is a multi-tenant shift-scheduling model. After the 2026 normalisation, **`Position` → `Role`**, **`Location` → `Area`**, and **`UserRole` → `MembershipRole`** (the per-site membership tier on `UserSiteAccess`, distinct from job-role `Role`s). `ScheduleShift` is gone — replaced by an `Occurrence` / `ShiftRequirement` / `Shift` triangle.

- `Organisation` owns `Site`s and the catalogue of `Role`s. Users get a tier (`MembershipRole` enum: admin / manager / user) on a site via `UserSiteAccess`, are invited via `UserInvite` (which can pre-assign roles), and can self-register via `Site.selfRegistrationToken`.
- `Site` owns `Area`s (hierarchical, parent/children tree). `Role` has `allowedAreas`.
- A user's **job-role** assignments at a site live in `UserSiteRoleAssignment` (user × site × role). A user's **managed areas** live in `UserAreaManagement` (user × area). Both have `cascade: persist` + `orphanRemoval: true` on the `User` side, so mutating the in-memory collections (`User::setRolesForSite()`, `User::setManagedAreasForSite()`) is enough — never write a manual diff/replace repository method.
- `Schedule` (with `RecurringOptions`) owns `OccurrenceTemplate`s. Each materialises into `Occurrence` rows on demand (via `OccurrenceRepository::findOrCreate()`). `OccurrenceTemplate` carries `ShiftRequirement` slots (role, optional area, optional time override, min/max qty). `Shift` is the concrete assignment: `(user, occurrence, requirement)` — created when a user is assigned. `ShiftTemplate` is a reusable blueprint that can seed `ShiftRequirement`.
- `User` is the security subject. Authentication entrypoint is `src/Security/AppAuthenticator.php`; authorization is enforced via voters in `src/Security/Voter/` (notably `UserSiteAccessVoter`, which `rector.php` exempts from `RemoveAlwaysTrueIfConditionRector`).

**Multi-tenancy:** every site-scoped query is gated by the `site` Doctrine SQL filter (`src/Doctrine/Filter/SiteFilter.php`), enabled per-request by `src/EventSubscriber/SiteRequestEventSubscriber.php`. Site-scoped routes use the custom `#[App\Attribute\Route(..., siteAware: true)]` attribute which prefixes `/s/{site}/` automatically.

API layer is API Platform 4: HTTP-exposed resources live in `src/ApiResource/`, with API-specific filters in `src/ApiResource/Filter/`, lifecycle listeners in `src/ApiResource/Listener/`, and signature/auth in `src/ApiResource/Signature/`. UI controllers live in `src/Controller/`. Live/twig components live in `src/Components/` and `templates/`. Custom Doctrine types/extensions live in `src/Doctrine/`. Domain enums in `src/Enum/`, repositories in `src/Repository/`, form types in `src/Form/`, messenger messages in `src/Message/` (Doctrine transport, see `config/packages/messenger.yaml`).

Frontend entry is `assets/app.js` + `assets/bootstrap.js` (loads Stimulus controllers from `assets/controllers/` plus auto-imports from `controllers.json` for Symfony UX packages). Live Components and UX Autocomplete are wired in.

The `solidworx/platform` Composer dep (`dev-main`, also referenced by `@solidworx/platform` from `vendor/solidworx/platform/assets`) is a shared internal package — changes there must be made in `vendor/solidworx/platform` and won't be picked up by `composer install` alone.

## Conventions

- PHP 8.4 features expected: typed class constants (`public const string APP_VERSION` in `Kernel.php`), property hooks, etc. Don't downgrade to older syntax.
- All PHP files use `declare(strict_types=1);` and a fixed MIT header comment (enforced by ECS `HeaderCommentFixer` — `ecs.php` is the source of truth, don't hand-edit headers).
- ECS sets: PSR-12, SPACES, DOCBLOCK, COMMENTS, PHPUNIT, NAMESPACES, CLEAN_CODE. Single quotes, `void` return where applicable, ordered imports (`const`, `class`, `function`).
- PHPStan runs at `level: max` with `treatPhpDocTypesAsCertain: false` and bleedingEdge enabled.
- PHPUnit is strict: deprecations, notices and warnings fail the suite; coverage metadata is required on every test (`requireCoverageMetadata="true"`). `DAMA\DoctrineTestBundle` wraps each test in a transaction — assume DB is rolled back between tests.
- Rector targets `PhpVersion::PHP_84` and imports short class names; certain entity files (`Site`, `User`, `UserSiteAccess`) are explicitly excluded from `AnnotationToAttributeRector` — keep their existing annotation/attribute style.

## Patterns to follow

- **Live Component actions**: always use the `live_action()` Twig function on elements you write yourself (e.g. `<button {{ live_action('save:prevent') }}>`, `<a {{ live_action('toggleRequirement:prevent', {requirementId: requirement.id}) }}>`). Modifiers like `prevent` / `stop` go after the action name with `:`. It emits the correct `data-action` + `data-live-action-param` + per-arg `data-live-*-param` attributes and handles kebab-casing of camelCase `LiveArg` names. Don't hand-roll `data-action="live#action..."` strings — they're easy to get wrong (the legacy `data-live-action-param="prevent|save"` form silently does a native form POST instead of the Live action). **Exception**: when binding a live action to a Symfony form, keep using `form_start(form, {attr: {'data-action': 'live#action:prevent', 'data-live-action-param': 'save'}})` — `form_start` builds its own `<form>` tag, so `live_action()` can't be injected into it; passing the attrs through the `attr` option is the canonical Symfony way.
- **Live Component props of entity-arrays**: Live can't auto-dehydrate `list<SomeEntity>`. Either add `#[LiveProp(hydrateWith: 'hydrateX', dehydrateWith: 'dehydrateX')]` with custom round-trip via the repository, or restructure to avoid the prop.
- **Form ↔ collection sync**: don't write manual `replaceForUserAndSite()`-style repository diff methods. Use Symfony's per-field `getter` + `setter` options on `EntityType` (mapped, with closures that delegate to entity methods like `User::setRolesForSite()`). Combined with `cascade: persist` + `orphanRemoval: true` on the `OneToMany`, Doctrine handles inserts and deletes on `flush()`.
- **Dependent form fields**: use `symfonycasts/dynamic-forms` (`DynamicFormBuilder::addDependent('child', 'parent', fn(DependentField $f, ?Type $value) => ...)`). Requires the form to live inside a Live Component for the dependent block to re-render without a full submit (see `src/Components/UserEditForm.php`).
- **Console commands**: prefix with `XDEBUG_MODE=off` to avoid the Xdebug overhead (`XDEBUG_MODE=off bin/console cache:clear`).
- **Frontend assets**: build with `bun run build` (not npm).
- **`solidworx/platform` shared package**: `vendor/solidworx/platform/` is editable in place — changes there need a `composer install` skip + local edit. The Kernel extends `PlatformBundle\Kernel`, and login flow goes through it (`config/packages/security.yaml` form_login + `LoginSuccessHandler`).
