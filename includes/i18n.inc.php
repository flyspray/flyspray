<?php

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

require_once BASEDIR . '/lang/en.php';

function L($key)
{
    global $language;
    if (empty($key)) {
        return '';
    }
    if (isset($language[$key])) {
        return $language[$key];
    }
    return "[[$key]]";
}

function load_translations()
{
    global $proj, $language;
    // Load translations
    $translation = BASEDIR . "/lang/{$proj->prefs['lang_code']}.php";
    if ($proj->prefs['lang_code'] != 'en' && is_file($translation)) {
        include_once($translation);
        $language = array_merge($language, $translation);
    }
}

?>
