<?php

/**
 * php artisan route:list --name=api
 * php artisan make:documentation {name}
 * php artisan make:documentation {name} --resource
 * php artisan documentation:generator
 * php artisan documentation:check
 */
return [
    'documentations' => [
        'api' => [
            'title' => 'API',
            'default_security' => [],
            'paths' => [
                'doc_json' => storage_path('docs/api/api.json'),
                'version' => storage_path('docs/api/components/version.json'),
                'components' => storage_path('docs/api/components/'),
                'configs' => storage_path('docs/api/configs/'),
                'responses' => storage_path('docs/api/responses/'),
            ],
            'routes' => [],
        ],
    ],
];
