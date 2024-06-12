<?php
namespace App\Traits;

trait EnumFormat {

    public static function toList($column = 'value', $key = 'name'): array
    {
        $list = [];
        foreach (self::cases() as $case) {
            $list[$case->$column] = $case->$key;
        }
        return $list;
    }
}

