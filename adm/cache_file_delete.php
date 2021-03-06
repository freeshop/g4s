<?
$sub_menu = "100900";
include_once("./_common.php");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.", $g4[path]);

$g4['title'] = "캐시파일 일괄삭제";
include_once("./admin.head.php");
?>

<div id="cache_del">
    <p>
        완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
    <p>
    <span id="delete_message">
    </span>
</div>

<?
include_once("./admin.tail.php");
flush();

if (!$dir=@opendir($g4['cache_latest_path'])) { 
  echo "최신글 캐시디렉토리를 열지못했습니다."; 
} 

$cnt=0;
while($file=readdir($dir)) { 
    if ($file=='.' || $file=='..') continue;

    $cache_file = $g4['cache_latest_path'].'/'.$file;

    if (!$atime=@fileatime($cache_file)) 
        continue; 

    $cnt++;
    $return = unlink($cache_file); 
    echo "<script>document.getElementById('delete_message').innerHTML += '{$cache_file}<br/>';</script>\n";

    flush();

    if ($cnt%10==0) 
        echo "<script>document.getElementById('delete_message').innerHTML = '';</script>\n";
} 
echo "<script>document.getElementById('delete_message').innerHTML += '최신글 캐시파일 {$cnt}건 삭제 완료.<br><br>프로그램의 실행을 끝마치셔도 좋습니다.';</script>\n";
?>
