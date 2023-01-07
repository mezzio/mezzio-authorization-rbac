<?php

declare(strict_types=1);

namespace Mezzio\Authorization\Rbac;

use Laminas\ServiceManager\ConfigInterface;

/** @psalm-import-type ServiceManagerConfigurationType from ConfigInterface */
class ConfigProvider
{
    /** @return array{dependencies: ServiceManagerConfigurationType} */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /** @return ServiceManagerConfigurationType */
    public function getDependencies(): array
    {
        return [
            // Legacy Zend Framework aliases
            'aliases'   => [
                'Zend\Expressive\Authorization\Rbac\ZendRbac' => LaminasRbac::class,
            ],
            'factories' => [
                LaminasRbac::class => LaminasRbacFactory::class,
            ],
        ];
    }
}
