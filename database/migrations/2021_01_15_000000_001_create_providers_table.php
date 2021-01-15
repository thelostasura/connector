<?php

namespace TheLostAsura\Connector\Database\Migrations;

use TheLostAsura\Connector\Models\Provider;
use TheLostAsura\Connector\Utils\DB;

class CreateProvidersTable {

    public static function up()
    {
        DB::create(Provider::TABLE_NAME, [
            'id' => [ 'BIGINT(20)', 'UNSIGNED', 'NOT NULL', 'AUTO_INCREMENT', ],
            'provider' => [ 'VARCHAR(255)', 'NOT NULL', ],
            'api_key' => [ 'VARCHAR(255)', 'NOT NULL', ],
            'api_secret' => [ 'VARCHAR(255)', 'NOT NULL', ],
            'endpoint' => [ 'VARCHAR(255)', 'NOT NULL', ],
            'version' => [ 'VARCHAR(255)', 'NOT NULL', ],
            'site_title' => [ 'VARCHAR(255)', 'NOT NULL', ],
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
        DB::drop(Provider::TABLE_NAME);
    }
}