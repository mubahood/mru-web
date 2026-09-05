<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Builds the production database as a single SQLite file.
 *
 * The live host is cPanel shared hosting reached only over FTP — there is no
 * SSH and no cPanel API access, so a MySQL database and user cannot be
 * provisioned from here. SQLite needs neither: the file ships with the
 * release, sits outside the document root, and pdo_sqlite is present on the
 * server (probed). For a site whose traffic is overwhelmingly reads of cached
 * content, that is a sound production choice rather than a compromise; moving
 * to MySQL later is a change of four lines in .env.
 *
 * The schema comes from the migrations, not from a MySQL dump, so it is
 * SQLite-correct rather than a translation. Rows are then copied table by
 * table from the working MySQL database.
 */
class BuildProductionSqlite extends Command
{
    protected $signature = 'mru:build-sqlite {path : Where to write the .sqlite file} {--chunk=400}';

    protected $description = 'Build a production SQLite database from the working MySQL one';

    /** Local-only tables: profiler output, and analytics that should start clean in production. */
    private const SKIP = ['telescope_entries', 'telescope_entries_tags', 'telescope_monitoring',
        'pulse_values', 'pulse_entries', 'pulse_aggregates', 'sessions', 'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens'];

    public function handle(): int
    {
        $path = (string) $this->argument('path');
        @mkdir(dirname($path), 0775, true);

        if (is_file($path)) {
            unlink($path);
        }
        touch($path);

        Config::set('database.connections.prod_sqlite', [
            'driver' => 'sqlite', 'database' => $path, 'prefix' => '', 'foreign_key_constraints' => false,
        ]);

        // Telescope's and Pulse's migrations ignore --database and use their own
        // configured connection, so without this they run against the working
        // MySQL database instead of the target and fail on tables that exist.
        // Neither package ships to production (composer install --no-dev), but
        // their migrations are still in database/migrations.
        Config::set('telescope.storage.database.connection', 'prod_sqlite');
        Config::set('pulse.storage.database.connection', 'prod_sqlite');

        $this->info('Running migrations into '.$path);
        Artisan::call('migrate', ['--force' => true, '--database' => 'prod_sqlite'], $this->output);

        $target = DB::connection('prod_sqlite');
        $target->statement('PRAGMA journal_mode=WAL');

        $tables = collect(Schema::getTableListing())
            ->map(fn ($t) => str_contains($t, '.') ? substr($t, strrpos($t, '.') + 1) : $t)
            ->reject(fn ($t) => in_array($t, self::SKIP, true) || $t === 'migrations')
            ->values();

        $copied = 0;
        $rows = 0;

        foreach ($tables as $table) {
            if (! Schema::connection('prod_sqlite')->hasTable($table)) {
                $this->warn("  skip {$table} — not in the migrated schema");

                continue;
            }

            $columns = Schema::connection('prod_sqlite')->getColumnListing($table);
            $target->table($table)->delete();
            $n = 0;
            $size = (int) $this->option('chunk');

            // offset(), not chunk(): chunk() needs a strictly ordered unique
            // column to page safely, and a table without an `id` (pivots,
            // keyed lookups) can hand back the same rows for ever. The source
            // is not being written to during this copy, so a plain offset walk
            // is both correct and predictable.
            $order = Schema::hasColumn($table, 'id') ? 'id' : $columns[0];
            $total = DB::table($table)->count();

            // One transaction for the whole table. Without it SQLite commits —
            // and fsyncs — on every statement, which turned a few thousand
            // rows into minutes.
            $target->transaction(function () use ($target, $table, $columns, $order, $size, $total, &$n) {
                for ($offset = 0; $offset < $total; $offset += $size) {
                    $batch = DB::table($table)->orderBy($order)->offset($offset)->limit($size)->get();

                    $payload = $batch->map(fn ($row) => collect((array) $row)
                        ->only($columns)   // a column dropped in a later migration must not be carried over
                        ->map(fn ($v) => is_bool($v) ? (int) $v : $v)
                        ->all())->all();

                    if ($payload !== []) {
                        $target->table($table)->insert($payload);
                        $n += count($payload);
                    }
                }
            });

            if ($n > 0) {
                $copied++;
                $rows += $n;
                $this->line(sprintf('  %-26s %d', $table, $n));
            }
        }

        $this->newLine();
        $this->info(sprintf('%d tables, %s rows, %s on disk.', $copied, number_format($rows),
            number_format(filesize($path) / 1048576, 1).' MB'));

        return self::SUCCESS;
    }
}
