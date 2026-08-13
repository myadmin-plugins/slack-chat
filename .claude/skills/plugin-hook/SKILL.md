---
name: plugin-hook
description: Adds a new event hook to src/Plugin.php following the static method pattern. Use when user says 'add hook', 'register event', 'handle event', or 'add handler'. Adds entry to getHooks() and creates the corresponding static method. Do NOT use for modifying existing hooks or editing getRequirements/getMenu/getSettings directly. For a plugin's contract or behavioral tests (tests/ContractTest.php, the shared harness, composer myadmin:scaffold-tests) use the plugin-contract-tests skill instead — this skill's reflection-only guidance predates that harness.
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

# plugin-hook

## Critical

- ALL handler methods MUST be `public static` — never instance methods
- Method signature MUST be `public static function methodName(GenericEvent $event): void`
- Use tabs for indentation (enforced by `.scrutinizer.yml`) — never spaces
- The handler referenced in `getHooks()` MUST exist as a method on `Plugin` — the test `testGetHooksValuesAreCallableArrays` will fail otherwise
- Use `self::class` (not `__CLASS__` or a string) for the handler array's first element

## Instructions

1. **Identify the event name and handler method name.**
   - Event name: a dot-namespaced string, e.g. `'system.settings'` or `'get_slack_chat'`
   - Method name: camelCase, e.g. `handleSlackMessage`
   - Verify neither already exists in `src/Plugin.php` before proceeding.

2. **Add the hook entry to `getHooks()` in `src/Plugin.php`.**
   Inside the returned array, append:
   ```php
   'your.event.name' => [self::class, 'yourMethodName'],
   ```
   Existing pattern for reference (from `src/Plugin.php:31`):
   ```php
   public static function getHooks()
   {
       return [
           'get_slack_chat' => [self::class, 'getRequirements'],
       ];
   }
   ```
   Verify the array key is a string and the value is exactly `[self::class, 'methodName']`.

3. **Add the handler method to `src/Plugin.php`.**
   Place it after the last existing handler method. Use this exact structure:
   ```php
   /**
    * @param \Symfony\Component\EventDispatcher\GenericEvent $event
    */
   public static function yourMethodName(GenericEvent $event)
   {
       $subject = $event->getSubject();
       // handler logic here
   }
   ```
   - Retrieve the subject via `$event->getSubject()` — never access event data any other way
   - Do not declare a return type (existing handlers omit it; tests assert `void` or absent)

4. **Add a signature test to `tests/PluginTest.php`.**
   Add inside the `PluginTest` class, following the pattern at line 220–231:
   ```php
   public function testYourMethodNameSignature(): void
   {
       $method = $this->reflection->getMethod('yourMethodName');
       $this->assertTrue($method->isPublic());
       $this->assertTrue($method->isStatic());
       $this->assertSame(1, $method->getNumberOfRequiredParameters());

       $param = $method->getParameters()[0];
       $type  = $param->getType();
       $this->assertNotNull($type);
       $this->assertSame(GenericEvent::class, $type->getName());
   }
   ```
   Also add `'yourMethodName'` to the `$expected` array in `testExpectedMethodsExist()` (line 386) and to `eventHandlerProvider()` (line 455).

5. **Run the tests to verify.**
   ```bash
   vendor/bin/phpunit
   ```
   All tests must pass before the task is complete.

## Examples

**User says:** "Add a hook for the `slack.message` event that handles incoming messages"

**Actions taken:**

`src/Plugin.php` — `getHooks()` updated:
```php
public static function getHooks()
{
    return [
        'slack.message' => [self::class, 'handleMessage'],
    ];
}
```

`src/Plugin.php` — new method added:
```php
/**
 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
 */
public static function handleMessage(GenericEvent $event)
{
    $subject = $event->getSubject();
    // process incoming Slack message
}
```

`tests/PluginTest.php` — new test added:
```php
public function testHandleMessageSignature(): void
{
    $method = $this->reflection->getMethod('handleMessage');
    $this->assertTrue($method->isPublic());
    $this->assertTrue($method->isStatic());
    $this->assertSame(1, $method->getNumberOfRequiredParameters());
    $param = $method->getParameters()[0];
    $type  = $param->getType();
    $this->assertNotNull($type);
    $this->assertSame(GenericEvent::class, $type->getName());
}
```

**Result:** `vendor/bin/phpunit` passes all tests.

## Common Issues

- **`Method 'yourMethodName' referenced in hook 'event.name' must exist on Plugin`** — `testGetHooksValuesAreCallableArrays` fails because the method name in `getHooks()` doesn't match the actual method name. Fix: ensure the string in the handler array exactly matches the method name, including case.

- **`Expected exactly 1 required parameter`** — method was declared with no parameters or with a default. Fix: the signature must be `public static function name(GenericEvent $event)` with no default value on `$event`.

- **`Parse error: syntax error`** after editing `getHooks()`** — trailing comma missing or extra bracket. Fix: each hook entry ends with a comma; the closing `]` and `;` belong to the return statement.

- **Indentation style rejection by Scrutinizer** — spaces were used instead of tabs. Fix: replace leading spaces with tabs in any added lines (`sed -i 's/^    /\t/g'` or configure your editor for tab indentation).