<?
include_once('./_common.php');

$g4['title'] = '패스워드 입력';

switch ($w) {
    case 'u' :
        $action = './write.php';
        break;
    case 'd' :
        $action = './delete.php';
        break;
    case 'x' :
        $action = './delete_comment.php';
        break;
    case 's' :
        // 패스워드 창에서 로그인 하는 경우 관리자 또는 자신의 글이면 바로 글보기로 감
        if ($is_admin || ($member['mb_id'] == $write['mb_id'] && $write['mb_id']))
            goto_url('./board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id);
        else
            $action = './password_check.php';
        break;
    default : 
        alert('w 값이 제대로 넘어오지 않았습니다.');
}

include_once($g4['path'].'/head.sub.php');

if ($board['bo_include_head']) { @include ($board['bo_include_head']); }
if ($board['bo_content_head']) { echo stripslashes($board['bo_content_head']); }

include_once($member_skin_path.'/password.skin.php');

if ($board['bo_content_tail']) { echo stripslashes($board['bo_content_tail']); }
if ($board['bo_include_tail']) { @include ($board['bo_include_tail']); }

include_once($g4['path'].'/tail.sub.php');
?>
