<?php

return [
    1 => [
        "status" => 200,
        "data" => [
            "status_code" => 1,
            "status_message" => "Success.",
        ],
    ],
    2 => [
        "status" => 201,
        "data" => [
            "status_code" => 2,
            "status_message" => "Success.",
        ],
    ],
    3 => [
        "status" => 403,
        "data" => [
            "status_code" => 3,
            "status_message" => "Authentication failed: You do not have permissions to access this resource.",
        ],
    ],
    4 => [
        "status" => 204,
        "data" => [
            "status_code" => 4,
            "status_message" => "Success.",
        ],
    ],
    5 => [
        //"status" => 400,
        "status" => 422,
        "data" => [
            "status_code" => 5,
            "status_message" => "Invalid parameters: Your request parameters are incorrect.",
        ],
    ],
    10 => [
        "status" => 500,
        "data" => [
            "status_code" => 10,
            "status_message" => "The requested changes could not be applied.",
        ],
    ],
    12 => [
        "status" => 405,
        "data" => [
            "status_code" => 12,
            "status_message" => "You are actually logued in. Log out and try again.",
        ],
    ],
    14 => [
        "status" => 405,
        "data" => [
            "status_code" => 14,
            "status_message" => "This method is not allowed actually. Try again later.",
        ],
    ],
    16 => [
        "status" => 405,
        "data" => [
            "status_code" => 16,
            "status_message" => "You are not allowed to access the specified API endpoint.",
        ],
    ],
    18 => [
        "status" => 400,
        "data" => [
            "status_code" => 18,
            "status_message" => "Value invalid: Values must go from 0.50 to 10.",
        ],
    ],/*
    19 => [
        "status" => 400,
        "data" => [
            "status_code" => 18,
            "status_message" => "Value too high: Value must be less than, or equal to 10.0.",
        ],
    ],*/
    34 => [
        "status" => 404,
        "data" => [
            "status_code" => 34,
            "status_message" => "The resource you requested could not be found.",
        ],
    ],

];
