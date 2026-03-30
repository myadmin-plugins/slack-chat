---
name: phpunit-test
description: Writes PHPUnit tests for `src/Plugin.php` following the ReflectionClass-based pattern in `tests/PluginTest.php`. Use when user says 'add test', 'write test', 'test this method', or 'test coverage'. Covers method signature assertions (public/static/parameter count/type), static property assertions, hook registration validation, and behavior tests with anonymous-class stubs. Do NOT use for non-Plugin test subjects or for testing classes outside `src/Plugin.php`.
---
# phpunit-test

## Critical

- **All tests go in `tests/PluginTest.php`**, namespace `Detain\MyAdminSlack\Tests\`, class `PluginTest extends TestCase`.
- **Use tabs for indentation** — enforced by `.scrutinizer.yml`. Never use spaces.
- **Never mock `GenericEvent`** — instantiate it directly: `new GenericEvent($subject)`.
- **Never mock the loader/settings subjects** — use anonymous classes with the required stub methods.
- Every new public static method on `Plugin` needs a signature test AND is added to `testExpectedMethodsExist()`.
- Run `composer test` before and after changes to confirm no regressions.

## Instructions

1. **Read `src/Plugin.php`** to identify the method signature (public/static, parameter types, return type) you are testing. Verify the method exists before writing any test.

2. **Add a signature test** for any new public static method. Pattern:
 ```php
 public function testMyMethodSignature(): void
 {
 	$method = $this->reflection->getMethod('myMethod');
 	$this->assertTrue($method->isPublic());
 	$this->assertTrue($method->isStatic());
 	$this->assertSame(1, $method->getNumberOfRequiredParameters());
 	$param = $method->getParameters()[0];
 	$type = $param->getType();
 	$this->assertNotNull($type);
 	$this->assertSame(GenericEvent::class, $type->getName());
 }
 ```
 Verify the method name string matches exactly what is in `src/Plugin.php`.

3. **Add the method name to `testExpectedMethodsExist()`** in the `$expected` array.

4. **Add an entry to `eventHandlerProvider()`** if the new method is an event handler (accepts `GenericEvent`), so `testEventHandlersReturnVoidOrNothing` covers it automatically.

5. **Write a behavior test using an anonymous-class stub** when the method calls methods on `$event->getSubject()`. Use a by-reference `$recorded` array to capture calls:
 ```php
 public function testMyMethodDoesSomething(): void
 {
 	$recorded = [];
 	$stub = new class ($recorded) {
 		private array $recorded;
 		public function __construct(array &$recorded) { $this->recorded = &$recorded; }
 		public function someMethod(string $arg): void { $this->recorded[] = $arg; }
 	};
 	$event = new GenericEvent($stub);
 	Plugin::myMethod($event);
 	$this->assertNotEmpty($recorded);
 	$this->assertContains('expected_value', $recorded);
 }
 ```
 Verify the stub method names match exactly what `src/Plugin.php` calls on the subject.

6. **For static property tests**, assert existence, visibility, type, and value:
 ```php
 public function testStaticPropertyFoo(): void
 {
 	$this->assertTrue($this->reflection->hasProperty('foo'));
 	$prop = $this->reflection->getProperty('foo');
 	$this->assertTrue($prop->isStatic());
 	$this->assertTrue($prop->isPublic());
 	$this->assertSame('expected', Plugin::$foo);
 }
 ```

7. **Run tests**: `composer test` targeting `tests/PluginTest.php`. All tests must pass (no failures, no errors).

## Examples

**User says:** "Add a test for a new `getNotifications()` method I added to Plugin."

**Actions taken:**
1. Read `src/Plugin.php` — confirm `public static function getNotifications(GenericEvent $event)` exists.
2. Add to `testExpectedMethodsExist()`: `'getNotifications'` in `$expected`.
3. Add to `eventHandlerProvider()`: `'getNotifications' => ['getNotifications']`.
4. Write signature test:
 ```php
 public function testGetNotificationsSignature(): void
 {
 	$method = $this->reflection->getMethod('getNotifications');
 	$this->assertTrue($method->isPublic());
 	$this->assertTrue($method->isStatic());
 	$this->assertSame(1, $method->getNumberOfRequiredParameters());
 	$param = $method->getParameters()[0];
 	$type = $param->getType();
 	$this->assertNotNull($type);
 	$this->assertSame(GenericEvent::class, $type->getName());
 }
 ```
5. Write behavior test with anonymous stub if method calls subject methods.
6. Run `composer test` — all green.

**Result:** New method is covered for signature, presence, return type (via data provider), and behavior.

## Common Issues

- **`Method not found` / `ReflectionException`**: The method name string passed to `getMethod()` doesn't match `src/Plugin.php`. Check exact casing — PHP method names are case-insensitive but the string must match what is declared.
- **`assertSame(1, ...) failed, got 0`**: The method has no required parameters (they have defaults). Use `getNumberOfParameters()` instead of `getNumberOfRequiredParameters()`.
- **Anonymous stub causes `TypeError`**: The stub method signature (param types) must match what `src/Plugin.php` passes. Check the actual call in `src/Plugin.php` before writing the stub.
- **Test fails with `Undefined index: tf` in `getMenu()`**: `getMenu()` reads `$GLOBALS['tf']`. Set it before the call: `$GLOBALS['tf'] = (object)['ima' => '']`.
- **phpunit not found**: Run `composer install` first to populate dependencies.
- **Indentation errors in CI / Scrutinizer**: Use tabs, not spaces. Run `:set noexpandtab` in vim or configure your editor before editing `tests/PluginTest.php`.
