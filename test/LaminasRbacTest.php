<?php

declare(strict_types=1);

namespace MezzioTest\Authorization\Rbac;

use Laminas\Permissions\Rbac\Rbac;
use Mezzio\Authorization\Exception;
use Mezzio\Authorization\Rbac\LaminasRbac;
use Mezzio\Authorization\Rbac\LaminasRbacAssertionInterface;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ServerRequestInterface;

class LaminasRbacTest extends TestCase
{
    use ProphecyTrait;

    /** @var Rbac|ObjectProphecy */
    private $rbac;

    /** @var LaminasRbacAssertionInterface|ObjectProphecy */
    private $assertion;

    protected function setUp(): void
    {
        $this->rbac      = $this->prophesize(Rbac::class);
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

    public function testIsGrantedWithoutRouteResult()
    {
        $laminasRbac = new LaminasRbac($this->rbac->reveal(), $this->assertion->reveal());

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn(false);

        $this->expectException(Exception\RuntimeException::class);
        $laminasRbac->isGranted('foo', $request->reveal());
    }

    public function testIsGrantedWithoutAssertion()
    {
        $this->rbac->isGranted('foo', 'home', null)->willReturn(true);
        $laminasRbac = new LaminasRbac($this->rbac->reveal());

        $routeResult = $this->getSuccessRouteResult('home');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn($routeResult);

        $result = $laminasRbac->isGranted('foo', $request->reveal());
        $this->assertTrue($result);
    }

    public function testIsNotGrantedWithoutAssertion()
    {
        $this->rbac->isGranted('foo', 'home', null)->willReturn(false);
        $laminasRbac = new LaminasRbac($this->rbac->reveal());

        $routeResult = $this->getSuccessRouteResult('home');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn($routeResult);

        $result = $laminasRbac->isGranted('foo', $request->reveal());
        $this->assertFalse($result);
    }

    public function testIsGrantedWitAssertion()
    {
        $routeResult = $this->getSuccessRouteResult('home');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn($routeResult);

        $this->rbac->isGranted('foo', 'home', $this->assertion->reveal())->willReturn(true);

        $laminasRbac = new LaminasRbac($this->rbac->reveal(), $this->assertion->reveal());

        $result = $laminasRbac->isGranted('foo', $request->reveal());
        $this->assertTrue($result);
        $this->assertion->setRequest($request->reveal())->shouldBeCalled();
    }

    public function testIsNotGrantedWitAssertion()
    {
        $routeResult = $this->getSuccessRouteResult('home');

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn($routeResult);

        $this->rbac->isGranted('foo', 'home', $this->assertion->reveal())->willReturn(false);

        $laminasRbac = new LaminasRbac($this->rbac->reveal(), $this->assertion->reveal());

        $result = $laminasRbac->isGranted('foo', $request->reveal());
        $this->assertFalse($result);
        $this->assertion->setRequest($request->reveal())->shouldBeCalled();
    }

    public function testIsGrantedWithFailedRouting()
    {
        $routeResult = $this->getFailureRouteResult(Route::HTTP_METHOD_ANY);

        $request = $this->prophesize(ServerRequestInterface::class);
        $request->getAttribute(RouteResult::class, false)->willReturn($routeResult);

        $laminasRbac = new LaminasRbac($this->rbac->reveal());

        $result = $laminasRbac->isGranted('foo', $request->reveal());
        $this->assertTrue($result);
    }

    private function getSuccessRouteResult(string $routeName): RouteResult
    {
        $route = $this->prophesize(Route::class);
        $route->getName()->willReturn($routeName);

        return RouteResult::fromRoute($route->reveal());
    }

    private function getFailureRouteResult(?array $methods): RouteResult
    {
        return RouteResult::fromRouteFailure($methods);
    }
}
