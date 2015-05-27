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

<html lang="ja">
<head>
<meta charset="UTF-8">
<title>sample</title>
</head>
<body>

<?php if($count == 0){ ?>
<h1>よくある質問（まだ登録されていません）</h1>
<?php }elseif($count <= $limit){ ?>
<h1>よくある質問（全<?php echo $count; ?>件表示）</h1>
<?php }else{ ?>
<h1>よくある質問（全<?php echo $count; ?>件中、<?php echo ($offset + 1); ?>件目から<?php echo $limit; ?>件表示）</h1>
<?php } ?>

<div>
<dl>
<?php
foreach($json['records'] as $value){
    if($value['回答内容']['value'] == ""){
        $value['回答内容']['value'] = "回答は少しお待ちください。m(__)m";
        $value['更新日時']['value'] = "準備中";
    }
?>
 <dt>
Q <strong><?php echo $value['質問区分']['value']; ?>：<?php echo $value['質問タイトル']['value']; ?></strong><br />
<?php echo $value['質問内容']['value']; ?>（<?php echo $value['作成日時']['value']; ?>）
</dt>
<dd>
A <?php echo $value['回答内容']['value']; ?>（<?php echo $value['更新日時']['value']; ?>）
</dd>
<?php
}
?>
</dl>
</div>

<h1>QandAで質問する</h1>
<?php if($errmsg != ""){ ?>
<p style="color:red"><?php echo $errmsg; ?></p>
<?php }elseif($msg != ""){ ?>
<p style="color:blue"><?php echo $msg; ?></p>
<?php } ?>

<form action="faq.php" method="post">
<p>質問区分：<select name="category">
<option value="技術の質問"<?php if($category == "技術の質問") echo " selected"; ?>>技術の質問</option>
<option value="運用の質問"<?php if($category == "運用の質問") echo " selected"; ?>>運用の質問</option>
<option value="コストの質問"<?php if($category == "コストの質問") echo " selected"; ?>>コストの質問</option>
<option value="その他"<?php if($category == "その他") echo " selected"; ?>>その他</option>
</select></p>
<p>質問タイトル：<input type="text" name="title" size="64" value="<?php echo $title; ?>" /></p>
<p>質問内容：<br /><textarea name="question" rows="4" cols="40"><?php echo $question; ?></textarea></p>
<input type="hidden" name="exec" value="add" />
<input type="submit" value="質問を投稿する" />
</form>

</body>
</html>