<?php

return [
    [[]],
    [
        [
            'boolean' => true,
        ]
    ],
    [
        [
            'boolean' => true,
            'char'    => 'abc',
        ]
    ],
    [
        [
            'boolean' => true,
            'char'    => 'abc',
            'integer' => 12
        ]
    ],
    [
        [
            'boolean' => true,
            'char'    => 'uye',
            'integer' => 12
        ]
    ],
    [
        [
            'boolean' => true,
            'char'    => 'def',
            'integer' => 12,
            'float'   => 56.24
        ]
    ],
    [
        [
            'boolean' => true,
            'char'    => 'def',
            'integer' => 2,
            'float'   => 56.24,
            'object'  => [
                'key1' => 'value'
            ]
        ]
    ],
    [
        [
            'boolean'  => true,
            'char'     => 'def',
            'integer'  => 2,
            'float'    => 56.24,
            'object'   => [
                'key1' => 'value'
            ],
            'many2one' => ['char' => 'value']
        ]
    ],
    [
        [
            'boolean'  => true,
            'char'     => 'def',
            'integer'  => 2,
            'float'    => 56.24,
            'object'   => [
                'key1' => 'value'
            ],
            // This should throw an exception because many2one->char is required
            'many2one' => [],
            'one2many' => [['char' => 'first value']]
        ]
    ],
    [
        [
            'boolean'  => true,
            'char'     => 'def',
            'integer'  => 2,
            'float'    => 56.24,
            'object'   => [
                'key1' => 'value'
            ],
            'many2one' => ['char' => 'value'],
            // This should throw an exception because one2many->char is required
            'one2many' => [['nonExistingAttribute' => 'first value']]
        ]
    ]
];