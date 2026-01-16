<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SqlDumpSeeder extends Seeder
{
    /**
     * Seed the application's database from the SQL dump.
     */
    public function run(): void
    {
        $path = database_path('seeders/sql/hr3_hr3systemdb.sql');

        if (!file_exists($path)) {
            $this->command?->warn("SQL dump not found: {$path}");
            return;
        }

        $sql = file_get_contents($path);
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*![\s\S]*?\*\//', '', $sql);
        $sql = preg_replace('/\/\*[\s\S]*?\*\//', '', $sql);

        $statements = preg_split('/;\s*(\r\n|\r|\n|$)/', $sql);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ($statement === '') {
                continue;
            }

            if (!preg_match('/^insert\s+into\s+/i', $statement)) {
                continue;
            }

            if (preg_match('/^insert\s+into\s+`?migrations`?/i', $statement)) {
                continue;
            }

            // Remove ID column from INSERT statement
            $statement = preg_replace('/^INSERT\s+INTO\s+`?(\w+)`?\s*\(/i', 'INSERT INTO $1 (', $statement);
            $statement = preg_replace('/^INSERT\s+INTO\s+`?(\w+)`?\s*\((\s*`?id`?\s*,?)/i', 'INSERT INTO $1 (', $statement);
            $statement = preg_replace('/\)\s*VALUES\s*\((\s*)\d+\s*,/i', ') VALUES ($1', $statement);

            try {
                DB::unprepared($statement);
            } catch (\Exception $e) {
                $this->command?->warn("Error executing statement: " . $e->getMessage());
                continue;
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
