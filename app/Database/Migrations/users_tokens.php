<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class users_tokens extends Migration
{
    public function up()
    {
        /* adding new fields in services table */
        $fields = array(
            'tax' => array(
                'type' => 'FLOAT',
                'constraint' => '32',
                'after' => 'tax_id'
            ),
        );
        $this->dbforge->add_column('services', $fields);

        /* adding new table users_tokens */
        $this->forge->addField(
            [
                'id' =>
                [
                    'type' => 'INT',
                    'auto_increment' => true,
                    'unsigned' => true,
                    'constraint' => 11,
                ],
                'user_id' =>
                [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'token' =>
                [
                    'type' => 'VARCHAR',
                    'constraint' => 512,
                ],
            ]
        );
        $this->forge->addKey('id', true);
        $this->forge->createTable('users_tokens');
    }
    public function down()
    {
        $this->forge->dropTable('users_tokens');
        $this->forge->dropTable('services');
    }
}
