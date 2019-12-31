<?php

/**
 * @see       https://github.com/mezzio/mezzio-authorization-rbac for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authorization-rbac/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authorization-rbac/blob/master/LICENSE.md New BSD License
 */

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

    protected function setUp()
    {
        $this->provider = new ConfigProvider();
    }

    public function testInvocationReturnsArray()
    {
        $config = ($this->provider)();
        $this->assertInternalType('array', $config);
        return $config;
    }

    /**
     * @depends testInvocationReturnsArray
     */
    public function testReturnedArrayContainsDependencies(array $config)
    {
        $this->assertArrayHasKey('dependencies', $config);
        $this->assertInternalType('array', $config['dependencies']);
        $this->assertArrayHasKey('factories', $config['dependencies']);

        $factories = $config['dependencies']['factories'];
        $this->assertInternalType('array', $factories);
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
            'authorization' => ['roles' => [], 'permissions' => []],
        ];
        $container = $this->getContainer($config['dependencies']);

        $dependencies = $this->provider->getDependencies();
        foreach ($dependencies['factories'] as $name => $factory) {
            $this->assertTrue($container->has($name), sprintf('Container does not contain service %s', $name));
            $this->assertInternalType(
                'object',
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
