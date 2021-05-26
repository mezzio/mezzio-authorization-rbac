<?php

declare(strict_types=1);

namespace Mezzio\Authorization\Rbac;

use Laminas\Permissions\Rbac\AssertionInterface;
use Laminas\Permissions\Rbac\Rbac;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Authorization\Exception;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

use function sprintf;

class LaminasRbac implements AuthorizationInterface
{
    /** @var Rbac */
    private $rbac;

    /** @var null|AssertionInterface */
    private $assertion;

    public function __construct(Rbac $rbac, ?LaminasRbacAssertionInterface $assertion = null)
    {
        $this->rbac      = $rbac;
        $this->assertion = $assertion;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function isGranted(string $role, ServerRequestInterface $request): bool
    {
        $routeResult = $request->getAttribute(RouteResult::class, false);
        if (false === $routeResult) {
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
        if (null !== $this->assertion) {
            $this->assertion->setRequest($request);
        }

        return $this->rbac->isGranted($role, $routeName, $this->assertion);
    }
}
