<?php
return [
    'room' => [
        'status' => [
            'unfilled' => 1,
            'fixing' => 2,
            'filled' => 3
        ]
    ],
    'contract' => [
        'status' => [
            'pending' => 1,
            'active' => 2,
            'finished' => 3,
            'canceled' => 4,
            'overdue' => 5,
        ]
    ],
    "token" => [
        'type' => [
            'login' => 1,
            'notify' => 2
        ]
    ],
    "rule" => [
        'admin' => 'admin',
        'user' => 'user',
        'manager' => 'manager',
    ],
    "object" => [
        "type" => [
            'user' => 'user',
            'room' => 'room',
            'lodging' => 'lodging',
            'rent' => 'rent',
            'service' => 'service'
        ]
    ],
    'transaction' => [
        'type' => [
            "deposit" => "deposit",
            "withdraw" => "withdraw",
            "transfer_in" => "transfer_in",
            "transfer_out" => "transfer_out",
            "payment" => "payment"
        ]
    ],
    'feedback' => [
        'status' => [
            'submitted' => 1,
            'received' => 2,
            'in_progress' => 3,
            'resolved' => 4,
            'closed' => 5
        ]
    ],

    'type' => [
        'feedback' => 'feedback',
        'equipment' => 'equipment',
    ],

    'payment' => [
        'status' => [
            'unpaid' => 1,
            'paid' => 2,
            'partial' => 3
        ],
        'method' => [
            'system' => 'system',
            'cash' => 'cash',
            'bank' => 'bank',
            'transfer' => 'transfer',
        ]
    ],

    'notification' => [
        'type' => [
            'normal' => 'normal',
            'important' => 'important',
        ]
    ],

    'service' => [
        'name' => [
            'water' => "Nước",
            'wifi' => "Wifi",
            'electricity' => "Điện",
            'garbage' => "Rác",
            'parking' => "Đổ xe",
        ]
    ],

    'equipment' => [
        'type' => [
            'private' => 1,
            'public' => 2,
        ]
    ]
];
