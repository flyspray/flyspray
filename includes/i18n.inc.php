<?php

if (!defined('IN_FS')) {
    die('Do not access this file directly.');
}

require_once BASEDIR . '/lang/en.php';
FlySprayI18N::init('en', $language);
FlySprayI18N::setDefault($language);

class FlySprayI18N {
    private static $translations = array();

    public static function init($lang, $translation) {
        self::$translations[$lang] = $translation;
    }

    public static function setDefault($translation) {
        self::$translations['default'] = $translation;
    }

    public static function L($key, $lang = null) {
        if (!isset($lang) || empty($lang) || !is_string($lang)) {
            $lang = 'default';
        }
        if ($lang != 'default' && $lang != 'en' && !array_key_exists($lang, self::$translations)) {
            // echo "<pre>Only once here for $lang!</pre>";
            $language = BASEDIR . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $lang . '.php';
            if (is_readable($language)) {
                if ((@require $language) !== FALSE) {
                    // echo "<pre>Loaded: $lang!</pre>";
                    self::$translations[$lang] = $translation;
                }
                else {
                    $lang = 'default';
                }
            }
            else {
                $lang = 'default';
            }
        }
        if (empty($key)) {
            return '';
        }
        if (isset(self::$translations[$lang][$key])) {
            // echo "<pre>Case 1: $lang!</pre>";
            return self::$translations[$lang][$key];
        }
        if (isset(self::$translations['default'][$key])) {
            // echo "<pre>Case 2: $lang!</pre>";
            return self::$translations['default'][$key];
        }
        if (isset(self::$translations['en'][$key])) {
            // echo "<pre>Case 3: $lang!</pre>";
            return self::$translations['en'][$key];
        }
        // echo "<pre>Case 4: $lang!". var_dump(self::$translations['en']) ."</pre>";
        return "[[$key]]";
    }
}
/**
 * get the language string $key
 * return string
 */

function L($key){
    global $language;
    if (empty($key)) {
        return '';
    }
    if (isset($language[$key])) {
        return $language[$key];
    }
    return "[[$key]]";
}

/**
 * get the language string $key in $lang
 * or current default language if $lang
 * is not given.
 * return string
 */

function tL($key, $lang = null) {
    return FlySprayI18N::L($key, $lang);
}
/**
 * html escaped variant of the previous
 * return $string
 */
function eL($key){
    return htmlspecialchars(L($key), ENT_QUOTES, 'utf-8');
}

function load_translations(){
	global $proj, $language, $user, $fs;
	# Load translations
	# if no valid lang_code, return english
	# valid == a-z and "_" case insensitive

	if (isset($user) && array_key_exists('lang_code', $user->infos)){
		$lang_code=$user->infos['lang_code'];
	}

	# 20150211 add language preferences detection of visitors
	# locale_accept_from_http() not available on every hosting, so we must parse it self.
	# TODO ..and we can loop later through $langs until we find a matching translation file
	if((!isset($lang_code) || $lang_code=='' || $lang_code=='browser') && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
		foreach( explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang) {
			# taken from a php.net comment
			$pattern = '/^(?P<primarytag>[a-zA-Z]{2,8})'.
				'(?:-(?P<subtag>[a-zA-Z]{2,8}))?(?:(?:;q=)'.
				'(?P<quantifier>\d\.\d))?$/';
			$splits = array();
			if (preg_match($pattern, $lang, $splits)) {
				$langs[]=$splits;
			}
		}
		# TODO maybe sort $langs-array by quantifiers, but for most browsers it should be ok, because they sent it in right order.
		if(isset($langs)){
			$lang_code=$langs[0]['primarytag'];
			if(isset($langs[0]['subtag'])){
				$lang_code.='_'.$langs[0]['subtag']; # '_' for our language files, '-' in HTTP_ACCEPT_LANGUAGE
			}
		}
	}

	if(!isset($lang_code) || $lang_code=='' || $lang_code=='project'){
		if($proj->prefs['lang_code']){
			$lang_code = $proj->prefs['lang_code'];
		}else{
			$lang_code = 'en';
		}
	}

	if (!preg_match('/^[a-z_]+$/iD', $lang_code)) {
		$lang_code ='en';
	}

	$lang_code = strtolower($lang_code);
	$translation = BASEDIR.'/lang/'.$lang_code.'.php';
	if ($lang_code != 'en' && is_readable($translation)) {
		include_once($translation);
		$language = is_array($translation) ? array_merge($language, $translation) : $language;
                FlySprayI18N::init($lang_code, $language);
	}elseif( 'en'!=substr($lang_code, 0, strpos($lang_code, '_')) && is_readable(BASEDIR.'/lang/'.(substr($lang_code, 0, strpos($lang_code, '_'))).'.php') ){
		# fallback 'de_AT' to 'de', but not for 'en_US'
		$translation=BASEDIR.'/lang/'.(substr($lang_code, 0, strpos($lang_code, '_'))).'.php';
		include_once($translation);
		$language = is_array($translation) ? array_merge($language, $translation) : $language;    
	}

        FlySprayI18N::setDefault($language);
    // correctly translate title since language not set when initialising the project
    if (isset($proj) && !$proj->id) {
        $proj->prefs['project_title'] = L('allprojects');
        $proj->prefs['feed_description']  = L('feedforall');
    }

    for ($i = 6; $i >= 1; $i--) {
        $fs->priorities[$i] = L('priority' . $i);
    }
    for ($i = 5; $i >= 1; $i--) {
        $fs->severities[$i] = L('severity' . $i);
    }
}
