<?
$sub_menu = "300100";
define('G4_CAPTCHA', 1);
include_once("./_common.php");

auth_check($auth[$sub_menu], 'w');

$token = get_token();

$g4['title'] = '게시판 복사';
$administrator = 1;
include_once($g4['path'].'/head.sub.php');
?>

<form id="fboardcopy" name="fboardcopy" method="post" action="./board_copy_update.php" onsubmit="return fboardcopy_check(this);">
<input type="hidden" id="bo_table" name="bo_table" value="<?=$bo_table?>">
<table>
<caption>기존 게시판을 새 게시판으로 복사</caption>
<tbody>
<tr>
    <th scope="col">원본 테이블명</th>
    <td><?=$bo_table?></td>
</tr>
<tr>
    <th scope="col"><label for="target_table">복사 테이블명<strong class="sound_only">필수</strong></label></th>
    <td><input type="text" id="target_table" name="target_table" maxlength="20" class="required alnum_" required title="복사할 TABLE"> 영문자, 숫자, _ 만 가능 (공백없이)</td>
</tr>
<tr>
    <th scope="col"><label for="target_subject">게시판 제목<strong class="sound_only">필수</strong></label></th>
    <td><input type="text" id="target_subject" name="target_subject" maxlength="120" class="required" value="[복사본] <?=$board['bo_subject']?>" required title="게시판 제목"></td>
</tr>
<tr>
    <th scope="col">복사 유형</th>
    <td>
        <input type="radio" id="copy_case" name="copy_case" value="schema_only" checked>
        <label for="copy_case">구조만</label>
        <input type="radio" id="copy_case2" name="copy_case" value="schema_data_both">
        <label for="copy_case2">구조와 데이터</label>
    </td>
</tr>
</tbody>
</table>

<? echo captcha_html(); ?>

<div class="btn_confirm">
    <input type="submit" value="복사">
    <input type="button" value="창닫기" onclick="window.close();">
</div>

</form>

<script>
function fboardcopy_check(f)
{
    if (f.bo_table.value == f.target_table.value) {
        alert("원본 테이블명과 복사할 테이블명이 달라야 합니다.");
        return false;
    }

    <? echo chk_captcha_js(); ?>

    return true;
}
</script>


<?
include_once($g4['path'].'/tail.sub.php');
?>
