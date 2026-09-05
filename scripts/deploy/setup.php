<?php
/**
 * One-time server-side setup, run over HTTPS because this host has no SSH.
 *
 * Everything here is work a deploy would normally do with artisan on the
 * server: unpack the uploaded archives, link the published storage, warm the
 * framework caches. It deletes itself when it finishes.
 *
 * It refuses to run without the token it was deployed with, so a leftover copy
 * cannot be driven by anyone who finds the URL.
 */
declare(strict_types=1);
@set_time_limit(0);
@ini_set('memory_limit', '512M');
header('Content-Type: text/plain; charset=utf-8');

const TOKEN = '__TOKEN__';

if (! hash_equals(TOKEN, (string) ($_GET['t'] ?? ''))) {
    http_response_code(404);
    exit("Not found\n");
}

$root    = __DIR__;                    // public_html
$home    = dirname($root);             // /home/mru
$laravel = $home.'/laravel';
$step    = (string) ($_GET['step'] ?? 'all');
$ok      = true;

function say(string $m): void { echo $m."\n"; @ob_flush(); @flush(); }
function bad(string $m): void { global $ok; $ok = false; say('  !! '.$m); }

/** Extract an uploaded zip, then remove it. */
function unpackZip(string $zip, string $dest): void
{
    if (! is_file($zip)) { say("  (no $zip — skipped)"); return; }
    $z = new ZipArchive;
    if ($z->open($zip) !== true) { bad("could not open $zip"); return; }
    @mkdir($dest, 0755, true);
    if (! $z->extractTo($dest)) { bad("extract failed: $zip"); $z->close(); return; }
    $n = $z->numFiles; $z->close();
    unlink($zip);
    say(sprintf('  %s -> %s (%d entries)', basename($zip), $dest, $n));
}

if ($step === 'all' || $step === 'unpack') {
    say('== unpacking ==');
    foreach (glob($home.'/_upload/laravel*.zip') ?: [] as $z) unpackZip($z, $laravel);
    foreach (glob($home.'/_upload/public*.zip')  ?: [] as $z) unpackZip($z, $root);
    foreach (glob($home.'/_upload/storage*.zip') ?: [] as $z) unpackZip($z, $laravel.'/storage/app/public');
}

if ($step === 'all' || $step === 'link') {
    say('== published storage ==');
    $target = $laravel.'/storage/app/public';
    $link   = $root.'/storage';
    if (! is_dir($target)) {
        bad("missing $target");
    } elseif (is_link($link) && readlink($link) === $target) {
        say('  already linked');
    } else {
        if (is_link($link)) @unlink($link);
        if (is_dir($link))  @rename($link, $link.'.old-'.time());
        if (@symlink($target, $link)) say('  symlinked public_html/storage -> '.$target);
        else bad('symlink() failed — copy the directory instead');
    }

    foreach ([$laravel.'/storage/framework/cache/data', $laravel.'/storage/framework/sessions',
              $laravel.'/storage/framework/views', $laravel.'/storage/logs',
              $laravel.'/bootstrap/cache'] as $d) {
        @mkdir($d, 0775, true);
        @chmod($d, 0775);
        is_writable($d) || bad("not writable: $d");
    }
    say('  writable directories checked');
}

if ($step === 'all' || $step === 'cache') {
    say('== framework caches ==');
    if (! is_file($laravel.'/artisan')) { bad('artisan not found'); }
    else {
        require $laravel.'/vendor/autoload.php';
        $app = require_once $laravel.'/bootstrap/app.php';
        $app->usePublicPath($root);
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        foreach ([['optimize:clear'], ['config:cache'], ['route:cache'], ['view:cache']] as $cmd) {
            $out = new Symfony\Component\Console\Output\BufferedOutput;
            try {
                $status = $kernel->call($cmd[0], [], $out);
                say(sprintf('  %-16s %s', $cmd[0], $status === 0 ? 'ok' : 'EXIT '.$status));
                if ($status !== 0) { bad(trim($out->fetch())); }
            } catch (Throwable $e) {
                bad($cmd[0].': '.$e->getMessage());
            }
        }
    }
}

if ($step === 'all' || $step === 'verify') {
    say('== verify ==');
    say('  php        '.PHP_VERSION);
    say('  laravel    '.(is_file($laravel.'/vendor/autoload.php') ? 'vendor ok' : 'VENDOR MISSING'));
    say('  env        '.(is_file($laravel.'/.env') ? 'present' : 'MISSING'));
    $db = $laravel.'/database/database.sqlite';
    say('  database   '.(is_file($db) ? number_format(filesize($db) / 1048576, 1).' MB' : 'MISSING'));
    say('  storage    '.(is_link($root.'/storage') ? 'linked' : (is_dir($root.'/storage') ? 'directory' : 'MISSING')));
    say('  index.php  '.(is_file($root.'/index.php') ? 'present' : 'MISSING'));
}

say('');
if ($ok && $step === 'all') {
    @unlink(__FILE__);
    say('DONE — setup.php has removed itself.');
} elseif ($ok) {
    say('DONE ('.$step.')');
} else {
    say('FINISHED WITH ERRORS — setup.php kept so it can be re-run.');
}
