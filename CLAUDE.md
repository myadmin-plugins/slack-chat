# MyAdmin Slack Chat Plugin

Slack Chat Bot integration plugin for the MyAdmin control-panel framework. Event-driven hooks via Symfony EventDispatcher.

## Commands

```bash
composer install                        # install deps
vendor/bin/phpunit                      # run all tests
vendor/bin/phpunit tests/PluginTest.php # run single test file
```

## Architecture

- **Plugin class**: `src/Plugin.php` · namespace `Detain\MyAdminSlack\` · all methods are `public static`
- **Tests**: `tests/PluginTest.php` · namespace `Detain\MyAdminSlack\Tests\` · uses `ReflectionClass` for structural assertions
- **Autoload**: PSR-4 · `Detain\MyAdminSlack\` → `src/` · `Detain\MyAdminSlack\Tests\` → `tests/`
- **Events**: `Symfony\Component\EventDispatcher\GenericEvent` · subject retrieved via `$event->getSubject()`
- **Requirements**: `$loader->add_requirement('class.Name', '/path/to/file.php')` in `getRequirements()`
- **Menu ACL**: check `$GLOBALS['tf']->ima == 'admin'` and `has_acl('client_billing')` in `getMenu()`
- **CI/CD**: `.github/` contains workflows for automated testing and deployment pipelines
- **IDE config**: `.idea/` contains inspectionProfiles, deployment.xml, and encodings.xml for JetBrains IDE settings

## Plugin Pattern

Every handler method follows this signature:
```php
public static function getHooks(): array { return []; }
public static function getMenu(GenericEvent $event): void { $menu = $event->getSubject(); }
public static function getRequirements(GenericEvent $event): void { $loader = $event->getSubject(); }
public static function getSettings(GenericEvent $event): void { $settings = $event->getSubject(); }
```

Hook entries map event name strings to `[Plugin::class, 'methodName']`:
```php
public static function getHooks(): array {
    return [
        'get_slack_chat' => [self::class, 'getRequirements'],
    ];
}
```

## Conventions

- Indentation: **tabs** (enforced by `.scrutinizer.yml`)
- Properties: camelCase (`$name`, `$description`, `$help`, `$type`)
- Constants: UPPERCASE
- Static plugin properties: `public static $name`, `$description`, `$help`, `$type`
- PHP >= 7.4 · `ext-soap` required · Symfony EventDispatcher `^5.0|^6.0|^7.0`
- License: LGPL-2.1-only

## Testing Conventions

Tests in `tests/PluginTest.php` use `ReflectionClass` to assert structure:
```php
$this->reflection = new ReflectionClass(Plugin::class);
$method = $this->reflection->getMethod('getHooks');
$this->assertTrue($method->isPublic());
$this->assertTrue($method->isStatic());
```

- Every new public static method needs a signature test
- Every hook registered in `getHooks()` must reference an existing `Plugin` method
- Use anonymous classes with `add_requirement()` stubs to test `getRequirements()`

<!-- caliber:managed:pre-commit -->
## Before Committing

**IMPORTANT:** Before every git commit, you MUST ensure Caliber syncs agent configs with the latest code changes.

First, check if the pre-commit hook is already installed:
```bash
grep -q "caliber" .git/hooks/pre-commit 2>/dev/null && echo "hook-active" || echo "no-hook"
```

- If **hook-active**: the hook handles sync automatically — just commit normally. Tell the user: "Caliber will sync your agent configs automatically via the pre-commit hook."
- If **no-hook**: run Caliber manually before committing:
  1. Tell the user: "Caliber: Syncing agent configs with your latest changes..."
  2. Run: `caliber refresh && git add CLAUDE.md .claude/ .cursor/ .cursorrules .github/copilot-instructions.md .github/instructions/ AGENTS.md CALIBER_LEARNINGS.md 2>/dev/null`
  3. After it completes, briefly tell the user what Caliber updated. Then proceed with the commit.

If `caliber` is not found, tell the user: "This project uses Caliber for agent config sync. Run /setup-caliber to get set up."
<!-- /caliber:managed:pre-commit -->

<!-- caliber:managed:learnings -->
## Session Learnings

Read `CALIBER_LEARNINGS.md` for patterns and anti-patterns learned from previous sessions.
These are auto-extracted from real tool usage — treat them as project-specific rules.
<!-- /caliber:managed:learnings -->
