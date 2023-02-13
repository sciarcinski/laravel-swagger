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
        [
            'title' => 'API',
            'security' => 'auto',
            'paths' => [
                'doc_json' => storage_path('docs/api/api.json'),
                'doc_yaml' => storage_path('docs/api/api.yaml'),
                'version' => storage_path('docs/api/components/version.json'),
                'security' => storage_path('docs/api/security/'),
                'components' => storage_path('docs/api/components/'),
                'configs' => storage_path('docs/api/configs/'),
                'responses' => storage_path('docs/api/responses/'),
            ],
            'routes' => [
                'api.test.index',
                'api.test.show',
                'api.test.store',
                'api.test.update',
                'api.test.destroy',
            ],
        ],
    ],
];
