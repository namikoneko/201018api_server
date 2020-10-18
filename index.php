<?php
require_once 'idiorm.php';
ORM::configure('sqlite:./data.db');
ORM::configure('return_result_sets', true);
require 'flight/Flight.php';

// post ##################################################
Flight::route('/post', function(){
    $rows = ORM::for_table('post')->order_by_desc('updated')->find_many();
    $i = 0;
    foreach($rows as $row){
        $list[$i]["id"] = $row["id"];
        $list[$i]["title"] = $row["title"];
        $list[$i]["text"] = $row["text"];
        $i++;
    }
    header("Content-Type: application/json; charset=utf-8");
    $arr = Flight::json($list);
    echo $arr;
});

// post_thread ##################################################
Flight::route('/post/@id', function($id){
    $row = ORM::for_table('post')->find_one($id);
    $list["title"] = $row->title;
    $list["text"] = $row->text;

//    $i = 0;
//    foreach($rows as $row){
//        $list[$i]["id"] = $row["id"];
//        $list[$i]["title"] = $row["title"];
//        $list[$i]["text"] = $row["text"];
//        $i++;
//    }
    header("Content-Type: application/json; charset=utf-8");
    $arr = Flight::json($list);
    echo $arr;
});

// post_ins ##################################################
Flight::route('/inspost', function(){
    $row = ORM::for_table('post')->create();
    $row->title = Flight::request()->data->title;
    $row->updated = time();
    $row->save();
});

// post_upd ##################################################
Flight::route('/postupd/@id', function($id){
    $row = ORM::for_table('post')->find_one($id);
    $list['title'] = $row->title;
    header("Content-Type: application/json; charset=utf-8");
    $arr = Flight::json($list);
    echo $arr;
});

// post_updexe ##################################################
Flight::route('/postupdexe', function(){
	$row = ORM::for_table('post')->find_one(Flight::request()->data->id);
	$row->title = Flight::request()->data->title;
	$row->save();
});

// post_del ##################################################
Flight::route('/postdel/@id', function($id){
	$row = ORM::for_table('post')->find_one($id);
	$row->delete();
	$rows = ORM::for_table('map')->where("postid",$id)->find_many();
	$rows->delete();
});

// post_up ##################################################
Flight::route('/postup/@id', function($id){
    $row = ORM::for_table('post')->find_one($id);
    $row->updated = time();
    $row->save();
    Flight::redirect('/post');
});

// thread ##################################################
Flight::route('/thread/@id', function($id){
    $rows = ORM::for_table('map')->where("postid",$id)->find_many();
    $i = 0;
    foreach($rows as $row){
	$mapid[] = $row->threadid;
        $i++;
    }
    $rows = ORM::for_table('thread')->where_in("id",$mapid)->order_by_desc('updated')->find_many();
    $i = 0;
//    $testarr[] = "tagA";
//    $testarr[] = "tagB";
    foreach($rows as $row){
        $list[$i]["id"] = $row["id"];
        $list[$i]["title"] = $row["title"];
        $list[$i]["text"] = $row["text"];
        //$list[$i]["post"] = $row->id;
        $rowsA = ORM::for_table('map')->where('threadid', $row->id)->find_many();
        $j = 0;
        $postid = array();
        foreach($rowsA as $rowA){
            $postid[$j] = $rowA->postid;
            $j++;
        }
//            $list[$i]["post"] = $postid;
        $rowsB = ORM::for_table('post')->where_in("id",$postid)->where_not_equal("id",$id)->find_many();
        $k = 0;
        foreach($rowsB as $rowB){
            $list[$i]["post"][$k]["id"] = $rowB->id;
            $list[$i]["post"][$k]["title"] = $rowB->title;
            $k++;
        }
        $i++;
    }

    header("Content-Type: application/json; charset=utf-8");
//    try{
//      $arr = Flight::json($list);
//    }catch(Exception $e){
//        $list[0]["id"] = 999;
//        $list[0]["title"] = "not existed";
//        $list[0]["text"] = "not existed";
//        $list[0]["post"] = "not existed";
//      $arr = Flight::json($list);
//    }
      $arr = Flight::json($list);
    echo $arr;
});

// thread_up ##################################################
Flight::route('/threadup/@postid/@id', function($postid,$id){
    $row = ORM::for_table('thread')->find_one($id);
    $row->updated = time();
    $row->save();
    Flight::redirect('/thread/' . $postid);
});

// insthread ##################################################
Flight::route('/insthread', function(){
    $postid = Flight::request()->data->postid;
    $row = ORM::for_table('thread')->create();
    $row->title = Flight::request()->data->title;
    $row->text = Flight::request()->data->text;
    $row->updated = time();
    $row->save();

    $max = ORM::for_table('thread')->max('id');
    $row = ORM::for_table('map')->create();
    $row->postid = $postid;
    $row->threadid = $max;
    $row->save();
    Flight::redirect('/thread/' . $postid);
});

// thread_upd ##################################################
Flight::route('/thread/upd/@id', function($id){
    $row = ORM::for_table('thread')->find_one($id);
    $list['title'] = $row->title;
    $list['text'] = $row->text;
    header("Content-Type: application/json; charset=utf-8");
    $arr = Flight::json($list);
    echo $arr;
});

// thread_updexe ##################################################
Flight::route('/threadupdexe', function(){
    //echo "updexe";

	$row = ORM::for_table('thread')->find_one(Flight::request()->data->id);
	$row->title = Flight::request()->data->title;
	$row->text = Flight::request()->data->text;
	$row->save();
	//Flight::redirect('/post/' . Flight::request()->data->postid);
});

// thread_del ##################################################
Flight::route('/thread/del/@id', function($id){
	$row = ORM::for_table('thread')->find_one($id);
	$row->delete();
	$rows = ORM::for_table('map')->where("threadid",$id)->find_many();
	$rows->delete();
});

// offpost ##################################################
Flight::route('/offpost/@id', function($id){
    $rows = ORM::for_table('map')->where("threadid",$id)->find_many();
    $i = 0;
    foreach($rows as $row){
	$mapid[] = $row->postid;
        $i++;
    }
    $rows = ORM::for_table('post')->where_in("id",$mapid)->find_many();
    $i = 0;
    foreach($rows as $row){
        $list[$i]["id"] = $row["id"];
        $list[$i]["title"] = $row["title"];
        //$list[$i]["text"] = $row["text"];
        $i++;
    }

    header("Content-Type: application/json; charset=utf-8");
    $arr = Flight::json($list);
    echo $arr;
});

// test ##################################################
Flight::route('/test', function(){
    //$rows = ORM::for_table('map')->raw_query('SELECT p.title FROM map m JOIN post p ON m.postid = p.id WHERE m.postid = 1')->find_many();
    $rows = ORM::for_table('map')->raw_query('SELECT p.id,p.title FROM map m JOIN post p ON m.postid = p.id')->find_many();

    $i = 0;
    foreach($rows as $row){
	$ptitle[] = $row->title;
        $i++;
    }
    $arr = Flight::json($ptitle);
    echo $arr;

//    header("Content-Type: application/json; charset=utf-8");
//    try{
//      $arr = Flight::json($list);
//    }catch(Exception $e){
//        $list[0]["id"] = 999;
//        $list[0]["title"] = "error";
//      $arr = Flight::json($list);
//    }
//    echo $arr;
});

// addpost ##################################################
Flight::route('/addpost/@id', function($id){
    $rows = ORM::for_table('map')->where("threadid",$id)->find_many();
    $i = 0;
    foreach($rows as $row){
	$mapid[] = $row->postid;
        $i++;
    }
    $rows = ORM::for_table('post')->where_not_in("id",$mapid)->find_many();
    $i = 0;
    foreach($rows as $row){
        $list[$i]["id"] = $row["id"];
        $list[$i]["title"] = $row["title"];
        //$list[$i]["text"] = $row["text"];
        $i++;
    }

    header("Content-Type: application/json; charset=utf-8");
    try{
      $arr = Flight::json($list);
    }catch(Exception $e){
        $list[0]["id"] = 999;
        $list[0]["title"] = "error";
      $arr = Flight::json($list);
    }
    echo $arr;
});

// offpost_updexe ##################################################
Flight::route('/offpostexe/@postid/@id', function($postid,$id){
    $row = ORM::for_table('map')->where(array(
                'postid' => $postid,
                'threadid' => $id
            ))->find_many();
    $row->delete();
    Flight::redirect('/offpost/' . $id);
});

// addpost_updexe ##################################################
Flight::route('/addpostexe/@postid/@id', function($postid,$id){
    if($postid != 999){
      $row = ORM::for_table('map')->create();
      $row->postid = $postid;
      $row->threadid = $id;
      $row->save();
    }
    Flight::redirect('/addpost/' . $id);
});

// addtag ##################################################
Flight::route('/tagadd/@id', function($id){
    $rows = ORM::for_table('map')->where("threadid",$id)->find_many();
    $i = 0;
    foreach($rows as $row){
	$mapid[] = $row->postid;
        $i++;
    }
    $rows = ORM::for_table('tag')->where_in("id",$mapid)->find_many();
    $i = 0;
    foreach($rows as $row){
        $list[$i]["id"] = $row["id"];
        $list[$i]["title"] = $row["title"];
        $list[$i]["text"] = $row["text"];
        $i++;
    }

    header("Content-Type: application/json; charset=utf-8");
    $arr = Flight::json($list);
    echo $arr;
});

// up ##################################################
Flight::route('/up', function(){
	//$results = ORM::for_table('test')->find_one(Flight::request()->query->id);

	$title = Flight::request()->query->title;
	$cat = Flight::request()->query->cat;

	$results = ORM::for_table('test');

	if($title){
		$results = $results->where_like('title',"%$title%");
	}

	if($cat){
		$results = $results->where_like('cat',"%$cat%");
	}

	$results = $results->order_by_desc('updated')->find_many();

	$myId_i = 0;
	$i = 0;
	foreach($results as $result){
		$ids[] = $result->id;
		// 配列の何番目か調べる
		if(Flight::request()->query->id == $result->id){
			$myId_i = $i;
		}
		$i++;
	}

	// 1番目のレコードでなければ
	if($myId_i != 0){
		$results[$myId_i]->updated = ORM::for_table('test')->find_one($ids[($myId_i - 1)])->updated + 1;
	}

	$results->save();
	Flight::redirect('?title=' . $title . '&cat=' . $cat);
});

// down ##################################################
Flight::route('/down', function(){

	$title = Flight::request()->query->title;
	$cat = Flight::request()->query->cat;

	$results = ORM::for_table('test');

	if($title){
		$results = $results->where_like('title',"%$title%");
	}

	if($cat){
		$results = $results->where_like('cat',"%$cat%");
	}

	$results = $results->order_by_desc('updated')->find_many();

	$i = 0;
	foreach($results as $result){
		$ids[] = $result->id;
		// 配列の何番目か調べる
		if(Flight::request()->query->id == $result->id){
			$myId_i = $i;
		}
		$i++;
	}

	// 最後のレコードでなければ
	$records = $results->count();
	if($myId_i != $records - 1){
		$results[$myId_i]->updated = ORM::for_table('test')->find_one($ids[($myId_i + 1)])->updated - 1;
	}
	$results->save();
	Flight::redirect('?title=' . $title . '&cat=' . $cat);
});

// cal_ins ##################################################
Flight::route('/cal_ins', function(){
	$y = Flight::request()->query->y;
	$m = Flight::request()->query->m;
	$m = substr($m + 100,1,2); 
	$d = Flight::request()->query->d;
	$d = substr($d + 100,1,2); 

	Flight::render('header', array('heading' => ''), 'header_content');
	Flight::render('cal_ins', array('y' => $y,'m' => $m,'d' => $d), 'body_content');
	Flight::render('layout', array('title' => 'todo'));
	//echo $d;
});
// cal_ins_exe ##################################################
Flight::route('/cal_ins_exe', function(){
	$result = ORM::for_table('test')->create();
	$result->date = Flight::request()->data->date;
	$result->title = Flight::request()->data->title;
	$result->text = Flight::request()->data->text;
	$result->save();
	Flight::redirect('/');
});
// cal_upd ##################################################
Flight::route('/cal_upd', function(){
	$id = Flight::request()->query->id;
	$results = ORM::for_table('test')->find_one($id);
	$date = $results->date;
	$title = $results->title;
	$text = $results->text;
	Flight::render('header', array('heading' => ''), 'header_content');
	Flight::render('cal_upd', array('date' => $date,'title' => $title,'text' => $text,'id' => $id), 'body_content');
	Flight::render('layout', array('title' => 'todo'));
});
// cal_upd_exe ##################################################
Flight::route('/cal_upd_exe', function(){
	$results = ORM::for_table('test')->find_one(Flight::request()->data->id);
	$results->date = Flight::request()->data->date;
	$results->title = Flight::request()->data->title;
	$results->text = Flight::request()->data->text;
	$results->save();
	Flight::redirect('/cal');
});
// cal_del ##################################################
Flight::route('/cal_del', function(){
	$results = ORM::for_table('test')->find_one(Flight::request()->query->id);
	$results->delete();
	Flight::redirect('/cal');
});
// cal ##################################################
Flight::route('/cal', function(){

	// クエリ
	$title = Flight::request()->query->title;
	$text = Flight::request()->query->text;

	//$results = ORM::for_table('test');
	//$results = $results->find_many();

	$ymd_now = date("Ymd");
	if(!empty(Flight::request()->query->y)){
		$y = Flight::request()->query->y;
		$m = Flight::request()->query->m;
	}else{
		$ym_now = date("Ym");
		$y = substr($ym_now,0,4);
		$m = substr($ym_now,4,2);
	}
	$d = 1;

	$str = "";
	$str .= "<table>";
	$str .= "<tr>";

	// 1日までのtdタグを表示
	$wd1 = date("w",mktime(0,0,0,$m,1,$y));
	for($i = 1;$i <= $wd1; $i++){
		$str .= "<td></td>";
	}

	// 1日から末日までを表示
	while(checkdate($m,$d,$y)){
		$str .= "<td";
		if(($y . $m . $d) == $ymd_now){
			$str .= " id='today'";
		}
		$str .= "><a href='cal_ins?y=$y&m=$m&d=$d'>$d</a>";
		$dStr = substr(($d + 100),1,2);

	//$results = ORM::for_table('test')->where("date",($y . "-" . $m . "-" . $dStr))->find_many();
	$results = ORM::for_table('test')->where("date",($y . "-" . $m . "-" . $dStr));
	//$results = $results->where("date",($y . "-" . $m . "-" . $dStr))->find_many();

	// titleがあれば検索
	if(!empty($title)){
		//単純検索title
		$results = $results->where_like('title',"%$title%");
	}

	// textがあれば検索
	if(!empty($text)){
		//単純検索text
		$results = $results->where_like('text',"%$text%");
	}
	$results = $results->find_many();

	foreach($results as $result){
		$str .= "<br>";
		$str .= "<a href='cal_upd?id=";
		$str .= $result->id;
		$str .= "'>" . $result->title . "</a>";
	}

	$str .= "</td>";

		if(date("w",mktime(0,0,0,$m,$d,$y)) == 6){
			$str .= "</tr>";
			if(checkdate($m,$d + 1,$y)){
				$str .= "<tr>";
			}
		}
		$d++;
	}
	// 最終週の土曜日までのtdタグを表示
	$wdx = date("w",mktime(0,0,0,$m + 1,0,$y));
	for($i = 1;$i < 7 - $wdx;$i++){
		$str .= "<td></td>";
	}
	$str .= "</tr>";
	$str .= "</table>";

	// 前月へ
	if($m == 1){
		$y--;
		$str .= "<a class='button' href='?y=" . $y . "&m=12'>previous</a>";
	}else{
		$str .= "<a class='button' href='?y=" . $y . "&m=" . ($m - 1) . "'>previous</a> ";
	}

	// 次月へ
	if($m == 12){
		$y++;
		$str .= "<a class='button' href='?y=" . $y . "&m=1'>next</a>";
	}else{
		$str .= "<a class='button' href='?y=" . $y . "&m=" . ($m + 1) . "'>next</a> ";
	}

	// 今月
	$str .= "<a class='button' href='/php/xdomain/181111'>now</a> ";

	//echo $str;
	$heading = $y . "-" . $m;
	Flight::render('header', array('heading' => $heading), 'header_content');
	Flight::render('cal', array('str' => $str,'heading' => $heading), 'body_content');
	Flight::render('layout', array('title' => 'cal'));
});

// list ##################################################
Flight::route('/calendar', function(){
	//echo "<a href='index.php?func=ins'>insert</a><br>";
	$str = "";
	//$str .= "<a href='ins'>insert</a><br>";

	/*
	// ページング
	if(isset(Flight::request()->query->page)){
		$page = Flight::request()->query->page;
	}else{
		$page = 1;
	}

	$records = ORM::for_table('test')->count();
	$per_page = 15;
	$offset = ($page - 1) * $per_page;
	*/

	// クエリ
	$title = Flight::request()->query->title;
	$cat = Flight::request()->query->cat;
	$q_all = Flight::request()->query->q_all;

	$results = ORM::for_table('test');
	// titleがあれば検索
	if(!empty($title)){
		//単純検索title
		$results = $results->where_like('title',"%$title%");
		$cats = $results->distinct()->select('cat')->find_many();
		//$cats = $results->find_many();
	}

	// catがあれば検索
	if(!empty($cat)){
		//単純検索cat
		$results = $results->where_like('cat',"%$cat%");
	}

	if(!empty($q_all)){
	//all検索
		$results = $results->where_raw('("title" like ? or "cat" like ? or "text" like ?)',array("%$q_all%","%$q_all%","%$q_all%"));
	}

	$results = $results->order_by_desc('updated')->find_many();

	$str .=<<<EOD
	<div class='row'>
		<div class="three columns">
		<form action='ins_exe' method='get'>
		<input type='text' name='title' value='
EOD;
		// titleがあれば表示
		if(!empty($title)){
			$str .= $title;
		}
					
	$str .=<<<EOD
		'>
		<input type='text' name='cat' value='
EOD;
		// catがあれば表示
		if(!empty($cat)){
			$str .= $cat;
		}

	$str .=<<<EOD
'><br>
					<textarea class='textList' name='text'></textarea>
					<br>
					<input type='submit' value='send'>
		</form>
		</div>

		<div class="three columns">
			<form action='' method='get'>
				<input type='text' name='title'>
				<input type='submit' value='title'>
			</form>
			<form action='' method='get'>
				<input type='hidden' name='title' value='
EOD;
	$str .= $title;
	$str .= "'>";
	$str .=<<<EOD
				<input type='text' name='cat'>
				<input type='submit' value='cat'>
			</form>
			<!--
			-->
			<a href='index.php'>clear</a>
			<ul>
EOD;

	if(!empty($title)){
		foreach($cats as $category){
			$str .= "<li>";
			$str .= $category->cat;
			$str .= "</li>";
		}
	}
/*
*/

	$str .=<<<EOD
			</ul>
		</div>
		<div class="three columns">
			<form action='' method='get'>
				<input type='text' name='q_all'>
				<input type='submit' value='all'>
			</form>
		</div>
	</div>
	<table>
		<thead>
			<tr>
				<th>id</th>
				<!--
				<th>date</th>
				-->
				<th>title</th>
				<th>cat</th>
				<th>text</th>
				<th>updated</th>
				<th>up</th>
				<th>down</th>
				<th>update</th>
				<th>delete</th>
			</tr>
		</thead>
		<tbody>
EOD;
	foreach($results as $result){
		$str .= "<tr>";
		$str .= "<td>";
		$str .= $result->id;
		$str .= "</td><td>";
		//$str .= $result->date;
		//$str .= "</td><td>";
		$str .= $result->title;
		$str .= "</td><td>";
		$str .= $result->cat;
		$str .= "</td><td>";
		$str .= nl2br($result->text,false);
		$str .= "</td><td>";
		$str .= $result->updated;
		$str .= "</td><td>";

		/*
		// 対象文字列
		$text = nl2br($result->text,false);
		// パターン
		$pattern = '/((?:https?|ftp):\/\/[-_.!~*\'()a-zA-Z0-9;\/?:@&=+$,%#]+)/u';
		// 置換後の文字列
		$replacement = '<a href="\1">\1</a>';
		// 置換
		$str .= preg_replace($pattern,$replacement,$text);

		$str .= "</td><td>";
		//$str .= $result->updated;
		//$str .= "</td><td>";
		//$str .= $result->archive;
		//$str .= "</td><td>";
		*/
		$str .= "<a href='up?id=" . $result->id . "&title=" . $title . "&cat=" . $cat . "'>up</a>";
		$str .= "</td><td>";
		$str .= "<a href='down?id=" . $result->id . "&title=" . $title . "&cat=" . $cat . "'>down</a>";
		$str .= "</td><td>";
		/*
		if(isset(Flight::request()->query->page)){
			$str .= "<a href='upd?id=" . $result->id . "&page=" . Flight::request()->query->page . "'>update</a>";
		}else{
		}
		*/
		$str .= "<a href='upd?id=" . $result->id . "'>update</a>";
		$str .= "</td><td>";
		$str .= "<a href='del?id=" . $result->id . "'>delete</a>";
		$str .= "</td>";
		$str .= "</tr>";
	}

	$str .=<<<EOD
	</tbody>
	</table>
EOD;

	/*
	// ページング
	if($page > 1){
		$str .= "<a class='button' href='?page=" . ($page - 1) . "'>previous</a>";
	}
	if($page < ceil($records/$per_page)){
		$str .= "<a class='button' href='?page=" . ($page + 1) . "'>after</a>";
	}
	*/
	//echo $str;
	//Flight::render('result.php', array('str' => $str));


	Flight::render('header', array('heading' => 'Hello'), 'header_content');
	Flight::render('body', array('str' => $str), 'body_content');
	Flight::render('layout', array('title' => 'todo'));

});
Flight::start();
