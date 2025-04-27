<?php

declare(strict_types=1);

namespace Mezzio\Authorization\Rbac;

use Laminas\Permissions\Rbac\Rbac;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Authorization\Exception;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

use function assert;
use function is_string;
use function sprintf;

/** @final */
class LaminasRbac implements AuthorizationInterface
{
    public function __construct(
        private Rbac $rbac,
        private ?LaminasRbacAssertionInterface $assertion = null
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function isGranted(string $role, ServerRequestInterface $request): bool
    {
        $routeResult = $request->getAttribute(RouteResult::class, false);
        if (! $routeResult instanceof RouteResult) {
            throw new Exception\RuntimeException(sprintf(
                'The %s attribute is missing in the request; cannot perform authorizations',
                RouteResult::class
            ));
        }

        // No matching route. Everyone can access.
        if ($routeResult->isFailure()) {
            return true;
        }

        $routeName = $routeResult->getMatchedRouteName();
        assert(is_string($routeName));
        if (null !== $this->assertion) {
            $this->assertion->setRequest($request);
        }

        return $this->rbac->isGranted($role, $routeName, $this->assertion);
    }
}
