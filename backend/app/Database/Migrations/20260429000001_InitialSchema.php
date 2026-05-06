<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InitialSchema extends Migration
{
    public function up()
    {
        $sqlFile = FCPATH . '../mjsir_db_backup.sql';
        
        if (!file_exists($sqlFile)) {
            echo "Backup file not found at: " . $sqlFile . "\n";
            return;
        }

        $sql = file_get_contents($sqlFile);
        
        $db = \Config\Database::connect();
        $hostname = $db->hostname;
        $username = $db->username;
        $password = $db->password;
        $database = $db->database;

        $mysqli = new \mysqli($hostname, $username, $password, $database);

        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: " . $mysqli->connect_error . "\n";
            return;
        }

        if ($mysqli->multi_query($sql)) {
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
            echo "Successfully imported mjsir_db_backup.sql!\n";
        } else {
            echo "Error executing SQL: " . $mysqli->error . "\n";
        }

        $mysqli->close();
    }

    public function down()
    {
        $this->forge->dropTable('manuscripts', true);
        $this->forge->dropTable('notifications', true);
        $this->forge->dropTable('users', true);
    }
}
