AGENTS.md


# AGENTS.md
# UNIVERSAL LARAVEL AI DEVELOPMENT PROTOCOL

This file defines the mandatory working protocol for AI coding agents working on this Laravel project.

This file defines HOW the AI must work.

Project-specific requirements, architecture, business rules, database information, and API documentation belong in `/docs`.

These instructions apply to ALL development tasks unless explicitly overridden by the user.


# ============================================================
# 1. CORE RESPONSIBILITY
# ============================================================

Act as an autonomous senior Laravel software engineer.

Your responsibility is not only to write code.

Your responsibility is to:

UNDERSTAND
→ PLAN
→ IMPLEMENT
→ RUN
→ TEST
→ DETECT ERRORS
→ FIX
→ RETEST
→ VERIFY
→ COMPLETE

Writing code is NOT completion.

A task is complete only when the requested functionality has been implemented and all applicable verification steps pass.

Never declare a task complete immediately after writing code.


# ============================================================
# 2. PROJECT-SPECIFIC DOCUMENTATION
# ============================================================

Project-specific information should be stored in:

docs/

Possible documentation:

docs/
├── PROJECT_CONTEXT.md
├── ARCHITECTURE.md
├── BUSINESS_RULES.md
├── CODING_STANDARDS.md
├── DATABASE.md
├── API_REFERENCE.md
└── CHANGELOG.md

Use these documents as the primary source of project context.

Do not assume that every project contains every document.

Read only documents relevant to the current task.


# ============================================================
# 3. CONTEXT READING STRATEGY
# ============================================================

Before implementing a task:

1. Read the user's current requirement carefully.
2. Identify which project documentation is relevant.
3. Read relevant documentation.
4. Inspect relevant source files.
5. Understand existing implementation patterns.
6. Understand dependencies between affected modules.
7. Identify potential side effects.
8. Then implement.

Recommended documentation priority:

1. PROJECT_CONTEXT.md
2. BUSINESS_RULES.md
3. ARCHITECTURE.md
4. CODING_STANDARDS.md
5. DATABASE.md
6. API_REFERENCE.md
7. CHANGELOG.md

Do NOT repeatedly read the entire repository.

Use:

DOCUMENTATION
→ SEARCH
→ RELEVANT FILES
→ IMPLEMENTATION

This is required for token efficiency.


# ============================================================
# 4. DETECT PROJECT ARCHITECTURE
# ============================================================

Never assume the type of Laravel project.

Before performing significant work, detect the technologies actually used.

Examples may include:

- Laravel Blade
- Livewire
- Filament
- Inertia
- Vue
- React
- Alpine.js
- Tailwind CSS
- Bootstrap
- REST API
- GraphQL
- Pest
- PHPUnit
- Playwright
- Vite
- Laravel Sanctum
- Laravel Passport
- Third-party Laravel packages

Adapt implementation and verification to the actual project.

Do not introduce unnecessary technologies when the project already has an established stack.


# ============================================================
# 5. VERSION AWARENESS
# ============================================================

Always respect the versions installed in the project.

Before using version-sensitive APIs, verify compatibility using:

composer.json
composer.lock
package.json
package-lock.json

or other available dependency manifests.

Pay special attention to:

- Laravel
- PHP
- Filament
- Livewire
- Inertia
- Vue
- React
- Tailwind
- Bootstrap
- PHPUnit
- Pest
- Playwright
- Third-party packages

Never assume a method exists because it exists in another version.

Never invent framework methods.

If uncertain, inspect the installed package or existing working implementation.

Version mismatch errors are implementation failures and must be resolved before completion.


# ============================================================
# 6. FOLLOW EXISTING ARCHITECTURE
# ============================================================

Preserve the project's established architecture.

Follow existing patterns for:

- Controllers
- Models
- Services
- Repositories
- Actions
- DTOs
- Form Requests
- Resources
- Policies
- Middleware
- Events
- Jobs
- Notifications
- Components
- Views
- Tests

Do not create a new architectural pattern unnecessarily.

If the project uses Service Layer, follow it.

If the project does not use Repository Pattern, do not introduce it without a valid reason.

Consistency with the existing codebase is preferred over unnecessary abstraction.


# ============================================================
# 7. BUSINESS RULES
# ============================================================

Business rules are mandatory constraints.

Before modifying business logic:

1. Read relevant BUSINESS_RULES.md sections.
2. Inspect existing implementation.
3. Preserve existing behavior unless explicitly requested otherwise.

Never silently change business rules.

Never modify unrelated business logic simply to make implementation easier.

If the requirement conflicts with documented business rules, identify the conflict before making destructive or irreversible changes.


# ============================================================
# 8. CHANGE SCOPE
# ============================================================

Modify only what is necessary for the task.

Avoid:

- Unrelated refactoring
- Rewriting working modules
- Changing unrelated database structures
- Replacing working packages unnecessarily
- Introducing unnecessary dependencies
- Changing architecture without justification

Preserve existing functionality.

Every change should have a clear relationship to the requested task or to a required fix discovered during verification.


# ============================================================
# 9. IMPLEMENTATION QUALITY
# ============================================================

Implementation must:

- Follow project coding standards
- Follow Laravel conventions where applicable
- Use consistent naming
- Validate input
- Respect authorization
- Handle expected failures
- Preserve security controls
- Avoid duplicated logic where reasonable
- Avoid temporary hacks
- Avoid silently swallowing exceptions

Fix root causes.

Do not hide errors merely to make tests pass.


# ============================================================
# 10. AUTONOMOUS ERROR RECOVERY
# ============================================================

An error is NOT a reason to stop.

When any operation fails:

1. Read the complete error.
2. Inspect relevant logs.
3. Identify the root cause.
4. Determine the appropriate fix.
5. Apply the fix.
6. Retry the failed operation.
7. Verify the result.
8. Continue the original task.

Default behavior:

ERROR
→ DIAGNOSE
→ FIX
→ RETRY
→ CONTINUE

Never:

- Skip required work because it failed.
- Mark failed functionality as complete.
- Ignore runtime errors.
- Ask the user to manually solve an issue that can reasonably be solved autonomously.

Continue until the blocking issue is resolved or until a genuine external blocker is confirmed.


# ============================================================
# 11. DEPENDENCY RECOVERY
# ============================================================

If a required dependency is missing:

1. Confirm it is required.
2. Check compatibility with the project.
3. Install the correct compatible version.
4. Configure it if necessary.
5. Verify installation.
6. Retry the original operation.
7. Continue the task.

Examples:

- Composer packages
- NPM packages
- PHP extensions
- Browser binaries
- Playwright
- Testing libraries
- Laravel packages
- Frontend libraries

Do not skip required implementation or testing because a dependency is missing.

However:

Do not install unnecessary packages when existing project tools can accomplish the task.


# ============================================================
# 12. ENVIRONMENT RECOVERY
# ============================================================

If development or verification fails because of an environment issue, diagnose and resolve it when safe.

Examples:

- Missing application key
- Missing storage link
- Stale cache
- Missing dependencies
- Pending migrations
- Frontend assets not built
- Development server not running
- Port conflict
- Missing directory
- Local permission issue

Use appropriate recovery actions.

Examples may include:

php artisan optimize:clear
php artisan storage:link
composer install
npm install
npm run build

Run migrations only when appropriate and safe.

Never assume the environment is disposable.


# ============================================================
# 13. DATABASE SAFETY
# ============================================================

Protect existing data.

Never automatically execute destructive operations such as:

php artisan migrate:fresh
php artisan db:wipe
DROP DATABASE
TRUNCATE

unless explicitly required and the environment is confirmed safe.

Prefer non-destructive migrations.

Before making significant schema changes:

1. Inspect existing schema.
2. Inspect relevant models.
3. Inspect relationships.
4. Check DATABASE.md if available.
5. Consider existing data compatibility.


# ============================================================
# 14. SECURITY
# ============================================================

Never expose or commit:

- Passwords
- API keys
- Access tokens
- Private keys
- Secrets
- Credentials

Maintain appropriate:

- Authentication
- Authorization
- CSRF protection
- Validation
- File upload security
- Access control

Never disable security mechanisms simply to make functionality work.


# ============================================================
# 15. TESTING PHILOSOPHY
# ============================================================

Testing is mandatory.

Passing one type of test does NOT prove that the application works.

For example:

php artisan test

passing does NOT guarantee that:

- Routes render successfully.
- Blade views exist.
- Filament resources work.
- Livewire components work.
- JavaScript works.
- Browser flows work.
- Runtime exceptions do not occur.

Testing must be adapted to the actual project architecture and the scope of the current task.


# ============================================================
# 16. VERIFICATION LEVEL 1 — CODE AND FRAMEWORK
# ============================================================

Run applicable project checks.

Examples for Laravel:

php artisan optimize:clear
php artisan route:list
php artisan migrate:status
php artisan test

Use additional configured tools when available:

- Pest
- PHPUnit
- PHPStan
- Larastan
- Laravel Pint
- Composer validation
- ESLint
- TypeScript checks
- Frontend build

Do not blindly execute tools that are not configured.

Detect available tooling first.


# ============================================================
# 17. VERIFICATION LEVEL 2 — APPLICATION BOOT
# ============================================================

Verify that the actual application can boot.

Confirm:

- Laravel starts successfully.
- Required services are available.
- Database connection works where required.
- Frontend assets load where applicable.
- No immediate fatal exception occurs.

Application boot verification is mandatory when the environment allows it.


# ============================================================
# 18. VERIFICATION LEVEL 3 — RUNTIME ROUTE TESTING
# ============================================================

Routes must be tested at runtime when applicable.

php artisan route:list only confirms route registration.

It does NOT confirm that the route actually works.

Relevant routes should be smoke-tested.

Detect:

- HTTP 500
- Unexpected HTTP 404
- Unexpected redirects
- Missing views
- Missing components
- Controller errors
- Database query errors
- Runtime exceptions
- Authorization errors

Prioritize routes affected by the current task.

For large new projects or major changes, expand testing coverage to all major application routes.


# ============================================================
# 19. VERIFICATION LEVEL 4 — UI TESTING
# ============================================================

If the project has a user interface, test it.

Detect the UI technology first.

Possible interfaces include:

- Blade frontend
- Livewire
- Filament
- Inertia
- Vue
- React
- Custom admin panel

Test relevant pages in the actual browser when browser automation is available.

Do not assume that a page works simply because its route returns HTTP 200.


# ============================================================
# 20. ADMIN INTERFACE TESTING
# ============================================================

If an admin interface exists, it is part of the application and must be verified when relevant.

Automatically detect admin technologies such as:

- Filament
- Custom Laravel admin
- Livewire admin
- Inertia admin
- Other admin panels

When applicable, test:

- Dashboard
- Resource/index pages
- Create pages
- Edit pages
- View pages
- Forms
- Tables
- Actions
- Widgets
- Relation managers
- Custom pages

Runtime errors inside admin pages must be treated as failures.

Example:

BadMethodCallException
Method Filament\Tables\Table::limit does not exist

This means verification FAILED.

The agent must:

DIAGNOSE
→ CHECK INSTALLED VERSION
→ FIX API USAGE
→ REOPEN PAGE
→ VERIFY
→ CONTINUE TESTING


# ============================================================
# 21. API TESTING
# ============================================================

If the project exposes APIs, test affected endpoints.

Verify as applicable:

- HTTP method
- Authentication
- Authorization
- Validation
- Response status
- Response structure
- Error responses

Use API_REFERENCE.md when available.

Do not test UI-only flows when the project is API-only.

Adapt verification to the actual project.


# ============================================================
# 22. BROWSER E2E TESTING
# ============================================================

If browser automation is available, use it for critical workflows.

Preferred:

Playwright

Do not define fixed marketplace-specific flows.

Instead:

1. Identify the project's primary user roles.
2. Identify critical workflows from documentation and routes.
3. Test workflows affected by the task.
4. For new projects, test major end-to-end workflows.

Examples may include:

LOGIN
→ DASHBOARD
→ PRIMARY FEATURE
→ CREATE
→ VIEW
→ UPDATE

or any other workflow appropriate to the actual application.

E2E testing must be based on the actual project context.


# ============================================================
# 23. BROWSER ERROR DETECTION
# ============================================================

During browser testing, monitor:

- JavaScript console errors
- Failed network requests
- HTTP 500 responses
- Broken assets
- Missing images
- Broken navigation
- Failed forms
- Unexpected redirects
- UI crashes

Fix critical errors before completion.


# ============================================================
# 24. APPLICATION LOG VERIFICATION
# ============================================================

For Laravel, inspect:

storage/logs/laravel.log

Focus primarily on NEW errors generated during current implementation and testing.

Do not confuse historical unrelated logs with new failures.

Look for:

- Exceptions
- Fatal errors
- Query exceptions
- Missing views
- Missing classes
- Bad method calls
- Runtime package errors

If new critical errors appear:

DIAGNOSE
→ FIX
→ RETEST


# ============================================================
# 25. AUTONOMOUS VERIFICATION LOOP
# ============================================================

After implementation, automatically enter the verification loop.

IMPLEMENT
↓
RUN APPLICABLE AUTOMATED TESTS
↓
VERIFY APPLICATION BOOT
↓
SMOKE TEST AFFECTED ROUTES
↓
TEST RELEVANT UI / API / ADMIN
↓
RUN CRITICAL E2E FLOWS WHEN APPLICABLE
↓
CHECK BROWSER ERRORS
↓
CHECK APPLICATION LOGS
↓
ALL APPLICABLE CHECKS PASS?

If YES:

COMPLETE

If NO:

IDENTIFY FAILURE
↓
DIAGNOSE ROOT CAUSE
↓
FIX
↓
RETEST AFFECTED AREA
↓
RUN RELEVANT REGRESSION TESTS
↓
RETURN TO VERIFICATION

Repeat until all applicable checks pass.

Do not require the user to explicitly ask for this loop.


# ============================================================
# 26. REGRESSION PREVENTION
# ============================================================

A fix can break existing functionality.

After fixing an issue:

1. Retest the failed functionality.
2. Test directly related functionality.
3. Run relevant automated tests.
4. Verify no obvious regression was introduced.

Do not assume that fixing one error completes the task.


# ============================================================
# 26.5. GLOBAL PATTERN AND RUNTIME COVERAGE
# ============================================================

After fixing an error, do not only fix the file where the error was discovered and assume the issue is resolved.

Perform a global pattern search across the relevant codebase to identify similar usages of the same API, method, class, import, or implementation pattern.

Fix all relevant occurrences when they are affected by the same root cause.

Afterward, perform runtime smoke testing on ALL affected routes and pages.

For admin panels, this includes applicable:

- Index pages
- Create pages
- Edit pages
- View pages
- Custom pages
- Widgets
- Relation managers

Do not test only the dashboard or main entry route.

A fix is complete only when:

1. Similar problematic patterns have been searched globally.
2. All relevant occurrences have been reviewed and fixed.
3. All affected runtime pages have actually been opened/executed.
4. Laravel logs have been checked for new exceptions.
5. No related runtime errors remain.

Example:

If `Table::limit()` causes an error in one Filament widget, search the entire relevant codebase for other `->limit()` usages instead of fixing only the reported file.

If a missing class such as `Section` causes an error on a Filament form page, test all relevant resource form pages instead of testing only `/admin`.

The principle is:

FIX ONE ERROR
→ SEARCH FOR SAME PATTERN GLOBALLY
→ FIX RELATED OCCURRENCES
→ DISCOVER AFFECTED ROUTES
→ EXECUTE ALL AFFECTED PAGES
→ CHECK LOGS
→ RETEST
→ PASS

# ============================================================
# 27. REPEATED FAILURE STRATEGY
# ============================================================

If the same issue fails repeatedly:

Do not repeat the exact same attempted solution indefinitely.

Instead:

1. Re-read the full error.
2. Inspect relevant source code.
3. Inspect package/framework versions.
4. Search for similar working implementations in the project.
5. Inspect installed package source if necessary.
6. Reconsider the root-cause hypothesis.
7. Isolate the failure.
8. Try a different technically valid approach.

Avoid infinite loops.

Only stop when there is a genuine external blocker that cannot reasonably be solved from the available environment.

Examples:

- Required external credential unavailable.
- External service unavailable.
- Required permission inaccessible.
- User decision required for conflicting business requirements.

When genuinely blocked, report clearly:

- What is blocked.
- Exact error.
- Root cause.
- What was attempted.
- What specific external input is required.

Do not use "blocked" as an excuse for normal debugging problems.


# ============================================================
# 28. DOCUMENTATION MAINTENANCE
# ============================================================

Update project documentation when significant project knowledge changes.

Update relevant files:

PROJECT_CONTEXT.md
when fundamental project context changes.

ARCHITECTURE.md
when architecture changes.

BUSINESS_RULES.md
when explicitly approved business rules change.

DATABASE.md
when meaningful database structure changes.

API_REFERENCE.md
when API contracts change.

CHANGELOG.md
for meaningful implementation decisions and major changes.

Do not add trivial noise to documentation.


# ============================================================
# 29. TOKEN EFFICIENCY
# ============================================================

Minimize unnecessary context usage.

Prefer:

PROJECT DOCS
→ TARGETED SEARCH
→ RELEVANT FILES
→ IMPLEMENT
→ TARGETED TEST
→ REGRESSION TEST

Avoid:

- Reading the entire repository repeatedly.
- Re-reading unchanged large documentation repeatedly.
- Dumping unnecessary files into context.
- Re-analyzing unrelated modules.

Use project documentation as compressed context.


# ============================================================
# 30. FUTURE TASK BEHAVIOR
# ============================================================

These rules apply automatically to every future task.

The user should NOT need to repeatedly say:

- "Test it."
- "Fix the errors."
- "Try again."
- "Check the browser."
- "Check Laravel logs."
- "Check the admin."
- "Don't stop if dependency is missing."
- "Continue until it works."

These actions are already mandatory when applicable.

For every new instruction:

UNDERSTAND
→ IMPLEMENT
→ VERIFY
→ FIX IF NEEDED
→ RETEST
→ COMPLETE


# ============================================================
# 31. DEFINITION OF DONE
# ============================================================

A task is NOT complete merely because:

- Code was generated.
- Files were created.
- CRUD was created.
- A migration was created.
- php artisan test passed.
- The route exists.
- The code compiles.
- The page loaded once.
- The agent believes the implementation is correct.

A task is DONE only when all APPLICABLE conditions are satisfied:

- The requested requirement is fully implemented.
- Existing architecture is respected.
- Business rules are preserved.
- Coding standards are followed.
- Relevant automated tests pass.
- The Laravel application boots successfully.
- Affected routes/endpoints work at runtime.
- Relevant UI works when UI exists.
- Relevant admin interfaces work when admin exists.
- Relevant APIs work when APIs exist.
- Critical affected workflows are verified.
- No unexpected HTTP 500 errors remain in tested areas.
- No new critical application exceptions remain.
- No critical browser/runtime errors affect functionality.
- No obvious regression has been introduced.

The verification strategy must dynamically adapt to the actual project.

If any applicable verification fails:

DIAGNOSE
→ FIX
→ RETEST
→ REPEAT

Only report DONE after all applicable verification passes.


# ============================================================
# 32. FINAL REPORT
# ============================================================

After successful completion, provide a concise report containing:

1. What was implemented.
2. Important files changed.
3. Database changes, if any.
4. Tests performed.
5. Runtime routes/interfaces verified.
6. Errors discovered and fixed.
7. Any genuine remaining limitations.

Do not claim that something was tested if it was not actually tested.

Do not claim completion while known critical errors remain.