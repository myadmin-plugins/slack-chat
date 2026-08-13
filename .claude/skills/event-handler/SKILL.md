---
name: event-handler
description: Implements a GenericEvent static handler method on Plugin in src/Plugin.php. Use when user says 'add method', 'implement handler', 'add getMenu logic', 'handle settings', or 'register hook'. Follows $event->getSubject() pattern with correct void return type and tab indentation. Do NOT use for non-event methods, new plugin creation, or standalone classes. NOTE: for a plugin's contract/behavioral tests (tests/ContractTest.php, the shared harness, composer myadmin:scaffold-tests) use the plugin-contract-tests skill instead — this skill's reflection-only guidance predates that harness.
---
<!-- myadmin-contract-harness-notice -->
> ### ⚠️ Read this before the rest of the file
>
> This package is on the **shared plugin contract harness**. Parts of the guidance below
> predate it and are now wrong in one specific way:
>
> **Any instruction here that a plugin's `getHooks()` / `getSettings()` / `getActivate()` /
> `getDeactivate()` / `getQueue()` must not be *called* — that only its existence, visibility
> or parameter count may be checked through `ReflectionClass` — no longer applies.** That rule
> existed because those methods reference bare constants (`PRORATE_BILLING` and friends) that
> only a live MyAdmin request defines, so calling them from a test used to fatal. The harness
> defines them first. It then executes the handlers for real, in a process of its own.
>
> A reflection-only assertion passes whether or not the thing works: `getActivate()` can exist,
> be public, be static, take one argument, and still fatal the moment it runs. Three real
> production bugs in this fleet were sitting behind assertions of exactly that shape.
>
> **Use the `plugin-contract-tests` skill** for anything touching `tests/ContractTest.php`,
> the contract inspectors, or `composer myadmin:scaffold-tests`.
>
> **Everything else in this file is still accurate and still applies** — this package's own
> classes, its API wrappers, its fixtures, its bootstrap, and the reasons certain classes must
> not be constructed. Nothing below has been removed.

# event-handler

## Critical

- **All handler methods must be `public static`** — never instance methods.
- **Parameter type must be `GenericEvent`** — import `use Symfony\Component\EventDispatcher\GenericEvent;` is already at the top of `src/Plugin.php`.
- **No return type or `void` only** — event handlers never return a value.
- **Indentation is tabs** (enforced by `.scrutinizer.yml`) — never spaces.
- **Register in `getHooks()`** — every new handler must have a corresponding entry, or the hook is dead code.
- **PHPDoc block required** — use `@param \Symfony\Component\EventDispatcher\GenericEvent $event` (fully qualified in the docblock).

## Instructions

1. **Open `src/Plugin.php`** and confirm `use Symfony\Component\EventDispatcher\GenericEvent;` is present at the top.
   Verify the file compiles: `vendor/bin/phpunit --dry-run` must produce no parse errors before proceeding.

2. **Add the handler method** inside the `Plugin` class after the last existing handler. Use this exact structure (tabs, not spaces):
   ```php
   /**
    * @param \Symfony\Component\EventDispatcher\GenericEvent $event
    */
   public static function getSettings(GenericEvent $event)
   {
   	/**
   	 * @var \MyAdmin\Settings $settings
   	 **/
   	$settings = $event->getSubject();
   }
   ```
   Replace `getSettings` / `$settings` / `\MyAdmin\Settings` with the new handler name and subject type.
   For `getMenu`, check `$GLOBALS['tf']->ima == 'admin'` and `has_acl('client_billing')` before acting.
   For `getRequirements`, call `$loader->add_requirement('key', '/path')` for each file needed.

3. **Register the hook in `getHooks()`** — add an entry mapping the event name string to `[self::class, 'yourMethodName']`:
   ```php
   public static function getHooks(): array
   {
   	return [
   		'system.settings' => [self::class, 'getSettings'],
   	];
   }
   ```
   Verify the method name string matches exactly the method added in Step 2.

4. **Add a signature test in `tests/PluginTest.php`** following the existing pattern:
   ```php
   public function testGetSettingsSignature(): void
   {
   	$method = $this->reflection->getMethod('getSettings');
   	$this->assertTrue($method->isPublic());
   	$this->assertTrue($method->isStatic());
   	$this->assertSame(1, $method->getNumberOfRequiredParameters());
   	$param = $method->getParameters()[0];
   	$type = $param->getType();
   	$this->assertNotNull($type);
   	$this->assertSame(GenericEvent::class, $type->getName());
   }
   ```
   Also add the method name to the `eventHandlerProvider()` array and to `testExpectedMethodsExist()`.

5. **Run tests**: `vendor/bin/phpunit tests/PluginTest.php` — all tests must pass before finishing.

## Examples

**User says:** "add a getSettings handler that reads the settings subject"

**Actions taken:**
1. Add to `src/Plugin.php` after `getRequirements()`:
   ```php
   /**
    * @param \Symfony\Component\EventDispatcher\GenericEvent $event
    */
   public static function getSettings(GenericEvent $event)
   {
   	/**
   	 * @var \MyAdmin\Settings $settings
   	 **/
   	$settings = $event->getSubject();
   }
   ```
2. Uncomment or add to `getHooks()`:
   ```php
   'system.settings' => [self::class, 'getSettings'],
   ```
3. Add `testGetSettingsSignature()` test and add `'getSettings'` to `eventHandlerProvider()` and `testExpectedMethodsExist()`.

**Result:** `vendor/bin/phpunit tests/PluginTest.php` — all tests green.

## Common Issues

- **"Method X referenced in hook Y must exist on Plugin"** (from `testGetHooksValuesAreCallableArrays`): the string in `getHooks()` doesn't match the actual method name — check for typos; both must be identical.
- **Test fails: expected 1 required parameter, got 0**: you forgot the `GenericEvent $event` parameter in the method signature.
- **CS error: spaces instead of tabs**: `.scrutinizer.yml` enforces tabs. Run `make php-cs-fixer` from the parent MyAdmin project, or manually replace leading spaces with tabs in `src/Plugin.php`.
- **`$type->getName()` returns wrong class**: the `use` import is missing or mis-spelled. Confirm `use Symfony\Component\EventDispatcher\GenericEvent;` is present in `src/Plugin.php` and the test file.
- **Handler is never called at runtime**: the event name key in `getHooks()` must match the string passed to `run_event()` exactly — check the caller site for the correct event name string.