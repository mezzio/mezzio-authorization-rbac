<?php

/**
 * @see       https://github.com/mezzio/mezzio-authorization-rbac for the canonical source repository
 * @copyright https://github.com/mezzio/mezzio-authorization-rbac/blob/master/COPYRIGHT.md
 * @license   https://github.com/mezzio/mezzio-authorization-rbac/blob/master/LICENSE.md New BSD License
 */

namespace Mezzio\Authorization\Rbac;

use Laminas\Permissions\Rbac\AssertionInterface;
use Psr\Http\Message\ServerRequestInterface;

interface LaminasRbacAssertionInterface extends AssertionInterface
{
    public function setRequest(ServerRequestInterface $request) : void;
}
