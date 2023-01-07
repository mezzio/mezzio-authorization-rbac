<?php

declare(strict_types=1);

namespace MezzioTest\Authorization\Rbac;

use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\ServiceManager;
use Mezzio\Authorization\Rbac\ConfigProvider;
use Mezzio\Authorization\Rbac\LaminasRbac;
use PHPUnit\Framework\TestCase;

use function array_merge_recursive;
use function file_get_contents;
use function json_decode;
use function sprintf;

/** @psalm-import-type ServiceManagerConfigurationType from ConfigInterface */
class ConfigProviderTest extends TestCase
{
    /** @var ConfigProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = new ConfigProvider();
    }

    /** @return array{dependencies: ServiceManagerConfigurationType} */
    public function testInvocationReturnsArray(): array
    {
        $config = ($this->provider)();
        /** @psalm-suppress RedundantCondition */
        self::assertIsArray($config);
        return $config;
    }

    /**
     * @param array{dependencies: ServiceManagerConfigurationType} $config
     * @depends testInvocationReturnsArray
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    public function testReturnedArrayContainsDependencies(array $config): void
    {
        self::assertArrayHasKey('dependencies', $config);
        self::assertIsArray($config['dependencies']);
        self::assertArrayHasKey('factories', $config['dependencies']);

        $factories = $config['dependencies']['factories'] ?? null;
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
        $factories    = $dependencies['factories'] ?? null;
        self::assertIsArray($factories);
        foreach ($factories as $name => $factory) {
            self::assertIsString($factory);
            self::assertTrue($container->has($name), sprintf('Container does not contain service %s', $name));
            self::assertIsObject(
                $container->get($name),
                sprintf('Cannot get service %s from container using factory %s', $name, $factory)
            );
        }
    }

    /** @param ServiceManagerConfigurationType $dependencies */
    private function getContainer(array $dependencies): ServiceManager
    {
        return new ServiceManager($dependencies);
    }
}
