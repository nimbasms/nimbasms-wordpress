<?php

namespace App\Base;
use App\DB\DBTable;


class Deactivate {

    public static function deactivate()
    {
        flush_rewrite_rules();
    }

}
