<?
/*******************************************************************************
** 공통 변수, 상수, 코드
*******************************************************************************/
//error_reporting(E_ALL ^ E_NOTICE);
error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

// 보안설정이나 프레임이 달라도 쿠키가 통하도록 설정
header('P3P: CP="ALL CURa ADMa DEVa TAIa OUR BUS IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE LOC OTC"');

if (!isset($set_time_limit)) $set_time_limit = 0;
@set_time_limit($set_time_limit);

// 짧은 환경변수를 지원하지 않는다면
if (isset($HTTP_POST_VARS) && !isset($_POST)) {
	$_POST   = &$HTTP_POST_VARS;
	$_GET    = &$HTTP_GET_VARS;
	$_SERVER = &$HTTP_SERVER_VARS;
	$_COOKIE = &$HTTP_COOKIE_VARS;
	$_ENV    = &$HTTP_ENV_VARS;
	$_FILES  = &$HTTP_POST_FILES;

    if (!isset($_SESSION))
		$_SESSION = &$HTTP_SESSION_VARS;
}


//==============================================================================
// php.ini 의 magic_quotes_gpc 값이 FALSE 인 경우 addslashes() 적용
// SQL Injection 등으로 부터 보호
// http://kr.php.net/manual/en/function.get-magic-quotes-gpc.php#97783
//------------------------------------------------------------------------------
if (!get_magic_quotes_gpc()) {
    $escape_function = 'addslashes($value)';
    $addslashes_deep = create_function('&$value, $fn', '
        if (is_string($value)) {
            $value = ' . $escape_function . ';
        } else if (is_array($value)) {
            foreach ($value as &$v) $fn($v, $fn);
        }
    ');
    
    // Escape data
    $addslashes_deep($_POST, $addslashes_deep);
    $addslashes_deep($_GET, $addslashes_deep);
    $addslashes_deep($_COOKIE, $addslashes_deep);
    $addslashes_deep($_REQUEST, $addslashes_deep);
}
//==============================================================================


//==============================================================================
// XSS(Cross Site Scripting) 공격에 의한 데이터 검증 및 차단
//------------------------------------------------------------------------------
function xss_clean($data) {
    // If its empty there is no point cleaning it :\
    if(empty($data))
        return $data;

    // Recursive loop for arrays
    if(is_array($data)) {
        foreach($data as $key => $value) {
            $data[$key] = xss_clean($value);
        }

        return $data;
    }

    // http://svn.bitflux.ch/repos/public/popoon/trunk/classes/externalinput.php
    // +----------------------------------------------------------------------+
    // | Copyright (c) 2001-2006 Bitflux GmbH                                 |
    // +----------------------------------------------------------------------+
    // | Licensed under the Apache License, Version 2.0 (the "License");      |
    // | you may not use this file except in compliance with the License.     |
    // | You may obtain a copy of the License at                              |
    // | http://www.apache.org/licenses/LICENSE-2.0                           |
    // | Unless required by applicable law or agreed to in writing, software  |
    // | distributed under the License is distributed on an "AS IS" BASIS,    |
    // | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
    // | implied. See the License for the specific language governing         |
    // | permissions and limitations under the License.                       |
    // +----------------------------------------------------------------------+
    // | Author: Christian Stocker <chregu@bitflux.ch>                        |
    // +----------------------------------------------------------------------+

    // Fix &entity\n;
    $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
    $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/', '$1;', $data);
    $data = preg_replace('/(&#x*[0-9A-F]+);*/i', '$1;', $data);

    if (function_exists("html_entity_decode"))
    {
        $data = html_entity_decode($data);
    }
    else
    {
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);
        $data = strtr($data, $trans_tbl);
    }

    // Remove any attribute starting with "on" or xmlns
    $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#i', '$1>', $data);

    // Remove javascript: and vbscript: protocols
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#i', '$1=$2nojavascript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#i', '$1=$2novbscript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#', '$1=$2nomozbinding...', $data);

    // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#i', '$1>', $data);

    // Remove namespaced elements (we do not need them)
    $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

    do {
        // Remove really unwanted tags
        $old_data = $data;
        $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
    } while ($old_data !== $data);

    return $data;
}

$_GET  = xss_clean($_GET);
$_POST = xss_clean($_POST);
//==========================================================================================================================


//==========================================================================================================================
// extract($_GET); 명령으로 인해 page.php?_POST[var1]=data1&_POST[var2]=data2 와 같은 코드가 _POST 변수로 사용되는 것을 막음
// 081029 : letsgolee 님께서 도움 주셨습니다.
//--------------------------------------------------------------------------------------------------------------------------
$ext_arr = array ('PHP_SELF', '_ENV', '_GET', '_POST', '_FILES', '_SERVER', '_COOKIE', '_SESSION', '_REQUEST',
                  'HTTP_ENV_VARS', 'HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_POST_FILES', 'HTTP_SERVER_VARS',
                  'HTTP_COOKIE_VARS', 'HTTP_SESSION_VARS', 'GLOBALS');
$ext_cnt = count($ext_arr);
for ($i=0; $i<$ext_cnt; $i++) {
    // GET 으로 선언된 전역변수가 있다면 unset() 시킴
    if (isset($_GET[$ext_arr[$i]])) unset($_GET[$ext_arr[$i]]);
}
//==========================================================================================================================

// PHP 4.1.0 부터 지원됨
// php.ini 의 register_globals=off 일 경우
@extract($_GET);
@extract($_POST);
@extract($_SERVER);

// 완두콩님이 알려주신 보안관련 오류 수정
// $member 에 값을 직접 넘길 수 있음
$config = array();
$member = array();
$board  = array();
$group  = array();
$g4     = array();

// index.php 가 있는곳의 상대경로
// php 인젝션 ( 임의로 변수조작으로 인한 리모트공격) 취약점에 대비한 코드
// prosper 님께서 알려주셨습니다.
if (!$g4_path || preg_match("/:\/\//", $g4_path)) {
    echo "<meta http-equiv='content-type' content='text/html; charset={$g4['charset']}'>";
    echo "<h3>잘못된 방법으로 변수가 정의되었습니다. (g4_path)</h3>";
    echo "<a href=\"{$g4['path']}/install/\">설치하기</a>";
    exit;
}


$g4['path'] = $g4_path;


//==============================================================================
// g4_path unset
//------------------------------------------------------------------------------
if (isset($_GET['g4_path']) || isset($_POST['g4_path']) || isset($_COOKIE['g4_path'])) {
    unset($_GET['g4_path']);
    unset($_POST['g4_path']);
    unset($_COOKIE['g4_path']);
}
unset($g4_path);
//==============================================================================


include_once($g4['path'].'/config.php');  // 설정 파일
include_once($g4['path'].'/lib/common.lib.php'); // 공통 라이브러리

// config.php 가 있는곳의 웹경로
if (!$g4['url']) {
    $g4['url'] = 'http://' . $_SERVER['HTTP_HOST'];
    $dir = dirname($_SERVER['PHP_SELF']);
    if (!file_exists('config.php'))
        $dir = dirname($dir);
    $cnt = substr_count($g4['path'], '..');
    for ($i=2; $i<=$cnt; $i++)
        $dir = dirname($dir);
    $g4['url'] .= $dir;
}
// \ 를 / 롤 변경
$g4['url'] = strtr($g4['url'], "\\", "/");
// url 의 끝에 있는 / 를 삭제한다.
$g4['url'] = preg_replace("/\/$/", "", $g4['url']);

//==============================================================================
// 공통
//------------------------------------------------------------------------------
$dirname = dirname(__FILE__).'/';
$dbconfig_file = 'data/dbconfig.php';
if (file_exists($g4['path'].'/'.$dbconfig_file)) {
    include_once($g4['path'].'/'.$dbconfig_file);
    $connect_db = sql_connect(G4_MYSQL_HOST, G4_MYSQL_USER, G4_MYSQL_PASSWORD) or die('MySQL Connect Error!!!');
    $select_db = sql_select_db(G4_MYSQL_DB, $connect_db) or die('MySQL DB Error!!!');
} else {
    echo "<meta http-equiv='content-type' content='text/html; charset={$g4['charset']}'>";
    echo "<h3>DB 설정 파일이 존재하지 않습니다.<br>프로그램 설치 후 실행하시기 바랍니다.</h3>";
    echo "<a href=\"{$g4['path']}/install/\">설치하기</a>";
    exit;
}

//==============================================================================
// SESSION 설정
//------------------------------------------------------------------------------
ini_set("session.use_trans_sid", 0);    // PHPSESSID를 자동으로 넘기지 않음
ini_set("url_rewriter.tags",""); // 링크에 PHPSESSID가 따라다니는것을 무력화함 (해뜰녘님께서 알려주셨습니다.)

session_save_path($g4['path'].'/data/session');

if (isset($SESSION_CACHE_LIMITER))
    @session_cache_limiter($SESSION_CACHE_LIMITER);
else
    @session_cache_limiter("no-cache, must-revalidate");
//==============================================================================


//==============================================================================
// 공용 변수
//------------------------------------------------------------------------------
// 기본환경설정
// 기본적으로 사용하는 필드만 얻은 후 상황에 따라 필드를 추가로 얻음
$config = sql_fetch(" select * from {$g4['config_table']} ");

ini_set("session.cache_expire", 180); // 세션 캐쉬 보관시간 (분)
ini_set("session.gc_maxlifetime", 10800); // session data의 garbage collection 존재 기간을 지정 (초)
ini_set("session.gc_probability", 1); // session.gc_probability는 session.gc_divisor와 연계하여 gc(쓰레기 수거) 루틴의 시작 확률을 관리합니다. 기본값은 1입니다. 자세한 내용은 session.gc_divisor를 참고하십시오.
ini_set("session.gc_divisor", 100); // session.gc_divisor는 session.gc_probability와 결합하여 각 세션 초기화 시에 gc(쓰레기 수거) 프로세스를 시작할 확률을 정의합니다. 확률은 gc_probability/gc_divisor를 사용하여 계산합니다. 즉, 1/100은 각 요청시에 GC 프로세스를 시작할 확률이 1%입니다. session.gc_divisor의 기본값은 100입니다.

session_set_cookie_params(0, "/");
ini_set("session.cookie_domain", $g4['cookie_domain']);

@session_start();

//==============================================================================
// Mobile 모바일 설정
// 쿠키에 저장된 값이 모바일이라면 브라우저 상관없이 모바일로 실행
// 그렇지 않다면 브라우저의 HTTP_USER_AGENT 에 따라 모바일 결정
// G4_MOBILE_AGENT : config.php 에서 선언
//------------------------------------------------------------------------------
$is_mobile = false;
if (isset($_REQUEST['pc']))
    $is_mobile = false;
else if (isset($_REQUEST['mobile']))
    $is_mobile = true;
else if (isset($_SESSION['ss_is_mobile'])) 
    $is_mobile = $_SESSION['ss_is_mobile'];
else if (is_mobile())
    $is_mobile = true;

$_SESSION['ss_is_mobile'] = $is_mobile;
define('G4_IS_MOBILE', $is_mobile);
if (G4_IS_MOBILE) {
    include_once($g4['path'].'/lib/mobile.lib.php'); // 모바일 전용 라이브러리
    $g4['mobile_path'] = $g4['path'].'/'.$g4['mobile_dir'];
}
//==============================================================================

// 4.00.03 : [보안관련] PHPSESSID 가 틀리면 로그아웃한다.
if (isset($_REQUEST['PHPSESSID']) && $_REQUEST['PHPSESSID'] != session_id())
    goto_url($g4['bbs_path'].'/logout.php');

// QUERY_STRING
$qstr = '';

if (isset($_REQUEST['sca']))  {
    $sca = escape_trim($_REQUEST['sca']);
    $qstr .= '&amp;sca=' . urlencode($sca);
} else {
    $sca = "";
}

if (isset($_REQUEST['sfl']))  {
    $sfl = escape_trim($_REQUEST['sfl']);
    $qstr .= '&amp;sfl=' . urlencode($sfl); // search field (검색 필드)
} else {
    $sfl = "";
}


if (isset($_REQUEST['stx']))  { // search text (검색어)
    $stx = escape_trim($_REQUEST['stx']);
    $qstr .= '&amp;stx=' . urlencode($stx);
} else {
    $stx = "";
}

if (isset($_REQUEST['sst']))  {
    $sst = escape_trim($_REQUEST['sst']);
    $qstr .= '&amp;sst=' . urlencode($sst); // search sort (검색 정렬 필드)
} else {
    $sst = "";
}

if (isset($_REQUEST['sod']))  { // search order (검색 오름, 내림차순)
    $sod = preg_match("/^(asc|desc)$/i", $sod) ? $sod : '';
    $qstr .= '&amp;sod=' . urlencode($sod);
} else {
    $sod = "";
}

if (isset($_REQUEST['sop']))  { // search operator (검색 or, and 오퍼레이터)
    $sop = preg_match("/^(or|and)$/i", $sop) ? $sop : '';
    $qstr .= '&amp;sop=' . urlencode($sop);
} else {
    $sop = "";
}

if (isset($_REQUEST['spt']))  { // search part (검색 파트[구간])
    $spt = (int)$spt;
    $qstr .= '&amp;spt=' . urlencode($spt);
} else {
    $spt = "";
}

if (isset($_REQUEST['page'])) { // 리스트 페이지
    $page = (int)$_REQUEST['page'];
    $qstr .= '&amp;page=' . urlencode($page);
} else {
    $page = "";
}

if (isset($_REQUEST['w'])) {
    $w = substr($w, 0, 2);
} else {
    $w = "";
}

if (isset($_REQUEST['wr_id'])) {
    $wr_id = (int)$_REQUEST['wr_id'];
} else {
    $wr_id = 0;
}

if (isset($_REQUEST['bo_table'])) {
    $bo_table = escape_trim($_REQUEST['bo_table']);
    $bo_table = substr($bo_table, 0, 20);
} else {
    $bo_table = "";
}

// URL ENCODING
if (isset($_REQUEST['url'])) {
    $url = escape_trim($_REQUEST['url']);
    $urlencode = urlencode($url);
} else {
    $url = "";
    $urlencode = urlencode(escape_trim($_SERVER['REQUEST_URI']));
}

if (isset($_REQUEST['gr_id'])) {
    $gr_id = escape_trim($_REQUEST['gr_id']);
} else {
    $gr_id = "";
}
//===================================


// 자동로그인 부분에서 첫로그인에 포인트 부여하던것을 로그인중일때로 변경하면서 코드도 대폭 수정하였습니다.
if ($_SESSION['ss_mb_id']) { // 로그인중이라면
    $member = get_member($_SESSION['ss_mb_id']);

    // 오늘 처음 로그인 이라면
    if (substr($member['mb_today_login'], 0, 10) != $g4['time_ymd']) {
        // 첫 로그인 포인트 지급
        insert_point($member['mb_id'], $config['cf_login_point'], "{$g4['time_ymd']} 첫로그인", "@login", $member['mb_id'], $g4['time_ymd']);

        // 오늘의 로그인이 될 수도 있으며 마지막 로그인일 수도 있음
        // 해당 회원의 접근일시와 IP 를 저장
        $sql = " update {$g4['member_table']} set mb_today_login = '{$g4['time_ymdhis']}', mb_login_ip = '{$_SERVER['REMOTE_ADDR']}' where mb_id = '{$member['mb_id']}' ";
        sql_query($sql);
    }

} else {
    // 자동로그인 ---------------------------------------
    // 회원아이디가 쿠키에 저장되어 있다면 (3.27)
    if ($tmp_mb_id = get_cookie('ck_mb_id')) {

        $tmp_mb_id = substr(preg_replace("/[^a-zA-Z0-9_]*/", "", $tmp_mb_id), 0, 20);
        // 최고관리자는 자동로그인 금지
        if ($tmp_mb_id != $config['cf_admin']) {
            $sql = " select mb_password, mb_intercept_date, mb_leave_date, mb_email_certify from {$g4['member_table']} where mb_id = '{$tmp_mb_id}' ";
            $row = sql_fetch($sql);
            $key = md5($_SERVER['SERVER_ADDR'] . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $row['mb_password']);
            // 쿠키에 저장된 키와 같다면
            $tmp_key = get_cookie('ck_auto');
            if ($tmp_key == $key && $tmp_key) {
                // 차단, 탈퇴가 아니고 메일인증이 사용이면서 인증을 받았다면
                if ($row['mb_intercept_date'] == '' &&
                    $row['mb_leave_date'] == '' &&
                    (!$config['cf_use_email_certify'] || preg_match('/[1-9]/', $row['mb_email_certify'])) ) {
                    // 세션에 회원아이디를 저장하여 로그인으로 간주
                    set_session('ss_mb_id', $tmp_mb_id);

                    // 페이지를 재실행
                    echo "<script type='text/javascript'> window.location.reload(); </script>";
                    exit;
                }
            }
            // $row 배열변수 해제
            unset($row);
        }
    }
    // 자동로그인 end ---------------------------------------
}

// 첫방문 쿠키
// 1년간 저장
if (!get_cookie('ck_first_call'))     set_cookie('ck_first_call', $g4['server_time'], 86400 * 365);
if (!get_cookie('ck_first_referer'))  set_cookie('ck_first_referer', $_SERVER['HTTP_REFERER'], 86400 * 365);

// 회원, 비회원 구분
$is_member = $is_guest = false;
$is_admin = "";
if ($member['mb_id']) {
    $is_member = true;
    $is_admin = is_admin($member['mb_id']);
    $member['mb_dir'] = substr($member['mb_id'],0,2);
} else {
    $is_guest = true;
    $member['mb_id'] = "";
    $member['mb_level'] = 1; // 비회원의 경우 회원레벨을 가장 낮게 설정
}

//$member['mb_level_title'] = $g4['member_level'][$member['mb_level']]; // 권한명

$write = array();
$write_table = "";
if ($bo_table) {
    $board = sql_fetch(" select * from {$g4['board_table']} where bo_table = '$bo_table' ");
    if ($board['bo_table']) {
        set_cookie("ck_bo_table", $board['bo_table'], 86400 * 1);
        $gr_id = $board['gr_id'];
        $write_table = $g4['write_prefix'] . $bo_table; // 게시판 테이블 전체이름
        //$comment_table = $g4['write_prefix'] . $bo_table . $g4['comment_suffix']; // 코멘트 테이블 전체이름
        if (isset($wr_id) && $wr_id)
            $write = sql_fetch(" select * from $write_table where wr_id = '$wr_id' ");
    }
}

if ($gr_id) {
    $group = sql_fetch(" select * from {$g4['group_table']} where gr_id = '$gr_id' ");
}

if ($is_admin != 'super') {
    // 접근가능 IP
    $cf_possible_ip = trim($config['cf_possible_ip']);
    if ($cf_possible_ip) {
        $is_possible_ip = false;
        $pattern = explode("\n", $cf_possible_ip);
        for ($i=0; $i<count($pattern); $i++) {
            $pattern[$i] = trim($pattern[$i]);
            if (empty($pattern[$i]))
                continue;

            //$pat = "/({$pattern[$i]})/";
            $pattern[$i] = str_replace(".", "\.", $pattern[$i]);
            $pat = "/^{$pattern[$i]}/";
            $is_possible_ip = preg_match($pat, $_SERVER['REMOTE_ADDR']);
            if ($is_possible_ip)
                break;
        }
        if (!$is_possible_ip)
            die ("접근이 가능하지 않습니다.");
    }

    // 접근차단 IP
    $is_intercept_ip = false;
    $pattern = explode("\n", trim($config['cf_intercept_ip']));
    for ($i=0; $i<count($pattern); $i++) {
        $pattern[$i] = trim($pattern[$i]);
        if (empty($pattern[$i]))
            continue;

        $pattern[$i] = str_replace(".", "\.", $pattern[$i]);
        $pat = "/^{$pattern[$i]}/";
        $is_intercept_ip = preg_match($pat, $_SERVER['REMOTE_ADDR']);
        if ($is_intercept_ip)
            die ("접근 불가합니다.");
    }
}


//==============================================================================
// 스킨경로
//------------------------------------------------------------------------------
$skin_path          = skin_path();
$board_skin_path    = $skin_path.'/board/'.$board['bo_skin'];
$member_skin_path   = $skin_path.'/member/'.$config['cf_member_skin'];
$new_skin_path      = $skin_path.'/new/'.$config['cf_new_skin'];
$search_skin_path   = $skin_path.'/search/'.$config['cf_search_skin'];
$connect_skin_path  = $skin_path.'/connect/'.$config['cf_connect_skin'];
$poll_skin_path     = $skin_path.'/poll/basic';
if (isset($_GET['skin_dir']))
    $poll_skin_path = $skin_path.'/poll/'.$_GET['skin_dir'];
//==============================================================================


// 방문자수의 접속을 남김
include_once($g4['bbs_path'].'/visit_insert.inc.php');


// common.php 파일을 수정할 필요가 없도록 확장합니다.
$tmp = dir($g4['path'].'/extend');
while ($entry = $tmp->read()) {
    // php 파일만 include 함
    if (preg_match("/(\.php)$/i", $entry))
        include_once($g4['path'].'/extend/'.$entry);
}

// 자바스크립트에서 go(-1) 함수를 쓰면 폼값이 사라질때 해당 폼의 상단에 사용하면
// 캐쉬의 내용을 가져옴. 완전한지는 검증되지 않음
header("Content-Type: text/html; charset=utf-8");
$gmnow = gmdate("D, d M Y H:i:s") . " GMT";
header("Expires: 0"); // rfc2616 - Section 14.21
header("Last-Modified: " . $gmnow);
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: pre-check=0, post-check=0, max-age=0"); // HTTP/1.1
header("Pragma: no-cache"); // HTTP/1.0
?>