<?php

/**
 * php artisan route:list --name=api
 * php artisan make:documentation {api_key} {route_name}
 * php artisan make:documentation {api_key} {route_group_name} --resource
 * php artisan documentation:generator
 * php artisan documentation:check
 */
return [
    'documentations' => [
        [
            'key' => 'api',
            'title' => 'API',
            'default_security' => [],
            'paths' => [
                'doc_json' => storage_path('docs/api/api.json'),
                'version' => storage_path('docs/api/version.json'),
                'components' => storage_path('docs/api/components/'),
                'configs' => storage_path('docs/api/configs/'),
                'responses' => storage_path('docs/api/responses/'),
            ],
            'routes' => [],
        ],
    ],
];
