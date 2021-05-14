<?php

declare(strict_types=1);

namespace Mezzio\Authorization\Rbac;

class ConfigProvider
{
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies() : array
    {
        return [
            // Legacy Zend Framework aliases
            'aliases' => [
                \Zend\Expressive\Authorization\Rbac\ZendRbac::class => LaminasRbac::class,
            ],
            'factories' => [
                LaminasRbac::class => LaminasRbacFactory::class,
            ],
        ];
    }
}
