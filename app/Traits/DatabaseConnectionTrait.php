<?php

namespace App\Traits;

use PDO;

trait DatabaseConnectionTrait
{
    /**
     * Get a PDO connection using Laravel's database configuration
     */
    protected function getPDOConnection()
    {
        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database', 'hr3_hr3systemdb');
        $username = config('database.connections.mysql.username', 'root');
        $password = config('database.connections.mysql.password', '');
        
        $dsn = "mysql:host={$host};port={$port};dbname={$database}";
        
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    }
    
    /**
     * Get database name from config
     */
    protected function getDatabaseName()
    {
        return config('database.connections.mysql.database', 'hr3_hr3systemdb');
    }
}
