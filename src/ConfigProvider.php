<?php

declare(strict_types=1);

namespace Mezzio\Authorization\Rbac;

use Laminas\ServiceManager\ServiceManager;

/** @psalm-import-type ServiceManagerConfiguration from ServiceManager */
class ConfigProvider
{
    /** @return array{dependencies: ServiceManagerConfiguration} */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /** @return ServiceManagerConfiguration */
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
