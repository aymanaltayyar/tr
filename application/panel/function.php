<?php

class funct {

  function CheckLogin(){
	if($_SESSION['access'] >= MULTIHUNTER and $_SESSION['id']){
	  return true;
	}else{
	  return false;
	}
  }

  function Act($get){
	global $admin;

	switch($get['action']){
	  case 'recountPop':
		$admin->recountPop($get['did']);
	  break;
	  case 'recountCp':
	    $admin->recountCp($get['did']);
	    break;
	  case 'recountPopUsr':
		$admin->recountPopUser($get['uid']);
	  break;
	  case 'StopDel':
		$admin->StopDelPlayer($get['uid']);
	  break;
	  case 'delVil':
		$admin->DelVillage($get['did']);
	  break;
	  case 'delBan':
		$admin->DelBan($get['uid'],$get['id']);
		//remove ban
	  break;
	  case 'addBan':
		if($get['time']){$end = time()+$get['time']; }else{$end = '';}

		  if(!is_numeric($get['uid'])){
		  $get['uid'] = $admin->getUserField(addslashes($get['uid']),'id',1);
		  }

		$admin->AddBan($get['uid'],$end,$get['reason']);
		//add ban
	  break;
	  case 'delOas':
		//oaza
	  break;
	  case 'logout':
		$this->LogOut();
	  break;
	}
	if($get['action'] == 'logout'){
	  header("Location: admin.php");
	}else{
	  header("Location: ".$_SERVER['HTTP_REFERER']);
	}
  }

  function Act2($post){
	global $admin;
	  switch($post['action']){
	  case 'DelPlayer':
		$admin->DelPlayer($post['uid'],$post['pass']);
		header("Location: ?p=search&msg=ursdel");
	  break;
	  case 'punish':
		$admin->Punish($post);
		header("Location: ".$_SERVER['HTTP_REFERER']);
	  break;
	  case 'addVillage':
		$admin->AddVillageA($post);
		header("Location: ".$_SERVER['HTTP_REFERER']);
	  break;
          case 'addTroops':
              $admin->changeTroops($post);
              header("Location: ".$_SERVER['HTTP_REFERER']);
              break;
	  }
  }

  function LogIN($username,$password){

	global $admin,$lang;
	if($admin->LoginA($username,$password)){
	  //$_SESSION['username'] = $username;
	  $_SESSION['access'] = $admin->getUserField($username,'access',1);
	  $_SESSION['id'] = $admin->getUserField($username,'id',1);
	  header("Location: ".$_SERVER['HTTP_REFERER']);
	  //header("Location: admin.php");
	}else{
		$_SESSION['Error'] = $lang['AError']['err01'];
	  	//echo "Error";
	}
  }

  function LogOut(){
	$_SESSION['access'] = '';
	$_SESSION['id'] = '';
  }

    public function procResType($ref) {
        global $lang;
        switch($ref) {
            case $ref: $build = $lang['buildings'][$ref]; break;
            default: $build = "Ошибка"; break;
        }
        return $build;
    }

};

$funct = new funct;
if($funct->CheckLogin()){
  if($_GET['action']){
	$funct->Act($_GET);
  }
  if($_POST['action']){
	$funct->Act2($_POST);
  }
}
if($_POST['action']=='login'){
  $funct->LogIN($_POST['name'],$_POST['pw']);
}