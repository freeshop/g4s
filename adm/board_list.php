<?
$sub_menu = "300100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

if (!isset($board['bo_use'])) {
    // 게시판 사용 필드 추가
    // both : pc, mobile 둘다 사용
    // pc : pc 전용 사용
    // mobile : mobile 전용 사용
    // none : 사용 안함
    sql_query(" ALTER TABLE  `{$g4['board_table']}` ADD  `bo_use` ENUM(  'both',  'pc',  'mobile',  'none' ) NOT NULL DEFAULT  'both' AFTER  `bo_subject` ", false);
}

$sql_common = " from {$g4['board_table']} a ";
$sql_search = " where (1) ";

if ($is_admin != "super") {
    $sql_common .= " , {$g4['group_table']} b ";
    $sql_search .= " and (a.gr_id = b.gr_id and b.gr_admin = '{$member['mb_id']}') ";
}

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        case "bo_table" :
            $sql_search .= " ($sfl like '$stx%') ";
            break;
        case "a.gr_id" :
            $sql_search .= " ($sfl = '$stx') ";
            break;
        default :
            $sql_search .= " ($sfl like '%$stx%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "a.gr_id, a.bo_table";
    $sod = "asc";
}
$sql_order = " order by $sst $sod ";

$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * {$sql_common} {$sql_search} {$sql_order} limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '';
if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';

$g4['title'] = '게시판관리';
include_once('./admin.head.php');

$colspan = 8;
?>

<script>
var list_update_php = 'board_list_update.php';
var list_delete_php = 'board_list_delete.php';
</script>

<form id="fsearch" name="fsearch" method="get">
<fieldset>
    <legend>게시판 검색</legend>
    <span>
        <?=$listall?>
        생성된 게시판수 <?=number_format($total_count)?>개
    </span>
    <label for="sfl">검색대상</label>
    <select id="sfl" name="sfl">
        <option value="bo_table" <?=get_selected($_GET['sfl'], "bo_subject", true);?>>TABLE</option>
        <option value="bo_subject" <?=get_selected($_GET['sfl'], "bo_subject");?>>제목</option>
        <option value="a.gr_id" <?=get_selected($_GET['sfl'], "a.gr_id");?>>그룹ID</option>
    </select>
    <input type="text" name="stx" class="required" required value="<?=$stx?>" title="검색어(필수)">
    <input type="submit" class="fieldset_submit" value="검색">
</fieldset>
</form>

<?if ($is_admin == 'super') {?>
<div id="btn_add">
    <a href="./board_form.php" id="bo_add">게시판 추가</a>
</div>
<?}?>

<form id="fboardlist" name="fboardlist" method="post" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">
<table class="tbl_bo_list">
<caption>
생성된 게시판 목록
<p>
    여러개의 게시판 설정을 한번에 바꾸실 때는 게시판 체크기능을 이용하세요.
</p>
</caption>
<thead>
<tr>
    <th scope="col"><input type="checkbox" id="chkall" name="chkall" value="1" title="현재 페이지 게시판 전체선택" onclick="check_all(this.form)"></th>
    <th scope="col"><?=subject_sort_link('a.gr_id')?>그룹</a></th>
    <th scope="col"><?=subject_sort_link('bo_table')?>TABLE</a></th>
    <th scope="col"><?=subject_sort_link('bo_skin', '', 'desc')?>스킨</a></th>
    <th scope="col"><?=subject_sort_link('bo_subject')?>제목</a></th>
    <th scope="col">읽기P<span class="sound_only">포인트</span></th>
    <th scope="col">쓰기P<span class="sound_only">포인트</span></th>
    <th scope="col">댓글P<span class="sound_only">포인트</span></th>
    <th scope="col">다운P<span class="sound_only">포인트</span></th>
    <th scope="col">검색<br>사용</th>
    <th scope="col">검색<br>순서</th>
    <th scope="col">관리</th>
</tr>
</thead>
<tbody>
<?
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $one_update = '<a href="./board_form.php?w=u&amp;bo_table='.$row['bo_table'].'&amp;'.$qstr.'">수정</a>';
    $one_copy = '<a href="./board_copy.php?bo_table='.$row['bo_table'].'" class="board_copy" target="win_board_copy">복사</a>';
?>

<tr>
    <td>
        <input type="checkbox" id="chk_<?=$i?>" name="chk[]" value="<?=$i?>" title="<?=get_text($row['bo_subject'])?> 게시판선택">
    </td>
    <td>
        <?if ($is_admin == 'super'){?>
            <?=get_group_select("gr_id[$i]", $row['gr_id'])?>
        <?}else{?>
            <input type="hidden" name="gr_id[<?=$i?>]" value="<?=$row['gr_id']?>"><?=$row['gr_subject']?>
        <?}?>
    </td>
    <td class="td_boid">
        <input type="hidden" name="board_table[<?=$i?>]" value="<?=$row['bo_table']?>">
        <a href="<?=$g4['bbs_path']?>/board.php?bo_table=<?=$row['bo_table']?>"><?=$row['bo_table']?></a>
    </td>
    <td>
        <?=get_skin_select("board", "bo_skin_$i", "bo_skin[$i]", $row['bo_skin']);?>
    </td>
    <td><input type="text" id="bo_subject[<?=$i?>]" name="bo_subject[<?=$i?>]" class="required" value="<?=get_text($row['bo_subject'])?>" title="게시판제목" size="10" required="required"></td>
    <td><input type="text" name="bo_read_point[<?=$i?>]" value="<?=$row['bo_read_point']?>" size="1" title="읽기"></td>
    <td><input type="text" name="bo_write_point[<?=$i?>]" value="<?=$row['bo_write_point']?>" size="1" title="쓰기"></td>
    <td><input type="text" name="bo_comment_point[<?=$i?>]" value="<?=$row['bo_comment_point']?>" size="1" title="댓글"></td>
    <td><input type="text" name="bo_download_point[<?=$i?>]" value="<?=$row['bo_download_point']?>" size="1" title="다운"></td>
    <td><input type="checkbox" id="bo_use_search_<?=$i?>" name="bo_use_search[<?=$i?>]" <?=$row['bo_use_search']?"checked":""?> value="1" title="검색사용"></td>
    <td><input type="text" id="bo_order_search_<?=$i?>" name="bo_order_search[<?=$i?>]" value="<?=$row['bo_order_search']?>" size="1" title="검색순서"></td>
    </td>
    <td>
        <select id="bo_use_<?=$i?>" name="bo_use[<?=$i?>]">
        <option value="both" <?=get_selected($row['bo_use'], 'both', true);?>>양쪽</option>
        <option value="pc" <?=get_selected($row['bo_use'], 'pc');?>>PC</option>
        <option value="mobile" <?=get_selected($row['bo_use'], 'mobile');?>>모바일</option>
        <option value="none" <?=get_selected($row['bo_use'], 'none');?>>미사용</option>
        </select>
    </td>
    <td><?=$one_update?> <?=$one_copy?></td>
</tr>
<?
}
if ($i == 0)
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
?>
</tbody>
</table>

<div class="btn_list">
    <input type="submit" name="btn_submit" onclick="document.pressed=this.value" value="선택수정">
    <?if ($is_admin == 'super') {?>
    <input type="submit" name="btn_submit" onclick="document.pressed=this.value" value="선택삭제">
    <a href="./board_form.php">게시판추가</a>
    <?}?>
</div>

<?
$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr.'&amp;page=');
?>
<div class="pg">
    <?=$pagelist?>
</div>

</form>

<script>
function fboardlist_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    return true;
}

$(function(){
    $(".board_copy").click(function(){
        window.open(this.href, "win_board_copy", "left=100,top=100,width=550,height=450");
        return false;
    });
});
</script>

<?
include_once('./admin.tail.php');
?>
