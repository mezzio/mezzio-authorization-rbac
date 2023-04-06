<?php

declare(strict_types=1);

namespace MezzioTest\Authorization\Rbac;

use Mezzio\Authorization\Exception;
use Mezzio\Authorization\Rbac\LaminasRbac;
use Mezzio\Authorization\Rbac\LaminasRbacAssertionInterface;
use Mezzio\Authorization\Rbac\LaminasRbacFactory;
use PHPUnit\Framework\TestCase;

class LaminasRbacFactoryTest extends TestCase
{
    private InMemoryContainer $container;

    protected function setUp(): void
    {
        $this->container = new InMemoryContainer();
    }

    public function testFactoryWithoutConfig(): void
    {
        $this->container->set('config', []);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage('mezzio-authorization-rbac');
        $factory($this->container);
    }

    public function testFactoryWithoutLaminasRbacConfig(): void
    {
        $this->container->set('config', ['mezzio-authorization-rbac' => []]);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage('mezzio-authorization-rbac.roles');
        $factory($this->container);
    }

    public function testFactoryWithoutPermissions(): void
    {
        $this->container->set('config', [
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
        $this->container->set('config', [
            'mezzio-authorization-rbac' => [
                'roles'       => [],
                'permissions' => [],
            ],
        ]);

        $factory     = new LaminasRbacFactory();
        $laminasRbac = $factory($this->container);
        self::assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testFactoryWithEmptyRolesPermissionsWithAssertion(): void
    {
        $assertion = $this->createMock(LaminasRbacAssertionInterface::class);
        $this->container->set('config', [
            'mezzio-authorization-rbac' => [
                'roles'       => [],
                'permissions' => [],
            ],
        ]);
        $this->container->set(LaminasRbacAssertionInterface::class, $assertion);

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

        $this->container->set('config', $config);

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
        $this->container->set('config', $config);
        $this->container->set(LaminasRbacAssertionInterface::class, $assertion);

        $factory     = new LaminasRbacFactory();
        $laminasRbac = $factory($this->container);
        self::assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testFactoryWithInvalidRole(): void
    {
        $this->container->set('config', [
            'mezzio-authorization-rbac' => [
                'roles'       => [
                    1 => [],
                ],
                'permissions' => [],
            ],
        ]);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container);
    }

    public function testFactoryWithUnknownRole(): void
    {
        $this->container->set('config', [
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

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container);
    }
}
