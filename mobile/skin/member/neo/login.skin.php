<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<form name="flogin" method="post" action="<?=$login_action_url?>" onsubmit="return flogin_submit(this);">
<input type="hidden" name="url" value='<?=$login_url?>'>

<fieldset>
    <label for="login_mb_id">회원아이디</label>
    <input type="text" id="login_mb_id" name="mb_id" maxLength="20" size="15" required title="회원아이디">
    <label for="login_mb_password">패스워드</label>
    <input type="password" id="login_mb_password" name="mb_password" maxLength="20" size="15" required title="패스워드">
    <input type="checkbox" id="login_auto_login" name="auto_login">
    <label for="login_auto_login">자동로그인</label>
    <input type="submit" value="로그인">
</fieldset>

<section>
    <h2>회원로그인 안내</h2>
    <p>
        회원아이디 및 패스워드가 기억 안나실 때는 아이디/패스워드 찾기를 이용하십시오.<br>
        아직 회원이 아니시라면 회원으로 가입 후 이용해 주십시오.
    </p>
    <a href="<?=$g4['bbs_url']?>/password_lost.php" id="login_password_lost" target="win_password_lost">아이디/패스워드 찾기</a>
    <a href="./register.php">회원가입</a>
</section>

</form>

<script>
$(function(){
    $("#login_auto_login").click(function(){
        if (this.checked) {
            this.checked = confirm("자동로그인을 사용하시면 다음부터 회원아이디와 패스워드를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?");
        }
    });
});

function flogin_submit(f)
{
    return true;
}
</script>
