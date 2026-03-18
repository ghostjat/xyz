<?php 
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSmtpSettingsTable extends Migration
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
            'smtp_host' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                 'default'    => 'mail.pharoseducation.in',
            ],
            'smtp_user' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'smtp_password_encrypted' => [
                'type' => 'TEXT', // TEXT is strictly required for long hex-encoded encrypted strings
                'null' => true,
            ],
            'smtp_port' => [
                'type'       => 'INT',
                'constraint' => 5,
                'default'    => 465, // Standard secure SSL port
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->createTable('smtp_settings');
    }

    public function down()
    {
        $this->forge->dropTable('smtp_settings');
    }
}