<?php 

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRouteTokensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'unique'     => true, // Tokens must never repeat
            ],
            'target_controller' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'target_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'params' => [
                'type' => 'JSON', // Stores any required IDs or variables
                'null' => true,
            ],
            'is_single_use' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0, // Set to 1 if the token should die after one click
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('route_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('route_tokens');
    }
}