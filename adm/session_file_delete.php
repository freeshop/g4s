<?
$sub_menu = "100800";
include_once("./_common.php");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.", $g4[path]);

$g4['title'] = "세션파일 일괄삭제";
include_once("./admin.head.php");
?>

<div id="session_del">
    <p>
        완료 메세지가 나오기 전에 프로그램의 실행을 중지하지 마십시오.
    <p>
    <span id="ct">
    </span>
</div>

<?
include_once("./admin.tail.php");
flush();

if (!$dir=@opendir($g4['session_path'])) { 
  echo "세션 디렉토리를 열지못했습니다."; 
} 

$cnt=0;
while($file=readdir($dir)) { 

    if (!strstr($file,'sess_')) continue; 
    if (strpos($file,'sess_')!=0) continue; 

    $session_file = $g4['session_path'].'/'.$file;

    if (!$atime=@fileatime($session_file)) { 
        continue; 
    } 
    if (time() > $atime + (3600 * 6)) {  // 지난시간을 초로 계산해서 적어주시면 됩니다. default : 6시간전
        $cnt++;
        $return = unlink($session_file); 
        echo "<script>document.getElementById('ct').innerHTML += '{$session_file}<br/>';</script>\n";

        flush();

        if ($cnt%10==0)
            echo "<script>document.getElementById('ct').innerHTML = '';</script>\n";
    } 
} 
echo "<script>document.getElementById('ct').innerHTML += '세션데이터 {$cnt}건 삭제 완료.<br><br>프로그램의 실행을 끝마치셔도 좋습니다.';</script>\n";
?>
