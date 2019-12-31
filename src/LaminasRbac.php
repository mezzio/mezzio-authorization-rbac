<?php

/**
 * @see       https://github.com/mezzio/mezzio-authorization-rbac for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authorization-rbac/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authorization-rbac/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authorization\Rbac;

use Laminas\Permissions\Rbac\AssertionInterface;
use Laminas\Permissions\Rbac\Rbac;
use Mezzio\Authorization\AuthorizationInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

class LaminasRbac implements AuthorizationInterface
{
    /**
     * @var Rbac
     */
    private $rbac;

    /**
     * @var AssertionInterface
     */
    private $assertion;

    public function __construct(Rbac $rbac, LaminasRbacAssertionInterface $assertion = null)
    {
        $this->rbac = $rbac;
        $this->assertion = $assertion;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function isGranted(string $role, ServerRequestInterface $request) : bool
    {
        $routeResult = $request->getAttribute(RouteResult::class, false);
        if (false === $routeResult) {
            throw new Exception\RuntimeException(sprintf(
                'The %s attribute is missing in the request; cannot perform authorizations',
                RouteResult::class
            ));
        }

        $routeName = $routeResult->getMatchedRouteName();
        if (null !== $this->assertion) {
            $this->assertion->setRequest($request);
        }

        return $this->rbac->isGranted($role, $routeName, $this->assertion);
    }
}
