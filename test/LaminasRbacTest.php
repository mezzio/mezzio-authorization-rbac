<?php

declare(strict_types=1);

namespace MezzioTest\Authorization\Rbac;

use Laminas\Permissions\Rbac\Rbac;
use Mezzio\Authorization\Exception;
use Mezzio\Authorization\Rbac\LaminasRbac;
use Mezzio\Authorization\Rbac\LaminasRbacAssertionInterface;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class LaminasRbacTest extends TestCase
{
    /** @var Rbac&MockObject */
    private Rbac $rbac;

    /** @var LaminasRbacAssertionInterface&MockObject */
    private LaminasRbacAssertionInterface $assertion;

    protected function setUp(): void
    {
        $this->rbac      = $this->createMock(Rbac::class);
        $this->assertion = $this->createMock(LaminasRbacAssertionInterface::class);
    }

    public function testConstructorWithoutAssertion(): void
    {
        $laminasRbac = new LaminasRbac($this->rbac);
        self::assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testConstructorWithAssertion(): void
    {
        $laminasRbac = new LaminasRbac($this->rbac, $this->assertion);
        self::assertInstanceOf(LaminasRbac::class, $laminasRbac);
    }

    public function testIsGrantedWithoutRouteResult(): void
    {
        $laminasRbac = new LaminasRbac($this->rbac, $this->assertion);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteResult::class, false)
            ->willReturn(false);

        $this->expectException(Exception\RuntimeException::class);
        $laminasRbac->isGranted('foo', $request);
    }

    public function testIsGrantedWithoutAssertion(): void
    {
        $this->rbac->expects(self::once())
            ->method('isGranted')
            ->with('foo', 'home', null)
            ->willReturn(true);

        $laminasRbac = new LaminasRbac($this->rbac);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteResult::class, false)
            ->willReturn($this->getSuccessRouteResult('home'));

        $result = $laminasRbac->isGranted('foo', $request);
        self::assertTrue($result);
    }

    public function testIsNotGrantedWithoutAssertion(): void
    {
        $this->rbac->expects(self::once())
            ->method('isGranted')
            ->with('foo', 'home', null)
            ->willReturn(false);

        $laminasRbac = new LaminasRbac($this->rbac);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteResult::class, false)
            ->willReturn($this->getSuccessRouteResult('home'));

        $result = $laminasRbac->isGranted('foo', $request);
        self::assertFalse($result);
    }

    public function testIsGrantedWitAssertion(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteResult::class, false)
            ->willReturn($this->getSuccessRouteResult('home'));

        $this->rbac->expects(self::once())
            ->method('isGranted')
            ->with('foo', 'home', $this->assertion)
            ->willReturn(true);

        $this->assertion->expects(self::once())
            ->method('setRequest')
            ->with($request);

        $laminasRbac = new LaminasRbac($this->rbac, $this->assertion);

        $result = $laminasRbac->isGranted('foo', $request);
        self::assertTrue($result);
    }

    public function testIsNotGrantedWitAssertion(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteResult::class, false)
            ->willReturn($this->getSuccessRouteResult('home'));

        $this->rbac->expects(self::once())
            ->method('isGranted')
            ->with('foo', 'home', $this->assertion)
            ->willReturn(false);

        $this->assertion->expects(self::once())
            ->method('setRequest')
            ->with($request);

        $laminasRbac = new LaminasRbac($this->rbac, $this->assertion);

        $result = $laminasRbac->isGranted('foo', $request);
        self::assertFalse($result);
    }

    public function testIsGrantedWithFailedRouting(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getAttribute')
            ->with(RouteResult::class, false)
            ->willReturn($this->getFailureRouteResult(Route::HTTP_METHOD_ANY));

        $laminasRbac = new LaminasRbac($this->rbac);

        $result = $laminasRbac->isGranted('foo', $request);
        self::assertTrue($result);
    }

    private function getSuccessRouteResult(string $routeName): RouteResult
    {
        $route = $this->createMock(Route::class);
        $route->method('getName')->willReturn($routeName);

        return RouteResult::fromRoute($route);
    }

    /** @param list<string>|null $methods */
    private function getFailureRouteResult(?array $methods): RouteResult
    {
        return RouteResult::fromRouteFailure($methods);
    }
}
