<?php

declare(strict_types=1);

namespace MezzioTest\Authorization\Rbac;

use Mezzio\Authorization\Exception;
use Mezzio\Authorization\Rbac\LaminasRbac;
use Mezzio\Authorization\Rbac\LaminasRbacAssertionInterface;
use Mezzio\Authorization\Rbac\LaminasRbacFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class LaminasRbacFactoryTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testFactoryWithoutConfig(): void
    {
        $this->container->method('get')->with('config')->willReturn([]);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage('mezzio-authorization-rbac');
        $factory($this->container);
    }

    public function testFactoryWithoutLaminasRbacConfig(): void
    {
        $this->container->method('get')->with('config')->willReturn(['mezzio-authorization-rbac' => []]);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage('mezzio-authorization-rbac.roles');
        $factory($this->container);
    }

    public function testFactoryWithoutPermissions(): void
    {
        $this->container->method('get')->with('config')->willReturn([
            'mezzio-authorization-rbac' => [
                'roles' => [],
            ],
        ]);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage('mezzio-authorization-rbac.permissions');
        $factory($this->container);
    }

    public function testFactoryWithEmptyRolesPermissionsWithoutAssertion(): void
    {
        $this->container->method('get')->with('config')->willReturn([
            'mezzio-authorization-rbac' => [
                'roles'       => [],
                'permissions' => [],
            ],
        ]);

        $this->container->method('has')
            ->withConsecutive(
                [LaminasRbacAssertionInterface::class],
                ['Zend\Expressive\Authorization\Rbac\ZendRbacAssertionInterface']
            )->willReturn(false, false);

        $factory     = new LaminasRbacFactory();
        $laminasRbac = $factory($this->container);
        self::assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testFactoryWithEmptyRolesPermissionsWithAssertion(): void
    {
        $assertion = $this->createMock(LaminasRbacAssertionInterface::class);
        $config    = [
            'mezzio-authorization-rbac' => [
                'roles'       => [],
                'permissions' => [],
            ],
        ];

        $this->container->method('get')
            ->willReturnMap([
                ['config', $config],
                [LaminasRbacAssertionInterface::class, $assertion],
            ]);

        $this->container->method('has')->with(LaminasRbacAssertionInterface::class)->willReturn(true);

        $factory     = new LaminasRbacFactory();
        $laminasRbac = $factory($this->container);
        self::assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testFactoryWithoutAssertion(): void
    {
        $config = [
            'mezzio-authorization-rbac' => [
                'roles'       => [
                    'administrator' => [],
                    'editor'        => ['administrator'],
                    'contributor'   => ['editor'],
                ],
                'permissions' => [
                    'contributor'   => [
                        'admin.dashboard',
                        'admin.posts',
                    ],
                    'editor'        => [
                        'admin.publish',
                    ],
                    'administrator' => [
                        'admin.settings',
                    ],
                ],
            ],
        ];
        $this->container->method('get')->with('config')->willReturn($config);
        $this->container->method('has')
            ->withConsecutive(
                [LaminasRbacAssertionInterface::class],
                ['Zend\Expressive\Authorization\Rbac\ZendRbacAssertionInterface'],
            )->willReturn(false, false);

        $factory     = new LaminasRbacFactory();
        $laminasRbac = $factory($this->container);
        self::assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testFactoryWithAssertion(): void
    {
        $config    = [
            'mezzio-authorization-rbac' => [
                'roles'       => [
                    'administrator' => [],
                    'editor'        => ['administrator'],
                    'contributor'   => ['editor'],
                ],
                'permissions' => [
                    'contributor'   => [
                        'admin.dashboard',
                        'admin.posts',
                    ],
                    'editor'        => [
                        'admin.publish',
                    ],
                    'administrator' => [
                        'admin.settings',
                    ],
                ],
            ],
        ];
        $assertion = $this->createMock(LaminasRbacAssertionInterface::class);

        $this->container->method('has')->with(LaminasRbacAssertionInterface::class)->willReturn(true);

        $this->container->method('get')
            ->willReturnMap([
                ['config', $config],
                [LaminasRbacAssertionInterface::class, $assertion],
            ]);

        $factory     = new LaminasRbacFactory();
        $laminasRbac = $factory($this->container);
        self::assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testFactoryWithInvalidRole(): void
    {
        $this->container->method('get')->with('config')->willReturn([
            'mezzio-authorization-rbac' => [
                'roles'       => [
                    1 => [],
                ],
                'permissions' => [],
            ],
        ]);

        $this->container->method('has')
            ->withConsecutive([
                LaminasRbacAssertionInterface::class,
                'Zend\Expressive\Authorization\Rbac\ZendRbacAssertionInterface',
            ])->willReturn(false, false);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container);
    }

    public function testFactoryWithUnknownRole(): void
    {
        $this->container->method('get')->with('config')->willReturn([
            'mezzio-authorization-rbac' => [
                'roles'       => [
                    'administrator' => [],
                ],
                'permissions' => [
                    'contributor' => [
                        'admin.dashboard',
                        'admin.posts',
                    ],
                ],
            ],
        ]);
        $this->container->method('has')
            ->withConsecutive([
                LaminasRbacAssertionInterface::class,
                'Zend\Expressive\Authorization\Rbac\ZendRbacAssertionInterface',
            ])->willReturn(false, false);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container);
    }
}
