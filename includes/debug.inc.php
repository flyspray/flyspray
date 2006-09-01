<?php
/*
 * devel and debug tools
 */

if (!empty($conf['debug']['validator'])) {
    $result = ob_get_clean();

    $fd = fopen(BASEDIR . '/attachments/valid.html', 'wb');
    fwrite($fd, $result);
    fclose($fd);

    $validator = $conf['debug']['validator'];
    $cmd = sprintf("%s/addons/validate.pl %s %s/attachments/valid.html",
            BASEDIR, $validator, BASEDIR);

    exec($cmd, $val);

    foreach ($val as $h) {
        if (preg_match("/^X-W3C-Validator-Errors: (\d+)$/", $h, $m)) {
            $replc = '';
            if ($m[1]) {
                $replc = "<a style='color: #800' href='$validator?uri={$baseurl}"
                    ."attachments/valid.html&amp;ss=1#result'>{$m[1]} XHTML ERROR(S) !!!</a><br />";
            }
            break;
        }
    }

    echo str_replace("<body>", "<body>$replc", $result);
}

?>
