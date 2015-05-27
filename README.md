kintone Rest API PHP Class
=====================

This PHP class simplifies kintone API programming. (Under the present creation) 

##Sample

'php:sampl.php'

<?php
 
 require_once('./kintone.php');
 
$kintone_subdomain  = "<- kintone your subdomain ->";
$kintone_app_id  = <- kintone your apprication id ->;
$kintone_app_token  = <- kintone your apprication token ->;

$limit  = 15;
$offset = 0;
$exec   = "";
if(isset($_REQUEST['offset'])) $offset = intval($_REQUEST['offset']);
if(isset($_REQUEST['exec']))   $exec   = htmlspecialchars($_REQUEST['exec']);

// Form Data Init
$errmsg = "";
$msg     = "";
$category = "";
$title    = "";
$question = "";

$kintone = new Ktn\DataAccess($kintone_subdomain, $kintone_app_id, $kintone_app_token, true);
if($kintone->errMsg != "") $errmsg = $kintone->errMsg;

// Question Data Add
if($exec == "add" && $kintone->errMsg == ""){
    if(isset($_REQUEST['category'])) $category = htmlspecialchars($_REQUEST['category']);
    if(isset($_REQUEST['title']))    $title    = htmlspecialchars($_REQUEST['title']);
    if(isset($_REQUEST['question'])) $question = htmlspecialchars($_REQUEST['question']);
    if($category != "" && $title != "" && $question != ""){
        // Add Request
        $record  = array(
            '公開・非公開' => array('value' => "Webに非公開"),
            '質問区分' => array('value' => $category),
            '質問タイトル' => array('value' => $title),
            '質問内容' => array('value' => $question)
        );
        if($kintone->postRecode($record)){
            if($kintone->errMsg != ""){
                $errmsg = $kintone->errMsg;
            }else{
                $category = "";
                $title    = "";
                $question = "";
               $msg = "質問を受付けました！回答が掲載されるまで少しお待ちください。m(__)m";
            }
        }else{
            $errmsg = "登録に失敗しました！再度登録してください。";
        }
    }else{
        $errmsg = "全ての項目に入力してください！";
    }
}

$query = "公開・非公開 in (\"Webに公開\")  order by 登録番号 desc";
$json = array();
$count = 0;
if($kintone->getRecodes($query, $limit, $offset)){
    $json = $kintone->responseData;
    $count = count($json['records']);
}
?>

 <dt>
<img src="images/q.png" alt="Q"/>
<strong><?php echo $value['質問区分']['value']; ?>：<?php echo $value['質問タイトル']['value']; ?></strong><br />
 <?php echo $value['質問内容']['value']; ?>（<?php echo $value['作成日時']['value']; ?>）
 </dt>
 
 <dd>
 <img src="images/a.png" alt="A"/> <?php echo $value['回答内容']['value']; ?>（<?php echo $value['更新日時']['value']; ?>）
 </dd>

'
