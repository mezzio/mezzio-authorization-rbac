<?php

/**
 * @see       https://github.com/mezzio/mezzio-authorization-rbac for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authorization-rbac/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authorization-rbac/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace MezzioTest\Authorization\Rbac;

use Mezzio\Authorization\Exception;
use Mezzio\Authorization\Rbac\LaminasRbac;
use Mezzio\Authorization\Rbac\LaminasRbacAssertionInterface;
use Mezzio\Authorization\Rbac\LaminasRbacFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class LaminasRbacFactoryTest extends TestCase
{
    /** @var ContainerInterface|ObjectProphecy */
    private $container;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryWithoutConfig()
    {
        $this->container->get('config')->willReturn([]);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryWithoutLaminasRbacConfig()
    {
        $this->container->get('config')->willReturn(['mezzio-authorization-rbac' => []]);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryWithoutPermissions()
    {
        $this->container->get('config')->willReturn([
            'mezzio-authorization-rbac' => [
                'roles' => []
            ]
        ]);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryWithEmptyRolesPermissionsWithoutAssertion()
    {
        $this->container->get('config')->willReturn([
            'mezzio-authorization-rbac' => [
                'roles' => [],
                'permissions' => []
            ]
        ]);
        $this->container->has(LaminasRbacAssertionInterface::class)->willReturn(false);
        $this->container->has(\Zend\Expressive\Authorization\Rbac\ZendRbacAssertionInterface::class)->willReturn(false);

        $factory = new LaminasRbacFactory();
        $laminasRbac = $factory($this->container->reveal());
        $this->assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testFactoryWithEmptyRolesPermissionsWithAssertion()
    {
        $this->container->get('config')->willReturn([
            'mezzio-authorization-rbac' => [
                'roles' => [],
                'permissions' => []
            ]
        ]);

        $assertion = $this->prophesize(LaminasRbacAssertionInterface::class);
        $this->container->has(LaminasRbacAssertionInterface::class)->willReturn(true);
        $this->container->get(LaminasRbacAssertionInterface::class)->willReturn($assertion->reveal());

        $factory = new LaminasRbacFactory();
        $laminasRbac = $factory($this->container->reveal());
        $this->assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testFactoryWithoutAssertion()
    {
        $this->container->get('config')->willReturn([
            'mezzio-authorization-rbac' => [
                'roles' => [
                    'administrator' => [],
                    'editor'        => ['administrator'],
                    'contributor'   => ['editor'],
                ],
                'permissions' => [
                    'contributor' => [
                        'admin.dashboard',
                        'admin.posts',
                    ],
                    'editor' => [
                        'admin.publish',
                    ],
                    'administrator' => [
                        'admin.settings',
                    ],
                ],
            ],
        ]);
        $this->container->has(LaminasRbacAssertionInterface::class)->willReturn(false);
        $this->container->has(\Zend\Expressive\Authorization\Rbac\ZendRbacAssertionInterface::class)->willReturn(false);

        $factory = new LaminasRbacFactory();
        $laminasRbac = $factory($this->container->reveal());
        $this->assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testFactoryWithAssertion()
    {
        $this->container->get('config')->willReturn([
            'mezzio-authorization-rbac' => [
                'roles' => [
                    'administrator' => [],
                    'editor'        => ['administrator'],
                    'contributor'   => ['editor'],
                ],
                'permissions' => [
                    'contributor' => [
                        'admin.dashboard',
                        'admin.posts',
                    ],
                    'editor' => [
                        'admin.publish',
                    ],
                    'administrator' => [
                        'admin.settings',
                    ],
                ],
            ],
        ]);
        $assertion = $this->prophesize(LaminasRbacAssertionInterface::class);
        $this->container->has(LaminasRbacAssertionInterface::class)->willReturn(true);
        $this->container->get(LaminasRbacAssertionInterface::class)->willReturn($assertion->reveal());

        $factory = new LaminasRbacFactory();
        $laminasRbac = $factory($this->container->reveal());
        $this->assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testFactoryWithInvalidRole()
    {
        $this->container->get('config')->willReturn([
            'mezzio-authorization-rbac' => [
                'roles' => [
                    1 => [],
                ],
                'permissions' => [],
            ],
        ]);
        $this->container->has(LaminasRbacAssertionInterface::class)->willReturn(false);
        $this->container->has(\Zend\Expressive\Authorization\Rbac\ZendRbacAssertionInterface::class)->willReturn(false);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container->reveal());
    }

    public function testFactoryWithUnknownRole()
    {
        $this->container->get('config')->willReturn([
            'mezzio-authorization-rbac' => [
                'roles' => [
                    'administrator' => [],
                ],
                'permissions' => [
                    'contributor' => [
                        'admin.dashboard',
                        'admin.posts',
                    ]
                ]
            ]
        ]);
        $this->container->has(LaminasRbacAssertionInterface::class)->willReturn(false);
        $this->container->has(\Zend\Expressive\Authorization\Rbac\ZendRbacAssertionInterface::class)->willReturn(false);

        $factory = new LaminasRbacFactory();

        $this->expectException(Exception\InvalidConfigException::class);
        $factory($this->container->reveal());
    }
}
