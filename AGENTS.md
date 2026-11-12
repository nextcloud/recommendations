<!--
  - SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->
# AGENTS.md

This file provides guidance to AI coding assistants working with code in this repository.

## Commands

### Setup
```bash
composer i
npm ci
```

### JavaScript
See `package.json` scripts for all available commands (build, dev, watch, lint, stylelint, etc.).

### PHP
Available composer commands:
```bash
composer cs:check                # Check code style
composer cs:fix                  # Fix code style
composer psalm                   # Run static analysis
composer test:unit               # Run unit tests
composer openapi                 # Generate OpenAPI spec
```
See `composer.json` for all available commands.

## Architecture

### Stack
- **Backend**: PHP (see `appinfo/info.xml` for version requirements), Nextcloud app framework. Namespace: `OCA\Recommendations\`.
- **Frontend**: Vue 3, Pinia, bundled with Vite.

### Layering
Backend in `lib/` is layered: Controllers → Services → DB Mappers. Controllers stay thin; business logic lives in services. The Vue frontend lives in `src/`: HTTP calls in `service/`, state in the Pinia `store/`, UI in `components/`.

### Key Conventions
- **Registration**: `AppInfo/Application.php` registers event listeners, the dashboard widget and capabilities via the Nextcloud bootstrap API.
- **REUSE & SPDX**: Every file requires an SPDX license header. **New files must use `AGPL-3.0-or-later`, never `AGPL-3.0-only`**. Header format:
  ```php
  /**
   * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
   * SPDX-License-Identifier: AGPL-3.0-or-later
   */
  ```
- **OpenAPI**: `ResponseDefinitions.php` documents API types; run `composer openapi` to regenerate `openapi.json`.
- **Nextcloud API boundaries**: Use stable OCP interfaces, never another app's private `\OCA\OtherApp\*` API. Respect the server version range in `appinfo/info.xml`.

### Code comments

The diff shows *what* changed and *how*; a comment exists only to capture *why*.

- **Default to none.** Express intent through clear names and small functions.
- **Comment the *why*, never the *how*.** Legitimate reasons: a non-obvious workaround (link the issue), a subtle business rule.
- **No multi-line explanatory blocks, AI-style walkthroughs, or section banners.**

## Coding conventions

### Scope & commits
- **One concern per PR.** Keep unrelated edits, refactors, style churn and dependency bumps out of feature PRs.
- **The commit type must match the change**: moving code with no behavior change is `refactor:`, not `feat:`.
- **Reuse before reinventing.** Grep for an existing helper, source or pattern and mirror it.

### PHP backend
- **Types over PHPDoc.** Prefer native param/return/property types; keep `@throws` and PHPDoc that adds information. Use constructor property promotion and strict comparison (`===`, `in_array(..., true)`).
- **Exceptions.** Catch narrowly; when wrapping, pass the original as `$previous`. Never swallow errors.
- **Logging.** Inject `LoggerInterface` and log with `['exception' => $e]` context at the right level.
- **Dependency injection.** Inject collaborators via the constructor. Use `ITimeFactory` instead of `time()`. Don't inject `userId` into services; pass it as an argument so services stay usable from background jobs.
- **Controllers.** Keep them thin; use `#[NoAdminRequired]` for non-admin routes, `HTTP::STATUS_*` constants, and never act on a user-supplied id without checking ownership.
- **Queries.** Build every query with `IQueryBuilder` (`expr()`, `func()`, `createNamedParameter()`); no raw SQL. Scope by user and push filters/limits into the query.
- **Migrations.** Keep them re-runnable (check existence first) and portable across MySQL/MariaDB, PostgreSQL, SQLite and Oracle.

### Frontend (Vue / JS)
- `async`/`await` with `try`/`catch`, never mixed with `.then`; never `await` inside `forEach`.
- HTTP handling in `service/`, state mutations in the store, business logic out of components.
- Early returns over nested conditions; `const`/`let`, never `var`.
- Wrap every user-facing string, including aria-labels, in `t('recommendations', …)`; use `n()` for counts; never concatenate translated fragments.
- Use semantic elements and `@nextcloud/vue` components so required a11y attributes are injected.
- Use the standard Nextcloud CSS variables for colors, spacing and dimensions; no magic numbers.

## Testing

### Unit Tests
Located in `tests/Unit/` with structure mirroring `lib/`.

- Use **arrange-act-assert** structure with blank lines separating each phase (no literal comments)
- Mock dependencies via `$this->createMock(Interface::class)`; only mock external collaborators, never the class under test
- Declare typed fixture properties (`private Foo&MockObject $foo;`); test methods return `void`
- Cover the error and edge paths, not just the happy path; new classes and changed logic need tests

```bash
composer test:unit                                        # Run all unit tests
composer test:unit -- tests/Unit/Listeners/SomeTest.php   # Run specific test file
composer test:unit -- --filter="TestClassName"            # Run tests matching filter
```

## Git Workflow

Do NOT commit changes unless explicitly asked to do so.

Never fully automate the resolution of issues labeled [`good first issue`](https://github.com/issues?q=org%3Anextcloud+label%3A%22good+first+issue%22) or similar beginner-friendly labels.

After completing code changes:
1. Verify your work is complete and tests pass
2. Never push directly to `master` — always create a feature branch with a descriptive name
3. Make sure there is no trailing whitespace
4. Leave changes in the working directory or staged (do not commit)
5. Provide a summary of what was changed and why
6. Suggest a commit message using Conventional Commits format

### Commit Message Format

Commit messages capture **intent**: the *why*. The diff already shows the what.

- **Subject**: conventional-commit, imperative, under 60 characters.
- **Body**: state the problem and the reason for this approach, matter-of-fact. Record what was intentionally left out. Don't restate the diff.

All commits must include these trailers at the end:
1. Agent/model attribution: `Assisted-by: <AgentName>:<model-id>`
2. DCO sign-off: use `git commit -s`

Example:
```
fix(service): skip files the user can no longer read

Assisted-by: ClaudeCode:claude-sonnet-5-5
Signed-off-by: Name <email>
```

### GitHub comments and reviews

Agents may post GitHub text directly (PR and issue comments, review comments, PR descriptions), as long as it is written **for humans**: plain, concise, and skimmable.

- A human must review the text before it is posted.
- Disclose the AI assistance in every posted body, e.g. with an `Assisted by <harness>:<model-id>` footer.
- Fill out the repo's PR/issue template completely when opening a PR or issue.
