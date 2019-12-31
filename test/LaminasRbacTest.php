<?php

/**
 * @see       https://github.com/mezzio/mezzio-authorization-rbac for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authorization-rbac/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authorization-rbac/blob/master/LICENSE.md New BSD License
 */

namespace MezzioTest\Authorization\Rbac;

use Laminas\Permissions\Rbac\Rbac;
use Mezzio\Authorization\Rbac\LaminasRbac;
use Mezzio\Authorization\Rbac\LaminasRbacAssertionInterface;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class LaminasRbacTest extends TestCase
{
    protected function setUp()
    {
        $this->rbac = $this->prophesize(Rbac::class);
        $this->assertion = $this->prophesize(LaminasRbacAssertionInterface::class);
    }

    public function testConstructorWithoutAssertion()
    {
        $laminasRbac = new LaminasRbac($this->rbac->reveal());
        $this->assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testConstructorWithAssertion()
    {
        $laminasRbac = new LaminasRbac($this->rbac->reveal(), $this->assertion->reveal());
        $this->assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    /**
     * @expectedException Mezzio\Authorization\Rbac\Exception\RuntimeException
     */
    public function testIsGrantedWithoutRouteResult()
    {
        $laminasRbac = new LaminasRbac($this->rbac->reveal(), $this->assertion->reveal());

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn(false);

        $laminasRbac->isGranted('foo', $request->reveal());
    }

    public function testIsGrantedWithoutAssertion()
    {
        $this->rbac->isGranted('foo', 'home', null)->willReturn(true);
        $laminasRbac = new LaminasRbac($this->rbac->reveal());

        $routeResult = $this->prophesize(RouteResult::class);
        $routeResult->getMatchedRouteName()->willReturn('home');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)
                ->willReturn($routeResult->reveal());

        $result = $laminasRbac->isGranted('foo', $request->reveal());
        $this->assertTrue($result);
    }

    public function testIsNotGrantedWithoutAssertion()
    {
        $this->rbac->isGranted('foo', 'home', null)->willReturn(false);
        $laminasRbac = new LaminasRbac($this->rbac->reveal());

        $routeResult = $this->prophesize(RouteResult::class);
        $routeResult->getMatchedRouteName()->willReturn('home');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)
                ->willReturn($routeResult->reveal());

        $result = $laminasRbac->isGranted('foo', $request->reveal());
        $this->assertFalse($result);
    }

    public function testIsGrantedWitAssertion()
    {
        $routeResult = $this->prophesize(RouteResult::class);
        $routeResult->getMatchedRouteName()->willReturn('home');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)
                ->willReturn($routeResult->reveal());

        $this->rbac->isGranted('foo', 'home', $this->assertion->reveal())->willReturn(true);

        $laminasRbac = new LaminasRbac($this->rbac->reveal(), $this->assertion->reveal());

        $result = $laminasRbac->isGranted('foo', $request->reveal());
        $this->assertTrue($result);
        $this->assertion->setRequest($request->reveal())->shouldBeCalled();
    }

    public function testIsNotGrantedWitAssertion()
    {
        $routeResult = $this->prophesize(RouteResult::class);
        $routeResult->getMatchedRouteName()->willReturn('home');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)
                ->willReturn($routeResult->reveal());

        $this->rbac->isGranted('foo', 'home', $this->assertion->reveal())->willReturn(false);

        $laminasRbac = new LaminasRbac($this->rbac->reveal(), $this->assertion->reveal());

        $result = $laminasRbac->isGranted('foo', $request->reveal());
        $this->assertFalse($result);
        $this->assertion->setRequest($request->reveal())->shouldBeCalled();
    }
}
