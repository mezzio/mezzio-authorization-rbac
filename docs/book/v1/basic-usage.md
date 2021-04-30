# Basic Usage

## Configure an RBAC system

You can configure your RBAC using a configuration file, as follows:

```php
// config/autoload/authorization.local.php
return [
    // ...
    'mezzio-authorization-rbac' => [
        'roles' => [
            'administrator' => [],
            'editor'        => ['administrator'],
            'contributor'   => ['editor'],
        ],
        'permissions' => [
            'contributor' => [
                'admin.dashboard',
                'admin.posts',
            ],
            'editor' => [
                'admin.publish',
            ],
            'administrator' => [
                'admin.settings',
            ],
        ],
    ]
];
```

In the above example, we designed an RBAC system with 3 roles: `administator`,
`editor`, and `contributor`. We defined a hierarchy of roles as follows:

- `administrator` has no parent role.
- `editor` has `administrator` as a parent. That means `administrator` inherits
  the permissions of the `editor`.
- `contributor` has `editor` as a parent. That means `editor` inherits the
  permissions of `contributor`, and following the chain, `administator` inherits
  the permissions of `contributor`.

For each role, we specified an array of permissions. As you can notice, a
permission is just a string; it can represent anything. In our implementation,
this string represents a route name.  That means the `contributor` role can
access the routes `admin.dashboard` and `admin.posts` but cannot access the
routes `admin.publish` (assigned to `editor` role) and `admin.settings`
(assigned to `administrator`).

## Custom Authorization Logic

If you want to change the authorization logic for each permission, you can write
your own `Mezzio\Authorization\AuthorizationInterface` implementation.
That interface defines the following method:

```php
isGranted(string $role, ServerRequestInterface $request): bool;
```

where `$role` is the role and `$request` is the PSR-7 HTTP request to authorize.
