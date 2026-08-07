<?php

declare(strict_types=1);

namespace Detain\MyAdminSlack\Tests;

use Detain\MyAdminSlack\Plugin;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Test suite for the Detain\MyAdminSlack\Plugin class.
 *
 * Validates class structure, static properties, hook registration,
 * event handler signatures, and runtime behavior of pure methods.
 *
 * @covers \Detain\MyAdminSlack\Plugin
 */
class PluginTest extends TestCase
{
    /**
     * Reflected mirror of the Plugin class, reused across structural tests.
     *
     * @var ReflectionClass<Plugin>
     */
    private ReflectionClass $reflection;

    /**
     * Set up a ReflectionClass instance for every test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(Plugin::class);
    }

    // ------------------------------------------------------------------
    //  Class structure
    // ------------------------------------------------------------------

    /**
     * The Plugin class must live in the expected namespace.
     *
     * @return void
     */
    public function testClassExistsInCorrectNamespace(): void
    {
        $this->assertTrue(class_exists(Plugin::class));
        $this->assertSame('Detain\\MyAdminSlack', $this->reflection->getNamespaceName());
    }

    /**
     * The class must be instantiable (not abstract, not an interface).
     *
     * @return void
     */
    public function testClassIsInstantiable(): void
    {
        $this->assertTrue($this->reflection->isInstantiable());
    }

    /**
     * The constructor must be public and accept zero required parameters.
     *
     * @return void
     */
    public function testConstructorIsPublicAndParameterless(): void
    {
        $constructor = $this->reflection->getConstructor();
        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPublic());
        $this->assertSame(0, $constructor->getNumberOfRequiredParameters());
    }

    /**
     * Creating a new Plugin instance must not throw.
     *
     * @return void
     */
    public function testCanBeInstantiated(): void
    {
        $plugin = new Plugin();
        $this->assertInstanceOf(Plugin::class, $plugin);
    }

    // ------------------------------------------------------------------
    //  Static properties
    // ------------------------------------------------------------------

    /**
     * The $name static property must exist and contain the expected string.
     *
     * @return void
     */
    public function testStaticPropertyName(): void
    {
        $this->assertTrue($this->reflection->hasProperty('name'));
        $prop = $this->reflection->getProperty('name');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertSame('Slack Plugin', Plugin::$name);
    }

    /**
     * The $description static property must exist and be a non-empty string.
     *
     * @return void
     */
    public function testStaticPropertyDescription(): void
    {
        $this->assertTrue($this->reflection->hasProperty('description'));
        $prop = $this->reflection->getProperty('description');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertIsString(Plugin::$description);
        $this->assertNotEmpty(Plugin::$description);
    }

    /**
     * The $help static property must exist and be a string.
     *
     * @return void
     */
    public function testStaticPropertyHelp(): void
    {
        $this->assertTrue($this->reflection->hasProperty('help'));
        $prop = $this->reflection->getProperty('help');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertIsString(Plugin::$help);
    }

    /**
     * The $type static property must be 'plugin'.
     *
     * @return void
     */
    public function testStaticPropertyType(): void
    {
        $this->assertTrue($this->reflection->hasProperty('type'));
        $prop = $this->reflection->getProperty('type');
        $this->assertTrue($prop->isStatic());
        $this->assertTrue($prop->isPublic());
        $this->assertSame('plugin', Plugin::$type);
    }

    // ------------------------------------------------------------------
    //  getHooks()
    // ------------------------------------------------------------------

    /**
     * getHooks() must be a public static method.
     *
     * @return void
     */
    public function testGetHooksIsPublicStatic(): void
    {
        $method = $this->reflection->getMethod('getHooks');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    /**
     * getHooks() must return an array.
     *
     * @return void
     */
    public function testGetHooksReturnsArray(): void
    {
        $hooks = Plugin::getHooks();
        $this->assertIsArray($hooks);
    }

    /**
     * Every value in getHooks() must be a callable-shaped array [class, method].
     * When hooks are registered, each must reference an existing Plugin method.
     *
     * @return void
     */
    public function testGetHooksValuesAreCallableArrays(): void
    {
        $hooks = Plugin::getHooks();
        // It is valid for hooks to be empty (all commented out); assert the type
        $this->assertIsArray($hooks);
        foreach ($hooks as $eventName => $handler) {
            $this->assertIsString($eventName, 'Hook keys must be event name strings.');
            $this->assertIsArray($handler, "Handler for '{$eventName}' must be an array.");
            $this->assertCount(2, $handler, "Handler for '{$eventName}' must have exactly 2 elements.");
            $this->assertSame(Plugin::class, $handler[0], "Handler class must be Plugin for '{$eventName}'.");
            $this->assertTrue(
                $this->reflection->hasMethod($handler[1]),
                "Method '{$handler[1]}' referenced in hook '{$eventName}' must exist on Plugin."
            );
        }
    }

    /**
     * getHooks() must accept zero parameters.
     *
     * @return void
     */
    public function testGetHooksRequiresNoParameters(): void
    {
        $method = $this->reflection->getMethod('getHooks');
        $this->assertSame(0, $method->getNumberOfRequiredParameters());
    }

    // ------------------------------------------------------------------
    //  Event-handler method signatures
    // ------------------------------------------------------------------

    /**
     * getMenu() must be public, static, accept exactly one GenericEvent parameter.
     *
     * @return void
     */
    public function testGetMenuSignature(): void
    {
        $method = $this->reflection->getMethod('getMenu');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $this->assertSame(1, $method->getNumberOfRequiredParameters());

        $param = $method->getParameters()[0];
        $type = $param->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * getRequirements() must be public, static, accept exactly one GenericEvent parameter.
     *
     * @return void
     */
    public function testGetRequirementsSignature(): void
    {
        $method = $this->reflection->getMethod('getRequirements');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $this->assertSame(1, $method->getNumberOfRequiredParameters());

        $param = $method->getParameters()[0];
        $type = $param->getType();
        $this->assertNotNull($type);
        $this->assertSame(GenericEvent::class, $type->getName());
    }

    /**
     * getSettings() must be public, static, accept exactly one GenericEvent parameter.
     *
     * @return void
     */
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

    // ------------------------------------------------------------------
    //  getRequirements() behaviour with a stub loader
    // ------------------------------------------------------------------

    /**
     * Every source registered via getRequirements() must resolve to a file that
     * exists on disk, so that function_requirements() can actually require it.
     *
     * This replaces testGetRequirementsAddsRequirements() and
     * testGetRequirementsPathsAreNonEmptyStrings(), which only ever inspected the
     * registration table (the names registered, and that the paths were non-empty
     * strings) and never the filesystem. They were green for years while every
     * registration in this package pointed at a file that has never existed
     * (src/Slack.php and src/abuse.inc.php), so they locked the bug in place.
     *
     * The registration table is now empty, so the loop body never runs and this test
     * passes vacuously - which is correct: a package that registers nothing cannot
     * register anything broken. It bites the moment a requirement is added back.
     *
     * Uses an anonymous class as a lightweight stub to avoid mocking vendor classes.
     *
     * @return void
     */
    public function testGetRequirementsRegistersOnlyExistingFiles(): void
    {
        $recorded = [];

        $loader = new class ($recorded) {
            /** @var array<int, array{0: string, 1: string}> */
            private array $recorded;

            /**
             * @param array<int, array{0: string, 1: string}> $recorded
             */
            public function __construct(array &$recorded)
            {
                $this->recorded = &$recorded;
            }

            /**
             * @param string $name
             * @param string $path
             * @return void
             */
            public function add_requirement(string $name, string $path): void
            {
                $this->recorded[] = [$name, $path];
            }
        };

        $event = new GenericEvent($loader);
        Plugin::getRequirements($event);

        $packageRoot = dirname(__DIR__);
        $marker = '/vendor/detain/myadmin-slack-chat/';
        $missing = [];

        foreach ($recorded as [$name, $source]) {
            $candidates = [];
            // How MyAdmin resolves it: INCLUDE_ROOT . '/' . $source, with INCLUDE_ROOT
            // being <project>/include when this package sits in <project>/vendor.
            $candidates[] = dirname($packageRoot, 3) . '/include/' . ltrim($source, '/');
            // Same target, resolved without needing an installed parent project, so the
            // suite is meaningful when this repo is checked out standalone.
            $offset = strpos($source, $marker);
            if ($offset !== false) {
                $candidates[] = $packageRoot . '/' . substr($source, $offset + strlen($marker));
            }
            $candidates[] = $packageRoot . '/' . ltrim($source, '/');

            if (array_filter($candidates, 'is_file') === []) {
                $missing[] = "{$name} => {$source}";
            }
        }

        $this->assertSame(
            [],
            $missing,
            'Every requirement registered by getRequirements() must name a source file that exists on disk; '
            . 'function_requirements() would fatal on these.'
        );
    }

    // ------------------------------------------------------------------
    //  getSettings() behaviour with a stub settings object
    // ------------------------------------------------------------------

    /**
     * getSettings() must retrieve the subject from the event without error.
     *
     * @return void
     */
    public function testGetSettingsRetrievesSubject(): void
    {
        $settings = new class {
        };
        $event = new GenericEvent($settings);

        // Should not throw
        Plugin::getSettings($event);

        // The subject must remain the same object reference
        $this->assertSame($settings, $event->getSubject());
    }

    // ------------------------------------------------------------------
    //  Static analysis: expected methods & properties exist
    // ------------------------------------------------------------------

    /**
     * All publicly documented methods must exist on the class.
     *
     * @return void
     */
    public function testExpectedMethodsExist(): void
    {
        $expected = ['getHooks', 'getMenu', 'getRequirements', 'getSettings'];
        foreach ($expected as $methodName) {
            $this->assertTrue(
                $this->reflection->hasMethod($methodName),
                "Plugin must declare method {$methodName}()."
            );
        }
    }

    /**
     * All expected static properties must exist on the class.
     *
     * @return void
     */
    public function testExpectedStaticPropertiesExist(): void
    {
        $expected = ['name', 'description', 'help', 'type'];
        foreach ($expected as $propName) {
            $this->assertTrue(
                $this->reflection->hasProperty($propName),
                "Plugin must declare static property \${$propName}."
            );
        }
    }

    /**
     * All static properties must be of type string.
     *
     * @return void
     */
    public function testAllStaticPropertiesAreStrings(): void
    {
        $props = ['name', 'description', 'help', 'type'];
        foreach ($props as $propName) {
            $value = $this->reflection->getProperty($propName)->getValue();
            $this->assertIsString($value, "Static property \${$propName} must be a string.");
        }
    }

    // ------------------------------------------------------------------
    //  Event handler return-type analysis
    // ------------------------------------------------------------------

    /**
     * All event handlers must have void or no declared return type.
     *
     * @dataProvider eventHandlerProvider
     *
     * @param string $methodName
     * @return void
     */
    public function testEventHandlersReturnVoidOrNothing(string $methodName): void
    {
        $method = $this->reflection->getMethod($methodName);
        $returnType = $method->getReturnType();

        if ($returnType !== null) {
            $this->assertSame('void', $returnType->getName());
        } else {
            // No return type declared is acceptable for event handlers
            $this->assertNull($returnType);
        }
    }

    /**
     * Data provider listing all event handler method names.
     *
     * @return array<string, array{0: string}>
     */
    public function eventHandlerProvider(): array
    {
        return [
            'getMenu' => ['getMenu'],
            'getRequirements' => ['getRequirements'],
            'getSettings' => ['getSettings'],
        ];
    }
}
