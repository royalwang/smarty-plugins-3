<?php
/**
 * Description:
 * The Function detects the current browers' name, version and
 * operating system. It's currently very badly written and exectued, but yet working :D
 * 
 * Requirement:
 * As stated at php.net:
 * In order for this to work, your browscap configuration setting in php.ini must point
 * to the correct location of the browscap.ini file on your system.
 * browscap.ini is not bundled with PHP,o but you may find an up-to-date » php_browscap.ini file here.
 * While browscap.ini contains information on many browsers, it relies on
 * user updates to keep the database current. The format of the file is fairly self-explanatory.
 * @todo Check only by headers.
 * @todo Test on for other browsers different than IE.
 * @todo Clean up, refactor.
 * @date 10.3.2013
 * @author vcalaelen@gmail.com
 */

function smarty_function_is_browser($params, &$smarty) {
    //If the user hasn't stated any parameters
    if (empty($params)) {
        return;
    }
    $var = $params['assign'];

    //The system doesn't cover the requirements needed
    // if (!get_cfg_var('get_cfg_var')) {
    //  return get_from_browsecap($params);
    // }
    //Else do the things manually
    
    if (isset($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] != "") {
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
    } else {
        $user_agent = 'Unknown';
    }

    $name = 'unknown';
    $version = '0';

    /**
     * @todo There was a way to this with two array, but im too lazy right now 
     */
    if (preg_match('~MSIE~', $user_agent)) {
        $name = 'msie';
        $ub = 'msie';
    } elseif (preg_match('~Firefox~', $user_agent)) {
        $name = 'firefox';
    } elseif (preg_match('~Chrome~', $user_agent)) {
        $name = 'chrome';
    } elseif (preg_match('~Safari~', $user_agent)) {
        $name = 'safari';
    } elseif (preg_match('~Opera~', $user_agent)) {
        $name = 'opera';
    }

    //Getting the version of the browser
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#i';
    if (!preg_match_all($pattern, $user_agent, $matches)) {
        // we have no matching number just continue
    }
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($visitor_user_agent, "Version") < strripos($visitor_user_agent, $ub)) {
            $version = $matches['version'][0];
        } else {
            $version = $matches['version'][1];
        }
    } else {
        $version = $matches['version'][0];
    }

    //Get the OS

    // the order of this array is important
    $oses   = array(
        'win311' => 'Win16',
        'win95' => '(Windows 95)|(Win95)|(Windows_95)',
        'winme' => '(Windows 98)|(Win 9x 4.90)|(Windows ME)',
        'win98' => '(Windows 98)|(Win98)',
        'win2000' => '(Windows NT 5.0)|(Windows 2000)',
        'winxp' => '(Windows NT 5.1)|(Windows XP)',
        'winserver2003' => '(Windows NT 5.2)',
        'winvista' => '(Windows NT 6.0)',
        'windows 7' => '(Windows NT 6.1)',
        'windows 8' => '(Windows NT 6.2)',
        'winNT' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
        'openbsd' => 'OpenBSD',
        'sunos' => 'SunOS',
        'ubuntu' => 'Ubuntu',
        'android' => 'Android',
        'linux' => '(Linux)|(X11)',
        'iphone' => 'iPhone',
        'ipad' => 'iPad',
        'macOS' => '(Mac_PowerPC)|(Macintosh)',
        'qnx' => 'QNX',
        'beos' => 'BeOS',
        'os2' => 'OS\/2',
        'searchbot' => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(MSNBot)|(Ask Jeeves\/Teoma)|(ia_archiver)'
    );
    $os = '';
    // $user_agent = strtolower($user_agent);
    foreach ($oses as $temp_os => $pattern) {
        if (preg_match('/' . $pattern . '/i', $user_agent)) {
            $os = $temp_os;
        }
    }

    $data = array(
        'name' => isset($params['name']) ? $params['name'] : $name,
        'version' => isset($params['version']) ? $params['version'] : $version,
        'os' => isset($params['os']) ? $params['os'] : $os
    );

    $data = array_filter($data, 'strtolower');
    $result = false;
    if( ($data['name'] == $name) &&
           ($data['version'] == $version) &&
           ($data['os'] == $os) ) {
        $result = true;
    }

    $smarty->assign($var, $result);
}

//If PHP defined browsecap
function get_from_browsecap($params) {
    @$current_browser = get_browser();
    $data = array(
        'version' => isset($params['version']) ? $params['version'] : $current_browser->version,
        'name' => isset($params['name']) ? $params['name'] : $current_browser->name,
        'os' => isset($params['os']) ? $params['os'] : $current_browser->os 
    );

    //Make all data to lowercase
    $data = array_filter($data, 'strtolower');

    return ($data['name'] == strtolower($current_browser->name)) &&
           ($data['version'] == strtolower($current_browser->version)) && 
           ($data['os'] == strtolower($current_browser->platform));
}