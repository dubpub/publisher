<?php return [
    'dubpub/publisher' => [
        'assets' => [
            'assets/scripts       ->  {public,web,htdocs}/assets/js/',
            'assets/styles        ->  {public,web,htdocs}/assets/css/'
        ],
        'configs' => [
            'configs/*.ini.dist -> configs/dist'
        ],
        'link' => [
            '@link/* -> link'
        ],
        'empty' => [
        ]
    ]
];