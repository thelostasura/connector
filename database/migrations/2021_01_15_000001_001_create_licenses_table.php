<?php

namespace TheLostAsura\Connector\Database\Migrations;

use TheLostAsura\Connector\Models\License;
use TheLostAsura\Connector\Utils\DB;

class CreateLicensesTable {

    public static function up()
    {
        DB::create(License::TABLE_NAME, [
            'id' => [ 'BIGINT(20)', 'UNSIGNED', 'NOT NULL', 'AUTO_INCREMENT', ],
            'provider_id' => [ 'BIGINT(20)', 'UNSIGNED', 'NOT NULL', ],
            'license' => [ 'VARCHAR(255)', 'NOT NULL', ],
            'hash' => [ 'VARCHAR(255)', 'NOT NULL', ],
            'status' => [ 'TINYINT(1)', 'NOT NULL', 'DEFAULT 1' ],
            'created_at' => [ 'TIMESTAMP(1)', 'NULL', 'DEFAULT NULL' ],
            'updated_at' => [ 'TIMESTAMP(1)', 'NULL', 'DEFAULT NULL' ],
            'PRIMARY KEY (<id>)',
        ], [
            'ENGINE' =>'InnoDB',
            'DEFAULT CHARACTER SET' => DB::wpdb()->charset,
            'COLLATE' => DB::wpdb()->collate,
        ]);
    }

    public static function down()
    {
        DB::drop(License::TABLE_NAME);
    }
}