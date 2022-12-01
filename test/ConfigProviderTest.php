<?php

declare(strict_types=1);

namespace MezzioTest\Authorization\Rbac;

use Laminas\ServiceManager\ServiceManager;
use Mezzio\Authorization\Rbac\ConfigProvider;
use Mezzio\Authorization\Rbac\LaminasRbac;
use PHPUnit\Framework\TestCase;

use function array_merge_recursive;
use function file_get_contents;
use function json_decode;
use function sprintf;

class ConfigProviderTest extends TestCase
{
    /** @var ConfigProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray(): array
    {
        $config = ($this->provider)();
        self::assertIsArray($config);
        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config): void
    {
        self::assertArrayHasKey('dependencies', $config);
        self::assertIsArray($config['dependencies']);
        self::assertArrayHasKey('factories', $config['dependencies']);

        $factories = $config['dependencies']['factories'];
        self::assertIsArray($factories);
        self::assertArrayHasKey(LaminasRbac::class, $factories);
    }

    public function testServicesDefinedInConfigProvider(): void
    {
        $config = ($this->provider)();

        $json = json_decode(
            file_get_contents(__DIR__ . '/../composer.lock'),
            true
        );
        self::assertIsArray($json);
        self::assertArrayHasKey('packages', $json);
        self::assertIsArray($json['packages']);
        foreach ($json['packages'] as $package) {
            self::assertIsArray($package);
            if (isset($package['extra']['laminas']['config-provider'])) {
                $configProvider = new $package['extra']['laminas']['config-provider']();
                $config         = array_merge_recursive($config, $configProvider());
            }
        }

        $config['dependencies']['services']['config'] = [
            'mezzio-authorization-rbac' => ['roles' => [], 'permissions' => []],
        ];
        $container                                    = $this->getContainer($config['dependencies']);

        $dependencies = $this->provider->getDependencies();
        foreach ($dependencies['factories'] as $name => $factory) {
            self::assertTrue($container->has($name), sprintf('Container does not contain service %s', $name));
            self::assertIsObject(
                $container->get($name),
                sprintf('Cannot get service %s from container using factory %s', $name, $factory)
            );
        }
    }

    private function getContainer(array $dependencies): ServiceManager
    {
        return new ServiceManager($dependencies);
    }
}
