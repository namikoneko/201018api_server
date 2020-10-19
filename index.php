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

// find_thread ##################################################
Flight::route('/findthread', function(){
    $findtext= Flight::request()->data->text;
    $rows = ORM::for_table('thread')->where_like('title',"%" . $findtext . "%")->find_many();
    $i = 0;
    foreach($rows as $row){
        $list[$i]["id"] = $row["id"];
        $list[$i]["title"] = $row["title"];
        $list[$i]["text"] = $row["text"];
        $i++;
    }
    header("Content-Type: application/json; charset=utf-8");
//    try{
//      $arr = Flight::json($list);
//    }catch(Exception $e){
//        $list[0]["id"] = 999;
//        $list[0]["title"] = "not existed";
//        $list[0]["text"] = "not existed";
//      $arr = Flight::json($list);
//    }
      $arr = Flight::json($list);
    echo $arr;
});

// result_thread ##################################################
Flight::route('/resultthread/@id', function($id){
    $rows = ORM::for_table('thread')->where("id",$id)->find_many();
    $i = 0;
    foreach($rows as $row){
        $list[$i]["id"] = $row["id"];
        $list[$i]["title"] = $row["title"];
        $list[$i]["text"] = $row["text"];
        $i++;
        $rowsA = ORM::for_table('map')->where('threadid', $row->id)->find_many();
        $j = 0;
        $postid = array();
        foreach($rowsA as $rowA){
            $postid[$j] = $rowA->postid;
            $j++;
        }
        $rowsB = ORM::for_table('post')->where_in("id",$postid)->find_many();
        $k = 0;
        foreach($rowsB as $rowB){
            $list[$i]["post"][$k]["id"] = $rowB->id;
            $list[$i]["post"][$k]["title"] = $rowB->title;
            $k++;
        }
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
Flight::route('/xxx', function(){
//    $rows = ORM::for_table('foo')->table_alias('f')->join('bar', array('f.id', '=', 'b.id'), 'b')->find_many();
    $rows = ORM::for_table('foo')->table_alias('f')->select('f.title','f_title')->join('bar', array('f.id', '=', 'b.id'), 'b')->find_many();
//    $rows = ORM::for_table('foo')->join('bar', array('foo.id', '=', 'bar.id'))->find_many();


//    $rows = ORM::for_table('foo')->find_many();

    $i = 0;
    foreach($rows as $row){
	$list[$i]['id'] = $row['id'];
	$list[$i]['title'] = $row['f_title'];
        $i++;
    }
    $arr = Flight::json($list);
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
Flight::start();
