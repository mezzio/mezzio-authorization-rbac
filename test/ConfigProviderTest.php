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

    protected function setUp() : void
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray()
    {
        $config = ($this->provider)();
        $this->assertIsArray($config);
        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config)
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertIsArray($config['dependencies']);
        $this->assertArrayHasKey('factories', $config['dependencies']);

        $factories = $config['dependencies']['factories'];
        $this->assertIsArray($factories);
        $this->assertArrayHasKey(LaminasRbac::class, $factories);
    }

    public function testServicesDefinedInConfigProvider()
    {
        $config = ($this->provider)();

        $json = json_decode(
            file_get_contents(__DIR__ . '/../composer.lock'),
            true
        );
        foreach ($json['packages'] as $package) {
            if (isset($package['extra']['laminas']['config-provider'])) {
                $configProvider = new $package['extra']['laminas']['config-provider']();
                $config = array_merge_recursive($config, $configProvider());
            }
        }

        $config['dependencies']['services']['config'] = [
            'mezzio-authorization-rbac' => ['roles' => [], 'permissions' => []],
        ];
        $container = $this->getContainer($config['dependencies']);

        $dependencies = $this->provider->getDependencies();
        foreach ($dependencies['factories'] as $name => $factory) {
            $this->assertTrue($container->has($name), sprintf('Container does not contain service %s', $name));
            $this->assertIsObject(
                $container->get($name),
                sprintf('Cannot get service %s from container using factory %s', $name, $factory)
            );
        }
    }

    private function getContainer(array $dependencies) : ServiceManager
    {
        return new ServiceManager($dependencies);
    }
}
