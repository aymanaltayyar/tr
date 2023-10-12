<?php 

Class newTravian
{
    public function __construct() {
        global $database;
        $this->glob =& $database;
    }

	public function doThisNow(){
		global $database;
		$users = $database->query("SELECT * FROM s1_users");
		while($row = mysqli_fetch_assoc($users)){
			$time = $row['protect'] - (MINPROTECTION);
			$database->query("UPDATE " . TB_PREFIX . "users set protect = '" . $time . "' WHERE id = " . $row['id']);
		}
	}
	
	public function addGold($gold, $username){
		global $database, $message;
		//echo $username; die();
		$playerQuery = $database->query("SELECT * FROM " . TB_PREFIX . "users WHERE username = '".$username."'");
		if(mysqli_num_rows($playerQuery) == 0){
			return 'لا يوجد لاعب بهذا الإسم';
		}else{
			$database->query("UPDATE " . TB_PREFIX . "users set gold = gold + ".$gold." WHERE username = '".$username."'");
			$playerData = mysqli_fetch_assoc($playerQuery);
			
			$nowGold = $playerData['gold'] + $gold;
			$topic = 'تم شحن '.$gold.' ذهبة، رصيدك الحالي '.$nowGold.'.';
			$database->sendMessage($playerData['id'], 4, 'تم شحن الذهب',htmlspecialchars(addslashes($topic)), 0, 0, 0, 0,0);
			return 'تم إضافة الذهب بنجاح';
		}
		//$database->sendMessage($user, $session->uid, htmlspecialchars(addslashes($topic)), htmlspecialchars(addslashes($text)), 0, $alliance, $player, $coor, $report);
		
	}
	
	public function getUser($uid){
		global $database;
		$q = "SELECT * FROM " . TB_PREFIX . "users WHERE id =".$uid." LIMIT 1";
		$result = $database->query($q);
		
		return mysqli_fetch_array($result);
		
		
	}
	public function getInfoMessages(){
		global $database;
        $q = "SELECT * FROM " . TB_PREFIX . "adminmessages ORDER BY id ASC";
		$result = $this->glob->query($q);
		
		while ($msg = mysqli_fetch_array($result )){
			$output .= "<li class=\"firstElement\">".$msg['content']."</li>";
		}
		
		return $output;
	}
	
	//Residence CP Functions
	public function villageNumber($uid){
		global $database;
        $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE owner = ".$uid."";
		$result = $database->query($q);
		
		return mysqli_num_rows($result);
	}
	
	public function otherVillages($uid,$wref){
		global $database;
        $qQ = "SELECT * FROM " . TB_PREFIX . "vdata WHERE owner = ".$uid." AND wref != ".$wref."";
        $q = "SELECT sum(cp) FROM " . TB_PREFIX . "vdata WHERE owner = ".$uid." AND wref != ".$wref."";
		$result = $database->query($q);
		
		if(mysqli_num_rows($database->query($qQ)) == 0){
			return 0;
		}else{
			$row = mysqli_fetch_row($result);
			return $row[0];
		}

	}
	
	public function getVillagesLoyalty($uid){
		global $database;
        $q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE owner = ".$uid." ORDER BY wref";
		$result = $database->query($q);
		while ($data = mysqli_fetch_array($result)){
			$output .= '
			<tr>
			<td class="name"><a href="karte.php?d='.$data['wref'].'">'.$data['name'].'</a> '.($data['capital'] == 1 ? '<span class="mainVillage">('.BL_CAPITAL.')</span>' : '').'</td>
			<td>'.$data['pop'].'</td>
			<td class="medium">'.$data['loyalty'].'%</td>
			</tr>';
		}
		return $output;
	}
	
	// New Hero System
	public function ItemtypeToData($btype){
		switch($btype){
			case 1: // Helmet
				$slot = "helmet";
				$isUsableIfDead = "false";
			break;
			case 2: // Armour
				$slot = "body";
				$isUsableIfDead = "false";
			break;
			case 3: // left hand
				$slot = "leftHand";
				$isUsableIfDead = "false";
			break;
			case 4: // right hand
				$slot = "rightHand";
				$isUsableIfDead = "false";
			break;
			case 5: // boots
				$slot = "shoes";
				$isUsableIfDead = "false";
			break;
			case 6: // Horses
				$slot = "horse";
				$isUsableIfDead = "false";
			break;
			case 7: // Small bandage
			case 8: // Bandage
			case 9: // Cage
			case 10: // Scroll Gives hero 10 experience Stackable
			case 11: // Ointment Instantly heals hero by 1% Stackable
			case 12: // Bucket
			case 13: // Book of Wisdom
			case 14: // Tablet of Law
			case 15: // Artwork
				$slot = "bag";
				$instant = "true";
				$isUsableIfDead = "true";
			break;
		}
		
		return array('slot' => $slot,
		'instant' => $instant ? $instant : '',
		'isUsableIfDead' => $isUsableIfDead
		);
	}
	
	public function getuserItems($uid){
		
		global $database,$name,$title;
		$HeroItems = $database->getHeroFace($uid);
		$hero = $database->getHeroData($uid);
		$output .= '';
		$x=0;
		foreach ($database->getHeroItems($uid) as $row) {
			$dataArray = $this->ItemtypeToData($row['btype']);
			$btype = $row['btype']; $type=$row['type'];
			include_once "./application/application/views/Auction/alt.tpl";
			
			if ($row['btype'] <= 11 or $row['btype'] == 13) {
				if ($hero['dead'] == 1) {
					$dis = ' disabled';
					$deadTitle = "<span class='itemNotMoveable'>" . HERO_HERODEADORNOTHERE . "</span><br>";
				}
			}
			
			if($row['num'] != 0){
				$x++;
				if($btype <= 6){
					$output .= '{"id":'.$row['id'].',"typeId":'.$row['type'].',"placeId":'.$x.',"name":"'.$name.'","place":"inventory","slot":"'.$dataArray['slot'].'","amount":'.$row['num'].',"isUsableIfDead":'.$dataArray['isUsableIfDead'].',"attributes":["'.$title.'."]},';
				}else{
					$output .= '{"id":'.$row['id'].',"typeId":'.$row['type'].',"placeId":'.$x.',"name":"'.$name.'","place":"inventory","slot":"'.$dataArray['slot'].'","amount":'.$row['num'].',"instant":"'.$dataArray['instant'].'","isUsableIfDead":'.$dataArray['isUsableIfDead'].',"attributes":["'.$title.'."]},';
				}
			}
			switch($row['id']){
				case $HeroItems['bag']:
					$output .= '{"id":'.$row['id'].',"typeId":'.$row['type'].',"placeId":0,"name":"'.$name.'","place":"bag","slot":"bag","amount":'.$HeroItems['num'].',"instant":'.$dataArray['instant'].',"isUsableIfDead":'.$dataArray['isUsableIfDead'].',"attributes":["'.$title.'."]},';
				break;
				
				case $HeroItems['horse']:
					$output .= '{"id":'.$row['id'].',"typeId":'.$row['type'].',"placeId":0,"name":"'.$name.'","place":"horse","slot":"'.$dataArray['slot'].'","amount":1,"isUsableIfDead":'.$dataArray['isUsableIfDead'].',"attributes":["'.$title.'."]},';
				break;
				
				case $HeroItems['body']:
					$output .= '{"id":'.$row['id'].',"typeId":'.$row['type'].',"placeId":0,"name":"'.$name.'","place":"body","slot":"'.$dataArray['slot'].'","amount":1,"isUsableIfDead":'.$dataArray['isUsableIfDead'].',"attributes":["'.$title.'."]},';
				break;
				
				case $HeroItems['leftHand']:
					$output .= '{"id":'.$row['id'].',"typeId":'.$row['type'].',"placeId":0,"name":"'.$name.'","place":"leftHand","slot":"'.$dataArray['slot'].'","amount":1,"isUsableIfDead":'.$dataArray['isUsableIfDead'].',"attributes":["'.$title.'."]},';
				break;
				
				case $HeroItems['rightHand']:
					$output .= '{"id":'.$row['id'].',"typeId":'.$row['type'].',"placeId":0,"name":"'.$name.'","place":"rightHand","slot":"'.$dataArray['slot'].'","amount":1,"isUsableIfDead":'.$dataArray['isUsableIfDead'].',"attributes":["'.$title.'."]},';
				break;
				
				case $HeroItems['shoes']:
					$output .= '{"id":'.$row['id'].',"typeId":'.$row['type'].',"placeId":0,"name":"'.$name.'","place":"shoes","slot":"'.$dataArray['slot'].'","amount":1,"isUsableIfDead":'.$dataArray['isUsableIfDead'].',"attributes":["'.$title.'."]},';
				break;
				
				case $HeroItems['helmet']:
					$output .= '{"id":'.$row['id'].',"typeId":'.$row['type'].',"placeId":0,"name":"'.$name.'","place":"helmet","slot":"'.$dataArray['slot'].'","amount":1,"isUsableIfDead":'.$dataArray['isUsableIfDead'].',"attributes":["'.$title.'."]},';
				break;
				
				default:
				break;
			}
		}
		
		return rtrim($output, ", ");
	}
	
	public function itemToInventory(){
		
	}
	
	function helmetEffects($type, $mode){
		global $database,$session;
		$uid=$session->uid;
		// mode 1 = +, 2 = -
		switch ($type) {
			case 1:
				$database->modifyHero($uid, 0, "itemextraexpgain", 15, $mode);
			break;
			case 2:
				$database->modifyHero($uid, 0, "itemextraexpgain", 20, $mode);
			break;
			case 3:
				$database->modifyHero($uid, 0, "itemextraexpgain", 25, $mode);
			break;
			case 4:
				$database->modifyHero($uid, 0, "itemautoregen", 10, $mode);
			break;
			case 5:
				$database->modifyHero($uid, 0, "itemautoregen", 15, $mode);
			break;
			case 6:
				$database->modifyHero($uid, 0, "itemautoregen", 20, $mode);
			break;
			case 7:
				$database->modifyHero($uid, 0, "itemcpproduction", 5, $mode);
			break;
			case 8:
				$database->modifyHero($uid, 0, "itemcpproduction", 10, $mode);
			break;
			case 9:
				$database->modifyHero($uid, 0, "itemcpproduction", 15, $mode);
			break;
			case 10:
				$database->modifyHero($uid, 0, "itemcavalrytrain", 10, $mode);
			break;
			case 11:
				$database->modifyHero($uid, 0, "itemcavalrytrain", 15, $mode);
			break;
			case 12:
				$database->modifyHero($uid, 0, "itemcavalrytrain", 20, $mode);
			break;
			case 13:
				$database->modifyHero($uid, 0, "iteminfantrytrain", 10, $mode);
			break;
			case 14:
				$database->modifyHero($uid, 0, "iteminfantrytrain", 15, $mode);
			break;
			case 15:
				$database->modifyHero($uid, 0, "iteminfantrytrain", 20, $mode);
			break;
		}		
	}
	function bodyEffects($type, $mode){
		global $database,$session;
		$uid=$session->uid;
		// mode 1 = +, 2 = -
		switch ($type) {
			case 82:
			$database->modifyHero($uid, 0, "itemautoregen", 20, $mode);
			break;
			case 83:
			$database->modifyHero($uid, 0, "itemautoregen", 30, $mode);
			break;
			case 84:
			$database->modifyHero($uid, 0, "itemautoregen", 40, $mode);
			break;
			case 85:
			$database->modifyHero($uid, 0, "itemautoregen", 10, $mode);
			$database->modifyHero($uid, 0, "itemextraresist", 4, $mode);
			break;
			case 86:
			$database->modifyHero($uid, 0, "itemautoregen", 15, $mode);
			$database->modifyHero($uid, 0, "itemextraresist", 6, $mode);
			break;
			case 87:
			$database->modifyHero($uid, 0, "itemautoregen", 20, $mode);
			$database->modifyHero($uid, 0, "itemextraresist", 8, $mode);
			break;
			case 88:
			$database->modifyHero($uid, 0, "itemfs", 500, $mode);
			break;
			case 89:
			$database->modifyHero($uid, 0, "itemfs", 1000, $mode);
			break;
			case 90:
			$database->modifyHero($uid, 0, "itemfs", 1500, $mode);
			break;
			case 91:
			$database->modifyHero($uid, 0, "itemfs", 250, $mode);
			$database->modifyHero($uid, 0, "itemextraresist", 3, $mode);
			break;
			case 92:
			$database->modifyHero($uid, 0, "itemfs", 500, $mode);
			$database->modifyHero($uid, 0, "itemextraresist", 4, $mode);
			break;
			case 93:
			$database->modifyHero($uid, 0, "itemfs", 750, $mode);
			$database->modifyHero($uid, 0, "itemextraresist", 5, $mode);
			break;
		}
	}
	function leftHandEffects($type, $mode){
		global $database,$session;
		$uid=$session->uid;
		// mode 1 = +, 2 = -
			switch ($type) {
                    case 61:
                        $database->modifyHero($uid, 0, "itemreturnmspeed", 30, $mode);
                        break;
                    case 62:
                        $database->modifyHero($uid, 0, "itemreturnmspeed", 40, $mode);
                        break;
                    case 63:
                        $database->modifyHero($uid, 0, "itemreturnmspeed", 50, $mode);
                        break;
                    case 64:
                        $database->modifyHero($uid, 0, "itemaccountmspeed", 30, $mode);
                        break;
                    case 65:
                        $database->modifyHero($uid, 0, "itemaccountmspeed", 40, $mode);
                        break;
                    case 66:
                        $database->modifyHero($uid, 0, "itemaccountmspeed", 50, $mode);
                        break;
                    case 67:
                        $database->modifyHero($uid, 0, "itemallymspeed", 15, $mode);
                        break;
                    case 68:
                        $database->modifyHero($uid, 0, "itemallymspeed", 20, $mode);
                        break;
                    case 69:
                        $database->modifyHero($uid, 0, "itemallymspeed", 25, $mode);
                        break;
                    case 73:
                        $database->modifyHero($uid, 0, "itemrob", 10, $mode);
                        break;
                    case 74:
                        $database->modifyHero($uid, 0, "itemrob", 15, $mode);
                        break;
                    case 75:
                        $database->modifyHero($uid, 0, "itemrob", 20, $mode);
                        break;
                    case 76:
                        $database->modifyHero($uid, 0, "itemfs", 500, $mode);
                        break;
                    case 77:
                        $database->modifyHero($uid, 0, "itemfs", 1000, $mode);
                        break;
                    case 78:
                        $database->modifyHero($uid, 0, "itemfs", 1500, $mode);
                        break;
                    case 79:
                        $database->modifyHero($uid, 0, "itemvsnatars", 25, $mode);
                        break;
                    case 80:
                        $database->modifyHero($uid, 0, "itemvsnatars", 50, $mode);
                        break;
                    case 81:
                        $database->modifyHero($uid, 0, "itemvsnatars", 75, $mode);
                        break;
                }
                		
	}	
	function rightHandEffects($type, $mode){
		global $database,$session;
 		$uid=$session->uid;
               switch ($type) {
                    case 16:
                    case 19:
                    case 22:
                    case 25:
                    case 28:
                    case 31:
                    case 34:
                    case 37:
                    case 40:
                    case 43:
                    case 46:
                    case 49:
                    case 52:
                    case 55:
                    case 58:
					case 115:
					case 118:
					case 121:
					case 124:
					case 127:
					case 130:
					case 133:
					case 136:
					case 139:
					case 142:
                        $database->modifyHero($uid, 0, "itemfs", 500, $mode);
                        break;
                    case 17:
                    case 20:
                    case 23:
                    case 26:
                    case 29:
                    case 32:
                    case 35:
                    case 38:
                    case 41:
                    case 44:
                    case 47:
                    case 50:
                    case 53:
                    case 56:
                    case 59:
					case 116:
					case 119:
					case 122:
					case 125:
					case 128:
					case 131:
					case 134:
					case 137:
					case 140:
					case 143:
                        $database->modifyHero($uid, 0, "itemfs", 1000, $mode);
                        break;
                    case 18:
                    case 21:
                    case 24:
                    case 27:
                    case 30:
                    case 33:
                    case 36:
                    case 39:
                    case 42:
                    case 45:
                    case 48:
                    case 51:
                    case 54:
                    case 57:
                    case 60:
					case 117:
					case 120:
					case 123:
					case 126:
					case 129:
					case 132:
					case 135:
					case 138:
					case 141:
					case 144:
                        $database->modifyHero($uid, 0, "itemfs", 1500, $mode);
                        break;
                }
	}
	function shoesEffects($type, $mode){
		global $database,$session;
		$uid=$session->uid;
		switch ($type) {
                    case 94:
                        $database->modifyHero($uid, 0, "itemautoregen", 10, $mode);
                        break;
                    case 95:
                        $database->modifyHero($uid, 0, "itemautoregen", 15, $mode);
                        break;
                    case 96:
                        $database->modifyHero($uid, 0, "itemautoregen", 20, $mode);
                        break;
                    case 97:
                        $database->modifyHero($uid, 0, "itemattackmspeed", 25, $mode);
                        break;
                    case 98:
                        $database->modifyHero($uid, 0, "itemattackmspeed", 50, $mode);
                        break;
                    case 99:
                        $database->modifyHero($uid, 0, "itemattackmspeed", 75, $mode);
                        break;
                    case 100:
                        $database->modifyHero($uid, 0, "itemspeed", 3, $mode);
                        break;
                    case 101:
                        $database->modifyHero($uid, 0, "itemspeed", 4, $mode);
                        break;
                    case 102:
                        $database->modifyHero($uid, 0, "itemspeed", 5, $mode);
                        break;
                }
	}
	function horseEffects($type, $mode){
		global $database,$session;
		$uid=$session->uid;
		switch ($type) {
			case 103:
			$database->modifyHero($uid, 0, "itemspeed", 14, $type);
			break;
			case 104:
			$database->modifyHero($uid, 0, "itemspeed", 17, $type);
			break;
			case 105:
			$database->modifyHero($uid, 0, "itemspeed", 20, $type);
			break;
		}	
	}
	function itemType($btype){
		switch($btype){
			case 1: // Helmet
			$itemType = array('column' => 'helmet','functionEffect' => 'helmetEffects');
			break;
			case 2: // Armour
			$itemType = array('column' => 'body','functionEffect' => 'bodyEffects');
			break;
			case 3: // left hand
			$itemType = array('column' => 'leftHand','functionEffect' => 'leftHandEffects');
			break;
			case 4: // right hand
			$itemType = array('column' => 'rightHand','functionEffect' => 'rightHandEffects');
			break;
			case 5: // boots
			$itemType = array('column' => 'shoes','functionEffect' => 'shoesEffects');
			break;
			case 6 :// Horses
			$itemType = array('column' => 'horse','functionEffect' => 'horseEffects');
			break;
		}
		return $itemType;
	}
	public function useHeroItem($data){
		global $database,$session,$village,$Quest;
		$uid = $session->uid;
		$heroFace = $database->getHeroFace($uid);
		$heroData = $database->getHeroData($session->uid);
        if ($data['drid'] == 'helmet') {
            $item = $database->getHeroItem($data['id']);
            if ($item) {
				if($heroFace['helmet'] != 0){
					$helmetData = mysqli_fetch_assoc($database->query("SELECT * FROM ".TB_PREFIX."heroitems WHERE id='".$heroFace['helmet']."'"));
					$this->helmetEffects($helmetData['type'],2);
					$database->modifyHeroItem($helmetData['id'], 'proc', 0, 0);
					$database->modifyHeroItem($helmetData['id'], 'num', 1, 0);
					$database->modifyHeroFace($uid, "helmet", 0);
				}
				
				$database->modifyHeroItem($item['id'], 'proc', 1, 0);
				$database->modifyHeroItem($item['id'], 'num', 0, 0);
				$database->modifyHeroFace($uid, "helmet", $item['id']);
				$this->helmetEffects($item['type'],1);
				
            }
        } elseif ($data['drid'] == 'body') {
            $item = $database->getHeroItem($data['id']);
            if ($item) {
				if($heroFace['body'] != 0){
					$bodyData = mysqli_fetch_assoc($database->query("SELECT * FROM ".TB_PREFIX."heroitems WHERE id='".$heroFace['body']."'"));
					$this->bodyEffects($bodyData['type'],2);
					$database->modifyHeroItem($bodyData['id'], 'proc', 0, 0);
					$database->modifyHeroItem($bodyData['id'], 'num', 1, 0);
					$database->modifyHeroFace($uid, "body", 0);
				}
				
				$database->modifyHeroItem($data['id'], 'proc', 1, 0);
				$database->modifyHeroItem($data['id'], 'num', 0, 0);
				$database->modifyHeroFace($uid, "body", $item['id']);
				$this->bodyEffects($item['type'],1);
				
            }
        } elseif ($data['drid'] == 'leftHand') {
            $item = $database->getHeroItem($data['id']);
            if ($item) {
                if($heroFace['leftHand'] != 0){
					$leftHandData = mysqli_fetch_assoc($database->query("SELECT * FROM ".TB_PREFIX."heroitems WHERE id='".$heroFace['leftHand']."'"));
					$this->leftHandEffects($leftHandData['type'],2);
					$database->modifyHeroItem($leftHandData['id'], 'proc', 0, 0);
					$database->modifyHeroItem($leftHandData['id'], 'num', 1, 0);
					$database->modifyHeroFace($uid, "leftHand", 0);
				}
				$database->modifyHeroItem($item['id'], 'proc', 1, 0);
				$database->modifyHeroItem($item['id'], 'num', 0, 0);
				$database->modifyHeroFace($uid, "leftHand", $item['id']);
				$this->leftHandEffects($item['type'],1);
            }
        } elseif ($data['drid'] == 'rightHand') {
            $item = $database->getHeroItem($data['id']);
            if ($item) {
				if($heroFace['rightHand'] != 0){
					$rightHandData = mysqli_fetch_assoc($database->query("SELECT * FROM ".TB_PREFIX."heroitems WHERE id='".$heroFace['rightHand']."'"));
					$this->rightHandEffects($rightHandData['type'],2);
					$database->modifyHeroItem($rightHandData['id'], 'proc', 0, 0);
					$database->modifyHeroItem($rightHandData['id'], 'num', 1, 0);
					$database->modifyHeroFace($uid, "rightHand", 0);
				}
				$database->modifyHeroItem($item['id'], 'proc', 1, 0);
				$database->modifyHeroItem($item['id'], 'num', 0, 0);
				$database->modifyHeroFace($uid, "rightHand", $item['id']);
				$this->rightHandEffects($item['type'],1);
            }
        } elseif ($data['drid'] == 'shoes') {
            $item = $database->getHeroItem($data['id']);
            if ($item) {
                if($heroFace['shoes'] != 0){
					$shoesData = mysqli_fetch_assoc($database->query("SELECT * FROM ".TB_PREFIX."heroitems WHERE id='".$heroFace['shoes']."'"));
					$this->shoesEffects($shoesData['type'],2);
					$database->modifyHeroItem($shoesData['id'], 'proc', 0, 0);
					$database->modifyHeroItem($shoesData['id'], 'num', 1, 0);
					$database->modifyHeroFace($uid, "shoes", 0);
				}
				
				$database->modifyHeroItem($data['id'], 'proc', 1, 0);
				$database->modifyHeroItem($data['id'], 'num', 0, 0);
				$database->modifyHeroFace($uid, "shoes", $data['id']);
				$this->shoesEffects($item['type'],1);
            }
        } elseif ($data['drid'] == 'horse') {
			$item = $database->getHeroItem($data['id']);
			if($heroFace['horse'] != 0){
				$horseData = mysqli_fetch_assoc($database->query("SELECT * FROM ".TB_PREFIX."heroitems WHERE id='".$heroFace['horse']."'"));
				$this->horseEffects($horseData['type'],2);
				$database->modifyHeroItem($horseData['id'], 'proc', 0, 0);
				$database->modifyHeroItem($item['id'], 'num', 1, 0);
				$database->modifyHeroFace($uid, "horse", 0);
			}

			$database->modifyHeroItem($item['id'], 'proc', 0, 1);
			$database->modifyHeroItem($item['id'], 'num', 0, 0);
			$database->modifyHeroFace($uid, "horse", $item['id']);
			
        } elseif ($data['drid'] == 'bag') {
			// need code if have something on bag swap
			$item = $database->getHeroItem($data['id']);
			if($item['num'] < $data['amount']){ $data['amount'] = $item['num']; }
			if($item['btype'] == 12){ // Bucket for revive
				if($heroData['dead'] == 1){
					$database->query("UPDATE ".TB_PREFIX."hero SET dead = 0,health = 100 WHERE uid =".$uid." ");
					$database->modifyHeroItem($item['id'], 'num', $data['amount'], 2);
					$database->modifyHeroItem($item['id'], 'proc', 0, 0);
					$database->editTableField('units', 'hero', 1, 'vref', $village->wid);
					$_SESSION['reload'] = TRUE;
				}
			}elseif($item['btype'] == 10){ // Scrolls
				if($heroData['dead'] == 0){
					if($data['amount'] >= $item['num']){ $numberUsed = $item['num'];
						$database->modifyHeroItem($item['id'], 'num', $item['num'], 2);
						$database->modifyHeroItem($item['id'], 'proc', 0, 0);
					}else{ $numberUsed = $data['amount'];
						$database->modifyHeroItem($item['id'], 'num', $data['amount'], 2);
					}
					$newEXP = $heroData['experience'] + ($numberUsed*200);
					$database->query("UPDATE ".TB_PREFIX."hero SET experience = ".$newEXP." WHERE uid =".$uid." ");
				}
				$_SESSION['reload'] = TRUE;
			}elseif($item['btype'] == 11){ // Ointment
				if($heroData['dead'] == 0){
					if ($session->quest_progress[0] == 12 && $session->quest_progress[2] != 1) {
						$Quest->UpdateQuestProgress($session->uid,'12,1,1,0,0');
					}
					if($data['amount'] >= $item['num']){ $numberUsed = $item['num'];
						$database->modifyHeroItem($item['id'], 'num', $item['num'], 2);
						$database->modifyHeroItem($item['id'], 'proc', 0, 0);
					}else{ $numberUsed = $data['amount'];
						$database->modifyHeroItem($item['id'], 'num', $data['amount'], 2);
					}
					
					if(round($heroData['healt']) != 100){
						$newHealth = $heroData['health'] + $numberUsed;
						if($newHealth > 100){
							$remainingOin = $newHealth - 100;
							$newHealth = 100;
							//$database->modifyHeroItem($item['id'], 'num', $remainingOin, 0);
							$database->modifyHeroFace($uid, "bag", $item['id']);
							$database->modifyHeroFace($uid, "num", $remainingOin,1);
						}
						$database->query("UPDATE ".TB_PREFIX."hero SET health = ".$newHealth." WHERE uid =".$uid." ");
					}else{
						$database->modifyHeroFace($uid, "bag", $item['id']);
						$database->modifyHeroFace($uid, "num", $data['amount'],1);
					}
					
				}
			}else{
				$database->modifyHeroItem($item['id'], 'num', $data['amount'], 2);
				$database->modifyHeroItem($item['id'], 'proc', 1, 0);
				$database->modifyHeroFace($uid, "bag", $item['id']);
				$database->modifyHeroFace($uid, "num", $data['amount'],1);
			}
			
        }else{
			$item = $database->getHeroItem($data['id']);						
			// Just a secure for items
			if($item['btype'] > 7 && $item['btype'] != 12){
				if($data['amount'] > $heroFace['num']){
					$data['amount'] = $heroFace['num']; 
				}
				$database->modifyHeroFace($uid, "bag", 0);
				$database->modifyHeroFace($uid, "num", 0);
				$database->modifyHeroItem($item['id'], 'num', $heroFace['num'], 1);
				
			}else{
				$this->{$this->itemType($item['btype'])['functionEffect']}($item['type'],2);
				$database->modifyHeroItem($data['id'], 'num', 1, 0);
				$database->modifyHeroItem($data['id'], 'proc', 0, 0);
				$database->modifyHeroFace($uid, $this->itemType($item['btype'])['column'], 0);
			}
		}		
	}
	
	public function getRandomApperance(){
		global $database,$session;
		$heroData = $database->getHeroFace($session->uid);
		if ($heroData['gender'] == 0) {
			$face = $_SESSION['face'] = rand(0, 4);
			$gen = 'male';
		} else {
			$face = $_SESSION['face'] = rand(0, 5);
			$gen = 'female';
		}
		$headp = '<img style=\"width:254px; height:330px; position:absolute;left:0px;top:0px;\" src=\"'.GP_LOCATE.'/img/hero/'.$gstr.'/'.$gen.'/head/254x330/face0.png\" alt=\"\">';
		$headp .= '<img style=\"width:254px; height:330px; position:absolute;left:0px;top:0px;\" src=\"'.GP_LOCATE.'/img/hero/'.$gstr.'/'.$gen.'/head/254x330/face/face' . $face . '.png\" alt=\"\">';
		$color = $_SESSION['getcolor'] = rand(0, 4);
		switch ($color) {
			case 0:
			$color = 'black';
			break;
		case 1:
			$color = 'brown';
			break;
		case 2:
			$color = 'darkbrown';
			break;
		case 3:
			$color = 'yellow';
			break;
		case 4:
			$color = 'red';
			break;
		}
		$gethair = $_SESSION['gethair'] = rand(0, 5);
		$hearp = '<img style=\"width:254px; height:330px; position:absolute;left:0px;top:0px;\" src=\"' . GP_LOCATE . 'img/hero/'.$gen.'/head/254x330/hair/hair' . $gethair . '-' . $color . '.png\" alt=\"\" >';
                    
		if ($heroData['gender'] == 0) {
		$getear = $_SESSION['getear'] = rand(0, 4);
		} else {
		$getear = $_SESSION['getear'] = rand(0, 7);
		}
					
		$earp = '<img style=\"width:254px; height:330px; position:absolute;left:0px;top:0px;\" src=\"' . GP_LOCATE . '\/img\/hero\/'.$gen.'\/head\/254x330\/ear\/ear' . $getear . '.png\" alt=\"\">';
                    
		if ($heroData['gender'] == 0) {
		$geteyebrow = $_SESSION['geteyebrow'] = rand(0, 3);
		} else {
		$geteyebrow = $_SESSION['geteyebrow'] = rand(0, 9);
		}
					
		if ($heroData['gender'] == 0) {
		$eyebrow = $geteyebrow . '-' . $color;
		} else {
		$eyebrow = $geteyebrow;
		}
					
		$eyebp = '<img style=\"width:254px; height:330px; position:absolute;left:0px;top:0px;\" src=\"' . GP_LOCATE . '\/img\/hero\/'.$gen.'\/head\/254x330\/eyebrow\/eyebrow' . $eyebrow . '.png\" alt=\"\">';
		if ($heroData['gender'] == 0) {
		$geteye = $_SESSION['geteye'] = rand(0, 4);
		} else {
		$geteye = $_SESSION['geteye'] = rand(0, 9);
		}
		$eyep = '<img style=\"width:254px; height:330px; position:absolute;left:0px;top:0px;\" src=\"' . GP_LOCATE . '\/img\/hero\/'.$gen.'\/head\/254x330\/eye\/eye' . $geteye . '.png\" alt=\"\">';
		if ($heroData['gender'] == 0) {
		$getnose = $_SESSION['getnose'] = rand(0, 4);
		} else {
		$getnose = $_SESSION['getnose'] = rand(0, 7);
		}
		$nosep = '<img style=\"width:254px; height:330px; position:absolute;left:0px;top:0px;\" src=\"' . GP_LOCATE . '\/img\/hero\/'.$gen.'\/head\/254x330\/nose\/nose' . $getnose . '.png\" alt=\"\">';
		if ($heroData['gender'] == 0) {
		$getmouth = $_SESSION['getmouth'] = rand(0, 3);
		} else {
		$getmouth = $_SESSION['getmouth'] = rand(0, 8);
		}
					
		$mop = '<img style=\"width:254px; height:330px; position:absolute;left:0px;top:0px;\" src=\"' . GP_LOCATE . '\/img\/hero\/'.$gen.'\/head\/254x330\/mouth\/mouth' . $getmouth . '.png\" alt=\"\">';
                    
		if ($heroData['gender'] == 0) {
                        $getbeard = $_SESSION['getbeard'] = rand(0, 5);
						$beardp = '<img style=\"width:254px; height:330px; position:absolute;left:0px;top:0px;\" src=\"' . GP_LOCATE . '\/img\/hero\/'.$gen.'\/head\/254x330\/beard\/beard' . $getbeard . '-' . $color . '.png\" alt=\"\">';
		} else {
			$beardp = '';
		}		
		
		return $headp . $hearp . $earp . $eyebp . $eyep . $nosep . $mop . $beardp;
	}
	
	function getRandomNumber($min,$max,$exclude='') {
		if($exclude != ''){
			do {
				$n = mt_rand($min,$max);
			} while(in_array($n, $exclude));
		}else{
			$n = mt_rand($min,$max);
		}
		// 30% percent to find nothing
		if((float)rand()/(float)getrandmax()  <= 0.3){
			return 0;
		}else{
			return $n;
		}
	}
	
	public function getResTitle($type){
		global $database, $village, $generator;
		//$village->maxstore
		switch($type){
			case 'wood':
				if(time()-((($village->maxstore-$village->awood) / $village->getProd('wood'))*60*60) < time()){
					$timeToFull = round((($village->maxstore-$village->awood) / $village->getProd('wood'))*60*60);
				}
				$ouput = ''.PD_LUMBER.'||'.RES_PRODUCTION.': '.number_format($village->getProd('wood')).'<br>'.RES_FULLIN.': '.gmdate('H:i:s',$timeToFull).'<br>'.RES_MOREINFO.'';
			break;
			
			case 'clay':
				if(time()-((($village->maxstore-$village->aclay) / $village->getProd('clay'))*60*60) < time()){
					$timeToFull = round((($village->maxstore-$village->aclay) / $village->getProd('clay'))*60*60);
				}
				$ouput = ''.PD_CLAY.'||'.RES_PRODUCTION.': '.number_format($village->getProd('clay')).'<br>'.RES_FULLIN.': '.gmdate('H:i:s',$timeToFull).'<br>'.RES_MOREINFO.'';
			break;
			
			case 'iron':
				if(time()-((($village->maxstore-$village->airon) / $village->getProd('iron'))*60*60) < time()){
					$timeToFull = round((($village->maxstore-$village->airon) / $village->getProd('iron'))*60*60);
				}
				$ouput = ''.PD_IRON.'||'.RES_PRODUCTION.': '.number_format($village->getProd('iron')).'<br>'.RES_FULLIN.': '.gmdate('H:i:s',$timeToFull).'<br>'.RES_MOREINFO.'';
			break;
			
			case 'crop':
				if(time()-((($village->maxstore-$village->acrop) / $village->getProd('crop'))*60*60) < time()){
					$timeToFull = round((($village->maxstore-$village->acrop) / $village->getProd('crop'))*60*60);
				}
				$ouput = ''.PD_CROP.'||'.RES_PRODUCTION.': '.number_format($village->getProd('crop')).'<br>'.RES_FULLIN.': '.gmdate('H:i:s',$timeToFull).'<br>'.RES_MOREINFO.'';
			break;
			
			case 'FreeCrop':
				$ouput = ''.RES_CROPBALANCETITLE.'||'.RES_CROPBALANCE.': '.number_format($village->allcrop).'<br>'.RES_MOREINFO.'';
			break;
		}
		
		return $ouput;
	}
	
	public function checkHeroMovement($wref){
		global $database;
		
		$q = "SELECT * FROM " . TB_PREFIX . "movement WHERE (`from` =".$wref." OR `to` =".$wref.") AND isHero != 0 AND proc = 0";
		$result = $database->query($q);
		$data = mysqli_fetch_assoc($result);
		
		if(mysqli_num_rows($result) == 1){
			return array(
				'isHero' => explode(',',$data['isHero']),
				'isAdventure' => 1,
				'toValley' => $data['to'],
				'endTime' =>  $data['endtime']
			);
		}else{
			return mysqli_num_rows($result);
		}
				
	}
	
	public function getReport($id,$mode){
		global $database, $session;
		if($mode == 'Next'){
			$q = $database->query("SELECT * FROM ".TB_PREFIX."ndata WHERE `uid` = " . $session->uid . " and `archive` = 0 and `id` > ".$id." ORDER BY id ASC LIMIT 1");
			if(mysqli_num_rows($q) != 0){
				return array(
					'Data' =>mysqli_fetch_assoc($q),
					'isDisable' => 0
				);
			}else{
				return array('isDisable' => 1);
			}
		}elseif($mode == 'Last'){
			$q = $database->query("SELECT * FROM ".TB_PREFIX."ndata WHERE `uid` = " . $session->uid . " and `archive` = 0 and `id` < ".$id." ORDER BY id DESC LIMIT 1");
			if(mysqli_num_rows($q) != 0){
				return array(
					'Data' =>mysqli_fetch_assoc($q),
					'isDisable' => 0
				);
			}else{
				return array('isDisable' => 1);
			}
		}
	}
	
	public function getReportImage($ntype){
		switch($ntype){
			case 1:
			break;
			
			case 9: // Adventure
			break;
			
			case 8: // Reinforcement
			break;
			
		}
	}
		
	public function getVillData($wref){
		global $database;
		$q = "SELECT * FROM " . TB_PREFIX . "wdata WHERE `id` =".$wref."";
		
		return mysqli_fetch_assoc($database->query($q));
	}
	
	public function getVillageData($wref){
		global $database;
		$q = "SELECT * FROM " . TB_PREFIX . "vdata WHERE `wref` =".$wref."";
		
		return mysqli_fetch_assoc($database->query($q));
	}
	
	public function redirect($location){
		header('Location: '.$location.'.php');
		exit();
	}
	public function plusData(){
		global $database, $session;
		
	}
	
	public function sanitize_output($buffer) {

		$search = array(
			'/\>[^\S ]+/s',     // strip whitespaces after tags, except space
			'/[^\S ]+\</s',     // strip whitespaces before tags, except space
			'/(\s)+/s',         // shorten multiple whitespace sequences
			'/<!--(.|\s)*?-->/' // Remove HTML comments
		);

		$replace = array(
			'>',
			'<',
			'\\1',
			''
		);

		$buffer = preg_replace($search, $replace, $buffer);
		$buffer = str_replace('\t', '', $buffer);
		$buffer = str_replace('\n', '', $buffer);
		$buffer = str_replace('
	', '', $buffer);

		return $buffer;
	}
	
	public function getTribe($tribe){
		return constant('TRIBE'.$tribe);
	}
	
	public function tribeData($tribe){
		switch($tribe){
			case 1:
				$data = array(
					'start' => 1,
					'end' => 9
				);
		}
		
		return $data;
	}
	
	public function getDistance($coorx1, $coory1, $coorx2, $coory2){
		$max = 2 * WORLD_MAX + 1;
		$x1 = intval($coorx1);
		$y1 = intval($coory1);
		$x2 = intval($coorx2);
		$y2 = intval($coory2);
		$distanceX = min(abs($x2 - $x1), abs($max - abs($x2 - $x1)));
		$distanceY = min(abs($y2 - $y1), abs($max - abs($y2 - $y1)));
		$dist = sqrt(pow($distanceX, 2) + pow($distanceY, 2));
		return round($dist, 1);
	}
	
	
	// Panel Functions
	function getWref($x,$y) {
		global $database;
		$q = "SELECT * FROM ".TB_PREFIX."wdata where x = $x and y = $y";      
		$result = $database->query($q);
		$r = mysqli_fetch_assoc($result);				
		return $r['id'];
	}

	public function ResetMap($post){ // Reinstall the whole thing
		global $database,$account,$session;
		set_time_limit(0);
		
		$database->query("TRUNCATE TABLE ".TB_PREFIX."a2b");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."abdata");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."activate");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."active");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."adventure");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."alidata");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."ali_invite");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."ali_log");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."ali_permission");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."allimedal");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."artefacts");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."attacks");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."auction");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."autoauction");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."bdata");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."deleting");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."demolition");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."diplomacy");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."emailinvite");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."enforcement");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."farmlist");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."fdata");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."forum_cat");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."forum_edit");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."forum_poll");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."forum_post");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."forum_topic");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."fpost_rules");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."gold_fin_log");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."hero");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."heroface");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."heroitems");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."links");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."map_marks");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."market");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."mdata");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."medal");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."movement");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."msg_reports");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."natarsprogress");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."ndata");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."newproc");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."odata");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."raidlist");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."refrence");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."research");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."route");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."send");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."stats");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."tdata");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."training");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."trapped");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."units");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."users_setting");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."vdata");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."fdata");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."wdata");
		$database->query("TRUNCATE TABLE ".TB_PREFIX."x_world");
		
		$database->query("INSERT INTO ".TB_PREFIX."natarsprogress VALUES(0, 0, 0, 0, ".$post['artefactreleasedat'].", 0, ".$post['wwbpreleasedat'].")");
		
		// Delete the accounts
		$database->query("DELETE FROM ".TB_PREFIX."users WHERE id>4");
		
		// Update the config 
		//stats_time
		$database->query("UPDATE ".TB_PREFIX."config SET commence=".time()."");
		$database->query("UPDATE ".TB_PREFIX."config SET isInstalled=1");
		
		// Wdata 
		$xyas = (1 + (2 * WORLD_MAX));
		$nareadis = NATARS_MAX;
		for ($i = 0; $i < $xyas; $i++) {
			$y = (WORLD_MAX - $i);
			for ($j = 0; $j < $xyas; $j++) {
				$x = ((WORLD_MAX * -1) + $j);
				//choose a field type
				if ($x >= -2 && $x <= 2 && $y >= -2 && $y <= 1) {
					if ($x == 0 && $y == 0) {
						$typ = '1';
					} else {
						$typ = '3';
					}
					$otype = '0';
				} else {
					$rand = rand(1, 1000);
					if (900 >= $rand) {
						if (10 >= $rand) {
							$typ = '1';
							$otype = '0';
						} else if (90 >= $rand) {
							$typ = '2';
							$otype = '0';
						} else if (400 >= $rand) {
							$typ = '3';
							$otype = '0';
						} else if (480 >= $rand) {
							$typ = '4';
							$otype = '0';
						} else if (560 >= $rand) {
							$typ = '5';
							$otype = '0';
						} else if (570 >= $rand) {
							$typ = '6';
							$otype = '0';
						} else if (600 >= $rand) {
							$typ = '7';
							$otype = '0';
						} else if (630 >= $rand) {
							$typ = '8';
							$otype = '0';
						} else if (660 >= $rand) {
							$typ = '9';
							$otype = '0';
						} else if (740 >= $rand) {
							$typ = '10';
							$otype = '0';
						} else if (820 >= $rand) {
							$typ = '11';
							$otype = '0';
						} else {
							$typ = '12';
							$otype = '0';
						}
					}
				}

			//image pick
			$image = "grassland" . rand(1, 11) . "";

			$q = "INSERT into " . TB_PREFIX . "wdata values (0,'" . $typ . "','" . $otype . "','" . $x . "','" . $y . "',0,'" . $image . "','')";
			$database->query($q);
			}
		}
		
		// Tiles
		$list = array('lake0','clay0','hill0','forest0','lake1','clay1','lake0','clay0','hill1','forest1','lake2','clay2','hill0','forest0','hill2','forest2','lake3','clay3','lake0','clay0','hill3','forest3','lake4','clay4','hill0','forest0','hill4','forest4','lake5','clay5','hill0','forest0','hill5','forest5','lake6','clay6','lake0','clay0','hill6','forest0','lake7','clay7','hill0','forest0','hill2','forest5',);
		$xt = 60;
		for ($rand1 = 0; $rand1 <= 198; $rand1++) {
			$xtile[$rand1] = $xt;
			$xt += 60;
		}

		$Volcanolist = 'vulcano';

		$vol_location = array(
			'-2 1 0', '-1 1 4', '0 1 8', '1 1 12', '2 1 16',
			'-2 0 1', '-1 0 5', '0 0 9', '1 0 13', '2 0 17',
			'-2 -1 2', '-1 -1 6', '0 -1 10', '1 -1 14', '2 -1 18',
			'-2 -2 3', '-1 -2 7', '0 -2 11', '1 -2 15', '2 -2 19',
		);

		$counter = $totalz = $maxcounter = 0;

		foreach ($vol_location as $loc) {
			$locs = explode(' ', $loc);
			$x = $locs[0];
			$y = $locs[1];
			$pos = $locs[2];
			$image = $Volcanolist;
			//if ($pos == 10) {
			//    $image = 'grassland1';
			//}
			if ($pos == 13) {
				$fieldtyp = 3;
			} else {
				$fieldtyp = 0;
			}
			$q = "UPDATE " . TB_PREFIX . "wdata set `fieldtype` = $fieldtyp , `image` = '$image' , `pos` ='$pos' WHERE x = '$x' AND y = '$y'";
			$database->query($q);
		}

		$max[] = "5880 6060";
		$max[] = "5940 6060";
		$max[] = "6000 6060";
		$max[] = "6060 6060";
		$max[] = "6120 6060";

		$max[] = "5880 6000";
		$max[] = "5940 6000";
		$max[] = "6000 6000";
		$max[] = "6060 6000";
		$max[] = "6120 6000";

		$max[] = "5880 5940";
		$max[] = "5940 5940";
		$max[] = "6000 5940";
		$max[] = "6060 5940";
		$max[] = "6120 5940";

		$max[] = "5880 5880";
		$max[] = "5940 5880";
		$max[] = "6000 5880";
		$max[] = "6060 5880";
		$max[] = "6120 5880";

		for ($i = 0; $i <= 198; $i += $rand_i) {
			unset($temp, $tile_counter);
			for ($z = 0; $z <= 198; $z += $rand_z) {
				unset($temp, $tile_counter);
				$lists = $list[mt_rand(0, 35)];
				if (isset($lists)) {
					$imgloc = "". GP_LOCATE . "img/mkarte/" . $lists . ".gif";
					//$img = imagecreatefromgif($imgloc);
					list($width, $height) = getimagesize($imgloc);
					$width2 = $width - 60;
					$height2 = $height - 60;
					$pic_x_max = ($width) / 60;
					$pic_y_max = ($height) / 60;
					$maxwidth = round((540 - $width) / 60);
					$maxheight = round((540 - $height) / 60);
					$start_x = $xtile[$i];
					$start_y = $start_y_org = $xtile[$z];
					$temp = array();
					if ($start_x < 11820 && $start_y < 11820) {
						$tile_counter[] = $lists;
						for ($iii = 0; $iii <= $width2 / 60; $iii++) {
							if ($start_x >= 11820&& $start_y >= 11820) {
								unset($temp);
								break;
							}
							for ($zzz = 0; $zzz <= $height2 / 60; $zzz++) {
								$temp[] = "$start_x $start_y";
								$start_y += 60;
							}
							$start_x += 60;
							$start_y = $start_y_org;

						}
						$notallow = $tiles = 0;
						foreach ($temp as $temporary) {
							//foreach ($max as $max2) {
								if (in_array($temporary, $max) != false) {
									$notallow = 1;
									break;
								}
						   // }
						}

						if($notallow == 1){
							unset($temp);
							continue;
						}

						$maxcounter = 0;
						if ($notallow == 0) {
							foreach ($temp as $temporary) {
								$max[] = $temporary;
								$tile_split = preg_split('#(?=\d)(?<=[a-z])#i', $tile_counter[0]);
								$xtp = 0;
								for ($rand3 = -99; $rand3 <= 99; $rand3++) {
									$maptile[$xtp] = $rand3;
									$xtp += 60;
								}
								$xtp2 = 0;
								for ($rand4 = 99; $rand4 > -99; $rand4--) {
									$maptile2[$xtp2] = $rand4;
									$xtp2 += 60;
								}

								$res = explode(' ', $temporary);
								$x = $maptile[$res[0]];
								$y = $maptile2[$res[1]];

								$t = $tiles;
								$notil = $tile_split[0] . '_' . $tile_split[1];
								$text = $notil . "_" . $t;
								$rander = rand(0, 10);
								
								$advance = '';

								if ($rander < 5) {
									if ($tile_split[0] == 'lake') {
										$advance = "`oasistype` = " . rand(10, 12) . " , `fieldtype` = 0 , ";
									} elseif ($tile_split[0] == 'hill') {
										$advance = "`oasistype` = " . rand(7, 9) . " , `fieldtype` = 0 , ";
									} elseif ($tile_split[0] == 'clay') {
										$advance = "`oasistype` = " . rand(4, 6) . " , `fieldtype` = 0 , ";
									} elseif ($tile_split[0] == 'forest') {
										$advance = "`oasistype` = " . rand(1, 3) . " , `fieldtype` = 0 , ";
									}
								} else {
									$advance = "`oasistype` = 0 , `fieldtype` = 0 , ";
								}

								$q = "UPDATE " . TB_PREFIX . "wdata set $advance `image` = '$notil' , `pos` ='$t' WHERE x = '$x' AND y = '$y'";
								$database->query($q);
								$tiles++;
							}
						}
						//$maxcounter++;
					}
				}
				$rand_z = rand(1, 4);
			}
			$rand_i = rand(1, 4);
		}
		
		// Natars
		$wid = $this->getWref(0, 0); $uid = 2;
        $status = $database->getVillageState($wid);
        if($status == 0) {
        	$database->setFieldTaken($wid);
        	$database->addVillage($wid, $uid, 'Natars', '1');
        	$database->addResourceFields($wid, $database->getVillageType($wid));
        	$database->addUnits($wid);
        	$database->addTech($wid);
        	$database->addABTech($wid);
        }
        $database->query("UPDATE " . TB_PREFIX . "vdata SET pop = '781' WHERE owner = $uid");
        $database->query("UPDATE " . TB_PREFIX . "units SET u41 = " . (274700 * SPEED) . ", u42 = " . (995231 * SPEED) . ", u43 = 10000, u44 = " . (3048 * SPEED) . ", u45 = " . (964401 * SPEED) . ", u46 = " . (617602 * SPEED) . ", u47 = " . (6034 * SPEED) . ", u48 = " . (3040 * SPEED) . " , u49 = 1, u50 = 9 WHERE vref = " . $wid . "");
		$status = 0;
		for($i=1;$i<=13;$i++){
			$nareadis = NATARS_MAX;
			do{
				$x = rand(3,intval(floor(NATARS_MAX)));if(rand(1,10)>5)$x = $x*-1;
				$y = rand(3,intval(floor(NATARS_MAX)));if(rand(1,10)>5)$y = $y*-1;
				$dis = sqrt(($x*$x)+($y*$y));
				$wid = $this->getWref($x, $y);
				$status = $database->getVillageState($wid);
			}while(($dis>$nareadis) || $status!=0);
			if($status == 0) {
				$database->setFieldTaken($wid);
				$database->addVillage($wid, $uid, 'Natars', '1');
				$database->addResourceFields($wid, $database->getVillageType($wid));
				$database->addUnits($wid);
				$database->addTech($wid);
				$database->addABTech($wid);
				$database->query("UPDATE " . TB_PREFIX . "vdata SET pop = '238' WHERE wref = '$wid'");
				$database->query("UPDATE " . TB_PREFIX . "vdata SET name = 'WW Village' WHERE wref = '$wid'");
				$database->query("UPDATE " . TB_PREFIX . "vdata SET capital = 0 WHERE wref = '$wid'");
				$database->query("UPDATE " . TB_PREFIX . "vdata SET natar = 1 WHERE wref = '$wid'");
				$database->query("UPDATE " . TB_PREFIX . "units SET u41 = " . (rand(3000, 6000) * SPEED) . ", u42 = " . (rand(4500, 6000) * SPEED) . ", u43 = 10000, u44 = " . (rand(635, 1575) * SPEED) . ", u45 = " . (rand(3600, 5700) * SPEED) . ", u46 = " . (rand(4500, 6000) * SPEED) . ", u47 = " . (rand(1500, 2700) * SPEED) . ", u48 = " . (rand(300, 900) * SPEED) . " , u49 = 0, u50 = 9 WHERE vref = " . $wid . "");
				$database->query("UPDATE " . TB_PREFIX . "fdata SET f22t = 27, f22 = 10, f28t = 25, f28 = 10, f19t = 23, f19 = 10, f99t = 40, f26 = 0, f26t = 0, f21 = 1, f21t = 15, f39 = 1, f39t = 16 WHERE vref = " . $wid . "");
			}
		}
		
		// Oasis
		$database->poulateOasisdata();
		$database->populateOasis();
		$database->populateOasisUnitsLow();
		
		// Install Villages for support and multihunter
		$frandom0 = rand(0, 3); $frandom1 = rand(0, 3); $frandom2 = rand(0, 4); $frandom3 = rand(0, 3);
		$database->addHeroFace(1, $frandom0, $frandom1, $frandom2, $frandom3, $frandom3, $frandom2, $frandom1, $frandom0, $frandom2);
		$database->addHero(1);
		//$database->updateUserField(1, "act", "", 1);
		$this->generateBase('', 1, 'Support');
		$database->modifyUnit($database->getVFH(1), 'hero', 1, 1);
		$database->modifyHero(1, 0, 'wref', $database->getVFH(1), 0);
		for ($s = 1; $s <= 3; $s++) {
			$database->addAdventure($database->getVFH(1), 1);
		}
		$database->modifyGold($uid, 1000, 1);
		$database->query("INSERT INTO " . TB_PREFIX . "users_setting (`id`) values ('1')");
		
		$frandom0 = rand(0, 3); $frandom1 = rand(0, 3); $frandom2 = rand(0, 4); $frandom3 = rand(0, 3);
		$database->addHeroFace(4, $frandom0, $frandom1, $frandom2, $frandom3, $frandom3, $frandom2, $frandom1, $frandom0, $frandom2);
		$database->addHero(4);
		//$database->updateUserField(4, "act", "", 1);
		$this->generateBase('', 4, 'Multihunter');
		$database->modifyUnit($database->getVFH(4), 'hero', 1, 1);
		$database->modifyHero(4, 0, 'wref', $database->getVFH(4), 0);
		for ($s = 1; $s <= 3; $s++) {
			$database->addAdventure($database->getVFH(4), 1);
		}
		$database->modifyGold($uid, 1000, 1);
		$database->query("INSERT INTO " . TB_PREFIX . "users_setting (`id`) values ('4')");
		
		header('Location: panel.php?p=2&done');
	}
	
	function generateBase($kid, $uid, $username){
		global $database;
		if ($kid == '') {
			$kid = rand(1, 4);
		}
		if ($kid == 'nw') {
			$kid = 2;
		} elseif ($kid == 'no') {
			$kid = 3;
		} elseif ($kid == 'sw') {
			$kid = 1;
		} elseif ($kid == 'so') {
			$kid = 4;
		}

		$wid = $database->generateBase($kid);
		
		$database->setFieldTaken($wid);
		$database->addVillage($wid, $uid, $username, 1);
		$database->addResourceFields($wid, $database->getVillageType($wid));
		$database->addUnits($wid);
		$database->addTech($wid);
		$database->addABTech($wid);
		$database->updateUserField($uid, "location", "", 1);
		
        }
	
	
	public function getFieldType($wref){
		global $database;
		$isoasis = $database->isVillageOases($wref);
		if ($isoasis) {
			$get = $database->getOMInfo($wref);
			$type = $get['type'];
		} else {
			$get = $database->getMInfo($wref);
			$type = $get['fieldtype'];
		}
		
		switch ($type) {
			case 1:
			case 2:
			case 3:
				return 'forest';
			break;
			case 4:
			case 5:
			case 6:
				return 'grassland';
			break;
			case 7:
			case 8:
			case 9:
				return 'mountain';
			break;
			case 10:
			case 11:
			case 12:
				return 'sea';
			break;
			default:
				return 'clay';
			break;
		}
		
	}
	
	public function getData($table,$addon='',$column=''){
		global $database;
		
		if(isset($column)){
			return mysqli_fetch_assoc($database->query("SELECT ".$column." FROM ".$table." ".$addon.""));
		}else{
			return mysqli_fetch_assoc($database->query("SELECT * FROM ".$table." ".$addon.""));
		}
	}
	
	public function userData($id, $required){
		global $database;
		$Query = $database->query("SELECT * FROM ".TB_PREFIX."users WHERE id=".$id."");
		$userData = mysqli_fetch_assoc($Query);
		
		return $userData[$required];
	}
	
	public function setLang($lang){
		if(in_array($lang, array("ar","en"))){
			$_SESSION['language'] = $lang;
			header("Location: dorf1.php");

		}
	}
	
	public function getSVG($idBuilding, $t, $isWall = ''){
		global $session;
		//echo $idBuilding; die();
		if(!empty($isWall)){
			switch($idBuilding){
				case '31Bottom': // Teuton
					$sVG = '<svg class="buildingShape 31Bottom " '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="675" height="534" viewBox="0 0 675 534" >
						<g class="clickShape">
						<path d="M18 222.6c0 1.4-.2 1.4-1.6.3-1.4-1.1-1.8-1-3.4 1-.9 1.3-2.2 2.1-2.8 1.7-.6-.4-1.9.5-2.8 1.9-.9 1.4-2.2 2.5-2.8 2.5-2.6 0-4.9 2.7-4.1 4.6 1.4 3.3 1.6 5.6.4 7-1.3 1.6-.5 4.4 1.3 4.4s3.8 4.6 2.6 5.8c-1.3 1.3-.6 6.7.9 6.7.6 0 1.5.3 1.9.7.4.5 1.5.8 2.5.8 1.1 0 1.6.5 1.3 1.4-.4.9 1 1.7 4.3 2.6 3.3 1 4.2 1.5 2.7 1.7-3.7.7-5.8 7.9-2.9 10.3 1.1.9 1.2 1.7.5 3-1.5 2.7-1.2 4 .8 4 .9 0 2.2.8 2.9 1.9 1.3 2.1 7.8 3.7 9.6 2.4.7-.5 2.3-.8 3.7-.8 2.4 0 2.6.4 3.7 8.8.7 4.8 2.2 10.5 3.3 12.7 1.1 2.1 3.1 7.8 4.5 12.6s2.9 9.2 3.4 9.8c.5.6 1.2 2.6 1.6 4.4.3 1.8 1.2 3.8 1.8 4.5.7.6 2 2.8 3 4.7 5.6 10.8 12.9 21.8 14.2 21.4.8-.3 4.8 5.3 6.4 8.9.2.5 2.7 3.2 5.6 6.1 5 4.9 5.2 5.4 5.1 10 0 4.5.3 5.2 3.6 7.9l3.7 2.9 4.3-2.4 4.3-2.4.1-9.4c0-5.3.5-9.4 1-9.2 2.3.6 17.3 12.8 17.7 14.3.3.9 0 7.9-.6 15.4l-1 13.7 3.6 3.6c3.3 3.2 3.8 3.4 6.4 2.3 2.6-1 3.9-.6 14.8 4.9 6.6 3.4 14 7.4 16.5 9 2.5 1.6 7.2 3.6 10.5 4.5 3.3.9 10.6 3.4 16.2 5.7 10.2 4 10.2 4 10.5 7.6.3 3.5.5 3.7 3.6 3.6 2.5-.2 3.2.2 3 1.5-.2 1.2.6 1.6 3.5 1.8 2 0 4.5.4 5.3.7.9.4 3.2.1 5-.6 2.7-.9 3.7-.9 4.6 0 .8.8 4.5 1.2 10.2 1.2 8.2 0 16.7 1.2 50.6 7.5 13.3 2.5 14 2.6 14 4 0 1.5 5.4 3 6.9 2.1.5-.4 2.2.5 3.8 2 2.9 2.7 4.2 2.8 18.5.8 2.7-.3 6.2-.1 8.2.6 3.2 1 3.9.9 6.7-1 2.1-1.6 3.9-2.1 5.8-1.7 3.4.7 5.1-.8 5.1-4.5 0-1.5.3-2.8.8-2.8 5.1-.1 23.5-3 43.8-7 16.7-3.3 27.7-5 32.7-5 5.3 0 8.7-.6 11.8-2 2.4-1.1 5.4-2 6.6-2 1.2 0 2.6-.7 3-1.5.5-.8 3.8-2.5 7.5-3.9s8.9-3.9 11.5-5.5c3.9-2.5 4.9-2.7 5.6-1.5.5.9 2.5 1.4 6.2 1.4 4 0 5.6.4 6.3 1.6 1.1 1.9 3.2 1 3.2-1.3 0-1.1.6-1.4 2.5-.9 1.8.5 2.8.1 3.7-1.4 1.6-2.5 2.5-2.5 4.6 0 1.5 1.8 2.1 1.9 5.2.9 5.4-1.7 8.8-4.9 9.5-9.1.6-3.2.3-4-1.9-6.1-1.4-1.3-2.6-2.7-2.6-3.1 0-.3 1.7-.6 3.9-.6 6.1 0 6.4-1 5.6-17.3l-.7-14.2 8.8-6.8 8.9-6.8.5 9.8c.5 9.3.6 9.7 3 10.4 4.9 1.4 6.7.8 12-3.7 9.1-7.8 9.7-8 12.4-5.5 2.2 2.1 2.3 2.1 3.9.3.9-1 1.7-3.1 1.7-4.5 0-3.1.8-3.4 2.5-1 2.2 2.9 4.5-.1 4.5-5.7 0-3.7-.6-5.5-3-8.5-1.7-2.1-3-4.2-3-4.7 0-.6 2.9-4 6.3-7.6 7.5-7.9 17.4-25.3 23.1-40.6 3.2-8.5 8.6-30.2 8.6-34.3 0-.9-1.6-1.3-4.9-1.3h-4.9l-3.1 9.2c-5.7 17-11 26.8-22.8 42.7-7.9 10.6-24.5 27.8-23.7 24.4.7-2.5-2.7-6.3-5.5-6.3s-5.1 2.7-5.1 5.9c0 1.6-.7 3.4-1.5 4.1-.8.7-1.5 2.9-1.5 5 0 3.5-.6 4.3-8.5 11-4.7 4-8.9 7-9.2 6.6-.4-.4-1.2-2.1-1.8-3.9-2.4-7.4-11.4-6.3-11.5 1.4 0 1.5-.7 3.2-1.6 4-.9.7-1.5 2.6-1.4 4.8.5 10.3 1.6 9.1-14.5 16.5-14.2 6.4-47 18.1-59 21-3.3.8-9.6 2.4-14 3.5-8.4 2.2-21.1 4.5-45.5 8.3-12.8 2-18.2 2.2-46.5 2.2-40.8-.1-64.7-2.7-98.5-10.8-10.6-2.5-38.4-11.8-46.5-15.6-20.4-9.5-31.9-15.2-36.7-18.4-5.8-3.7-5.8-3.7-5.8-8.6 0-3.6-.5-5.5-2-7-1.1-1.1-1.7-2.6-1.4-3.3.7-2-3.4-6.7-6-6.7-3 0-5.6 3-5.6 6.3v2.7l-4.7-3.5c-12.4-9-15.6-12-15.8-15.3-.2-1.8-.9-3.9-1.6-4.8-.7-.8-1.3-2.6-1.4-3.9-.1-3-3.2-6.5-5.8-6.5-1.2 0-3 .9-4.2 2l-2.2 2.1-6.4-6.8c-7.2-7.7-18.3-23.7-25.2-36.3-7.5-13.9-16-38.9-17.2-50.5-.9-9.1-.9-24.2-.1-32.3l.8-7.2H28.1c-8.6 0-10.1.2-10.1 1.6z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="hoverShape">
						<path d="M18 222.6c0 1.4-.2 1.4-1.6.3-1.4-1.1-1.8-1-3.4 1-.9 1.3-2.2 2.1-2.8 1.7-.6-.4-1.9.5-2.8 1.9-.9 1.4-2.2 2.5-2.8 2.5-2.6 0-4.9 2.7-4.1 4.6 1.4 3.3 1.6 5.6.4 7-1.3 1.6-.5 4.4 1.3 4.4s3.8 4.6 2.6 5.8c-1.3 1.3-.6 6.7.9 6.7.6 0 1.5.3 1.9.7.4.5 1.5.8 2.5.8 1.1 0 1.6.5 1.3 1.4-.4.9 1 1.7 4.3 2.6 3.3 1 4.2 1.5 2.7 1.7-3.7.7-5.8 7.9-2.9 10.3 1.1.9 1.2 1.7.5 3-1.5 2.7-1.2 4 .8 4 .9 0 2.2.8 2.9 1.9 1.3 2.1 7.8 3.7 9.6 2.4.7-.5 2.3-.8 3.7-.8 2.4 0 2.6.4 3.7 8.8.7 4.8 2.2 10.5 3.3 12.7 1.1 2.1 3.1 7.8 4.5 12.6s2.9 9.2 3.4 9.8c.5.6 1.2 2.6 1.6 4.4.3 1.8 1.2 3.8 1.8 4.5.7.6 2 2.8 3 4.7 5.6 10.8 12.9 21.8 14.2 21.4.8-.3 4.8 5.3 6.4 8.9.2.5 2.7 3.2 5.6 6.1 5 4.9 5.2 5.4 5.1 10 0 4.5.3 5.2 3.6 7.9l3.7 2.9 4.3-2.4 4.3-2.4.1-9.4c0-5.3.5-9.4 1-9.2 2.3.6 17.3 12.8 17.7 14.3.3.9 0 7.9-.6 15.4l-1 13.7 3.6 3.6c3.3 3.2 3.8 3.4 6.4 2.3 2.6-1 3.9-.6 14.8 4.9 6.6 3.4 14 7.4 16.5 9 2.5 1.6 7.2 3.6 10.5 4.5 3.3.9 10.6 3.4 16.2 5.7 10.2 4 10.2 4 10.5 7.6.3 3.5.5 3.7 3.6 3.6 2.5-.2 3.2.2 3 1.5-.2 1.2.6 1.6 3.5 1.8 2 0 4.5.4 5.3.7.9.4 3.2.1 5-.6 2.7-.9 3.7-.9 4.6 0 .8.8 4.5 1.2 10.2 1.2 8.2 0 16.7 1.2 50.6 7.5 13.3 2.5 14 2.6 14 4 0 1.5 5.4 3 6.9 2.1.5-.4 2.2.5 3.8 2 2.9 2.7 4.2 2.8 18.5.8 2.7-.3 6.2-.1 8.2.6 3.2 1 3.9.9 6.7-1 2.1-1.6 3.9-2.1 5.8-1.7 3.4.7 5.1-.8 5.1-4.5 0-1.5.3-2.8.8-2.8 5.1-.1 23.5-3 43.8-7 16.7-3.3 27.7-5 32.7-5 5.3 0 8.7-.6 11.8-2 2.4-1.1 5.4-2 6.6-2 1.2 0 2.6-.7 3-1.5.5-.8 3.8-2.5 7.5-3.9s8.9-3.9 11.5-5.5c3.9-2.5 4.9-2.7 5.6-1.5.5.9 2.5 1.4 6.2 1.4 4 0 5.6.4 6.3 1.6 1.1 1.9 3.2 1 3.2-1.3 0-1.1.6-1.4 2.5-.9 1.8.5 2.8.1 3.7-1.4 1.6-2.5 2.5-2.5 4.6 0 1.5 1.8 2.1 1.9 5.2.9 5.4-1.7 8.8-4.9 9.5-9.1.6-3.2.3-4-1.9-6.1-1.4-1.3-2.6-2.7-2.6-3.1 0-.3 1.7-.6 3.9-.6 6.1 0 6.4-1 5.6-17.3l-.7-14.2 8.8-6.8 8.9-6.8.5 9.8c.5 9.3.6 9.7 3 10.4 4.9 1.4 6.7.8 12-3.7 9.1-7.8 9.7-8 12.4-5.5 2.2 2.1 2.3 2.1 3.9.3.9-1 1.7-3.1 1.7-4.5 0-3.1.8-3.4 2.5-1 2.2 2.9 4.5-.1 4.5-5.7 0-3.7-.6-5.5-3-8.5-1.7-2.1-3-4.2-3-4.7 0-.6 2.9-4 6.3-7.6 7.5-7.9 17.4-25.3 23.1-40.6 3.2-8.5 8.6-30.2 8.6-34.3 0-.9-1.6-1.3-4.9-1.3h-4.9l-3.1 9.2c-5.7 17-11 26.8-22.8 42.7-7.9 10.6-24.5 27.8-23.7 24.4.7-2.5-2.7-6.3-5.5-6.3s-5.1 2.7-5.1 5.9c0 1.6-.7 3.4-1.5 4.1-.8.7-1.5 2.9-1.5 5 0 3.5-.6 4.3-8.5 11-4.7 4-8.9 7-9.2 6.6-.4-.4-1.2-2.1-1.8-3.9-2.4-7.4-11.4-6.3-11.5 1.4 0 1.5-.7 3.2-1.6 4-.9.7-1.5 2.6-1.4 4.8.5 10.3 1.6 9.1-14.5 16.5-14.2 6.4-47 18.1-59 21-3.3.8-9.6 2.4-14 3.5-8.4 2.2-21.1 4.5-45.5 8.3-12.8 2-18.2 2.2-46.5 2.2-40.8-.1-64.7-2.7-98.5-10.8-10.6-2.5-38.4-11.8-46.5-15.6-20.4-9.5-31.9-15.2-36.7-18.4-5.8-3.7-5.8-3.7-5.8-8.6 0-3.6-.5-5.5-2-7-1.1-1.1-1.7-2.6-1.4-3.3.7-2-3.4-6.7-6-6.7-3 0-5.6 3-5.6 6.3v2.7l-4.7-3.5c-12.4-9-15.6-12-15.8-15.3-.2-1.8-.9-3.9-1.6-4.8-.7-.8-1.3-2.6-1.4-3.9-.1-3-3.2-6.5-5.8-6.5-1.2 0-3 .9-4.2 2l-2.2 2.1-6.4-6.8c-7.2-7.7-18.3-23.7-25.2-36.3-7.5-13.9-16-38.9-17.2-50.5-.9-9.1-.9-24.2-.1-32.3l.8-7.2H28.1c-8.6 0-10.1.2-10.1 1.6z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="clickShapeWinter">
						<path d="M29.9 221.7c-.2 1.3-1.9 27.7-2.9 43.8-.4 8.2-1.2 17-1.5 19.4-.5 3.2-.3 5.4.8 7.5 7.2 14.7 12.6 26.5 13.1 28.6.3 1.4 2.6 6.1 5.1 10.5 8.6 14.9 14.3 27.1 15.9 33.7.3 1.4 2.5 4.1 4.8 5.9 2.4 1.8 5 4 5.9 4.8 1.5 1.6 9.7 13.5 13.5 19.7 2.8 4.6 2.8 4.6 8.3 5.5 3.2.5 7.4 2.3 11.8 5.2 3.7 2.4 8 5.1 9.6 6 2 1.3 2.7 2.6 2.7 4.7 0 1.9.8 3.7 2.1 4.8 1.2.9 3.6 2.8 5.3 4.3 1.7 1.4 6.5 4.2 10.6 6.3 4.1 2.2 8 4.3 8.6 4.7 3.5 2.7 19.3 10.1 27.4 12.8 8.2 2.8 22.6 9.1 27 12 .8.5 4.2 2.2 7.5 3.8 3.3 1.5 6.6 3.4 7.3 4 .7.7 2.5 1.5 4 1.8 1.5.3 4.7 1 7.2 1.7 2.5.6 6.8 1.4 9.5 1.8 2.8.5 9.3 2.4 14.5 4.3 5.2 2 11.8 4.4 14.5 5.3 2.8 1 7.1 2.5 9.6 3.5 3.3 1.3 9.8 2.1 22.6 2.9 16.2 1 19.7.9 34.4-.9 9-1.1 25-2.5 35.4-3.1 10.5-.6 21.3-1.5 24-2 2.8-.5 8.2-1.2 12-1.5 12.3-1.1 23.8-3.4 32.5-6.3 4.7-1.6 13-4.2 18.5-5.7 10.3-2.8 27.9-8.9 35.7-12.4 2.3-1 7-3 10.3-4.4 3.3-1.4 9.2-4 13-5.8 3.9-1.7 7.9-3.5 9.1-3.9 2.4-.9 14.4-8.5 15.9-10 .6-.5 3.5-4.2 6.5-8.2 7.1-9.2 24.6-26.4 29.6-29.1 4.3-2.2 7.7-6.6 11.5-14.7 5.9-12.7 11-22.3 12.9-24.5 4-4.6 15-25.6 20.6-39.5 3.5-8.6 6.3-21.1 6.3-28.3l.1-5.7h-21.5l-2.9 8.5c-1.6 4.7-3.6 9.8-4.4 11.3-.7 1.5-3.7 7.2-6.4 12.7-2.8 5.5-6.5 11.8-8.2 14-1.8 2.2-4 5.3-5 6.9-3.2 5.1-9.9 11.7-13.8 13.7-3.1 1.6-5 1.8-10.6 1.3-7.6-.7-8-.5-12.5 6.5-3.6 5.7-18.1 15-25.9 16.5-3.5.8-6.2 5.3-8.3 14.1-1 3.8-2.8 9-4 11.5-2.2 4.3-3.1 4.9-14.2 10.2-10.9 5.2-17.2 7.3-41.8 14.1-4.9 1.3-13.3 4-18.5 5.9-5.2 1.9-13.5 4.3-18.5 5.2-4.9 1-11 2.2-13.5 2.8-4.2.9-20.3 2.6-31 3.3-15.8 1-76.8 1.3-84.9.5-15.7-1.7-34-4.9-44.6-8-5.5-1.5-12.6-3.4-15.8-4-6.6-1.3-18.3-5.1-22.4-7.3-6.8-3.6-22.6-10.7-23.8-10.7-.7 0-2.6-1.1-4.2-2.4-1.5-1.3-6.1-3.7-10-5.4-7-3-15.3-9.7-15.3-12.5 0-.7-1.1-3.5-2.4-6.2-3.1-6.5-8.7-12.5-11.7-12.5-2.7 0-4-.7-13.5-7.2-8.8-5.9-11.7-8.7-14.9-14.3-2.1-3.6-3.5-4.7-6.7-5.6-2.3-.6-7.8-3.1-12.3-5.5-7.8-4.2-8.4-4.8-14.6-14.1-8.2-12.6-17-30.2-18.7-37.5-.7-3.2-2.2-7.8-3.3-10.4-5.1-11.5-6.1-17.7-6.1-39.2V221h-3.4c-1.9 0-3.4.3-3.5.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="hoverShapeWinter">
						<path d="M29.6 224.7c-1.3 8.2-.6 30.9 1.3 41.4 1.1 5.7 2.2 11.2 2.6 12.1.4 1 .3 1.8-.3 1.8-.5 0-1.2 1-1.6 2.3-.3 1.3-1.9 3.6-3.5 5.2-2.3 2.4-2.7 3.4-1.9 4.8.6 1.3 1.7 1.8 3.3 1.5 2-.4 2.7.3 4.8 5.1 4.9 10.6 8.9 20.3 10.2 25 2.8 9.5 7.6 21.5 11.5 28.1 2.2 3.7 4.5 9.1 5.1 11.9.9 4.3 1.7 5.5 5.1 7.8 2.2 1.6 5.9 4.3 8.1 6.1 2.3 1.8 5.9 4 8 4.9 3.8 1.7 3.9 1.7 3.5 6.7-.3 4.6 0 5.3 2.7 7.8 3.6 3.3 4.4 3.4 9.7.7l4-2.1-.4-9.4c-.3-5.2-.1-9.4.3-9.4.5 0 4.8 3 9.5 6.7l8.6 6.8-.6 15.5-.7 15.5 4.5 3.7c3.5 3 6 4.1 11.7 5.3 4 .9 7.6 1.9 7.9 2.3.3.4 1.6 1.3 3 2 8.9 4.6 10.9 5.7 14 8.1 4.5 3.5 7.3 4.6 14.6 6.1 5.8 1.1 16.3 4.4 19.6 6.1.9.5 1.8 2.1 2 3.5.2 1.5 1 3 1.8 3.5 14.3 7.3 21.5 9.4 38.5 11.4 20.7 2.4 20.1 2.3 21.4 4.8.7 1.3 3.7 3.8 6.8 5.6 4.9 2.9 6.7 3.4 16.1 4.2 5.8.4 14.1 1.6 18.4 2.5 7.7 1.7 8 1.7 12.6-.4 3.4-1.5 7.6-2.2 15.4-2.6 5.9-.3 11.7-1 12.9-1.7 1.6-.8 4.7-.9 10.3-.4 9.8.9 17.9.8 31.1-.5 5.5-.5 15-1.4 21.2-1.9 6.1-.6 12.9-1.8 15-2.6 2.1-.9 7-2.3 10.8-3 3.9-.7 11.7-2.7 17.5-4.5 5.8-1.7 12.1-3.5 14-4 6.9-1.5 23.7-8.2 36.7-14.6 7.2-3.5 13.9-6.4 14.7-6.4 2.4 0 8.4-2.9 16.3-7.9 6.4-4.1 7.5-5.3 10.6-11.5l3.5-6.8-3.5-3.9c-2-2.1-4.1-3.9-4.8-3.9-.7 0-3.2 1.6-5.6 3.5-3.2 2.5-4.6 3.1-5.1 2.2-.4-.7-.9-6.3-1.1-12.4l-.4-11.2 3.6-3.4c4.6-4.3 12.6-10.7 13.5-10.7.3 0 .6 4.2.6 9.4v9.4l4.7 1.6c4.3 1.5 4.8 1.5 6.4-.2 3.2-3.2 5.5-4.8 6.6-4.5.6.2 2.7-.5 4.7-1.5 2-1.1 4.5-1.8 5.4-1.5.9.2 3.7-.5 6.2-1.6 4.2-1.8 4.7-2.5 6.8-8.7 1.4-3.9 3.4-7.5 4.8-8.7 1.3-1 4.3-5.1 6.5-9 2.2-4 5.6-9.4 7.5-12.2 3.9-5.7 14.2-26 18-35.5 3.5-8.7 6.3-21.2 6.3-28.3l.1-5.7h-21.5l-2.9 8.5c-1.6 4.7-3.6 9.8-4.4 11.3-.7 1.5-3.7 7.2-6.5 12.7-2.7 5.5-6.6 12.2-8.6 14.8-9.6 12.7-11.4 14.8-16.4 18.9-2.9 2.4-6.6 6.1-8.2 8.3l-2.9 4-1.1-5c-1-4.5-1.3-5-3.5-4.7-3.4.3-6.2 3.9-5.8 7.4.2 1.6-.3 3.2-.9 3.6-.7.5-1.4 2.5-1.5 4.6-.3 3.5-1.2 4.7-8.6 11.2-4.5 4.1-8.6 7.4-9.2 7.4-1.1 0-2.4-1.9-2.7-4.3-.5-3-2.3-5.8-3.4-5.2-.5.4-.9.1-.9-.4 0-1.6-3.6-1.3-5 .4-.7.9-1.4 3.2-1.5 5.1-.1 1.9-1 4.3-1.9 5.3-1.2 1.3-1.5 3.4-1.4 8l.3 6.3-15.4 7.3c-14.1 6.7-20.4 8.8-45.6 15.8-4.9 1.3-13.3 4-18.5 5.9-5.2 1.9-13.5 4.3-18.5 5.2-4.9 1-11 2.2-13.5 2.8-4.2.9-20.3 2.6-31 3.3-15.8 1-76.8 1.3-84.9.5-15.7-1.7-34-4.9-44.6-8-5.5-1.5-12.6-3.4-15.8-4-6.7-1.3-18.3-5.2-22.4-7.3-7.2-3.9-18.6-8.6-19.4-8.1-1.2.7-12.6-5.2-16-8.3-1.4-1.3-3.4-2.5-4.5-2.7-3.9-.8-7.5-2.6-12.3-6.2-4.3-3.3-4.9-4.2-5.5-8.4-.4-2.6-1.3-5.3-2.1-6-.8-.7-1.5-2.5-1.5-4.1 0-3.4-3.8-6.4-7-5.4-2.6.8-4.3 4-3.5 6.5.4 1.1.3 2 0 2-1 0-9.1-5.8-15.5-11.1-5.1-4.3-6-5.5-6-8.3 0-1.9-.7-3.9-1.5-4.6-.8-.7-1.5-2.4-1.5-3.9 0-3.6-1.7-5.1-5.6-5.1-2.1 0-3.6.7-4.6 2.1-1.4 2.1-1.6 1.9-9.1-6.8-14.1-16.3-28.9-41.4-32.2-54.8-.9-3.3-2.5-8.2-3.6-11-5.3-12.3-6.1-17.8-6.1-39.3V221h-3.3c-3 0-3.3.3-3.9 3.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						</svg>';
				break;
				case '31Top': // Teuton
					$sVG = '<svg class="buildingShape 31Top" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="675" height="534" viewBox="0 0 675 534" >
						<g class="clickShape">
						<path d="M277.5 28.6c-29.7 1.9-63.2 9.4-93.8 21-9 3.4-11.7 3.5-11.7.3 0-2.1-3.7-5.9-5.7-5.9-2.2 0-5.5 4-5 6.1.3 1.2-.2 2.4-1.3 3.2-1.3.9-2 2.8-2.1 5.5-.3 4-.5 4.3-7.7 8.7-4.1 2.6-8.1 4.4-8.8 4.1-.7-.3-2-2.1-2.8-4-2-4.7-6.1-5.9-8.8-2.6-1 1.3-1.7 2.9-1.4 3.6.3.7-.4 2-1.5 3-1.5 1.3-1.9 2.9-1.8 5.6.3 3.7.2 3.8-8.3 9.2-12 7.5-18.5 12.7-31.5 25-12 11.5-23.7 25.3-29.5 35.1-9.8 16.5-17.7 34.8-20.9 48.7-4.2 18.6-3.6 17.1-7.6 16.5-3.2-.6-3.6-.3-4.6 2.3-.7 1.8-1.8 2.9-2.7 2.7-.9-.1-1.6.7-1.8 2-.3 2.3-.1 2.3 9.7 2.3h10l2.6-8.8c1.4-4.8 2.9-9.6 3.2-10.7 1-3.6 13.5-24.8 21.6-36.6 6.5-9.7 8.6-12 12.1-13.5 5.1-2.3 12-8.9 11-10.7-.4-.6-.4-.9.1-.5.9.8 1.9-.2 12.8-12.5 4.6-5.2 9.9-9.7 16.7-14.1 9.9-6.5 9.9-6.5 13.7-5.2 4.5 1.5 10 .3 10.8-2.5.4-1 .1-7.3-.5-14-.6-6.6-1-12.2-.8-12.4.9-.8 16.3-9.3 16.5-9.1.1.1-.2 4.4-.8 9.5-.6 5.2-.7 9.7-.2 10.1.4.3 3.2 1.2 6.2 1.9 5.1 1.1 5.6 1 7.8-1.1 1.2-1.2 2.3-3 2.3-3.9 0-1.1 1.4-2.6 3.3-3.5 9.3-4.9 34.6-14.8 45.2-17.8 10.4-3 14.1-3.5 27.5-4.1 13.9-.6 16.2-.9 22.8-3.6 7.2-2.8 7.7-2.9 26-2.9 32.5 0 63.2 1.8 76.1 4.6 6.3 1.3 11.9 2.8 12.6 3.4.6.5 2.7.7 4.7.4 2.3-.4 4.4-.1 5.9.9 1.8 1.1 5.7 1.5 14.7 1.6l12.3.2L447 74c24.2 8.3 28 10.1 28 13.1 0 1.2 1.3 2.5 3.4 3.5 3 1.5 3.8 1.5 7.7.2l4.4-1.5-.4-8.8-.3-8.8 2.9 1.9c1.7 1 5.8 3.7 9.2 5.9l6.2 4-.7 14.5-.6 14.5 5 2.4c3.2 1.5 5.9 2.1 7.5 1.7 1.9-.4 2.6-.1 3.6 2 1.1 2.5 1.3 2.5 3.5 1.1 2.2-1.5 2.5-1.4 4.7.9 1.3 1.4 3.4 2.4 4.9 2.4 2.2 0 5.4 2.8 16.1 13.8 7.4 7.6 16.6 18 20.5 23.2 7.5 10 17.9 27.4 22.5 37.5 3.5 7.9 10.5 28 11.3 32.7.6 3.5.5 3.8-2.1 4.4-3.7.9-7.3 3.7-14.5 10.9-6.5 6.7-6.8 8.2-3.1 16 1.7 3.7 2.4 4.1 9.7 6.3l7.8 2.4-.6 5.1c-.4 2.9-1 6.2-1.3 7.5-.5 2.2-.3 2.3 4.3 2l4.9-.3 1.1-8.5c1.5-10.2 1.7-45.6.4-59.6-2.1-23.7-18.4-58.9-39.8-85.8-6.9-8.7-26.3-28.2-39.4-39.6-9.1-7.9-9.8-8.7-9.8-12.2 0-2-.7-4.3-1.5-5.2-.8-.8-1.5-2.4-1.5-3.5 0-2.2-3.5-5.1-6.2-5.1-2 0-5.3 4.4-4.4 5.9.3.5-.1 1.1-.9 1.5-.8.3-1.5 1.2-1.5 2.1 0 2.4-1.3 1.9-9.4-4.1-7.5-5.4-7.6-5.6-7.6-9.9 0-2.5-.6-5-1.5-5.9-.8-.8-1.2-2.2-.9-3 1-2.7-2.3-6.6-5.6-6.6s-6.5 3.6-5.4 6.3c.3.9-.2 1.9-1.3 2.5-1.3.7-5.5-.2-16.3-3.9-8-2.7-19.2-6-25-7.4-5.8-1.4-13-3.2-16-4-16.6-4.4-41-7.9-69.5-10-23.2-1.7-43.3-1.7-72 .1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="hoverShape">
						<path d="M277.5 28.6c-29.7 1.9-63.2 9.4-93.8 21-9 3.4-11.7 3.5-11.7.3 0-2.1-3.7-5.9-5.7-5.9-2.2 0-5.5 4-5 6.1.3 1.2-.2 2.4-1.3 3.2-1.3.9-2 2.8-2.1 5.5-.3 4-.5 4.3-7.7 8.7-4.1 2.6-8.1 4.4-8.8 4.1-.7-.3-2-2.1-2.8-4-2-4.7-6.1-5.9-8.8-2.6-1 1.3-1.7 2.9-1.4 3.6.3.7-.4 2-1.5 3-1.5 1.3-1.9 2.9-1.8 5.6.3 3.7.2 3.8-8.3 9.2-12 7.5-18.5 12.7-31.5 25-12 11.5-23.7 25.3-29.5 35.1-9.8 16.5-17.7 34.8-20.9 48.7-4.2 18.6-3.6 17.1-7.6 16.5-3.2-.6-3.6-.3-4.6 2.3-.7 1.8-1.8 2.9-2.7 2.7-.9-.1-1.6.7-1.8 2-.3 2.3-.1 2.3 9.7 2.3h10l2.6-8.8c1.4-4.8 2.9-9.6 3.2-10.7 1-3.6 13.5-24.8 21.6-36.6 6.5-9.7 8.6-12 12.1-13.5 5.1-2.3 12-8.9 11-10.7-.4-.6-.4-.9.1-.5.9.8 1.9-.2 12.8-12.5 4.6-5.2 9.9-9.7 16.7-14.1 9.9-6.5 9.9-6.5 13.7-5.2 4.5 1.5 10 .3 10.8-2.5.4-1 .1-7.3-.5-14-.6-6.6-1-12.2-.8-12.4.9-.8 16.3-9.3 16.5-9.1.1.1-.2 4.4-.8 9.5-.6 5.2-.7 9.7-.2 10.1.4.3 3.2 1.2 6.2 1.9 5.1 1.1 5.6 1 7.8-1.1 1.2-1.2 2.3-3 2.3-3.9 0-1.1 1.4-2.6 3.3-3.5 9.3-4.9 34.6-14.8 45.2-17.8 10.4-3 14.1-3.5 27.5-4.1 13.9-.6 16.2-.9 22.8-3.6 7.2-2.8 7.7-2.9 26-2.9 32.5 0 63.2 1.8 76.1 4.6 6.3 1.3 11.9 2.8 12.6 3.4.6.5 2.7.7 4.7.4 2.3-.4 4.4-.1 5.9.9 1.8 1.1 5.7 1.5 14.7 1.6l12.3.2L447 74c24.2 8.3 28 10.1 28 13.1 0 1.2 1.3 2.5 3.4 3.5 3 1.5 3.8 1.5 7.7.2l4.4-1.5-.4-8.8-.3-8.8 2.9 1.9c1.7 1 5.8 3.7 9.2 5.9l6.2 4-.7 14.5-.6 14.5 5 2.4c3.2 1.5 5.9 2.1 7.5 1.7 1.9-.4 2.6-.1 3.6 2 1.1 2.5 1.3 2.5 3.5 1.1 2.2-1.5 2.5-1.4 4.7.9 1.3 1.4 3.4 2.4 4.9 2.4 2.2 0 5.4 2.8 16.1 13.8 7.4 7.6 16.6 18 20.5 23.2 7.5 10 17.9 27.4 22.5 37.5 3.5 7.9 10.5 28 11.3 32.7.6 3.5.5 3.8-2.1 4.4-3.7.9-7.3 3.7-14.5 10.9-6.5 6.7-6.8 8.2-3.1 16 1.7 3.7 2.4 4.1 9.7 6.3l7.8 2.4-.6 5.1c-.4 2.9-1 6.2-1.3 7.5-.5 2.2-.3 2.3 4.3 2l4.9-.3 1.1-8.5c1.5-10.2 1.7-45.6.4-59.6-2.1-23.7-18.4-58.9-39.8-85.8-6.9-8.7-26.3-28.2-39.4-39.6-9.1-7.9-9.8-8.7-9.8-12.2 0-2-.7-4.3-1.5-5.2-.8-.8-1.5-2.4-1.5-3.5 0-2.2-3.5-5.1-6.2-5.1-2 0-5.3 4.4-4.4 5.9.3.5-.1 1.1-.9 1.5-.8.3-1.5 1.2-1.5 2.1 0 2.4-1.3 1.9-9.4-4.1-7.5-5.4-7.6-5.6-7.6-9.9 0-2.5-.6-5-1.5-5.9-.8-.8-1.2-2.2-.9-3 1-2.7-2.3-6.6-5.6-6.6s-6.5 3.6-5.4 6.3c.3.9-.2 1.9-1.3 2.5-1.3.7-5.5-.2-16.3-3.9-8-2.7-19.2-6-25-7.4-5.8-1.4-13-3.2-16-4-16.6-4.4-41-7.9-69.5-10-23.2-1.7-43.3-1.7-72 .1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="clickShapeWinter">
						<path d="M327.9 27.1c-.8.5-9 .9-18.4.9-20.5 0-54.2 1.8-57 3-1.1.5-6.7 1.8-12.5 3s-16.6 3.7-24 5.7l-13.5 3.5-19.5-.4c-21.6-.6-22.4-.4-29 5.9-1.9 1.8-7.5 5.6-12.5 8.3-9.6 5.2-14.3 9.2-18.3 15.4-1.3 2.2-2.8 4.4-3.3 5-.5.6-3.6 4.6-6.9 8.8-3.3 4.3-6.4 7.8-6.9 7.8-1.4 0-16.1 12.9-25.6 22.5-17.2 17.2-28.2 33.8-38.8 58.7-4.1 9.6-9.4 29.9-10.4 39.5l-.6 6.3h7.8l1.7-7.2c2.9-11.8 10.1-25 18.6-33.8 4-4.1 8.8-9.8 10.7-12.7 1.9-2.8 4.9-6.2 6.5-7.5 1.7-1.3 4.4-3.4 6.2-4.8 1.7-1.3 4.6-4.7 6.3-7.5 3.2-4.9 10.5-12.7 16.2-17.3 1.5-1.3 7-5.9 12.2-10.2 7.8-6.6 10.1-8 14-8.6 2.5-.3 6.1-1.5 7.9-2.6 2.6-1.6 3.2-2.6 3.2-5.4 0-1.9.4-3.4.8-3.4.5 0 1.7-.8 2.8-1.8 1-.9 3.9-3.2 6.2-5.1 4.3-3.3 4.5-3.4 7.2-1.8 6 3.6 10.7 2.3 16.8-4.5 3.8-4.2 12.4-8.8 23.2-12.3 3-1 6-2.1 6.5-2.5.6-.4 3.5-1.5 6.5-2.5s6.9-2.5 8.5-3.3c2.2-1.2 8-1.8 23-2.3 17-.6 20.7-1.1 24.9-2.9 4.5-1.9 7.9-2.2 38-3.1 23.8-.7 35.2-.7 40.6.1 4.1.6 10.6 1.5 14.3 2 3.8.5 11.7 2.5 17.5 4.4 8.5 2.7 12.9 3.6 21.7 4.1 28.6 1.5 35.2 2.5 49.4 7.7 15 5.5 18.1 6.9 20.1 9.3 2.1 2.6 10 7.5 11.8 7.4 4-.2 8.7-2.6 8.7-4.5 0-1.3.6-1.8 2-1.6 3.6.6 14.4 6.5 14.7 8.1.2.9 0 4.9-.5 9-.5 4-.6 8.3-.3 9.6.8 3 10.7 7.2 22.9 9.5 8.7 1.7 10.3 2.3 14.8 6.2 2.8 2.3 7.4 7.2 10.2 10.8 4.6 5.9 6.8 8.4 14.7 16.9 4.2 4.5 10 11.8 10 12.6 0 .4 1.4 2.7 3 5.2 4.4 6.5 6.6 11.3 7.3 15.6.3 2.1 2.2 6.3 4.2 9.3 2 3 4.7 7.9 6.1 10.9 2.1 4.9 2.3 6.5 1.7 12.7-.4 5-1.1 7.5-2.4 8.5-.9.8-5.1 4.7-9.3 8.7-8.8 8.4-9.2 9.9-5 20.5 1.9 5 4 8 8.5 12.3 3.2 3.2 5.9 6.4 5.9 7.2 0 2.9 3 4.6 8 4.6 4.5 0 4.9-.2 5.4-2.8 1.9-9.3 2.5-17.9 2.6-34.7 0-38.8-6.6-63-25.4-93-3.1-4.9-5.6-9.3-5.6-9.8s-1-1.9-2.2-3.1c-1.3-1.2-5.2-6-8.8-10.6-6.2-8-20.9-23.3-33.5-35.1-8.3-7.7-11.8-12.9-16.6-24.7-1.6-3.7-2.8-5.4-4.5-5.8-1.3-.3-2.4-1-2.4-1.4 0-.5-1.7-1.4-3.7-2.1-6.8-2.1-13-5.8-18.9-10.9-6.4-5.6-8.6-6.5-14.2-5.6-2 .3-8.6 1-14.6 1.6-10.7 1.1-11 1.1-21.4-2-5.8-1.8-12.6-3.5-15.1-4-2.5-.4-8.4-1.7-13.1-2.8-17.2-4.1-29.9-6.2-45.9-7.7-9-.8-16.9-1.9-17.5-2.4-1.6-1.2-21.9-2.1-23.7-1zM616.3 259.5c0 2.7.2 3.8.4 2.2.2-1.5.2-3.7 0-5-.2-1.2-.4 0-.4 2.8z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="hoverShapeWinter">
						<path d="M327.9 27.1c-.8.5-9 .9-18.4.9-20.5 0-54.2 1.8-57 3-1.1.5-6.7 1.8-12.5 3s-17.4 3.9-25.8 6.1c-8.4 2.1-16.2 3.9-17.3 3.9-1 0-1.9.4-1.9 1 0 .5-.7 1-1.7 1-.9 0-3.9 1.1-6.7 2.4-12.9 6.1-13.4 6.1-15 1.1-.3-1.1-1.7-3.4-3-5-2.2-2.7-2.6-2.9-4.1-1.4-.8.8-1.8 3.3-2.2 5.5-.3 2.1-1.5 4.5-2.5 5.2-1.2.9-1.8 2.6-1.8 5.1 0 3.5-.3 3.9-4.2 5.7-4.1 1.8-5.1 2.5-10 6.7-2 1.6-2.3 1.6-3.9.1-1-.9-1.7-2-1.4-2.4.8-1.4-2.7-5-4.9-5-2.8 0-4.6 1.8-4.6 4.6 0 1.2-.7 2.4-1.5 2.8-1 .4-1.5 2-1.5 5 0 3.9-.4 4.8-2.7 6.3C105 94 95.5 101.5 80.5 116.5c-17.2 17.1-28.2 33.8-38.8 58.7-4.1 9.6-9.4 29.9-10.4 39.5l-.6 6.3h7.8l1.7-7c1.8-7.4 13.2-32 14.8-32 1.1 0 7.7-10 9.1-13.7 1.5-3.9 11.7-14 17.5-17.3 4.4-2.4 5.4-3.6 5.9-6.3.6-2.9 1.3-3.6 4.6-4.6 4.1-1.2 7.3-4.8 6.4-7.2-.5-1.6 5.7-9 7.8-9.3.8-.1 3.6-2.3 6.2-4.9 2.8-2.9 5.3-4.6 6.2-4.3.8.3 1.2.2.9-.3-.9-1.3 10.9-5.3 13.7-4.6 1.5.4 3.8 0 5.7-1l3.3-1.7-.7-13.8-.7-13.8 8.2-4.6c4.4-2.5 8.3-4.6 8.5-4.6.5 0-.4 14.1-1.1 17.6-.8 4 7.5 6.9 13.3 4.5 2.6-1.1 3.2-1.9 3-3.9-.3-2.4.5-3 9.2-6.8 5.2-2.4 11.8-5 14.5-6 2.8-.9 6.4-2.2 8-3 5.2-2.3 16.8-6.5 22.5-8.1 4.1-1.1 7.9-1.4 15.1-.9 11.6.8 22-.6 29.8-3.9 5.8-2.4 6-2.4 45.2-2.7 30-.2 40.6 0 44.4 1 2.8.7 6.8 1.8 9 2.4 2.2.6 5.1 1.2 6.5 1.3 4.2.4 8.4 1.8 9.9 3.2 1 1 2.5 1.2 5.2.8 2.7-.5 3.9-.3 3.9.5 0 .7 1.5 1.1 4.3.9 2.4-.2 4.7 0 5.2.3.6.3 5.7.6 11.5.7 11.8.1 19 1.8 36 8.2 15.9 6 22 9.1 22 11.2 0 2.4 3.9 5.6 6.5 5.6 1.1 0 3.6-.6 5.5-1.3l3.4-1.3.1-8.1c.1-4.5.4-8.4.7-8.7.3-.3 4.2 1.8 8.8 4.7l8.2 5.2-.6 9.1c-.3 4.9-.9 11.6-1.2 14.7-.6 5.5-.5 5.8 2 6.9 1.4.7 2.6 1.8 2.6 2.6 0 .7 1.1 1.3 2.6 1.3 1.4 0 3.2.7 4.1 1.6.9.8 2.3 1.2 3.1.9.8-.3 1.8-.1 2.2.5.5.8 6 2.2 13.6 3.3 3 .4 11.9 8.3 17.7 15.7 4.6 5.9 6.8 8.4 14.7 16.9 4.2 4.5 10 11.8 10 12.6 0 .4 1.4 2.7 3 5.2 4.3 6.4 8 13.9 8 16.3 0 1.1.7 2.3 1.6 2.7.8.3 1.3 1.2 1 1.9-.3.8 0 1.3.6 1.1.7-.1 1.3.2 1.4.8.5 2.7 1.7 5.5 2.5 5.5.4 0 2.4 5.3 4.4 11.7 2 6.5 4.1 13.1 4.6 14.7.5 1.6.9 3.1.9 3.3 0 .2-1.8 1-4 1.9-2.2.9-4 2-4 2.6 0 .5-.4.6-1 .3-.5-.3-1-.1-1 .4 0 .6-.7 1.1-1.6 1.1-1.7 0-5.4 3.6-5.4 5.2 0 .4-1.6 2.6-3.5 4.7-4.1 4.5-4.1 6.5-.3 13.4 2 3.6 2.9 4.3 6.8 5 7.3 1.3 12 2.9 12 4.1-.1.6-.7 2.9-1.5 5.1-.8 2.2-1.5 5-1.5 6.2 0 2.1.5 2.3 4.9 2.3 4.6 0 5-.2 5.5-2.8 1.9-9.3 2.5-17.9 2.6-34.7 0-38.8-6.6-63-25.4-93-3.1-4.9-5.6-9.3-5.6-9.8s-1-1.9-2.2-3.1c-1.3-1.2-5.2-6-8.8-10.6-8.4-10.9-24.7-27.3-38.2-38.6-9.3-7.6-10.8-9.3-10.8-11.9 0-1.7-.7-4.3-1.5-5.9-.8-1.5-1.5-3.4-1.5-4 0-.7-1.1-2.3-2.5-3.6-2.1-2-2.8-2.2-5-1.2-1.7.8-2.5 2-2.5 3.6 0 1.4-.6 2.9-1.4 3.3-.8.4-1.5 1.6-1.7 2.6-.3 1.6-1.6 1-8.5-3.9-8-5.7-8.2-5.9-8.8-10.8-.3-2.7-1-5.4-1.6-6-.5-.5-1-2.2-1-3.6 0-6.8-9.2-8-9.8-1.3-.6 6-1.5 6-20.9-.2-24.5-7.8-23.5-7.5-28.9-8.4-2.7-.5-8.7-1.8-13.4-2.9-17.2-4.1-29.9-6.2-45.9-7.7-9-.8-16.9-1.9-17.5-2.4-1.6-1.2-21.9-2.1-23.7-1zM110 120.4c0 .2-.8 1-1.7 1.7-1.6 1.3-1.7 1.2-.4-.4 1.3-1.6 2.1-2.1 2.1-1.3zM616.3 259.5c0 2.7.2 3.8.4 2.2.2-1.5.2-3.7 0-5-.2-1.2-.4 0-.4 2.8z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						</svg>';
				break;
				case '32Bottom': // Teuton
					$sVG = '<svg class="buildingShape 32Bottom " '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="675" height="534" viewBox="0 0 675 534" >
						<g class="clickShape">
								<path d="M20.7 224.2c-2.4 5-3.2 9.4-5.7 28.3-.8 6.4-.7 9.1.5 13.5 1.2 4.7 1.2 5.7 0 7.6-1 1.6-1.3 4.9-1.1 13 .2 6-.1 12-.6 13.4-1.4 3.5-1.8 20.1-.6 22.3.7 1.4.5 2.4-.7 3.7-2.2 2.4-1.3 8.6 3.6 25.9 3.7 12.9 4.8 15.1 7.7 15.1.7 0 2.1 1.9 3.1 4.2 1 2.4 3.3 5.6 5 7.2 1.7 1.7 3.1 4 3.1 5.3 0 1.7.6 2.3 2.3 2.3 1.7 0 2.8 1.1 4.5 4.6 2.1 4.2 2.5 4.6 5.2 4.1 2.6-.6 3.2-.1 6 4.3 4.7 7.3 4.7 7.3 13.1 5.1 8.4-2.3 27.6-5.1 34.7-5.1 4 0 5.3-.6 9.2-3.9l4.5-3.9 8 6.7c4.4 3.6 7.3 5.7 6.6 4.6-1.4-1.9-1.4-1.9.2-.4 1.6 1.4 1.5 2-1.9 9.4-2.8 6.1-5.3 9.5-10.8 15-3.9 3.8-8.9 9.2-11.1 11.9l-3.9 4.8 1.9 3.6c1.2 2.2 3.8 4.6 6.3 6 2.9 1.6 4.2 3 4.2 4.5.1 4.2 7 10 10.8 9 2.7-.7 12.2 2.5 12.2 4.1 0 .7 2.8 2.8 6.1 4.6 3.5 1.8 6.8 4.5 7.8 6.1 2.2 4 5.4 5.2 14.6 5.9 7.5.5 8.1.7 9.3 3.2 1.2 2.5 1.9 2.8 6.1 2.8 3.1 0 5.3.5 6.1 1.5 1.6 1.9 5.8 1.9 10.1.1 2.9-1.2 3.7-1.2 6.3.1 1.6.8 3.1 1.1 3.5.6.3-.5 1.8-.8 3.4-.8 1.5.1 2.7-.3 2.7-.8s.7-.7 1.5-.3c1 .4 1.5-.1 1.5-1.5 0-1.7.3-1.8 2.2-.8 1.7.9 2.3.9 2.6 0 .3-.9.7-.9 1.8 0 1.2 1 1.6.8 2.1-.9.6-2.4 1.8-2.8 2.8-1.1.4.6 1.3.8 2.1.5.7-.3 1.9.4 2.5 1.5.8 1.6 1.4 1.8 2.4.9.9-.8 1.5-.8 2 0 .3.5 1.7 1 3.1 1 1.9 0 2.4-.5 2.4-2.6 0-2.5.1-2.6 2.4-1.1 1.3.9 2.7 1.3 3 1 1-1 4.6-1.1 4.6-.1 0 .4.7.8 1.5.8s1.5.4 1.5.8c0 .5 1.1 1.5 2.5 2.2 1.4.7 2.5 1.7 2.5 2.1 0 1.1 9.8 10.2 11.8 11.1.9.4 2.9.9 4.5 1.2 1.5.4 2.7 1.3 2.7 2.1 0 1.5 3.2 2.1 9.5 1.7 2.4-.2 3.4.4 4.8 2.8 2 3.2 8.3 7 10.5 6.2.8-.2 3 .3 5 1.1 7.9 3.3 17.2 2.6 17.2-1.3 0-2.5 3.5-3.1 8.8-1.4 11.2 3.5 38.4 2.2 43.1-2 1.1-1 2.1-1.2 3.3-.5 2 1.1 6.2-1.2 7.9-4.2.8-1.6 2.2-1.9 8.2-1.9 4 0 7.8.4 8.5.9 2 1.2 6 .1 6.5-1.9s8.5-5 13.4-5c1.9 0 4.7-.7 6.2-1.5 1.8-.9 4.2-1.2 6.5-.8 2.5.4 4.2.1 5.4-.9 2.1-1.9 8.2-5.1 8.2-4.3 0 .3-1 1.9-2.2 3.6-1.8 2.3-2 3.3-1.1 4.7.8 1.4 2.4 1.7 7.9 1.5 5.7-.1 7.1-.5 8.6-2.3 2.5-3 2.3-3.7-1.7-6.2-4.5-2.9-4.4-3.8.3-3.8 2.4 0 5.1-.9 7.6-2.6 3.5-2.4 4.4-2.6 10.1-2 4.9.5 6.4.3 6.8-.8.3-.7 2.5-1.6 5-2 2.4-.4 5.3-1.3 6.3-2.1 1.1-.8 2.9-1.5 4-1.5 1.6 0 2.3-.8 2.7-3 .8-3.8 6.8-6.7 10.5-5 1.8.8 2.8.7 4.5-.5 1.2-.8 2.9-1.5 3.7-1.5.9 0 3.6-2 6-4.5 2.7-2.8 5.3-4.5 6.7-4.5 3.6 0 8.5-2.4 11.7-5.9 1.7-1.7 4-3.1 5.3-3.1 3.2 0 5.7-5.2 5.7-12.3l.1-5.8-6.7-6c-3.6-3.2-8.5-9-10.8-12.7-2.3-3.7-5.9-8.4-7.8-10.5l-3.6-3.7 4.4-4.5c4.1-4.2 4.5-4.4 5.2-2.5.4 1.1 1.6 2 2.8 2 1.1 0 3.8 1.1 5.9 2.5 2.2 1.5 4.5 2.3 5.5 1.9.9-.3 2.3-.1 3.1.6 1.1.9 4.5 1.1 12.3.8 8.5-.4 12.1-.1 17.4 1.3 8.1 2.3 7.8 2.2 7.8.6 0-.8.9-2.2 2-3.2s2-2.5 2-3.3c0-.8 1.1-3.4 2.5-5.8 1.8-3.1 3.9-4.9 7.7-6.8 6.9-3.2 13.2-9.6 12.4-12.4-.9-2.7 2.9-8.9 6.4-10.7 4.1-2.1 9.1-10 9.4-14.9.2-2.3 1.7-6.9 3.4-10.3 1.8-3.5 3.2-7.8 3.2-9.7 0-2 .9-5.9 2-8.8 2-5.5 5-18.5 5-21.9 0-1.8-.9-1.9-24.4-1.9h-24.4l-.6 2.4c-.3 1.3-1.7 3.1-3 4-3.9 2.5-9.9 12.2-9.4 15.1.5 2.5-1.6 5.2-5.8 7.3-1.1.6-2.9 2.8-4 4.8-2.5 5-10.3 15.4-11.5 15.4-.5 0-.9.7-.9 1.5s-1.3 2.1-2.9 2.9c-1.6.9-3.4 2.9-3.9 4.6-.6 1.7-1.8 3.3-2.6 3.6-2.5.9-9.5 11.2-10.9 15.9-1 3.5-3.4 6.6-11.2 14.5-8.7 8.8-10 9.8-11.4 8.6-.9-.7-4-1.9-7-2.5-5.7-1.2-7.5-.6-12.5 4.1-1.1 1-2.9 1.8-4.1 1.8-1.1 0-4.2 2-6.8 4.4-2.7 2.4-6.1 4.7-7.5 5.1-1.5.3-5.5 1.7-8.9 3.1-3.4 1.3-7.5 2.4-9.2 2.4-1.7 0-3.4.7-3.9 1.6-1.1 2-15.6 8.4-19.1 8.4-4.5 0-12.7 2.7-16.1 5.1-2.4 1.8-4.6 2.4-9.3 2.6-3.4.1-9.3.9-13.2 1.8-3.8.9-9.7 1.8-12.9 2-3.4.3-6.6 1.1-7.6 2-1.8 1.6-6.5 2.1-7.4.7-.3-.4-6-.7-12.8-.6-9.4.1-13.7.6-18.3 2.2-6.8 2.3-17.7 2.3-27 .1-4.5-1.1-12.3-1.2-30.5-.5-4.9.2-7.5-.2-10.8-1.8-3.5-1.7-4.6-1.8-6.2-.8-2.4 1.5-6.5 1.6-9 .2-1.2-.7-2.8-.7-4.4 0-2.1.7-3.6.4-8.3-2.2-5.3-2.8-6.6-3.1-16-3.2-9.7-.1-10.2-.2-12.2-2.8-1.7-2.2-2.9-2.6-6.6-2.6-2.5.1-5.1-.4-5.7-1-.7-.7-1.9-1.2-2.9-1.2-.9 0-3.7-1.1-6.3-2.5-2.6-1.4-6.1-2.5-7.8-2.5-2.4 0-4.2-1.2-8-4.9-4.7-4.7-7.9-6.2-13.5-6.5-1.2-.1-3.2-1.1-4.5-2.3-1.3-1.2-4.7-3.1-7.6-4.2-3.4-1.2-6.8-3.5-9.1-6-4.2-4.6-7.4-5.2-12.1-2.4-2.8 1.6-2.9 1.6-8.4-2.2-5.9-4-6.4-4.5-14-12.4-2.7-2.8-5.3-5.1-5.8-5.1-.4 0-.8-.7-.8-1.5 0-3.2-5.3-13.2-8.8-16.6-2-1.9-5.7-5.9-8.2-8.8-7.7-9-8.9-10.1-10.7-10.1-1 0-3.9-2.5-6.5-5.4-4.3-4.9-4.8-5.9-4.8-10.3 0-4.2-.5-5.4-3-7.8-1.6-1.6-3-3.7-3-4.8 0-1.1-1.8-4.1-4-6.7-2.2-2.6-4-5.6-4-6.6s-.9-2.7-1.9-3.8c-1.2-1.2-2.8-5.9-4-11.2-1.1-5-3.2-12.4-4.7-16.5-2.5-6.9-2.6-8-2-19.4.3-6.6 1.2-15.5 1.9-19.8l1.4-7.7H23.1l-2.4 5.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShape">
								<path d="M20.7 224.2c-2.4 5-3.2 9.4-5.7 28.3-.8 6.4-.7 9.1.5 13.5 1.2 4.7 1.2 5.7 0 7.6-1 1.6-1.3 4.9-1.1 13 .2 6-.1 12-.6 13.4-1.4 3.5-1.8 20.1-.6 22.3.7 1.4.5 2.4-.7 3.7-2.2 2.4-1.3 8.6 3.6 25.9 3.7 12.9 4.8 15.1 7.7 15.1.7 0 2.1 1.9 3.1 4.2 1 2.4 3.3 5.6 5 7.2 1.7 1.7 3.1 4 3.1 5.3 0 1.7.6 2.3 2.3 2.3 1.7 0 2.8 1.1 4.5 4.6 2.1 4.2 2.5 4.6 5.2 4.1 2.6-.6 3.2-.1 6 4.3 4.7 7.3 4.7 7.3 13.1 5.1 8.4-2.3 27.6-5.1 34.7-5.1 4 0 5.3-.6 9.2-3.9l4.5-3.9 8 6.7c4.4 3.6 7.3 5.7 6.6 4.6-1.4-1.9-1.4-1.9.2-.4 1.6 1.4 1.5 2-1.9 9.4-2.8 6.1-5.3 9.5-10.8 15-3.9 3.8-8.9 9.2-11.1 11.9l-3.9 4.8 1.9 3.6c1.2 2.2 3.8 4.6 6.3 6 2.9 1.6 4.2 3 4.2 4.5.1 4.2 7 10 10.8 9 2.7-.7 12.2 2.5 12.2 4.1 0 .7 2.8 2.8 6.1 4.6 3.5 1.8 6.8 4.5 7.8 6.1 2.2 4 5.4 5.2 14.6 5.9 7.5.5 8.1.7 9.3 3.2 1.2 2.5 1.9 2.8 6.1 2.8 3.1 0 5.3.5 6.1 1.5 1.6 1.9 5.8 1.9 10.1.1 2.9-1.2 3.7-1.2 6.3.1 1.6.8 3.1 1.1 3.5.6.3-.5 1.8-.8 3.4-.8 1.5.1 2.7-.3 2.7-.8s.7-.7 1.5-.3c1 .4 1.5-.1 1.5-1.5 0-1.7.3-1.8 2.2-.8 1.7.9 2.3.9 2.6 0 .3-.9.7-.9 1.8 0 1.2 1 1.6.8 2.1-.9.6-2.4 1.8-2.8 2.8-1.1.4.6 1.3.8 2.1.5.7-.3 1.9.4 2.5 1.5.8 1.6 1.4 1.8 2.4.9.9-.8 1.5-.8 2 0 .3.5 1.7 1 3.1 1 1.9 0 2.4-.5 2.4-2.6 0-2.5.1-2.6 2.4-1.1 1.3.9 2.7 1.3 3 1 1-1 4.6-1.1 4.6-.1 0 .4.7.8 1.5.8s1.5.4 1.5.8c0 .5 1.1 1.5 2.5 2.2 1.4.7 2.5 1.7 2.5 2.1 0 1.1 9.8 10.2 11.8 11.1.9.4 2.9.9 4.5 1.2 1.5.4 2.7 1.3 2.7 2.1 0 1.5 3.2 2.1 9.5 1.7 2.4-.2 3.4.4 4.8 2.8 2 3.2 8.3 7 10.5 6.2.8-.2 3 .3 5 1.1 7.9 3.3 17.2 2.6 17.2-1.3 0-2.5 3.5-3.1 8.8-1.4 11.2 3.5 38.4 2.2 43.1-2 1.1-1 2.1-1.2 3.3-.5 2 1.1 6.2-1.2 7.9-4.2.8-1.6 2.2-1.9 8.2-1.9 4 0 7.8.4 8.5.9 2 1.2 6 .1 6.5-1.9s8.5-5 13.4-5c1.9 0 4.7-.7 6.2-1.5 1.8-.9 4.2-1.2 6.5-.8 2.5.4 4.2.1 5.4-.9 2.1-1.9 8.2-5.1 8.2-4.3 0 .3-1 1.9-2.2 3.6-1.8 2.3-2 3.3-1.1 4.7.8 1.4 2.4 1.7 7.9 1.5 5.7-.1 7.1-.5 8.6-2.3 2.5-3 2.3-3.7-1.7-6.2-4.5-2.9-4.4-3.8.3-3.8 2.4 0 5.1-.9 7.6-2.6 3.5-2.4 4.4-2.6 10.1-2 4.9.5 6.4.3 6.8-.8.3-.7 2.5-1.6 5-2 2.4-.4 5.3-1.3 6.3-2.1 1.1-.8 2.9-1.5 4-1.5 1.6 0 2.3-.8 2.7-3 .8-3.8 6.8-6.7 10.5-5 1.8.8 2.8.7 4.5-.5 1.2-.8 2.9-1.5 3.7-1.5.9 0 3.6-2 6-4.5 2.7-2.8 5.3-4.5 6.7-4.5 3.6 0 8.5-2.4 11.7-5.9 1.7-1.7 4-3.1 5.3-3.1 3.2 0 5.7-5.2 5.7-12.3l.1-5.8-6.7-6c-3.6-3.2-8.5-9-10.8-12.7-2.3-3.7-5.9-8.4-7.8-10.5l-3.6-3.7 4.4-4.5c4.1-4.2 4.5-4.4 5.2-2.5.4 1.1 1.6 2 2.8 2 1.1 0 3.8 1.1 5.9 2.5 2.2 1.5 4.5 2.3 5.5 1.9.9-.3 2.3-.1 3.1.6 1.1.9 4.5 1.1 12.3.8 8.5-.4 12.1-.1 17.4 1.3 8.1 2.3 7.8 2.2 7.8.6 0-.8.9-2.2 2-3.2s2-2.5 2-3.3c0-.8 1.1-3.4 2.5-5.8 1.8-3.1 3.9-4.9 7.7-6.8 6.9-3.2 13.2-9.6 12.4-12.4-.9-2.7 2.9-8.9 6.4-10.7 4.1-2.1 9.1-10 9.4-14.9.2-2.3 1.7-6.9 3.4-10.3 1.8-3.5 3.2-7.8 3.2-9.7 0-2 .9-5.9 2-8.8 2-5.5 5-18.5 5-21.9 0-1.8-.9-1.9-24.4-1.9h-24.4l-.6 2.4c-.3 1.3-1.7 3.1-3 4-3.9 2.5-9.9 12.2-9.4 15.1.5 2.5-1.6 5.2-5.8 7.3-1.1.6-2.9 2.8-4 4.8-2.5 5-10.3 15.4-11.5 15.4-.5 0-.9.7-.9 1.5s-1.3 2.1-2.9 2.9c-1.6.9-3.4 2.9-3.9 4.6-.6 1.7-1.8 3.3-2.6 3.6-2.5.9-9.5 11.2-10.9 15.9-1 3.5-3.4 6.6-11.2 14.5-8.7 8.8-10 9.8-11.4 8.6-.9-.7-4-1.9-7-2.5-5.7-1.2-7.5-.6-12.5 4.1-1.1 1-2.9 1.8-4.1 1.8-1.1 0-4.2 2-6.8 4.4-2.7 2.4-6.1 4.7-7.5 5.1-1.5.3-5.5 1.7-8.9 3.1-3.4 1.3-7.5 2.4-9.2 2.4-1.7 0-3.4.7-3.9 1.6-1.1 2-15.6 8.4-19.1 8.4-4.5 0-12.7 2.7-16.1 5.1-2.4 1.8-4.6 2.4-9.3 2.6-3.4.1-9.3.9-13.2 1.8-3.8.9-9.7 1.8-12.9 2-3.4.3-6.6 1.1-7.6 2-1.8 1.6-6.5 2.1-7.4.7-.3-.4-6-.7-12.8-.6-9.4.1-13.7.6-18.3 2.2-6.8 2.3-17.7 2.3-27 .1-4.5-1.1-12.3-1.2-30.5-.5-4.9.2-7.5-.2-10.8-1.8-3.5-1.7-4.6-1.8-6.2-.8-2.4 1.5-6.5 1.6-9 .2-1.2-.7-2.8-.7-4.4 0-2.1.7-3.6.4-8.3-2.2-5.3-2.8-6.6-3.1-16-3.2-9.7-.1-10.2-.2-12.2-2.8-1.7-2.2-2.9-2.6-6.6-2.6-2.5.1-5.1-.4-5.7-1-.7-.7-1.9-1.2-2.9-1.2-.9 0-3.7-1.1-6.3-2.5-2.6-1.4-6.1-2.5-7.8-2.5-2.4 0-4.2-1.2-8-4.9-4.7-4.7-7.9-6.2-13.5-6.5-1.2-.1-3.2-1.1-4.5-2.3-1.3-1.2-4.7-3.1-7.6-4.2-3.4-1.2-6.8-3.5-9.1-6-4.2-4.6-7.4-5.2-12.1-2.4-2.8 1.6-2.9 1.6-8.4-2.2-5.9-4-6.4-4.5-14-12.4-2.7-2.8-5.3-5.1-5.8-5.1-.4 0-.8-.7-.8-1.5 0-3.2-5.3-13.2-8.8-16.6-2-1.9-5.7-5.9-8.2-8.8-7.7-9-8.9-10.1-10.7-10.1-1 0-3.9-2.5-6.5-5.4-4.3-4.9-4.8-5.9-4.8-10.3 0-4.2-.5-5.4-3-7.8-1.6-1.6-3-3.7-3-4.8 0-1.1-1.8-4.1-4-6.7-2.2-2.6-4-5.6-4-6.6s-.9-2.7-1.9-3.8c-1.2-1.2-2.8-5.9-4-11.2-1.1-5-3.2-12.4-4.7-16.5-2.5-6.9-2.6-8-2-19.4.3-6.6 1.2-15.5 1.9-19.8l1.4-7.7H23.1l-2.4 5.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="clickShapeWinter">
								<path d="M16.8 233.2c-3.6 7.7-4.8 23.3-5 68.8-.1 36.6 1 60.6 3.3 66.7.4 1.2 3.8 4.1 7.6 6.6 3.7 2.5 9.3 6.8 12.3 9.7 3 2.9 7.3 6.5 9.6 8.1 2.3 1.5 5.9 5.1 8 7.9 3.1 4 4.3 5 5.9 4.5 5.7-1.8 13-3.6 17-4.4l4.6-.9 5.7 5.7c7.6 7.5 13.4 12.1 20.7 16.5 3.3 1.9 6.6 4 7.4 4.5 1.1.7.1 2.6-5.3 8.9-6.6 8-6.6 8.1-5.7 12.1 1.1 4.9 5.1 10.5 9 12.5 1.5.8 4.2 3.1 5.9 5 1.7 2 3.4 3.6 3.7 3.6.4 0 3.2 1.2 6.3 2.6 3.1 1.5 7.3 3.4 9.2 4.2 1.9.8 4.7 2.1 6.2 2.9 1.4.7 3.3 1.3 4.2 1.3.9 0 2.4.8 3.5 1.9 2.1 2.1 5.7 3.3 15.1 5.2 3.6.7 7.4 1.8 8.5 2.5 1.1.7 4.3 1.8 7 2.4 2.8.6 5.7 1.3 6.5 1.6.8.3 5.3.7 9.9.9 9.4.5 23.2 3 27.3 5.1 1.6.8 5.8 1.4 10.2 1.4 6.8 0 14.2 1.4 35.6 6.6 7.9 2 19.3 6.8 25.4 10.9 4.3 2.9 10.4 3.4 42.6 4 23.4.5 32.2.3 42.5-1 20.5-2.5 24.4-3.1 35.2-5.9 5.6-1.4 13-2.6 16.5-2.6 3.5-.1 8.1-.8 10.3-1.6 5.7-2.2 11.8-4 20.9-6 4.3-1 9.1-2.7 10.5-3.8 3.9-3.1 6.8-4.6 9.1-4.6 1.1 0 3.9-1.7 6.3-3.9 3.2-2.9 5.3-4 9-4.4 2.8-.4 7.4-2.2 10.8-4.2 3.3-1.9 6.6-3.5 7.3-3.5 1.8 0 12-6.3 16.7-10.3 2.2-1.9 6.8-4.9 10.2-6.7 8.9-4.7 15.5-11.3 16.2-16.4.8-5-.7-9.1-4.6-13.2l-3-3.1 3.4-3.4c1.8-1.9 3.8-4.1 4.3-5 .5-.8 2.9-2.9 5.4-4.6 2.5-1.7 6.3-5 8.5-7.2l4-4.2 6.5.7c5.8.5 6.7.4 8.4-1.5 1.1-1.2 2.8-2.1 3.8-2.1 1.1 0 2.3-1.1 2.8-2.6 1.3-3.4 13.1-16.4 14.9-16.4 1.3 0 6.1-4.2 14.5-12.5 1.6-1.7 2.6-2.3 2.1-1.5-.4.8.4.4 1.9-1 2.5-2.2 2.7-3 2.3-7-.4-2.9 0-5.4.8-7 .8-1.4 1.4-4.6 1.5-7.2 0-2.9 1-7.1 2.6-10.5 7.8-17.6 9.3-22.9 9.9-34.8l.6-11.5h-46.9l-3.1 2.6c-1.9 1.6-3.6 4.4-4.5 7.5-.8 2.7-2.6 6.4-3.9 8.1-1.4 1.8-3.2 5-4.1 7.2-.9 2.1-2.9 5.4-4.6 7.3-1.7 1.8-4.1 5.6-5.5 8.3-1.3 2.7-3.3 5.1-4.4 5.5-1.2.3-2.7 2.5-3.7 5.2-1.3 3.3-4.2 7-10.1 12.7-24.4 23.7-32 30-41 34-2.3 1-5.2 2.9-6.4 4.2-1.3 1.4-4.4 3.1-7 3.9-2.7.7-7.6 3.3-11.1 5.7-6.9 4.7-11.1 6.8-13.8 6.8-1.6 0-5.9 1.6-14.7 5.6-1.7.8-3.6 1.4-4.3 1.4-.7 0-3.8 1.6-7 3.6-4.2 2.6-7.3 3.7-11.6 4.1-3.3.3-7 1.2-8.4 1.9-2.9 1.5-23.3 6.6-33.2 8.3-3.8.6-9.8 1.6-13.5 2.3-3.8.6-14 1.2-22.9 1.3-13.3 0-16.5.4-18.7 1.8-2.3 1.5-3.9 1.5-16.4.6-15.6-1.3-18.9-1.4-32.3-1.2-14.8.3-28.9-.5-32.6-1.7-1.9-.6-4.6-1-6.1-.9-1.6.2-3.3 0-4-.4-2.5-1.6-10.9-3.7-14.8-3.7-5.9-.1-14.3-2.3-20.2-5.4-3.8-2-6-2.6-8.1-2.1-2 .4-3.6 0-5.3-1.3-2.2-1.8-5.5-3.2-8.4-3.6-1.7-.2-18.2-9.7-21.5-12.3-1.5-1.2-2.9-2-3.2-1.8-1 1-7.4-1.9-11.1-5.1-4-3.4-9.2-5.5-19.2-8-4.1-1.1-7.1-2.6-10-5.2-2.2-2.1-5.8-4.8-8-6.2-5.9-3.5-13.5-10-13.5-11.5 0-.7-1.9-3.7-4.2-6.5-4.7-5.8-7.5-9.5-11.4-15.4-2.8-4.2-18.2-19.6-19.6-19.6-.5 0-1.8-2.1-2.9-4.8-1.2-2.6-3.2-6.7-4.5-9.2-1.3-2.5-4-7.9-6-12s-5-10.2-6.7-13.4c-1.7-3.3-3.6-8.4-4.3-11.5-.7-3.1-2.8-10.8-4.5-17.1-2.5-8.9-3.3-14-3.6-22.8l-.4-11.2H19.3l-2.5 5.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShapeWinter">
								<path d="M18 230.7c-.7 1.6-1.5 4.4-1.6 6.3-.9 11-1.4 14-2.3 15.8-1.1 2.1-.7 10.5.9 16 .7 2.7.6 4.1-.7 6.3-1.3 2.2-1.4 3.2-.4 5.6.8 1.9.9 4.3.3 7.4-.6 2.8-.5 5.4 0 6.3.7 1.2.5 2.4-.5 3.9-1.9 3-2.7 7.8-1.6 9.9.6 1.2.6 3.2-.1 5.7-.8 3-.7 4.6.5 7.4 1.1 2.6 1.2 3.8.4 4.3-1.6.9-.3 16.5 1.6 20.1.8 1.6 1.5 3.6 1.5 4.6 0 1 .8 4.8 1.7 8.4 1.2 5 1.3 6.8.5 7.1-.7.2-1.7 1.2-2.2 2.2-.7 1.3-.4 2.2 1.2 3.6 1.4 1.2 1.8 1.4 1.3.4-.5-.8.3-.4 1.7 1s5 3.8 7.9 5.3c5 2.8 12.9 9.9 12.9 11.8 0 .5 2.1 1.9 4.7 3.1 2.8 1.4 5 3.1 5.3 4.4.3 1.1 1.7 3.5 3.2 5.4 2.4 3.1 2.8 3.2 5.5 2.2 8-3.2 25.5-6.2 37.2-6.4 6.7-.1 7.9-.4 11.7-3.2 4-2.9 4.2-3.3 3.7-7.1l-.6-4 7.9 7.6c4.4 4.1 9 8.3 10.4 9.2l2.4 1.7-4.2 8.5c-3.1 6.1-6.1 10.3-10.6 14.8-9.4 9.3-15.9 17.5-15.3 19.2.4.8 3.1 3.4 6.2 5.7 4.1 3.2 5.5 4.9 5.5 6.9 0 1.6 1 3.3 3 4.7 1.7 1.3 3 3.1 3 4 0 2.5 8.1 5.1 15.2 4.9 4.9-.1 6.4.3 10 2.8 2.4 1.7 5 4.5 5.8 6.2 1.8 3.8 7.7 5.9 13.9 4.8 5.1-.9 10.4.9 12.5 4.1 1.2 1.7 2.6 2.4 4.9 2.4 1.8 0 4.2.7 5.5 1.5 1.7 1.3 2.6 1.4 4.4.4 3.2-1.7 18.5-.4 25.6 2.2 3.1 1.1 6.6 2 7.7 2 21.2-1.5 23.3-1.2 31 4.5 1.8 1.2 5.2 2.4 8 2.7 7.8.8 13.4 2.3 15.4 4.1.9.9 3.4 1.6 5.3 1.6 5 0 6.6.8 8.4 4.1.8 1.6 2.7 3.5 4.2 4.2 3.7 1.9 23.1 4.3 24.7 3.1 5.6-4.1 6-4.3 11-3.4 17.6 2.9 22.2 3.2 30.7 2.2 4.9-.6 9.8-1.7 11-2.3 2.4-1.3 7.4-1.9 14.2-1.9 2.2.1 4.2-.3 4.5-.9.9-1.3 3.2-1.6 7-.7 2.7.7 3.6.5 4.3-.8.5-.9 2-1.6 3.2-1.6 1.7 0 2.6-.7 3-2.5 1-3.8 4.3-5.2 13.4-5.9 4.6-.3 8.8-.9 9.5-1.3.6-.4 3-.6 5.3-.4 3.3.2 4.8-.3 7.4-2.6 2.8-2.3 3.2-2.4 2.6-.8-.5 1.1-1.1 3.3-1.4 4.9l-.5 2.9 8.3-.7c6.1-.5 8.7-1.1 9.6-2.4 2.1-2.7 1.6-4.1-2.1-5.9-4.4-2.1-4.4-2.8-.2-3 1.7 0 4.3-.4 5.7-.7 1.4-.3 5.7-1.3 9.5-2.1 3.9-.9 10.6-2.9 15-4.6 6.6-2.5 8.9-4 13.2-8.5l5.2-5.6 6.5.7 6.6.7 4.5-4.6c2.7-2.7 5.5-4.6 6.8-4.6 3.3 0 12.4-4.6 13.9-6.9.7-1.2 2-2.1 2.9-2.1 2.5 0 6.7-5.2 7.4-9.2 1.1-5.9-1-10.2-7.6-15.9-3.2-2.9-8.1-8.6-10.8-12.8-2.7-4.2-5.9-8.5-7.1-9.6-4.4-4-4.3-4.8 2.1-11.1 5.9-5.8 5.9-5.9 5.3-2.6-.6 2.9-.4 3.2 1.6 3.2 1.3 0 3.8 1.1 5.6 2.5 3.6 2.8 3.2 2.7 19.9 2.9 9.2.2 13.1.6 16.7 2.1 4.7 1.9 4.7 1.9 7-.2 1.3-1.2 3.1-2.3 4.2-2.5 1.1-.2 2.6-2.3 4-5.4 1.1-2.8 2.8-5.9 3.6-7 2.5-3.3 7.1-6.4 9.5-6.4 2.3 0 9.8-7.7 8.9-9.2-.3-.4.6-.8 2-.8 1.5 0 3.3-1.1 4.6-2.8 1.2-1.5 1.8-2 1.4-1.2-.4.8.5.4 2-1 2.5-2.2 2.7-3 2.3-7-.4-2.9 0-5.4.8-7 .8-1.4 1.4-4.6 1.5-7.2 0-2.9 1-7.1 2.6-10.5 7.8-17.6 9.3-22.9 9.9-34.8l.6-11.5h-45.8l-1.2 5.5c-.9 4.1-2 6-4.1 7.5-4.3 3.1-9.2 10.8-9.2 14.7 0 3.2-1.2 4.9-5.2 6.8-1.1.5-3 2.9-4.1 5.2-1.4 2.9-3.1 4.7-5.7 5.9-2.1.9-3.8 1.7-3.8 1.8-2 6.9-4.1 11.3-5.8 12.2-2.5 1.2-9.2 8.1-9.2 9.4 0 .5-1.6 3-3.6 5.5-2 2.6-4.2 6.6-5 8.8-.8 2.4-3.6 6.2-6.7 9.2-2.8 2.8-7.3 7.5-9.8 10.5l-4.7 5.5-2.8-1.6c-1.6-.9-5-2-7.6-2.3-5-.7-7.9.2-11.1 3.5-1 1.1-2.9 1.9-4.3 1.9-1.6 0-3.9 1.5-6.7 4.5-2.8 2.9-5.2 4.5-6.8 4.5-1.3 0-4.4.6-6.9 1.4-2.5.7-5.1 1.1-5.9.8-.9-.3-1.1 0-.6.8.6 1 .4 1.2-.8.7-2.7-1-6.5.9-8.1 4.1-1 2-2.8 3.2-5.7 4.1-2.3.7-5.8 2.1-7.6 3.1-2.5 1.3-4.2 1.6-6.3 1-5.5-1.6-10-.5-12.8 3-2.5 3.2-2.6 3.2-8.3 2.6-4.4-.5-7.5-.2-12.6 1.3-3.8 1.1-9.6 2.5-13.1 3-3.4.5-8.4 2.2-11.1 3.6l-4.8 2.6-5.2-1.7c-7.3-2.4-19.3-1.5-29.5 2.1-7.4 2.6-7.8 2.6-11.6 1.1-2.2-.9-6.1-1.6-8.6-1.6s-6.8-.7-9.5-1.6c-3.4-1.1-6.3-1.4-9.2-.9-2.3.3-5.8.9-7.7 1.2-1.9.3-4 1-4.7 1.5-.7.6-1.9.5-3.3-.2-3.6-1.9-6.1-2.3-16.5-2.7-13.1-.5-13.5-.6-13.5-1.6 0-.5-1.9-.7-4.2-.5-3.2.4-5.7-.2-9.7-2-5-2.3-6.1-2.4-15-1.9l-9.6.7-2-3c-2.7-4-7.6-6.3-11.8-5.5-2.5.5-3.7.2-5.1-1.4-1.5-1.6-3.8-2.6-8.6-3.6-.3 0-2.4-1.1-4.7-2.3-2.3-1.2-5.1-2.2-6.3-2.2-1.1 0-2.5-.9-3.2-2-.7-1.1-2.3-2-3.5-2-1.3 0-2.3-.6-2.3-1.4 0-1.9-2.6-3.9-5.6-4.2-1.4-.2-4.4-1.2-6.7-2.2-2.3-1.1-6.3-2.8-8.9-3.7-2.7-1-4.8-2.1-4.8-2.6 0-3.5-9.5-5.4-12-2.4-2.5 3-3.5 2.2-19.8-14.8-1.5-1.6-3.9-3.5-5.4-4.3-1.8-.9-3.9-3.9-6.3-8.9-2.2-4.6-4.9-8.5-6.9-10s-3.8-4.1-4.5-6.5c-.9-2.9-3.7-6.5-10.3-13-5-5-9.4-9-9.7-9-1.7 0-5.1-5.3-5.1-7.8 0-1.5-1.3-4.6-2.9-6.8-1.6-2.1-3.2-5-3.6-6.4-.4-1.4-1.8-3.9-3.1-5.7-1.3-1.7-2.4-3.7-2.4-4.3 0-.6-1.5-2.7-3.2-4.6-1.8-2-3.4-4.6-3.4-5.7-.2-3.7-5.1-21.3-6.8-24.6-1.8-3.4-2.3-8.3-2.5-25.4L44 228H19.3l-1.3 2.7zM205.5 425c.3.5.2 1-.4 1-.5 0-1.3-.5-1.6-1-.3-.6-.2-1 .4-1 .5 0 1.3.4 1.6 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
						</svg>';
				break;
				case '32Top': // Teuton
					$sVG = '<svg class="buildingShape 32Top" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="675" height="534" viewBox="0 0 675 534" >
						<g class="clickShape">
							<path d="M307 32.2c-3 .5-14.9 1.1-26.5 1.2-11.5.1-23 .5-25.5.8-2.5.4-5.8.8-7.5 1.1-1.6.2-4.6 1.1-6.5 1.9-2 .8-6.1 1.8-9.3 2.2-3.1.4-6 1.2-6.3 1.8-.4.5-2.1.7-4 .4-2.4-.5-4.5-.1-7.4 1.4-2.2 1.1-6.4 2.5-9.2 3-2.9.6-7.1 1.9-9.3 3-2.3 1.1-4.7 2-5.4 2-.6 0-2.9.9-5.1 2-2.2 1.1-5.2 2-6.8 2-1.6 0-3.7.9-4.6 1.9-1.6 1.8-3.5 2.4-11.3 3.8-3.3.5-7.5 4.9-12.1 12.5-3 4.9-22.1 15.4-25.1 13.8-3.4-1.8-6.3-1.1-9.5 2.4-1.7 1.8-6.3 5.1-10.3 7.2-3.9 2-7.7 4.8-8.4 6.1-.7 1.3-2 2.3-3 2.3s-3 1.7-4.4 3.7c-2.4 3.7-10 10.3-11.6 10.3-.5 0-1.8 1.7-2.9 3.7-4.5 8.2-5.6 9.8-8.2 12.1-1.5 1.3-3.9 4.7-5.4 7.5-1.4 2.9-4.5 7.2-7 9.8-2.4 2.5-4.6 5.4-4.9 6.5-.9 3.9-3.1 9-5.1 11.9-3.1 4.3-9.1 16.8-9.9 20.5-.4 1.7-1.3 4.3-2.1 5.8s-1.4 3.3-1.4 3.9c0 .7-1.6 4.5-3.5 8.4-1.9 4-3.5 7.8-3.5 8.6 0 1 2.7 1.3 12.3 1.3h12.3l3.2-6.5c1.8-3.6 3.2-7.6 3.2-8.8 0-1.3 1.1-3.8 2.5-5.7 1.4-1.9 2.5-4 2.5-4.7 0-.7 1.4-2.8 3-4.6 1.7-1.9 3-4.2 3-5 0-.9 1-1.7 2.2-1.9 2.7-.4 4.7-5.1 4.7-11.6.1-3.5.5-4.4 2.6-5.4 1.4-.6 2.5-1.5 2.5-2 0-.4 1.1-1.8 2.5-2.9 1.3-1.2 2.7-3.4 3-4.8.4-2 1.2-2.6 3.3-2.7 6-.3 12.7-4.1 14.3-8.1.5-1.3 1.5-2.3 2.4-2.3 1.9 0 1.9-1.4 0-3-1.4-1.2-1.4-1.5.1-3.1 1-1.1 2.8-1.9 4.1-1.9 3.3 0 11.3-7.6 11.3-10.7 0-1.8.5-2.3 2.5-2.3 1.4 0 2.8-.7 3.1-1.5.4-1 1.3-1.3 2.5-1 2.1.7 11.8-6.1 11.9-8.2 0-.7.7-1.6 1.7-2.1 2.6-1.5.8-4.5-2.3-3.9-2.1.4-2.7 0-3.5-2.2-.5-1.4-1.7-3.9-2.6-5.5-1.5-2.7-1.5-3 .3-4.3 3.1-2.3 11.2-6.3 12.9-6.3.8 0 1.5-.4 1.5-.8 0-.5 1.2-1.4 2.7-2.1 2.5-1.1 2.9-.9 7.7 3.7 6.6 6.4 8.1 7.1 13.8 6.5 3.4-.4 4.8-1 5.1-2.4.3-1 1.8-2.1 3.3-2.4 1.6-.3 4.4-1.3 6.1-2 1.8-.8 3.9-1.2 4.8-.9.8.4 1.5.1 1.5-.5 0-1.3 5.1-3.5 10.5-4.5 2.2-.4 4.5-1.1 5-1.6.6-.4 3.4-1.3 6.3-1.9 6.3-1.3 18.7-6.4 20.8-8.6.9-.8 3.3-1.5 5.5-1.5s5-.5 6.1-1.1c1.5-.8 2.2-.8 2.7 0s2 .9 4.6.3c3-.6 4.1-1.3 4.5-3.2.6-2.7 4.4-4 6.3-2.1 2.2 2.2 14.2.6 20.6-2.8 1.1-.6 2.2-.7 2.5-.2.7 1 5.6.2 7.3-1.1.7-.6 2.1-.7 3-.3 2.7 1 28.5 2.5 29.8 1.7.5-.4 3.7-.6 7-.4 13.1.6 17.4 1 18.2 1.5 1.7 1 12.9 1.7 14.2.8.9-.6 2.8-.4 5.5.7 2.3.8 6.8 1.6 10.1 1.8 3.3.1 13.6 1.5 23 3.1 12.8 2.3 17.7 2.8 20.3 2.1 2.6-.7 3.8-.6 5.1.6 1 .9 3.8 1.9 6.4 2.2 3.2.5 5.6 1.6 7.7 3.5 1.6 1.6 3.6 2.9 4.4 2.9.7 0 1.9.7 2.7 1.6.9 1.1 2.2 1.4 4.2 1 1.8-.4 3.4-.1 4.2.8.8.7 4.9 1.5 10 1.9 8.9.6 13.5 2.4 13.5 5.2 0 2 2.4 3.1 9.6 4.5 5.6 1.1 6.5 1 10.5-.8 6.7-3.2 11.2-7.6 10.4-10.1-.9-2.9.3-2.7 7 1.4 3.2 1.9 6.7 3.7 7.7 4.1 2 .6 2.4 1.9.8 2.9-.5.3-1 1.7-1 3.1 0 2.9-1.4 6-4.2 9.1-3.8 4.1 1 12.3 7.2 12.3 1.9 0 3 .5 3 1.4 0 .8 1.4 1.9 3 2.5 1.9.6 3 1.7 3 2.9 0 1.1 1.8 4 4.1 6.6 3.3 3.8 4.7 4.6 7.5 4.6 2.2 0 3.4.5 3.4 1.3s2.2 2.4 4.8 3.6c5 2.3 8.2 6.3 8.2 9.9 0 1.1 1.4 3.2 3 4.5 3.3 2.8 5 6.4 5 10.4 0 1.5.7 5 1.7 7.9 1.3 4.3 2.3 5.6 5.7 7.5 7.4 4.1 13.7 9.3 14.3 11.9.3 1.4 1.9 4 3.5 5.8 1.5 1.8 2.8 3.7 2.8 4.2s1.1 1.2 2.4 1.6c2.4.6 2.4.8 1.9 7.3-.4 5.3-.2 7.3 1.1 9.3 1.6 2.5 1.6 2.6-.9 4.5-2.6 1.9-2.6 1.9-1.4 8.7.6 3.7.9 8.5.6 10.7-.4 2.9 0 4.5 1.4 6.4 2.6 3.2 3.4 15.1 1.3 20.9-.7 2.3-1.4 6.3-1.4 9 0 3.7-.6 5.5-2.5 7.8-1.6 1.9-2.5 4.2-2.5 6.3 0 1.8-.3 4.1-.6 4.9-.6 1.5 1.7 1.6 23.2 1.4l23.9-.3.7-15.5c.3-8.5.7-16.2.7-17v-5.8c.1-2.3-.5-5.2-1.3-6.5-1.5-2.4-3.3-13-4.6-27.4-.6-6.8-1.4-10-2.9-12.2-1.2-1.6-2.1-4-2.1-5.4 0-1.3-1.1-4.9-2.4-7.8-1.3-3-2.7-7-3.1-8.9-1-4.7-3-8.5-8-15-2.3-3-6.8-10.6-10-16.8-3.2-6.2-7.5-13.2-9.5-15.5-2-2.3-5.3-6.7-7.3-9.7-2.3-3.6-5.3-6.5-8.6-8.5-3.1-1.9-5.5-4.1-6.2-6-.7-1.7-2.6-4.4-4.3-6.2-1.7-1.7-5.8-6.1-9.1-9.7-3.3-3.6-6.5-6.6-7.2-6.6-.6 0-2.8-2-4.8-4.4-2-2.4-5.5-5.3-7.8-6.4-2.3-1.1-5.1-2.9-6.1-4.1-2.3-2.6-7.2-2.8-9.9-.4-1.8 1.7-2.1 1.6-6-.6-2.3-1.3-7.6-4.1-11.8-6.2-7.3-3.6-7.7-4-8.8-8.3-.6-2.5-2.4-6-4-7.8-3-3.4-7.4-4.9-18.6-6.3-6-.8-12.3-2.9-18.6-6.1-3.1-1.6-7.1-2.5-13-3-4.9-.4-10-1.5-12.4-2.6-3.1-1.5-5.8-1.8-12.5-1.7-4.7.2-9.1 0-9.7-.4-.6-.4-4.2-.8-7.9-.8-3.8-.1-7.8-.6-8.8-1.2-2-1-36-3.2-45.5-2.9-3 .1-5.9-.2-6.5-.7-1-.9-21.1-1.4-33.6-.9-.8 0-2.8-.2-4.5-.5-1.6-.3-5.5-.1-8.5.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="hoverShape">
							<path d="M307 32.2c-3 .5-14.9 1.1-26.5 1.2-11.5.1-23 .5-25.5.8-2.5.4-5.8.8-7.5 1.1-1.6.2-4.6 1.1-6.5 1.9-2 .8-6.1 1.8-9.3 2.2-3.1.4-6 1.2-6.3 1.8-.4.5-2.1.7-4 .4-2.4-.5-4.5-.1-7.4 1.4-2.2 1.1-6.4 2.5-9.2 3-2.9.6-7.1 1.9-9.3 3-2.3 1.1-4.7 2-5.4 2-.6 0-2.9.9-5.1 2-2.2 1.1-5.2 2-6.8 2-1.6 0-3.7.9-4.6 1.9-1.6 1.8-3.5 2.4-11.3 3.8-3.3.5-7.5 4.9-12.1 12.5-3 4.9-22.1 15.4-25.1 13.8-3.4-1.8-6.3-1.1-9.5 2.4-1.7 1.8-6.3 5.1-10.3 7.2-3.9 2-7.7 4.8-8.4 6.1-.7 1.3-2 2.3-3 2.3s-3 1.7-4.4 3.7c-2.4 3.7-10 10.3-11.6 10.3-.5 0-1.8 1.7-2.9 3.7-4.5 8.2-5.6 9.8-8.2 12.1-1.5 1.3-3.9 4.7-5.4 7.5-1.4 2.9-4.5 7.2-7 9.8-2.4 2.5-4.6 5.4-4.9 6.5-.9 3.9-3.1 9-5.1 11.9-3.1 4.3-9.1 16.8-9.9 20.5-.4 1.7-1.3 4.3-2.1 5.8s-1.4 3.3-1.4 3.9c0 .7-1.6 4.5-3.5 8.4-1.9 4-3.5 7.8-3.5 8.6 0 1 2.7 1.3 12.3 1.3h12.3l3.2-6.5c1.8-3.6 3.2-7.6 3.2-8.8 0-1.3 1.1-3.8 2.5-5.7 1.4-1.9 2.5-4 2.5-4.7 0-.7 1.4-2.8 3-4.6 1.7-1.9 3-4.2 3-5 0-.9 1-1.7 2.2-1.9 2.7-.4 4.7-5.1 4.7-11.6.1-3.5.5-4.4 2.6-5.4 1.4-.6 2.5-1.5 2.5-2 0-.4 1.1-1.8 2.5-2.9 1.3-1.2 2.7-3.4 3-4.8.4-2 1.2-2.6 3.3-2.7 6-.3 12.7-4.1 14.3-8.1.5-1.3 1.5-2.3 2.4-2.3 1.9 0 1.9-1.4 0-3-1.4-1.2-1.4-1.5.1-3.1 1-1.1 2.8-1.9 4.1-1.9 3.3 0 11.3-7.6 11.3-10.7 0-1.8.5-2.3 2.5-2.3 1.4 0 2.8-.7 3.1-1.5.4-1 1.3-1.3 2.5-1 2.1.7 11.8-6.1 11.9-8.2 0-.7.7-1.6 1.7-2.1 2.6-1.5.8-4.5-2.3-3.9-2.1.4-2.7 0-3.5-2.2-.5-1.4-1.7-3.9-2.6-5.5-1.5-2.7-1.5-3 .3-4.3 3.1-2.3 11.2-6.3 12.9-6.3.8 0 1.5-.4 1.5-.8 0-.5 1.2-1.4 2.7-2.1 2.5-1.1 2.9-.9 7.7 3.7 6.6 6.4 8.1 7.1 13.8 6.5 3.4-.4 4.8-1 5.1-2.4.3-1 1.8-2.1 3.3-2.4 1.6-.3 4.4-1.3 6.1-2 1.8-.8 3.9-1.2 4.8-.9.8.4 1.5.1 1.5-.5 0-1.3 5.1-3.5 10.5-4.5 2.2-.4 4.5-1.1 5-1.6.6-.4 3.4-1.3 6.3-1.9 6.3-1.3 18.7-6.4 20.8-8.6.9-.8 3.3-1.5 5.5-1.5s5-.5 6.1-1.1c1.5-.8 2.2-.8 2.7 0s2 .9 4.6.3c3-.6 4.1-1.3 4.5-3.2.6-2.7 4.4-4 6.3-2.1 2.2 2.2 14.2.6 20.6-2.8 1.1-.6 2.2-.7 2.5-.2.7 1 5.6.2 7.3-1.1.7-.6 2.1-.7 3-.3 2.7 1 28.5 2.5 29.8 1.7.5-.4 3.7-.6 7-.4 13.1.6 17.4 1 18.2 1.5 1.7 1 12.9 1.7 14.2.8.9-.6 2.8-.4 5.5.7 2.3.8 6.8 1.6 10.1 1.8 3.3.1 13.6 1.5 23 3.1 12.8 2.3 17.7 2.8 20.3 2.1 2.6-.7 3.8-.6 5.1.6 1 .9 3.8 1.9 6.4 2.2 3.2.5 5.6 1.6 7.7 3.5 1.6 1.6 3.6 2.9 4.4 2.9.7 0 1.9.7 2.7 1.6.9 1.1 2.2 1.4 4.2 1 1.8-.4 3.4-.1 4.2.8.8.7 4.9 1.5 10 1.9 8.9.6 13.5 2.4 13.5 5.2 0 2 2.4 3.1 9.6 4.5 5.6 1.1 6.5 1 10.5-.8 6.7-3.2 11.2-7.6 10.4-10.1-.9-2.9.3-2.7 7 1.4 3.2 1.9 6.7 3.7 7.7 4.1 2 .6 2.4 1.9.8 2.9-.5.3-1 1.7-1 3.1 0 2.9-1.4 6-4.2 9.1-3.8 4.1 1 12.3 7.2 12.3 1.9 0 3 .5 3 1.4 0 .8 1.4 1.9 3 2.5 1.9.6 3 1.7 3 2.9 0 1.1 1.8 4 4.1 6.6 3.3 3.8 4.7 4.6 7.5 4.6 2.2 0 3.4.5 3.4 1.3s2.2 2.4 4.8 3.6c5 2.3 8.2 6.3 8.2 9.9 0 1.1 1.4 3.2 3 4.5 3.3 2.8 5 6.4 5 10.4 0 1.5.7 5 1.7 7.9 1.3 4.3 2.3 5.6 5.7 7.5 7.4 4.1 13.7 9.3 14.3 11.9.3 1.4 1.9 4 3.5 5.8 1.5 1.8 2.8 3.7 2.8 4.2s1.1 1.2 2.4 1.6c2.4.6 2.4.8 1.9 7.3-.4 5.3-.2 7.3 1.1 9.3 1.6 2.5 1.6 2.6-.9 4.5-2.6 1.9-2.6 1.9-1.4 8.7.6 3.7.9 8.5.6 10.7-.4 2.9 0 4.5 1.4 6.4 2.6 3.2 3.4 15.1 1.3 20.9-.7 2.3-1.4 6.3-1.4 9 0 3.7-.6 5.5-2.5 7.8-1.6 1.9-2.5 4.2-2.5 6.3 0 1.8-.3 4.1-.6 4.9-.6 1.5 1.7 1.6 23.2 1.4l23.9-.3.7-15.5c.3-8.5.7-16.2.7-17v-5.8c.1-2.3-.5-5.2-1.3-6.5-1.5-2.4-3.3-13-4.6-27.4-.6-6.8-1.4-10-2.9-12.2-1.2-1.6-2.1-4-2.1-5.4 0-1.3-1.1-4.9-2.4-7.8-1.3-3-2.7-7-3.1-8.9-1-4.7-3-8.5-8-15-2.3-3-6.8-10.6-10-16.8-3.2-6.2-7.5-13.2-9.5-15.5-2-2.3-5.3-6.7-7.3-9.7-2.3-3.6-5.3-6.5-8.6-8.5-3.1-1.9-5.5-4.1-6.2-6-.7-1.7-2.6-4.4-4.3-6.2-1.7-1.7-5.8-6.1-9.1-9.7-3.3-3.6-6.5-6.6-7.2-6.6-.6 0-2.8-2-4.8-4.4-2-2.4-5.5-5.3-7.8-6.4-2.3-1.1-5.1-2.9-6.1-4.1-2.3-2.6-7.2-2.8-9.9-.4-1.8 1.7-2.1 1.6-6-.6-2.3-1.3-7.6-4.1-11.8-6.2-7.3-3.6-7.7-4-8.8-8.3-.6-2.5-2.4-6-4-7.8-3-3.4-7.4-4.9-18.6-6.3-6-.8-12.3-2.9-18.6-6.1-3.1-1.6-7.1-2.5-13-3-4.9-.4-10-1.5-12.4-2.6-3.1-1.5-5.8-1.8-12.5-1.7-4.7.2-9.1 0-9.7-.4-.6-.4-4.2-.8-7.9-.8-3.8-.1-7.8-.6-8.8-1.2-2-1-36-3.2-45.5-2.9-3 .1-5.9-.2-6.5-.7-1-.9-21.1-1.4-33.6-.9-.8 0-2.8-.2-4.5-.5-1.6-.3-5.5-.1-8.5.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="clickShapeWinter">
							<path d="M252.7 31c-2.9 2.3-16.2 6.4-26.3 8-16.4 2.7-19.2 3.3-24.8 5.1-3.3 1-7.3 1.9-9 1.9-5.4.1-27.7 11.1-37.6 18.5-1.9 1.5-7.2 4.4-11.6 6.6-4.5 2.1-11.2 5.9-14.9 8.4-3.7 2.5-7.6 4.5-8.7 4.5-2.1 0-9.9 3.7-15.2 7.3-5.7 3.9-42.4 41.8-45.3 46.7-1.4 2.5-3.2 5.2-3.9 6-.7.9-2.5 4.7-3.9 8.5-1.4 3.9-4.4 10.8-6.7 15.5-2.2 4.7-4.5 9.6-5.1 11-.5 1.4-1.4 3.3-1.9 4.3-.5 1.1-2.6 6.7-4.7 12.5-3 8.3-9.7 23.6-13.8 31.4-.6 1.1 27.9 1 29.6-.1.8-.5 1.7-2.9 2.2-5.2C53 211.2 62.4 190 67.4 185c1.9-1.9 5.2-7 7.2-11.3 2-4.2 4-7.7 4.4-7.7.9 0 8.2-7 37.5-36 3.8-3.8 13.4-12.5 19.8-17.9l6.4-5.5-3.4-4.4c-1.8-2.4-3.3-4.7-3.3-5.2s2-2.1 4.5-3.6c2.5-1.4 4.8-3.2 5.3-3.8.4-.7 2.8-2 5.3-2.8l4.7-1.6 3.6 3.7c5.3 5.4 9.3 6.6 15.2 4.6 2.6-.9 6.3-2.9 8.1-4.5 1.9-1.7 4.1-3.1 5.1-3.1 3.2-.2 3.7-.4 6.3-2.2 1.5-1 6-2.6 10-3.7 4.1-1.1 8.5-2.7 9.9-3.4 1.4-.8 5.9-2.4 10-3.5s10.1-2.9 13.3-4.1c3.2-1.1 8.5-2.3 11.8-2.7 3.3-.3 7.7-1.4 9.7-2.5 2-1 5.7-1.8 8.2-1.8 2.5-.1 7.2-.7 10.5-1.6 5.5-1.3 57-3.8 59.9-2.8.6.2 4.7.5 9.1.8 4.4.2 10.7 1 14 1.6 3.3.6 11.2 1.7 17.5 2.5 6.3.8 20.1 3.3 30.5 5.5 10.5 2.3 22.6 4.5 27 4.9 7.3.7 12.7 2.5 16.5 5.5.8.7 4 1.8 7 2.5 5.6 1.3 13.8 4.2 24.4 8.7 7.2 3 17.6 3.5 22.6 1 4.6-2.3 5.3-2.1 10.5 2.4 2.5 2.2 5.2 4 5.9 4 2.8 0 3.6 2.5 1.6 5.4-1.7 2.7-1.7 3.1-.3 6 .9 1.9 4.2 4.8 8.5 7.6 3.9 2.5 8.2 6.2 9.8 8.5 1.5 2.2 5.6 6.5 9.1 9.5 11.6 10 19.1 18.3 21.6 24 1.3 3 3.3 7.3 4.4 9.6 1 2.3 1.9 4.5 1.9 5s4.1 4.9 9.1 9.9c5 4.9 11.3 12.3 13.9 16.4 5.3 8.1 6.1 11.8 5.9 27.1 0 4.1.2 14.8.6 23.7.5 14.2.4 16.6-1.1 19.5-1.2 2.3-1.8 6.3-2.1 13l-.3 9.8h47v-17.3c0-11.9-.4-18.9-1.4-22.3-2-6.9-3.9-20.5-4.2-29.9-.2-6.8-.8-9.3-4.2-17-2.1-5-4.6-11.5-5.5-14.5-.9-3-3.2-8-5.1-11-2-3-3.6-6.6-3.6-8 0-1.4-1.5-5-3.3-8-1.8-3-3.9-7.1-4.7-9-1.9-4.9-4.6-8.6-10.8-15.2-3-3.1-6.2-6.9-7.2-8.5-1-1.5-3.8-4.8-6.4-7.3-2.5-2.4-4.6-5-4.6-5.7 0-.7-.4-1.3-.8-1.3-.5 0-2.3-2.1-4-4.8-3.2-4.8-18.3-20.5-24.6-25.5-10.6-8.4-15.3-10.7-22-10.7-3 0-5.8-.5-6.1-1-.4-.6-2.7-2.2-5.3-3.6-10.1-5.5-14.3-8.1-16.2-10.2-1.2-1.2-2.5-2.2-2.9-2.2-.5 0-2.6-1.6-4.7-3.6-4.2-3.8-9.9-6.7-15.4-7.7-4.9-1-14.2-3.7-16.3-4.8-1-.5-2.3-.7-2.9-.3-.7.4-.8.3-.4-.4.7-1.2-1.7-1.5-15.9-1.7-3.8 0-10.4-.4-14.5-.8-4.1-.3-11.8-.9-17-1.2-5.2-.4-12.6-1.3-16.4-2.1-3.8-.7-12.8-1.6-20-1.9-22.7-1-27.7-1.3-41.6-2.4-24.4-1.9-52.2-3.1-70.6-3.1-16.6 0-18.3.2-20.7 2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="hoverShapeWinter">
							<path d="M259.5 29.7c-1.1.3-3.6 1.7-5.5 3.1-1.9 1.4-4.6 2.7-6 2.8-1.4.1-4.5.9-7 1.7-6.8 2.3-14.7 3.8-22 4.2-3.6.2-10 1.5-14.2 2.9-4.2 1.4-8.5 2.6-9.6 2.6-2.6 0-5.3 1-4.8 1.9.2.3-1.7.4-4.3.3-3.4-.3-5.3.1-6.4 1.2-.8.9-2 1.6-2.5 1.6s-3.8 2.3-7.2 5c-3.5 2.8-7 4.8-7.7 4.5-2.3-.8-5 1.2-8.4 6.4-2.8 4.4-4.5 5.6-14.3 10.7-6.1 3.1-11.7 6.2-12.4 6.9-1.1 1-2.1 1-5.2-.2-3.7-1.5-4.1-1.5-8.3 1.1-2.9 1.6-4.9 3.6-5.6 5.7-.8 2.4-1.5 2.9-3 2.4-1.1-.3-3-.1-4.2.6-3 1.5-7.9 6.7-7.9 8.2 0 .7-1.4 2.3-3 3.6-1.7 1.3-3 3.2-3 4.3 0 1-.3 1.7-.8 1.4-1.9-1.1-7.4 2.5-9 6.1-3.9 8.7-6.2 11.3-9.7 11.3-4.2 0-5.4 1.6-6.2 8.1-.7 6.7-2.5 10.2-6.8 14-2.2 2-3.9 4.9-5.1 8.5-1 3.1-2.7 7-3.9 8.8-1.8 2.7-3.6 5.8-7.7 13.9-.5 1.1-2.6 6.7-4.7 12.5-3 8.3-9.7 23.6-13.8 31.4-.2.5 6.1.8 14.1.8H48v-3.1c0-1.7 2.1-7.9 4.6-13.8 9.2-21.3 12.3-26.5 17-28.9 1.6-.9 2.4-2.8 3.4-8.6 1.3-7.4 1.4-7.6 6-10.6 2.6-1.6 5.2-3 5.8-3 .6 0 2.1-1.4 3.4-3 1.2-1.7 2.7-3 3.4-3 .7 0 1.6-.9 1.9-2 .3-1.1 1.2-2 1.9-2 1.7 0 5.6-4.3 5.6-6.2 0-.9 1-1.8 2.2-2.2 1.7-.4 2-.8 1.1-1.7-2-2-.3-4.9 2.8-4.9 2 0 3.2-.6 3.7-1.9.7-1.9 7.4-7.1 9.2-7.1 1.8 0 7.1-5.3 6.6-6.6-.3-.9.2-1.4 1.3-1.4 1 0 4.7-2.6 8.4-5.8 6.4-5.6 6.5-5.7 4.7-7.6-1.5-1.6-2.2-1.7-3.4-.7-1.2 1-1.6.6-2.6-2.3-.6-1.9-2-4.6-3.1-5.9l-1.9-2.5 6.4-3.6c3.5-2 7.1-3.6 8-3.6.8 0 1.8-.5 2.1-1.1.4-.5 1.9-1.5 3.3-2 2.3-.9 3.1-.5 8.1 4.5 5.6 5.4 10.3 7.8 11.5 5.7.4-.6 1.8-1.1 3.2-1.1 1.4 0 3.2-.6 3.9-1.3 3.6-3.2 4.6-3.7 6.9-3.7 1.4 0 2.8-.5 3.2-1.1.3-.6 1.7-.8 3-.5 2 .5 2.6.2 3.4-1.9.5-1.4 1.7-2.6 2.7-2.7 2.4-.2 3.5-.4 5.3-1.1.8-.3 3.7-.9 6.5-1.2 2.7-.4 6.2-1.5 7.6-2.6 1.5-1 3.6-1.9 4.8-1.9 3.2 0 12.9-3.9 13.4-5.5.3-.7 2.7-1.6 5.4-2 2.6-.3 5.8-1 7-1.5 1.4-.6 2.5-.6 2.8 0 .4.6 2.2 1 4.1 1 2.7 0 3.6-.5 4.4-2.4.8-2.3 1.4-2.5 8.2-2.4 4 0 10-.7 13.3-1.5 6.9-1.8 8.2-1.9 31.2-1.7 9.4 0 17.4-.3 17.7-.8.4-.7 6.8-.3 22.6 1.4 3 .4 9.1.8 13.5.9 4.4.2 9.8 1 12 1.9 4.9 1.8 8.7 2 10.3.4.9-.9 1.5-.9 2.4 0 .7.7 2.7 1.2 4.5 1.2 1.8 0 5.6.9 8.5 1.9 2.9 1.1 7.7 2.2 10.6 2.6 2.8.4 5 1 4.8 1.3-.2.4 1.9.8 4.7.9 20 1 23.6 1.4 24.2 3.2.8 2.7 8.5 7.3 11.2 6.8 1.3-.2 2.8.2 3.4.9.8 1 3.6 1.4 8.8 1.4 9 0 16.1 2.6 16.1 5.9 0 2.9 3 4.1 10.7 4.2 7.9.1 10.7-.8 15.7-5.2 3.2-2.7 3.6-3.6 3-6.2-.3-1.6-.2-2.7.2-2.5.5.3 4.3 2.5 8.4 4.8 4.1 2.4 7.6 4.4 7.7 4.5.7.4-3.8 10.7-6 14-2.4 3.5-2.5 3.9-1 6.9 1.3 2.9 6.1 6.6 8.4 6.6.5 0 1.8.9 2.9 2 1.1 1.1 2.9 2 4 2 1.4 0 2 .7 2 2.1 0 2.4 6 10.3 9.1 12 1.5.8 2.4.7 3.5-.2 1.2-1 1.6-.9 2.1.9.3 1.3 2.1 2.7 4.9 3.8 3 1.1 4.4 2.3 4.4 3.5 0 1 .9 2.3 2 2.9 1.1.6 2 2.1 2 3.5 0 1.3.5 2.7 1 3 2.4 1.5 6.2 9 7.1 14.2 1.6 8.4 2.5 10 6.8 12.3 2.3 1.1 5.5 3.6 7.2 5.5 1.8 1.9 4 3.7 4.9 4 1 .3 2 1.9 2.4 3.6.3 1.7 2.7 5.1 5.6 8 5 5 5.1 5.2 4.9 10.7-.1 3.2.1 6.7.5 7.9.4 1.3.1 3.4-.9 5.2-1.1 2.2-1.3 4.5-.9 8.3.4 2.9.7 8.2.8 11.8.1 5.1.4 6.5 1.7 6.8 1.2.2 1.7 1.4 1.7 4.5 0 2.3.2 6.6.4 9.5.3 3.6 0 5.9-1 7.4-1 1.3-1.5 3.9-1.3 6.7.2 3.6-.4 5.4-2.3 8-1.5 1.9-2.6 4.3-2.6 5.3 0 1.7 1.5 1.8 23.5 1.8H652v-17.3c0-9.4-.5-18.8-1.1-20.7-1.9-6.5-4.9-28.2-4.3-31.9.4-2.6-.1-4.7-1.6-7.6-1.2-2.2-2.7-6-3.5-8.5-3.2-11-7.8-22.6-10.5-26.7-1.7-2.4-3-4.7-3-5.1 0-2.9-2.6-8.7-4.9-11.3-1.8-1.9-3.1-4.6-3.4-7.1-.7-5-5.1-10.7-10.1-13.1-2.4-1.1-4.9-3.7-7.2-7.3-2-3.1-5.5-6.9-7.9-8.6-3.9-2.7-4.3-3.4-3.8-6 .4-2 .1-3.2-1.1-4.1-.9-.7-3.3-3.7-5.4-6.7-3.2-4.6-4.4-5.6-7.3-5.8-4.6-.5-5.6-1.5-7.6-7.8-1.3-4.4-2.2-5.6-4.2-6.1-1.4-.3-4.2-2.3-6.1-4.3-1.9-2.1-5.2-4.7-7.4-5.9-3.8-2.2-3.9-2.2-8.8-.4-2.7.9-5.4 2.3-6 3-1.3 1.6-7-1.1-6.2-3 .3-.9-3-3.2-9.5-6.7-7.9-4.2-10.1-5.9-10.6-7.9-.8-3.8-10.5-14.1-13.2-14.1-1.2 0-2.8-.6-3.5-1.3-.8-.8-3.8-1.3-7.4-1.4-5.2 0-6.8-.5-10.8-3.2-2.5-1.8-5.1-2.9-5.8-2.5-.7.4-.8.3-.4-.4.5-.8-.2-1.2-2.1-1.2-1.5 0-5.2-.3-8.2-.7-4-.4-5.2-.3-4.7.5 1 1.7-8.9 1.6-11.7-.1-1.2-.6-6.2-1.3-11.2-1.5-18.8-.5-24.2-1-26.6-2.3-1.6-.9-7-1.4-16.4-1.6-7.7-.2-16.2-.5-19-.9-14.7-1.6-34.7-2.3-37.1-1.2-1.5.7-3 .6-5-.5-2.1-1.1-3.5-1.2-4.7-.5-1 .5-3.2.6-5 .2-1.8-.4-6.1-.9-9.7-1-10.1-.5-12.8-.8-12.4-1.6.3-.4-1.7-.5-4.3-.2-6.9.8-19 .8-24.8.1-2.7-.3-5.9-.3-7 0zm358.3 123.8c-.3.3-.9-.2-1.2-1.2-.6-1.4-.5-1.5.5-.6.7.7 1 1.5.7 1.8z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						</svg>';
				break;
				case '33Bottom': // 
					$sVG = '<svg class="buildingShape 33Bottom " '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="628" height="534" viewBox="0 0 628 534" >
						<g class="clickShape">
						<path d="M24.6 223.7c-.7 5.5-.1 33.7.9 43.3.4 3.6 1.2 7.1 1.7 7.7.6.7.8 1.9.5 2.7-.3.7.1 2.3 1 3.5.9 1.4 1.2 3.2.8 4.8-.4 1.9 0 3.1 1.5 4.5 1.6 1.5 2 2.7 1.5 4.8-.5 2.2 0 3.9 1.9 6.6 1.6 2.5 2.2 4.2 1.6 4.9-1.1 1.3 2.3 7.5 4.1 7.5.8 0 1 1.3.6 4-.4 3.5-.2 4.3 2.1 6.1 2.2 1.7 2.4 2.2 1.2 2.9-.8.6-1 1-.4 1 1.7 0 2.4 3.7 1.3 7.3-.6 2.2-.5 4.1.3 6.1.9 2.3.9 3.1 0 3.4-2.1.7-1.3 2.7 2.7 6.8 2.5 2.4 4.3 5.6 5.1 8.4 1.4 5.1 4.3 8.5 6.7 7.6 1.2-.5 1.4-.1 1 1.6-.7 2.5 1.7 5.8 4.3 5.8.9 0 2.6-.9 3.8-2l2.2-2 .6 2.5c.3 1.4 1.9 3.4 3.5 4.6 1.6 1.1 2.9 2.6 2.9 3.3 0 .7 1.1 1.9 2.5 2.6 1.8 1 2.5 2.2 2.5 4.3 0 2.5-.2 2.8-1.5 1.7-2.2-1.8-1.8.3.6 3.3 1.1 1.5 3.7 3.2 5.9 3.8 3.3 1 4.3.9 7.2-.8l3.3-1.9.3-9.7.3-9.8 3.2 2.3c1.8 1.2 5.3 4 7.9 6.3l4.8 4v15.7c0 11.6-.3 15.7-1.2 16.1-.8.2-.1 1.6 2.1 3.5 2.4 2.4 4.3 3.2 6.8 3.2 4.9 0 11.4 2 13.9 4.2 1.1 1 2.7 1.8 3.6 1.8.9 0 1.8.6 2.1 1.4.3.7 1.9 1.6 3.7 1.9 1.7.4 3.8 1.4 4.6 2.2.9.8 2.6 1.5 3.8 1.5 1.5 0 2.7.9 3.4 2.5.9 1.9 1.9 2.5 4.2 2.5 1.6 0 3 .5 3 1 0 1.7 5 3.2 6.5 2 .9-.8 1.5-.8 2 0 .3.5 1.5 1 2.6 1 1 0 1.9.6 1.9 1.4 0 1.9 2.4 3.7 5.2 3.9 1.3.2 3.2.7 4.3 1.3 1.1.6 4.5 1.7 7.5 2.4s7.3 2.3 9.6 3.5c2.3 1.3 6.9 2.6 10.5 3 3.5.3 9.1 1.5 12.3 2.6 4 1.4 6.1 1.7 6.6.9.4-.7 1.8-.6 4.6.3 2.1.8 7.3 1.7 11.5 2 4.1.3 8.9 1.2 10.5 1.9 1.7.7 7.3 1.2 13.6 1.3 10.2 0 10.8.1 13.5 2.7 4.2 4 11.2 7.3 12.5 6 .8-.8 1.4-.8 2.2.2.6.7 3.5 1.8 6.5 2.5 5 1.1 5.6 1 7.6-.9 2.1-2 2.5-2 5.7-.7 2.6 1.1 4 1.2 5.4.4 1-.6 4.6-1.2 7.9-1.2 6.7-.2 12.4-2.6 13.2-5.6.3-1 .6-1.9.7-2 0 0 4.8-.6 10.6-1.4 15.3-1.9 25.2-3.6 27.7-4.8 1.2-.5 3.8-.7 5.9-.4 3.9.7 15.5-1.5 21.7-4.1 3.1-1.3 4.3-1.3 5.8-.4 2.8 1.8 15.9-1.3 16.6-3.8.4-1.3 1.5-1.8 4.5-1.8 2.2-.1 5-.8 6.3-1.6 1.4-.9 3.1-1.6 3.8-1.6.8 0 2.6-.9 4-2s3.2-1.8 4-1.4c.8.3 1.7-.2 2-1 .4-.9 1.4-1.2 2.6-.9 1.3.3 2.3-.2 3.1-1.7.9-1.6 1.5-1.9 2.5-1 .7.6 4.2 1 7.7 1 3.4-.1 7.6-.1 9.2 0 1.6 0 3.7-.7 4.7-1.6 1.6-1.5 2.1-1.5 4.1-.2 3.3 2 8.2.9 8.6-2 .3-1.9.9-2.2 4.8-2.2h4.4v-6.4c0-6.1.1-6.4 2.6-7l2.7-.7-.7-15.9-.7-15.8 7.8-6.5 7.8-6.5 1.1 7.3c.5 4 1.6 8 2.3 8.9 2.7 3.3 11.7 1.1 14.3-3.4 1-1.8 3.1-3.2 5.9-4.1 3.6-1.2 4.5-1.9 4.7-4.2.2-1.8.9-2.7 2.2-2.7 1 0 2-.7 2.4-1.5.3-.8 1.4-1.5 2.4-1.5 2.7 0 4.3-2.3 5.3-7.3.6-3.5.4-4.6-1.2-6.3-1.9-2-1.9-2.1.1-3.9 1.1-1 2-2.2 2-2.8 0-.5 1.1-1.8 2.5-2.9 1.4-1.1 2.5-2.6 2.5-3.4 0-.8.9-2.2 2-3.1 1.1-1 2-2.5 2-3.3 0-.9 1.1-2.7 2.5-4 1.5-1.4 2.5-3.5 2.5-5.2 0-1.8.5-2.8 1.5-2.8.9 0 1.5-.9 1.5-2.5 0-1.4.6-2.8 1.4-3.1.8-.3 1.7-2.5 2.1-5 .4-2.9 1.3-4.8 2.6-5.4 1-.6 1.9-2.1 1.9-3.3 0-1.2.7-3.1 1.5-4.1.8-1.1 1.5-4.1 1.5-6.7 0-2.6.5-5.1 1-5.4 2.5-1.5.6-2.5-4.7-2.5l-5.8.1-3.8 7.9c-2.1 4.4-4.1 9.5-4.4 11.4-.4 2.4-1 3.3-1.9 2.9-.9-.3-1.4.2-1.4 1.6 0 1.2-.9 3-2 4.1-1.1 1.1-2 3.3-2 4.9 0 3.1-1.5 5-2.2 2.9-.3-.7-1.3 1.1-2.3 4.1-1.4 4.3-1.9 5.1-2.7 3.7-1.4-2.4-2.5-1.2-3.3 3.6-.6 3.6-.9 4-2 2.5-1.7-2.4-2.2-2.2-3.5 1.9-.8 2.4-1.5 3.2-2 2.4-1.1-1.8-2.8-.4-3.5 3.1-.5 2.3-1 2.8-1.8 2-1.8-1.8-3.4-1.3-4.1 1.4-.9 3.5-1.7 3.2-4.9-2-1.6-2.5-3.2-4.5-3.5-4.5-.4 0-2.6 3.5-5 7.6-2.9 5.1-4.1 8.2-3.7 9.5.8 2.4-.1 3.6-8.9 11.1-7.8 6.8-8.4 6.7-12.6-2.2-1.3-2.8-2.7-5-3.2-5-.4 0-2.4 3.1-4.3 7-3.1 6.2-3.5 7.8-3.5 15.2-.1 6.1-.3 7.8-1.1 6.5-1.6-2.7-2.7-2-4 2.4-1.1 3.6-1.4 3.8-2.1 2-1-2.9-2.7-2.6-3.9.6-2 5.7-2.1 5.9-2.6 4.5-.8-2.1-2.3-1.3-3.7 1.9l-1.3 3-1.7-2.5c-2.2-3.5-3.1-3.3-4.9 1.1-1.4 3.6-1.5 3.6-2.6 1.5-1.7-3.2-2.9-2.7-4.2 1.9-1 3.1-1.5 3.7-2.1 2.5-1.3-2.3-3.5-2-4.2.6l-.7 2.3-1.1-2.3c-1.3-2.6-3.1-2.4-3.7.4-.2 1-.7 3-1.2 4.3-.8 2-1.2 2.1-2.5 1s-1.7-1-2.5.4c-1.3 2.3-2.5 2.1-3.2-.3-.8-3-2.4-2.4-3.8 1.5l-1.3 3.5-1.3-2.5c-1.8-3.2-3.1-3.2-4.4.2-1.2 2.9-1.3 2.8-2.3-2-.9-3.8-2.1-3.4-4 1.4-1.5 3.8-1.8 4-3 2.4-1.6-2.2-3.6-1.2-3.6 1.8 0 2.2-.1 2.2-2-.3l-2-2.5-2.2 2.9-2.2 3-.6-2.5c-.8-3.4-3.3-3-4.4.8-.8 3-1 3.1-2.2 1.5-1.3-1.6-1.5-1.5-3.2.7-1.9 2.3-1.9 2.3-2.5.2-.7-2.9-2.5-2.7-3.9.5-1.1 2.4-1.3 2.5-2.6.7-1.2-1.7-1.4-1.7-3.2.6l-1.8 2.5-.4-2.4c-.5-3.9-2.6-3.5-3.4.7l-.7 3.8-1.4-3.3c-.7-1.7-1.7-3.2-2.1-3.2-.5 0-1.6 1.7-2.6 3.7l-1.8 3.8-1.5-3-1.6-2.9-1.8 2.2c-.9 1.2-2 2.8-2.2 3.5-.3.8-1 .6-2.1-.8-1.6-2-1.6-2-3.5.4l-1.9 2.4-.6-2.1c-1-3-2.3-2.7-4.5 1l-2 3.1-.8-3.3c-1.2-4.5-2.8-4.8-4.2-.9-.9 2.7-1.3 3-2.6 1.9-1.3-1-1.9-1-3.4.4-1.7 1.5-1.8 1.5-2.9-.9-1.5-3.2-2.8-3.2-3.9.2l-1 2.8-1.2-2.3c-1.5-2.7-2.8-2.8-3.6-.2-.9 2.7-1.9 2.5-3.3-.5-1.5-3.2-2.3-3.1-4.4.2l-1.6 2.7-1.3-2.7c-1.5-3.3-2.2-3.4-4.7-.2-2.5 3.1-2.8 3.1-3.6 0-.4-1.4-1.3-2.5-2.1-2.5s-1.7 1.1-2.1 2.5c-.7 2.6-3.4 3.5-3.4 1.1 0-3.1-2.1-2.6-3.7.9l-1.7 3.5-1.2-3c-1.6-3.7-2.7-3.8-4.4-.2l-1.3 2.7-.9-3.3c-1-3.7-2.3-4.1-3.8-1.1-1.1 2-1.2 2-2.6.2-1.4-1.7-1.5-1.7-3.1.5l-1.6 2.3-1.3-3c-1.5-3.6-3-4-3.9-1.1-.6 2-.7 2-2.6-.2l-1.9-2.3-1.8 2.3-1.8 2.3-1.3-2.8-1.3-2.8-2.2 2.8-2.2 2.8-1.2-2.6c-1.5-3.2-2.6-3.2-4.1.2l-1.3 2.8-1.3-3.3c-1.6-3.7-3-4-4.7-1.2-1.2 1.8-1.3 1.8-3.1.3-1.5-1.4-2.2-1.5-3.8-.5-1.5 1-1.9 1-1.9-.1 0-.7-.7-2.2-1.5-3.3-1.4-1.9-1.5-1.8-3.4.5l-1.9 2.5-1.6-3.8c-1.6-3.8-1.7-3.8-3.6-2.1-1.8 1.7-1.9 1.6-2.6-.8-1-4.4-3-5.2-5-2.2l-1.7 2.7-1.1-3.3c-1.3-4-3.2-4.8-4.1-1.8l-.7 2.2-1.9-2.4c-1.8-2.3-2-2.3-3-.5-1.1 1.7-1.3 1.7-3.4-1l-2.2-2.8-1.3 2.3c-1.2 2.3-1.2 2.3-3.1-1.8-2-4.5-3.4-5.2-4.4-2.1-.9 2.9-2.1 2.5-3.1-1.2-1.2-4.1-3.1-4.7-4-1.1l-.7 2.7-1.8-4.2c-2.1-4.9-3.3-5.3-4.7-1.5l-1 2.8-1.2-3.3c-1.4-3.7-3.3-4.1-4.7-1-1.2 2.5-1.2 2.3-3.8-5-.7-1.9-2.1-1.4-3.2 1.1l-1.1 2.2-1.2-2.8c-1.4-3-3.5-3.5-4.4-1.2-.9 2.4-2.3 1.8-3.5-1.5-1.2-3.5-2.7-3.9-3.6-.9-.7 2.1-.8 2-2.7-1-2.2-3.4-2.8-3.7-4.5-2-.9.9-1.5 0-2.4-3.5-1.1-4.3-3.9-6.6-3.9-3.1 0 .8-.4 1.5-1 1.5-.5 0-1-.4-1-.9s-.6-1.6-1.4-2.4c-.8-.7-1.6-3.6-1.8-6.3-.4-5.2-7.4-20.4-9.3-20.4-.5 0-2.3 2.3-4 5l-3 4.9-9.2-8c-7.1-6.2-9.3-8.7-9.3-10.6 0-1.4-.8-3.7-1.9-5.1-1-1.5-2.8-4.6-4-7-1.2-2.3-2.5-4.2-3-4.2s-1.9 1.7-3.1 3.7c-1.7 2.9-2.3 3.4-3.1 2.2-.7-1.2-1-1.2-1.8-.1-.6 1-1.1.6-1.9-1.7-1.1-3.1-2.7-4.1-3.4-2-.2.7-1-.7-1.7-3-.8-2.8-1.7-4.1-2.5-3.8-.8.3-2-.9-3-3-1-2-2.2-3.3-2.9-3-.8.3-2.5-2.1-4.6-6.4-1.8-3.8-3.9-6.9-4.6-6.9-.8 0-1.6-1.4-2-3-.4-1.7-1.5-3.9-2.4-4.8-1-.9-2.1-3.1-2.5-4.7-.4-1.7-2-5-3.5-7.5-3.5-5.9-8.6-18.9-10.5-27-4.7-20.5-4.8-21.7-4.4-36.7.3-12.3.7-15.3 2.2-17.6l1.8-2.7h-11l-.6 4.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="hoverShape">
						<path d="M24.6 223.7c-.7 5.5-.1 33.7.9 43.3.4 3.6 1.2 7.1 1.7 7.7.6.7.8 1.9.5 2.7-.3.7.1 2.3 1 3.5.9 1.4 1.2 3.2.8 4.8-.4 1.9 0 3.1 1.5 4.5 1.6 1.5 2 2.7 1.5 4.8-.5 2.2 0 3.9 1.9 6.6 1.6 2.5 2.2 4.2 1.6 4.9-1.1 1.3 2.3 7.5 4.1 7.5.8 0 1 1.3.6 4-.4 3.5-.2 4.3 2.1 6.1 2.2 1.7 2.4 2.2 1.2 2.9-.8.6-1 1-.4 1 1.7 0 2.4 3.7 1.3 7.3-.6 2.2-.5 4.1.3 6.1.9 2.3.9 3.1 0 3.4-2.1.7-1.3 2.7 2.7 6.8 2.5 2.4 4.3 5.6 5.1 8.4 1.4 5.1 4.3 8.5 6.7 7.6 1.2-.5 1.4-.1 1 1.6-.7 2.5 1.7 5.8 4.3 5.8.9 0 2.6-.9 3.8-2l2.2-2 .6 2.5c.3 1.4 1.9 3.4 3.5 4.6 1.6 1.1 2.9 2.6 2.9 3.3 0 .7 1.1 1.9 2.5 2.6 1.8 1 2.5 2.2 2.5 4.3 0 2.5-.2 2.8-1.5 1.7-2.2-1.8-1.8.3.6 3.3 1.1 1.5 3.7 3.2 5.9 3.8 3.3 1 4.3.9 7.2-.8l3.3-1.9.3-9.7.3-9.8 3.2 2.3c1.8 1.2 5.3 4 7.9 6.3l4.8 4v15.7c0 11.6-.3 15.7-1.2 16.1-.8.2-.1 1.6 2.1 3.5 2.4 2.4 4.3 3.2 6.8 3.2 4.9 0 11.4 2 13.9 4.2 1.1 1 2.7 1.8 3.6 1.8.9 0 1.8.6 2.1 1.4.3.7 1.9 1.6 3.7 1.9 1.7.4 3.8 1.4 4.6 2.2.9.8 2.6 1.5 3.8 1.5 1.5 0 2.7.9 3.4 2.5.9 1.9 1.9 2.5 4.2 2.5 1.6 0 3 .5 3 1 0 1.7 5 3.2 6.5 2 .9-.8 1.5-.8 2 0 .3.5 1.5 1 2.6 1 1 0 1.9.6 1.9 1.4 0 1.9 2.4 3.7 5.2 3.9 1.3.2 3.2.7 4.3 1.3 1.1.6 4.5 1.7 7.5 2.4s7.3 2.3 9.6 3.5c2.3 1.3 6.9 2.6 10.5 3 3.5.3 9.1 1.5 12.3 2.6 4 1.4 6.1 1.7 6.6.9.4-.7 1.8-.6 4.6.3 2.1.8 7.3 1.7 11.5 2 4.1.3 8.9 1.2 10.5 1.9 1.7.7 7.3 1.2 13.6 1.3 10.2 0 10.8.1 13.5 2.7 4.2 4 11.2 7.3 12.5 6 .8-.8 1.4-.8 2.2.2.6.7 3.5 1.8 6.5 2.5 5 1.1 5.6 1 7.6-.9 2.1-2 2.5-2 5.7-.7 2.6 1.1 4 1.2 5.4.4 1-.6 4.6-1.2 7.9-1.2 6.7-.2 12.4-2.6 13.2-5.6.3-1 .6-1.9.7-2 0 0 4.8-.6 10.6-1.4 15.3-1.9 25.2-3.6 27.7-4.8 1.2-.5 3.8-.7 5.9-.4 3.9.7 15.5-1.5 21.7-4.1 3.1-1.3 4.3-1.3 5.8-.4 2.8 1.8 15.9-1.3 16.6-3.8.4-1.3 1.5-1.8 4.5-1.8 2.2-.1 5-.8 6.3-1.6 1.4-.9 3.1-1.6 3.8-1.6.8 0 2.6-.9 4-2s3.2-1.8 4-1.4c.8.3 1.7-.2 2-1 .4-.9 1.4-1.2 2.6-.9 1.3.3 2.3-.2 3.1-1.7.9-1.6 1.5-1.9 2.5-1 .7.6 4.2 1 7.7 1 3.4-.1 7.6-.1 9.2 0 1.6 0 3.7-.7 4.7-1.6 1.6-1.5 2.1-1.5 4.1-.2 3.3 2 8.2.9 8.6-2 .3-1.9.9-2.2 4.8-2.2h4.4v-6.4c0-6.1.1-6.4 2.6-7l2.7-.7-.7-15.9-.7-15.8 7.8-6.5 7.8-6.5 1.1 7.3c.5 4 1.6 8 2.3 8.9 2.7 3.3 11.7 1.1 14.3-3.4 1-1.8 3.1-3.2 5.9-4.1 3.6-1.2 4.5-1.9 4.7-4.2.2-1.8.9-2.7 2.2-2.7 1 0 2-.7 2.4-1.5.3-.8 1.4-1.5 2.4-1.5 2.7 0 4.3-2.3 5.3-7.3.6-3.5.4-4.6-1.2-6.3-1.9-2-1.9-2.1.1-3.9 1.1-1 2-2.2 2-2.8 0-.5 1.1-1.8 2.5-2.9 1.4-1.1 2.5-2.6 2.5-3.4 0-.8.9-2.2 2-3.1 1.1-1 2-2.5 2-3.3 0-.9 1.1-2.7 2.5-4 1.5-1.4 2.5-3.5 2.5-5.2 0-1.8.5-2.8 1.5-2.8.9 0 1.5-.9 1.5-2.5 0-1.4.6-2.8 1.4-3.1.8-.3 1.7-2.5 2.1-5 .4-2.9 1.3-4.8 2.6-5.4 1-.6 1.9-2.1 1.9-3.3 0-1.2.7-3.1 1.5-4.1.8-1.1 1.5-4.1 1.5-6.7 0-2.6.5-5.1 1-5.4 2.5-1.5.6-2.5-4.7-2.5l-5.8.1-3.8 7.9c-2.1 4.4-4.1 9.5-4.4 11.4-.4 2.4-1 3.3-1.9 2.9-.9-.3-1.4.2-1.4 1.6 0 1.2-.9 3-2 4.1-1.1 1.1-2 3.3-2 4.9 0 3.1-1.5 5-2.2 2.9-.3-.7-1.3 1.1-2.3 4.1-1.4 4.3-1.9 5.1-2.7 3.7-1.4-2.4-2.5-1.2-3.3 3.6-.6 3.6-.9 4-2 2.5-1.7-2.4-2.2-2.2-3.5 1.9-.8 2.4-1.5 3.2-2 2.4-1.1-1.8-2.8-.4-3.5 3.1-.5 2.3-1 2.8-1.8 2-1.8-1.8-3.4-1.3-4.1 1.4-.9 3.5-1.7 3.2-4.9-2-1.6-2.5-3.2-4.5-3.5-4.5-.4 0-2.6 3.5-5 7.6-2.9 5.1-4.1 8.2-3.7 9.5.8 2.4-.1 3.6-8.9 11.1-7.8 6.8-8.4 6.7-12.6-2.2-1.3-2.8-2.7-5-3.2-5-.4 0-2.4 3.1-4.3 7-3.1 6.2-3.5 7.8-3.5 15.2-.1 6.1-.3 7.8-1.1 6.5-1.6-2.7-2.7-2-4 2.4-1.1 3.6-1.4 3.8-2.1 2-1-2.9-2.7-2.6-3.9.6-2 5.7-2.1 5.9-2.6 4.5-.8-2.1-2.3-1.3-3.7 1.9l-1.3 3-1.7-2.5c-2.2-3.5-3.1-3.3-4.9 1.1-1.4 3.6-1.5 3.6-2.6 1.5-1.7-3.2-2.9-2.7-4.2 1.9-1 3.1-1.5 3.7-2.1 2.5-1.3-2.3-3.5-2-4.2.6l-.7 2.3-1.1-2.3c-1.3-2.6-3.1-2.4-3.7.4-.2 1-.7 3-1.2 4.3-.8 2-1.2 2.1-2.5 1s-1.7-1-2.5.4c-1.3 2.3-2.5 2.1-3.2-.3-.8-3-2.4-2.4-3.8 1.5l-1.3 3.5-1.3-2.5c-1.8-3.2-3.1-3.2-4.4.2-1.2 2.9-1.3 2.8-2.3-2-.9-3.8-2.1-3.4-4 1.4-1.5 3.8-1.8 4-3 2.4-1.6-2.2-3.6-1.2-3.6 1.8 0 2.2-.1 2.2-2-.3l-2-2.5-2.2 2.9-2.2 3-.6-2.5c-.8-3.4-3.3-3-4.4.8-.8 3-1 3.1-2.2 1.5-1.3-1.6-1.5-1.5-3.2.7-1.9 2.3-1.9 2.3-2.5.2-.7-2.9-2.5-2.7-3.9.5-1.1 2.4-1.3 2.5-2.6.7-1.2-1.7-1.4-1.7-3.2.6l-1.8 2.5-.4-2.4c-.5-3.9-2.6-3.5-3.4.7l-.7 3.8-1.4-3.3c-.7-1.7-1.7-3.2-2.1-3.2-.5 0-1.6 1.7-2.6 3.7l-1.8 3.8-1.5-3-1.6-2.9-1.8 2.2c-.9 1.2-2 2.8-2.2 3.5-.3.8-1 .6-2.1-.8-1.6-2-1.6-2-3.5.4l-1.9 2.4-.6-2.1c-1-3-2.3-2.7-4.5 1l-2 3.1-.8-3.3c-1.2-4.5-2.8-4.8-4.2-.9-.9 2.7-1.3 3-2.6 1.9-1.3-1-1.9-1-3.4.4-1.7 1.5-1.8 1.5-2.9-.9-1.5-3.2-2.8-3.2-3.9.2l-1 2.8-1.2-2.3c-1.5-2.7-2.8-2.8-3.6-.2-.9 2.7-1.9 2.5-3.3-.5-1.5-3.2-2.3-3.1-4.4.2l-1.6 2.7-1.3-2.7c-1.5-3.3-2.2-3.4-4.7-.2-2.5 3.1-2.8 3.1-3.6 0-.4-1.4-1.3-2.5-2.1-2.5s-1.7 1.1-2.1 2.5c-.7 2.6-3.4 3.5-3.4 1.1 0-3.1-2.1-2.6-3.7.9l-1.7 3.5-1.2-3c-1.6-3.7-2.7-3.8-4.4-.2l-1.3 2.7-.9-3.3c-1-3.7-2.3-4.1-3.8-1.1-1.1 2-1.2 2-2.6.2-1.4-1.7-1.5-1.7-3.1.5l-1.6 2.3-1.3-3c-1.5-3.6-3-4-3.9-1.1-.6 2-.7 2-2.6-.2l-1.9-2.3-1.8 2.3-1.8 2.3-1.3-2.8-1.3-2.8-2.2 2.8-2.2 2.8-1.2-2.6c-1.5-3.2-2.6-3.2-4.1.2l-1.3 2.8-1.3-3.3c-1.6-3.7-3-4-4.7-1.2-1.2 1.8-1.3 1.8-3.1.3-1.5-1.4-2.2-1.5-3.8-.5-1.5 1-1.9 1-1.9-.1 0-.7-.7-2.2-1.5-3.3-1.4-1.9-1.5-1.8-3.4.5l-1.9 2.5-1.6-3.8c-1.6-3.8-1.7-3.8-3.6-2.1-1.8 1.7-1.9 1.6-2.6-.8-1-4.4-3-5.2-5-2.2l-1.7 2.7-1.1-3.3c-1.3-4-3.2-4.8-4.1-1.8l-.7 2.2-1.9-2.4c-1.8-2.3-2-2.3-3-.5-1.1 1.7-1.3 1.7-3.4-1l-2.2-2.8-1.3 2.3c-1.2 2.3-1.2 2.3-3.1-1.8-2-4.5-3.4-5.2-4.4-2.1-.9 2.9-2.1 2.5-3.1-1.2-1.2-4.1-3.1-4.7-4-1.1l-.7 2.7-1.8-4.2c-2.1-4.9-3.3-5.3-4.7-1.5l-1 2.8-1.2-3.3c-1.4-3.7-3.3-4.1-4.7-1-1.2 2.5-1.2 2.3-3.8-5-.7-1.9-2.1-1.4-3.2 1.1l-1.1 2.2-1.2-2.8c-1.4-3-3.5-3.5-4.4-1.2-.9 2.4-2.3 1.8-3.5-1.5-1.2-3.5-2.7-3.9-3.6-.9-.7 2.1-.8 2-2.7-1-2.2-3.4-2.8-3.7-4.5-2-.9.9-1.5 0-2.4-3.5-1.1-4.3-3.9-6.6-3.9-3.1 0 .8-.4 1.5-1 1.5-.5 0-1-.4-1-.9s-.6-1.6-1.4-2.4c-.8-.7-1.6-3.6-1.8-6.3-.4-5.2-7.4-20.4-9.3-20.4-.5 0-2.3 2.3-4 5l-3 4.9-9.2-8c-7.1-6.2-9.3-8.7-9.3-10.6 0-1.4-.8-3.7-1.9-5.1-1-1.5-2.8-4.6-4-7-1.2-2.3-2.5-4.2-3-4.2s-1.9 1.7-3.1 3.7c-1.7 2.9-2.3 3.4-3.1 2.2-.7-1.2-1-1.2-1.8-.1-.6 1-1.1.6-1.9-1.7-1.1-3.1-2.7-4.1-3.4-2-.2.7-1-.7-1.7-3-.8-2.8-1.7-4.1-2.5-3.8-.8.3-2-.9-3-3-1-2-2.2-3.3-2.9-3-.8.3-2.5-2.1-4.6-6.4-1.8-3.8-3.9-6.9-4.6-6.9-.8 0-1.6-1.4-2-3-.4-1.7-1.5-3.9-2.4-4.8-1-.9-2.1-3.1-2.5-4.7-.4-1.7-2-5-3.5-7.5-3.5-5.9-8.6-18.9-10.5-27-4.7-20.5-4.8-21.7-4.4-36.7.3-12.3.7-15.3 2.2-17.6l1.8-2.7h-11l-.6 4.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="clickShapeWinter">
						<path d="M24.6 223.7c-.7 5.5-.1 33.7.9 43.3.4 3.6 1.2 7.1 1.7 7.7.6.7.8 1.9.5 2.7-.3.7.1 2.3 1 3.5.9 1.4 1.2 3.2.8 4.8-.4 1.9 0 3.1 1.5 4.5 1.6 1.5 2 2.7 1.5 4.8-.5 2.2 0 3.9 1.9 6.6 1.6 2.5 2.2 4.2 1.6 4.9-1.1 1.3 2.3 7.5 4.1 7.5.8 0 1 1.3.6 4-.4 3.5-.2 4.3 2.1 6.1 2.2 1.7 2.4 2.2 1.2 2.9-.8.6-1 1-.4 1 1.7 0 2.4 3.7 1.3 7.3-.6 2.2-.5 4.1.3 6.1.9 2.3.9 3.1 0 3.4-2.1.7-1.3 2.7 2.7 6.8 2.5 2.4 4.3 5.6 5.1 8.4 1.4 5.1 4.3 8.5 6.7 7.6 1.2-.5 1.4-.1 1 1.6-.7 2.5 1.7 5.8 4.3 5.8.9 0 2.6-.9 3.8-2l2.2-2 .6 2.5c.3 1.4 1.9 3.4 3.5 4.6 1.6 1.1 2.9 2.6 2.9 3.3 0 .7 1.1 1.9 2.5 2.6 1.8 1 2.5 2.2 2.5 4.3 0 2.5-.2 2.8-1.5 1.7-2.2-1.8-1.8.3.6 3.3 1.1 1.5 3.7 3.2 5.9 3.8 3.3 1 4.3.9 7.2-.8l3.3-1.9.3-9.7.3-9.8 3.2 2.3c1.8 1.2 5.3 4 7.9 6.3l4.8 4v15.7c0 11.6-.3 15.7-1.2 16.1-.8.2-.1 1.6 2.1 3.5 2.4 2.4 4.3 3.2 6.8 3.2 4.9 0 11.4 2 13.9 4.2 1.1 1 2.7 1.8 3.6 1.8.9 0 1.8.6 2.1 1.4.3.7 1.9 1.6 3.7 1.9 1.7.4 3.8 1.4 4.6 2.2.9.8 2.6 1.5 3.8 1.5 1.5 0 2.7.9 3.4 2.5.9 1.9 1.9 2.5 4.2 2.5 1.6 0 3 .5 3 1 0 1.7 5 3.2 6.5 2 .9-.8 1.5-.8 2 0 .3.5 1.5 1 2.6 1 1 0 1.9.6 1.9 1.4 0 1.9 2.4 3.7 5.2 3.9 1.3.2 3.2.7 4.3 1.3 1.1.6 4.5 1.7 7.5 2.4s7.3 2.3 9.6 3.5c2.3 1.3 6.9 2.6 10.5 3 3.5.3 9.1 1.5 12.3 2.6 4 1.4 6.1 1.7 6.6.9.4-.7 1.8-.6 4.6.3 2.1.8 7.3 1.7 11.5 2 4.1.3 8.9 1.2 10.5 1.9 1.7.7 7.3 1.2 13.6 1.3 10.2 0 10.8.1 13.5 2.7 4.2 4 11.2 7.3 12.5 6 .8-.8 1.4-.8 2.2.2.6.7 3.5 1.8 6.5 2.5 5 1.1 5.6 1 7.6-.9 2.1-2 2.5-2 5.7-.7 2.6 1.1 4 1.2 5.4.4 1-.6 4.6-1.2 7.9-1.2 6.7-.2 12.4-2.6 13.2-5.6.3-1 .6-1.9.7-2 0 0 4.8-.6 10.6-1.4 15.3-1.9 25.2-3.6 27.7-4.8 1.2-.5 3.8-.7 5.9-.4 3.9.7 15.5-1.5 21.7-4.1 3.1-1.3 4.3-1.3 5.8-.4 2.8 1.8 15.9-1.3 16.6-3.8.4-1.3 1.5-1.8 4.5-1.8 2.2-.1 5-.8 6.3-1.6 1.4-.9 3.1-1.6 3.8-1.6.8 0 2.6-.9 4-2s3.2-1.8 4-1.4c.8.3 1.7-.2 2-1 .4-.9 1.4-1.2 2.6-.9 1.3.3 2.3-.2 3.1-1.7.9-1.6 1.5-1.9 2.5-1 .7.6 4.2 1 7.7 1 3.4-.1 7.6-.1 9.2 0 1.6 0 3.7-.7 4.7-1.6 1.6-1.5 2.1-1.5 4.1-.2 3.3 2 8.2.9 8.6-2 .3-1.9.9-2.2 4.8-2.2h4.4v-6.4c0-6.1.1-6.4 2.6-7l2.7-.7-.7-15.9-.7-15.8 7.8-6.5 7.8-6.5 1.1 7.3c.5 4 1.6 8 2.3 8.9 2.7 3.3 11.7 1.1 14.3-3.4 1-1.8 3.1-3.2 5.9-4.1 3.6-1.2 4.5-1.9 4.7-4.2.2-1.8.9-2.7 2.2-2.7 1 0 2-.7 2.4-1.5.3-.8 1.4-1.5 2.4-1.5 2.7 0 4.3-2.3 5.3-7.3.6-3.5.4-4.6-1.2-6.3-1.9-2-1.9-2.1.1-3.9 1.1-1 2-2.2 2-2.8 0-.5 1.1-1.8 2.5-2.9 1.4-1.1 2.5-2.6 2.5-3.4 0-.8.9-2.2 2-3.1 1.1-1 2-2.5 2-3.3 0-.9 1.1-2.7 2.5-4 1.5-1.4 2.5-3.5 2.5-5.2 0-1.8.5-2.8 1.5-2.8.9 0 1.5-.9 1.5-2.5 0-1.4.6-2.8 1.4-3.1.8-.3 1.7-2.5 2.1-5 .4-2.9 1.3-4.8 2.6-5.4 1-.6 1.9-2.1 1.9-3.3 0-1.2.7-3.1 1.5-4.1.8-1.1 1.5-4.1 1.5-6.7 0-2.6.5-5.1 1-5.4 2.5-1.5.6-2.5-4.7-2.5l-5.8.1-3.8 7.9c-2.1 4.4-4.1 9.5-4.4 11.4-.4 2.4-1 3.3-1.9 2.9-.9-.3-1.4.2-1.4 1.6 0 1.2-.9 3-2 4.1-1.1 1.1-2 3.3-2 4.9 0 3.1-1.5 5-2.2 2.9-.3-.7-1.3 1.1-2.3 4.1-1.4 4.3-1.9 5.1-2.7 3.7-1.4-2.4-2.5-1.2-3.3 3.6-.6 3.6-.9 4-2 2.5-1.7-2.4-2.2-2.2-3.5 1.9-.8 2.4-1.5 3.2-2 2.4-1.1-1.8-2.8-.4-3.5 3.1-.5 2.3-1 2.8-1.8 2-1.8-1.8-3.4-1.3-4.1 1.4-.9 3.5-1.7 3.2-4.9-2-1.6-2.5-3.2-4.5-3.5-4.5-.4 0-2.6 3.5-5 7.6-2.9 5.1-4.1 8.2-3.7 9.5.8 2.4-.1 3.6-8.9 11.1-7.8 6.8-8.4 6.7-12.6-2.2-1.3-2.8-2.7-5-3.2-5-.4 0-2.4 3.1-4.3 7-3.1 6.2-3.5 7.8-3.5 15.2-.1 6.1-.3 7.8-1.1 6.5-1.6-2.7-2.7-2-4 2.4-1.1 3.6-1.4 3.8-2.1 2-1-2.9-2.7-2.6-3.9.6-2 5.7-2.1 5.9-2.6 4.5-.8-2.1-2.3-1.3-3.7 1.9l-1.3 3-1.7-2.5c-2.2-3.5-3.1-3.3-4.9 1.1-1.4 3.6-1.5 3.6-2.6 1.5-1.7-3.2-2.9-2.7-4.2 1.9-1 3.1-1.5 3.7-2.1 2.5-1.3-2.3-3.5-2-4.2.6l-.7 2.3-1.1-2.3c-1.3-2.6-3.1-2.4-3.7.4-.2 1-.7 3-1.2 4.3-.8 2-1.2 2.1-2.5 1s-1.7-1-2.5.4c-1.3 2.3-2.5 2.1-3.2-.3-.8-3-2.4-2.4-3.8 1.5l-1.3 3.5-1.3-2.5c-1.8-3.2-3.1-3.2-4.4.2-1.2 2.9-1.3 2.8-2.3-2-.9-3.8-2.1-3.4-4 1.4-1.5 3.8-1.8 4-3 2.4-1.6-2.2-3.6-1.2-3.6 1.8 0 2.2-.1 2.2-2-.3l-2-2.5-2.2 2.9-2.2 3-.6-2.5c-.8-3.4-3.3-3-4.4.8-.8 3-1 3.1-2.2 1.5-1.3-1.6-1.5-1.5-3.2.7-1.9 2.3-1.9 2.3-2.5.2-.7-2.9-2.5-2.7-3.9.5-1.1 2.4-1.3 2.5-2.6.7-1.2-1.7-1.4-1.7-3.2.6l-1.8 2.5-.4-2.4c-.5-3.9-2.6-3.5-3.4.7l-.7 3.8-1.4-3.3c-.7-1.7-1.7-3.2-2.1-3.2-.5 0-1.6 1.7-2.6 3.7l-1.8 3.8-1.5-3-1.6-2.9-1.8 2.2c-.9 1.2-2 2.8-2.2 3.5-.3.8-1 .6-2.1-.8-1.6-2-1.6-2-3.5.4l-1.9 2.4-.6-2.1c-1-3-2.3-2.7-4.5 1l-2 3.1-.8-3.3c-1.2-4.5-2.8-4.8-4.2-.9-.9 2.7-1.3 3-2.6 1.9-1.3-1-1.9-1-3.4.4-1.7 1.5-1.8 1.5-2.9-.9-1.5-3.2-2.8-3.2-3.9.2l-1 2.8-1.2-2.3c-1.5-2.7-2.8-2.8-3.6-.2-.9 2.7-1.9 2.5-3.3-.5-1.5-3.2-2.3-3.1-4.4.2l-1.6 2.7-1.3-2.7c-1.5-3.3-2.2-3.4-4.7-.2-2.5 3.1-2.8 3.1-3.6 0-.4-1.4-1.3-2.5-2.1-2.5s-1.7 1.1-2.1 2.5c-.7 2.6-3.4 3.5-3.4 1.1 0-3.1-2.1-2.6-3.7.9l-1.7 3.5-1.2-3c-1.6-3.7-2.7-3.8-4.4-.2l-1.3 2.7-.9-3.3c-1-3.7-2.3-4.1-3.8-1.1-1.1 2-1.2 2-2.6.2-1.4-1.7-1.5-1.7-3.1.5l-1.6 2.3-1.3-3c-1.5-3.6-3-4-3.9-1.1-.6 2-.7 2-2.6-.2l-1.9-2.3-1.8 2.3-1.8 2.3-1.3-2.8-1.3-2.8-2.2 2.8-2.2 2.8-1.2-2.6c-1.5-3.2-2.6-3.2-4.1.2l-1.3 2.8-1.3-3.3c-1.6-3.7-3-4-4.7-1.2-1.2 1.8-1.3 1.8-3.1.3-1.5-1.4-2.2-1.5-3.8-.5-1.5 1-1.9 1-1.9-.1 0-.7-.7-2.2-1.5-3.3-1.4-1.9-1.5-1.8-3.4.5l-1.9 2.5-1.6-3.8c-1.6-3.8-1.7-3.8-3.6-2.1-1.8 1.7-1.9 1.6-2.6-.8-1-4.4-3-5.2-5-2.2l-1.7 2.7-1.1-3.3c-1.3-4-3.2-4.8-4.1-1.8l-.7 2.2-1.9-2.4c-1.8-2.3-2-2.3-3-.5-1.1 1.7-1.3 1.7-3.4-1l-2.2-2.8-1.3 2.3c-1.2 2.3-1.2 2.3-3.1-1.8-2-4.5-3.4-5.2-4.4-2.1-.9 2.9-2.1 2.5-3.1-1.2-1.2-4.1-3.1-4.7-4-1.1l-.7 2.7-1.8-4.2c-2.1-4.9-3.3-5.3-4.7-1.5l-1 2.8-1.2-3.3c-1.4-3.7-3.3-4.1-4.7-1-1.2 2.5-1.2 2.3-3.8-5-.7-1.9-2.1-1.4-3.2 1.1l-1.1 2.2-1.2-2.8c-1.4-3-3.5-3.5-4.4-1.2-.9 2.4-2.3 1.8-3.5-1.5-1.2-3.5-2.7-3.9-3.6-.9-.7 2.1-.8 2-2.7-1-2.2-3.4-2.8-3.7-4.5-2-.9.9-1.5 0-2.4-3.5-1.1-4.3-3.9-6.6-3.9-3.1 0 .8-.4 1.5-1 1.5-.5 0-1-.4-1-.9s-.6-1.6-1.4-2.4c-.8-.7-1.6-3.6-1.8-6.3-.4-5.2-7.4-20.4-9.3-20.4-.5 0-2.3 2.3-4 5l-3 4.9-9.2-8c-7.1-6.2-9.3-8.7-9.3-10.6 0-1.4-.8-3.7-1.9-5.1-1-1.5-2.8-4.6-4-7-1.2-2.3-2.5-4.2-3-4.2s-1.9 1.7-3.1 3.7c-1.7 2.9-2.3 3.4-3.1 2.2-.7-1.2-1-1.2-1.8-.1-.6 1-1.1.6-1.9-1.7-1.1-3.1-2.7-4.1-3.4-2-.2.7-1-.7-1.7-3-.8-2.8-1.7-4.1-2.5-3.8-.8.3-2-.9-3-3-1-2-2.2-3.3-2.9-3-.8.3-2.5-2.1-4.6-6.4-1.8-3.8-3.9-6.9-4.6-6.9-.8 0-1.6-1.4-2-3-.4-1.7-1.5-3.9-2.4-4.8-1-.9-2.1-3.1-2.5-4.7-.4-1.7-2-5-3.5-7.5-3.5-5.9-8.6-18.9-10.5-27-4.7-20.5-4.8-21.7-4.4-36.7.3-12.3.7-15.3 2.2-17.6l1.8-2.7h-11l-.6 4.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="hoverShapeWinter">
						<path d="M24.6 223.7c-.7 5.5-.1 33.7.9 43.3.4 3.6 1.2 7.1 1.7 7.7.6.7.8 1.9.5 2.7-.3.7.1 2.3 1 3.5.9 1.4 1.2 3.2.8 4.8-.4 1.9 0 3.1 1.5 4.5 1.6 1.5 2 2.7 1.5 4.8-.5 2.2 0 3.9 1.9 6.6 1.6 2.5 2.2 4.2 1.6 4.9-1.1 1.3 2.3 7.5 4.1 7.5.8 0 1 1.3.6 4-.4 3.5-.2 4.3 2.1 6.1 2.2 1.7 2.4 2.2 1.2 2.9-.8.6-1 1-.4 1 1.7 0 2.4 3.7 1.3 7.3-.6 2.2-.5 4.1.3 6.1.9 2.3.9 3.1 0 3.4-2.1.7-1.3 2.7 2.7 6.8 2.5 2.4 4.3 5.6 5.1 8.4 1.4 5.1 4.3 8.5 6.7 7.6 1.2-.5 1.4-.1 1 1.6-.7 2.5 1.7 5.8 4.3 5.8.9 0 2.6-.9 3.8-2l2.2-2 .6 2.5c.3 1.4 1.9 3.4 3.5 4.6 1.6 1.1 2.9 2.6 2.9 3.3 0 .7 1.1 1.9 2.5 2.6 1.8 1 2.5 2.2 2.5 4.3 0 2.5-.2 2.8-1.5 1.7-2.2-1.8-1.8.3.6 3.3 1.1 1.5 3.7 3.2 5.9 3.8 3.3 1 4.3.9 7.2-.8l3.3-1.9.3-9.7.3-9.8 3.2 2.3c1.8 1.2 5.3 4 7.9 6.3l4.8 4v15.7c0 11.6-.3 15.7-1.2 16.1-.8.2-.1 1.6 2.1 3.5 2.4 2.4 4.3 3.2 6.8 3.2 4.9 0 11.4 2 13.9 4.2 1.1 1 2.7 1.8 3.6 1.8.9 0 1.8.6 2.1 1.4.3.7 1.9 1.6 3.7 1.9 1.7.4 3.8 1.4 4.6 2.2.9.8 2.6 1.5 3.8 1.5 1.5 0 2.7.9 3.4 2.5.9 1.9 1.9 2.5 4.2 2.5 1.6 0 3 .5 3 1 0 1.7 5 3.2 6.5 2 .9-.8 1.5-.8 2 0 .3.5 1.5 1 2.6 1 1 0 1.9.6 1.9 1.4 0 1.9 2.4 3.7 5.2 3.9 1.3.2 3.2.7 4.3 1.3 1.1.6 4.5 1.7 7.5 2.4s7.3 2.3 9.6 3.5c2.3 1.3 6.9 2.6 10.5 3 3.5.3 9.1 1.5 12.3 2.6 4 1.4 6.1 1.7 6.6.9.4-.7 1.8-.6 4.6.3 2.1.8 7.3 1.7 11.5 2 4.1.3 8.9 1.2 10.5 1.9 1.7.7 7.3 1.2 13.6 1.3 10.2 0 10.8.1 13.5 2.7 4.2 4 11.2 7.3 12.5 6 .8-.8 1.4-.8 2.2.2.6.7 3.5 1.8 6.5 2.5 5 1.1 5.6 1 7.6-.9 2.1-2 2.5-2 5.7-.7 2.6 1.1 4 1.2 5.4.4 1-.6 4.6-1.2 7.9-1.2 6.7-.2 12.4-2.6 13.2-5.6.3-1 .6-1.9.7-2 0 0 4.8-.6 10.6-1.4 15.3-1.9 25.2-3.6 27.7-4.8 1.2-.5 3.8-.7 5.9-.4 3.9.7 15.5-1.5 21.7-4.1 3.1-1.3 4.3-1.3 5.8-.4 2.8 1.8 15.9-1.3 16.6-3.8.4-1.3 1.5-1.8 4.5-1.8 2.2-.1 5-.8 6.3-1.6 1.4-.9 3.1-1.6 3.8-1.6.8 0 2.6-.9 4-2s3.2-1.8 4-1.4c.8.3 1.7-.2 2-1 .4-.9 1.4-1.2 2.6-.9 1.3.3 2.3-.2 3.1-1.7.9-1.6 1.5-1.9 2.5-1 .7.6 4.2 1 7.7 1 3.4-.1 7.6-.1 9.2 0 1.6 0 3.7-.7 4.7-1.6 1.6-1.5 2.1-1.5 4.1-.2 3.3 2 8.2.9 8.6-2 .3-1.9.9-2.2 4.8-2.2h4.4v-6.4c0-6.1.1-6.4 2.6-7l2.7-.7-.7-15.9-.7-15.8 7.8-6.5 7.8-6.5 1.1 7.3c.5 4 1.6 8 2.3 8.9 2.7 3.3 11.7 1.1 14.3-3.4 1-1.8 3.1-3.2 5.9-4.1 3.6-1.2 4.5-1.9 4.7-4.2.2-1.8.9-2.7 2.2-2.7 1 0 2-.7 2.4-1.5.3-.8 1.4-1.5 2.4-1.5 2.7 0 4.3-2.3 5.3-7.3.6-3.5.4-4.6-1.2-6.3-1.9-2-1.9-2.1.1-3.9 1.1-1 2-2.2 2-2.8 0-.5 1.1-1.8 2.5-2.9 1.4-1.1 2.5-2.6 2.5-3.4 0-.8.9-2.2 2-3.1 1.1-1 2-2.5 2-3.3 0-.9 1.1-2.7 2.5-4 1.5-1.4 2.5-3.5 2.5-5.2 0-1.8.5-2.8 1.5-2.8.9 0 1.5-.9 1.5-2.5 0-1.4.6-2.8 1.4-3.1.8-.3 1.7-2.5 2.1-5 .4-2.9 1.3-4.8 2.6-5.4 1-.6 1.9-2.1 1.9-3.3 0-1.2.7-3.1 1.5-4.1.8-1.1 1.5-4.1 1.5-6.7 0-2.6.5-5.1 1-5.4 2.5-1.5.6-2.5-4.7-2.5l-5.8.1-3.8 7.9c-2.1 4.4-4.1 9.5-4.4 11.4-.4 2.4-1 3.3-1.9 2.9-.9-.3-1.4.2-1.4 1.6 0 1.2-.9 3-2 4.1-1.1 1.1-2 3.3-2 4.9 0 3.1-1.5 5-2.2 2.9-.3-.7-1.3 1.1-2.3 4.1-1.4 4.3-1.9 5.1-2.7 3.7-1.4-2.4-2.5-1.2-3.3 3.6-.6 3.6-.9 4-2 2.5-1.7-2.4-2.2-2.2-3.5 1.9-.8 2.4-1.5 3.2-2 2.4-1.1-1.8-2.8-.4-3.5 3.1-.5 2.3-1 2.8-1.8 2-1.8-1.8-3.4-1.3-4.1 1.4-.9 3.5-1.7 3.2-4.9-2-1.6-2.5-3.2-4.5-3.5-4.5-.4 0-2.6 3.5-5 7.6-2.9 5.1-4.1 8.2-3.7 9.5.8 2.4-.1 3.6-8.9 11.1-7.8 6.8-8.4 6.7-12.6-2.2-1.3-2.8-2.7-5-3.2-5-.4 0-2.4 3.1-4.3 7-3.1 6.2-3.5 7.8-3.5 15.2-.1 6.1-.3 7.8-1.1 6.5-1.6-2.7-2.7-2-4 2.4-1.1 3.6-1.4 3.8-2.1 2-1-2.9-2.7-2.6-3.9.6-2 5.7-2.1 5.9-2.6 4.5-.8-2.1-2.3-1.3-3.7 1.9l-1.3 3-1.7-2.5c-2.2-3.5-3.1-3.3-4.9 1.1-1.4 3.6-1.5 3.6-2.6 1.5-1.7-3.2-2.9-2.7-4.2 1.9-1 3.1-1.5 3.7-2.1 2.5-1.3-2.3-3.5-2-4.2.6l-.7 2.3-1.1-2.3c-1.3-2.6-3.1-2.4-3.7.4-.2 1-.7 3-1.2 4.3-.8 2-1.2 2.1-2.5 1s-1.7-1-2.5.4c-1.3 2.3-2.5 2.1-3.2-.3-.8-3-2.4-2.4-3.8 1.5l-1.3 3.5-1.3-2.5c-1.8-3.2-3.1-3.2-4.4.2-1.2 2.9-1.3 2.8-2.3-2-.9-3.8-2.1-3.4-4 1.4-1.5 3.8-1.8 4-3 2.4-1.6-2.2-3.6-1.2-3.6 1.8 0 2.2-.1 2.2-2-.3l-2-2.5-2.2 2.9-2.2 3-.6-2.5c-.8-3.4-3.3-3-4.4.8-.8 3-1 3.1-2.2 1.5-1.3-1.6-1.5-1.5-3.2.7-1.9 2.3-1.9 2.3-2.5.2-.7-2.9-2.5-2.7-3.9.5-1.1 2.4-1.3 2.5-2.6.7-1.2-1.7-1.4-1.7-3.2.6l-1.8 2.5-.4-2.4c-.5-3.9-2.6-3.5-3.4.7l-.7 3.8-1.4-3.3c-.7-1.7-1.7-3.2-2.1-3.2-.5 0-1.6 1.7-2.6 3.7l-1.8 3.8-1.5-3-1.6-2.9-1.8 2.2c-.9 1.2-2 2.8-2.2 3.5-.3.8-1 .6-2.1-.8-1.6-2-1.6-2-3.5.4l-1.9 2.4-.6-2.1c-1-3-2.3-2.7-4.5 1l-2 3.1-.8-3.3c-1.2-4.5-2.8-4.8-4.2-.9-.9 2.7-1.3 3-2.6 1.9-1.3-1-1.9-1-3.4.4-1.7 1.5-1.8 1.5-2.9-.9-1.5-3.2-2.8-3.2-3.9.2l-1 2.8-1.2-2.3c-1.5-2.7-2.8-2.8-3.6-.2-.9 2.7-1.9 2.5-3.3-.5-1.5-3.2-2.3-3.1-4.4.2l-1.6 2.7-1.3-2.7c-1.5-3.3-2.2-3.4-4.7-.2-2.5 3.1-2.8 3.1-3.6 0-.4-1.4-1.3-2.5-2.1-2.5s-1.7 1.1-2.1 2.5c-.7 2.6-3.4 3.5-3.4 1.1 0-3.1-2.1-2.6-3.7.9l-1.7 3.5-1.2-3c-1.6-3.7-2.7-3.8-4.4-.2l-1.3 2.7-.9-3.3c-1-3.7-2.3-4.1-3.8-1.1-1.1 2-1.2 2-2.6.2-1.4-1.7-1.5-1.7-3.1.5l-1.6 2.3-1.3-3c-1.5-3.6-3-4-3.9-1.1-.6 2-.7 2-2.6-.2l-1.9-2.3-1.8 2.3-1.8 2.3-1.3-2.8-1.3-2.8-2.2 2.8-2.2 2.8-1.2-2.6c-1.5-3.2-2.6-3.2-4.1.2l-1.3 2.8-1.3-3.3c-1.6-3.7-3-4-4.7-1.2-1.2 1.8-1.3 1.8-3.1.3-1.5-1.4-2.2-1.5-3.8-.5-1.5 1-1.9 1-1.9-.1 0-.7-.7-2.2-1.5-3.3-1.4-1.9-1.5-1.8-3.4.5l-1.9 2.5-1.6-3.8c-1.6-3.8-1.7-3.8-3.6-2.1-1.8 1.7-1.9 1.6-2.6-.8-1-4.4-3-5.2-5-2.2l-1.7 2.7-1.1-3.3c-1.3-4-3.2-4.8-4.1-1.8l-.7 2.2-1.9-2.4c-1.8-2.3-2-2.3-3-.5-1.1 1.7-1.3 1.7-3.4-1l-2.2-2.8-1.3 2.3c-1.2 2.3-1.2 2.3-3.1-1.8-2-4.5-3.4-5.2-4.4-2.1-.9 2.9-2.1 2.5-3.1-1.2-1.2-4.1-3.1-4.7-4-1.1l-.7 2.7-1.8-4.2c-2.1-4.9-3.3-5.3-4.7-1.5l-1 2.8-1.2-3.3c-1.4-3.7-3.3-4.1-4.7-1-1.2 2.5-1.2 2.3-3.8-5-.7-1.9-2.1-1.4-3.2 1.1l-1.1 2.2-1.2-2.8c-1.4-3-3.5-3.5-4.4-1.2-.9 2.4-2.3 1.8-3.5-1.5-1.2-3.5-2.7-3.9-3.6-.9-.7 2.1-.8 2-2.7-1-2.2-3.4-2.8-3.7-4.5-2-.9.9-1.5 0-2.4-3.5-1.1-4.3-3.9-6.6-3.9-3.1 0 .8-.4 1.5-1 1.5-.5 0-1-.4-1-.9s-.6-1.6-1.4-2.4c-.8-.7-1.6-3.6-1.8-6.3-.4-5.2-7.4-20.4-9.3-20.4-.5 0-2.3 2.3-4 5l-3 4.9-9.2-8c-7.1-6.2-9.3-8.7-9.3-10.6 0-1.4-.8-3.7-1.9-5.1-1-1.5-2.8-4.6-4-7-1.2-2.3-2.5-4.2-3-4.2s-1.9 1.7-3.1 3.7c-1.7 2.9-2.3 3.4-3.1 2.2-.7-1.2-1-1.2-1.8-.1-.6 1-1.1.6-1.9-1.7-1.1-3.1-2.7-4.1-3.4-2-.2.7-1-.7-1.7-3-.8-2.8-1.7-4.1-2.5-3.8-.8.3-2-.9-3-3-1-2-2.2-3.3-2.9-3-.8.3-2.5-2.1-4.6-6.4-1.8-3.8-3.9-6.9-4.6-6.9-.8 0-1.6-1.4-2-3-.4-1.7-1.5-3.9-2.4-4.8-1-.9-2.1-3.1-2.5-4.7-.4-1.7-2-5-3.5-7.5-3.5-5.9-8.6-18.9-10.5-27-4.7-20.5-4.8-21.7-4.4-36.7.3-12.3.7-15.3 2.2-17.6l1.8-2.7h-11l-.6 4.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						</svg>';
				break;
				case '33Top': // 
					$sVG = '<svg class="buildingShape 33Top" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="675" height="534" viewBox="0 0 675 534" >
						<g class="clickShape">
						<path d="M314.1 26c-1.1 1.9-2.1 3.9-2.1 4.5-.1.5-.7-.5-1.5-2.3-1.7-3.9-3-4.1-4-.5l-.7 2.8-1-2.8c-1.2-3.3-2.3-3.5-3.2-.5l-.7 2.3-1.1-2.3c-.6-1.2-1.7-2.2-2.3-2.2-.7 0-1.6 1-1.9 2.2l-.7 2.3-.9-2.3c-1.2-2.7-2.7-2.8-3.5-.2-.9 2.7-2.2 2.5-2.9-.5-.8-3.3-2.2-3.2-4 .2l-1.4 2.8-.7-2.8c-.4-1.5-1.3-2.7-2-2.7s-1.8 1.2-2.3 2.7l-.9 2.8-1.3-2.8c-.7-1.5-1.7-2.7-2.1-2.7-.4 0-1.2 1.2-1.7 2.7-.9 2.7-.9 2.8-1.6.5-.4-1.2-1.2-2.2-1.7-2.2s-1.4 1-1.8 2.2c-.9 2.3-.9 2.3-1.6-.5-.9-3.4-2.1-3.5-2.9-.1-.6 2.4-.8 2.5-2.5 1-2.5-2.2-2.8-2.1-3.7 1.6l-.7 3.3-1.5-2.3c-1.9-2.9-2.6-2.8-4.2.5-1.2 2.8-1.2 2.8-2.1.5-1-2.7-2.9-2.9-3.6-.2-.7 2.5-1.1 2.5-3.3-.3l-1.8-2.2-1.1 2.8c-.6 1.6-1.1 3.3-1.1 3.9 0 .6-.6.6-1.5-.2-1.2-1-1.8-1-3.1.1s-1.7 1-2.5-.4c-1.4-2.5-2.8-2.1-3.5 1-.7 2.5-.8 2.5-2.1.7-1.4-1.8-1.5-1.8-2.8-.1-1.3 1.8-1.4 1.7-2-.2-.9-3.1-2.2-2.6-3.5 1.5-1.2 3.5-1.2 3.5-2.7 1.5-1.9-2.8-3.1-2.6-3.9.4-.7 2.7-1.4 3.1-3.1 1.4-.8-.8-1.5-.5-2.5 1-1.5 2.1-1.5 2.1-2.6-.4-1.6-3.4-2.9-3.1-3.7.7-.7 2.9-.9 3-2.1 1.4-1.1-1.6-1.4-1.5-3.2 2-1.8 3.6-2 3.6-2.7 1.5-.9-3-2.6-2.2-4 2-1.1 3.2-1.2 3.2-1.9 1.1-1-3-2.4-2.8-3.7.5l-1.1 2.8-1.4-2.8c-1.6-3-3.4-2.8-3.4.4 0 1-.4 1.9-1 1.9-.5 0-1-.5-1-1.1 0-.6-.6-.9-1.4-.6-.9.4-2.4-1.4-4.5-5.3-1.8-3.3-3.5-6.2-4-6.4-.4-.3-2.7 2.5-5.1 6.2-3.3 5.1-4.3 7.7-4.4 11.2-.1 4.4-.1 4.4-7.6 9l-7.5 4.6-3.4-4.6c-3.4-4.3-5-6-6.1-6-1.5 0-7 11.5-7 14.5 0 1.9-.4 3.6-.9 3.9-.5.3-1.2 2.3-1.6 4.3-.6 3.4-.8 3.6-2 2-1.8-2.5-3.1-2.1-4.5 1.3-1 2.4-1.5 2.8-2.6 1.9-1.1-.9-1.7-.4-2.9 2.6-1.4 3.1-1.8 3.5-3.1 2.4-1.3-1-1.7-1-2.5.2-.5.8-.9 2.2-.9 3.3 0 1.5-.3 1.6-1.5.6s-1.8-.5-3.4 3.1c-1.1 2.4-2 3.6-2 2.7-.2-3.4-2.2-2.8-4 1.2-1.6 3.6-2 4-2.9 2.5-.5-1-1.4-1.5-2-1.1-1.5.9-3.3 7.6-2.5 9 .4.6 0 .5-.9-.1-1.6-1.4-3.2-.2-5.6 4.2-.6 1.1-1.2 1.4-1.2.7 0-3.1-1.9-.7-3 3.8-1 4.2-1.5 4.9-2.6 3.9-1.1-.9-1.6-.6-2.6 1.7-.7 1.6-1.9 2.9-2.7 2.9-.8 0-1.9 1.5-2.5 3.3-.8 2.4-1.4 3-2.3 2.1-.8-.8-1.4-.1-2.3 2.8-.7 2.1-1.8 4.5-2.4 5.3-.6.8-2.9 5.1-5 9.5-2.2 4.4-4.6 9.1-5.3 10.5-3.2 6-7.3 15.4-7.3 16.9 0 1-.7 2.6-1.5 3.7-.8 1-1.5 3-1.5 4.4 0 1.4-.7 4.6-1.5 7.2-.8 2.7-1.1 4.8-.8 4.8.3 0-.3 1.2-1.2 2.7-1 1.5-1.9 4.3-2.1 6.3-.2 1.9-.6 4.5-1 5.7-.6 2.2-.3 2.3 4 2.3 4.1 0 4.6-.2 4.6-2.3 0-1.2.7-3.1 1.5-4.1.8-1.1 1.5-3.2 1.5-4.8 0-1.8.5-2.8 1.5-2.8 1.1 0 1.5-1.2 1.5-4.4 0-2.6.6-4.8 1.5-5.6.8-.7 1.5-2.7 1.5-4.5 0-2.4.5-3.4 2-3.8 1.5-.4 2-1.4 2-4s.5-3.6 2-4c1.4-.3 2-1.4 2-3.1 0-1.9.5-2.6 2-2.6s2.1-.9 2.6-4c.4-2.4 1.2-4 2-4 .7 0 2-1.3 2.9-2.9.8-1.6 2.4-3.2 3.4-3.6 1.1-.3 2.1-1.6 2.3-2.8.2-1.4 1.1-2.3 2.5-2.5 1.5-.2 2.3-1 2.3-2.4 0-1.1.8-2.2 1.9-2.5 1-.3 2.3-1.9 2.8-3.6.6-2.2 1.6-3.3 3.1-3.5 1.2-.2 2.9-1.6 3.7-3.3.9-1.6 2.2-2.9 3.1-2.9.8 0 1.7-1.3 2-2.9.5-2.3 1.2-3.1 3.3-3.3 1.8-.2 2.7-1 2.9-2.5.2-1.5 1.3-2.4 3.3-2.8 2-.5 2.9-1.3 2.9-2.6s.7-1.9 2.3-1.9c1.3 0 3.2-.9 4.1-1.9 1-1.1 4-2.5 6.7-3.2 3.7-1 5.1-1.9 5.5-3.5.8-3.1 4.2-4.4 7.9-2.8 4.5 2 6.6 1.7 9.7-1.2 1.9-2 2.5-3.1 1.9-4.3-.5-.9-1.1-6.7-1.2-13l-.4-11.5 4.5-2.7c2.5-1.5 5.2-2.8 6.1-2.8.9-.1 1.9-.8 2.3-1.6.3-.8 1-1.5 1.6-1.5 1.4 0 1.3 11.6-.1 14.2-1 1.9-.7 2.5 2.2 4.9 3.4 2.8 7 3.5 11.6 2.3 1.5-.4 2.4-1.8 3-4.2.7-2.8 1.4-3.8 3.2-4 1.4-.2 5.8-2.1 9.9-4.3 10.3-5.4 18.8-8.3 39.2-13.5 9.6-2.5 19.6-5.2 22.2-6 4.8-1.6 19.3-1.5 26.5.1 2.4.5 3.7.3 4.8-.9 1.1-1 3.4-1.6 6.3-1.6 2.6 0 6.5-.5 8.7-1.1 2.9-.8 4-.8 4.3 0 .5 1.6 3.8 1.3 5.3-.5 1.1-1.3 2.6-1.5 7.6-1.1 3.6.4 6.5.2 6.8-.3 1-1.6 13.5-1.1 26.2.9 6.5 1.1 16.3 2.2 21.7 2.6 6.8.4 11.2 1.3 13.7 2.6 2.5 1.2 5.9 1.9 9.8 1.9 5.1 0 6.1.3 6.6 2 .7 2.3 4.3 2.7 5.1.5.5-1.2 1.2-1.1 4.5.5 2.2 1.2 5.7 2 8.1 2 8.8-.2 15.5.7 21.6 2.8 3.5 1.2 8 2.4 10 2.8 2 .4 7.1 2.3 11.2 4.2 4.1 2 8.2 3.8 9 4.1 4.4 1.7 10 5.7 10 7.2 0 5.2 11 7.9 15.1 3.8 1.9-1.9 2.1-3 1.7-10.5-.4-8.5-.1-9.6 2.3-6.8.7.8 3.6 2.7 6.3 4.1 2.8 1.4 5.3 2.7 5.6 3 .3.3.7 7.4 1 16 .5 14.7.7 15.6 2.9 17.4 1.3 1 3.7 1.9 5.5 1.9 1.7 0 4.6.9 6.4 2 1.8 1.1 4.2 2 5.5 2 2.5 0 8.8 3.5 10 5.6.4.8 2 1.4 3.6 1.4 1.9 0 3.4.9 4.9 3 1.2 1.6 2.2 3.3 2.2 3.9 0 .8 4 3.7 9.7 6.9 1.3.7 2.3 2.2 2.3 3.3 0 1.4 1 2.5 3 3.2 2.4.9 3 1.7 2.8 3.7-.3 2.1.3 2.7 3 3.5 2.6.7 3.2 1.4 3 3.4-.2 2 .4 2.9 3.1 4.2 2.1 1 3 1.9 2.2 2.3-1.4.9.4 4.6 2.2 4.6.7 0 2.2.6 3.2 1.4 1.6 1.2 1.7 1.6.6 3-1.2 1.4-1.1 1.9.3 3.4.9 1 1.6 3.3 1.6 5 0 3.3 5.5 12 7.6 12.1.7.1 1.4 1.1 1.7 2.2.4 1.4 1.1 2 2.1 1.6 1.2-.5 1.3-.2.6 1.8-.8 2-.4 2.6 2.4 4.6 2.9 2 3.3 2.6 2.6 5-.6 2.1-.4 3.2 1.2 4.6 1.4 1.2 1.8 2.6 1.4 4.4-.3 1.5.1 3.5 1 4.8.9 1.2 1.3 3 .9 4.2-.4 1.1-.2 2.5.3 3.2 1 1.2 1.9 5.2 2.7 11.7.2 1.9-.1 6.6-.6 10.5-.5 3.8-1.4 10.8-1.9 15.5-1 8.5-4.8 22.1-7.6 27.2l-1.5 2.8h12.8l.7-4.8c.3-2.6 1-7.2 1.6-10.2.6-3 1-8.2 1-11.4-.1-3.3.3-6.2 1-6.6.6-.4 1.3-6.4 1.6-15.1.5-11.1.3-16.3-1-22.8-.9-4.6-1.6-10.1-1.6-12.3 0-2.2-.6-6.1-1.4-8.7-1.3-3.9-5-16.6-7.1-23.8-.5-1.9-.1-2.3 3-2.9 4.1-.7 5.5-2.8 5.5-7.8 0-2 .4-3.6.9-3.6s1.7-2 2.7-4.5c1.3-3.5 1.4-4.9.6-5.7-.7-.7-1.2-2.9-1.2-5 0-2.7-.5-4.1-1.7-4.5-3.1-1.2-4.8-3.9-4.9-7.7-.2-4.3-.8-5.1-4.6-6-2.2-.5-2.8-.3-2.8 1 0 .9-.9 2.7-1.9 4l-2 2.5-1.3-2.8c-1.8-3.7-6.3-4.4-7.1-1.1-.5 2-1.4 2.3-5.6 2.6-4.8.2-5.1.1-5.7-2.5-.3-1.5-.9-3.9-1.4-5.3l-.8-2.5-1.2 3.2c-1.4 3.8-3 3.5-3-.5 0-4.4-1.1-7.4-2.6-6.8-.8.3-2.1-.7-3.1-2.4-1.1-2-2-2.6-2.8-2-1.5 1.2-2.5.3-2.5-2.3 0-3.6-2.3-8.8-3.7-8.3-.7.3-1.3-.1-1.3-1 0-.8-1-2.5-2.3-3.7-1.3-1.2-2.7-3-3-4-.5-1.3-1.3-1.6-2.7-1.2-1.3.4-2 .2-2-.7 0-.8-.7-2.3-1.5-3.4-1.4-1.8-1.6-1.8-2.9-.2-1.2 1.6-1.4 1.4-2.9-2.2-1.7-4.3-3.1-5-4.2-2.3-.5 1.4-1.3.5-3.2-3.7l-2.4-5.5-1.8 2.3-1.7 2.3-1.2-2.6c-.7-1.4-1.2-4.6-1.2-7.1 0-3-.8-5.7-2.5-8.4-1.4-2.2-2.5-4.3-2.5-4.7 0-.5-.9-1.9-1.9-3.3l-1.9-2.4-3.8 7.7-3.8 7.7-3.4-2.1c-1.9-1.1-5.4-3.7-7.8-5.7-3.4-2.9-4.3-4.4-4.4-6.9 0-1.7-.4-3.4-.9-3.7-.5-.4-2.6-3.5-4.6-7.1-2-3.6-4-6.5-4.5-6.5-.4 0-1.9 2.2-3.1 5-1.2 2.7-2.7 5-3.2 5s-1.6.8-2.3 1.7c-1.1 1.6-1.3 1.6-1.9-1-.7-2.7-3.5-3.9-3.5-1.5 0 .7-.9.2-2-1.2-1.7-2.2-1.9-2.3-2.5-.7-1 2.4-2.4 2.1-3.1-.8-.7-2.8-2.2-3.3-3-1-.8 1.9-2.4 2-2.4.1 0-2.1-1.8-3-3.4-1.7-1 .8-1.6.4-2.5-1.6l-1.3-2.7L441 45l-1.8 2.4-1.3-2.7c-1.4-3-2.7-3.5-3.5-1.2-.9 2.2-1.9 1.8-3.3-1.2-1.2-2.6-1.4-2.6-2.5-1-1.6 2.1-2.6 2.2-2.6.3 0-2.1-2.1-3.1-3.1-1.5-.7 1.1-1 .9-1.5-.9-.4-1.2-1-2.2-1.4-2.2-.4 0-1.4 1-2.3 2.2l-1.5 2.3-.7-2.8c-.9-3.2-2.3-3.5-3.3-.6-.8 2-.8 2-2-.5C409.1 35 407 34 407 36c0 .5-.4 1-1 1-.5 0-1-.7-1-1.5 0-1.8-3-2-3.1-.3 0 .7-.5.3-1.1-1-1.2-2.4-2.3-2.8-3.3-1.1-.5.7-1.4.4-2.7-.8-1.9-1.7-2-1.7-3.3 0-1.5 2.1-2.5 2.2-2.5.3 0-2.4-2.2-3.1-3.6-1.2-1.1 1.6-1.3 1.6-1.9-.3-.8-2.6-2.1-2.7-3.4-.4-.9 1.7-1 1.7-1.6 0-.9-2.1-2.8-2.2-4.6-.1-1.1 1.4-1.5 1.3-3.1-.8l-1.8-2.3-1.9 2.2-1.9 2.2L364 29c-1.5-3.7-2.6-3.8-3.5-.3l-.7 2.8-1.5-2.9-1.5-2.9L355 28l-1.8 2.3L352 28l-1.3-2.4-1.8 2.3-1.8 2.2-.7-2.5c-.8-3.2-1.1-3.2-3.5-.4-1.6 2-1.8 2-1.9.5 0-2.4-2.7-2.1-3.6.5-.6 2.1-.7 2.1-2.1-.5-1.6-3.2-2.1-3.3-3.9-1-1.1 1.7-1.3 1.6-2.3-1-.6-1.5-1.3-2.7-1.6-2.7-.3 0-1.1 1.2-1.7 2.7-1 2.5-1.2 2.6-1.6.8-.7-3.3-2.2-4.2-2.8-1.7-.8 2.8-1.8 2.8-3.6 0l-1.5-2.3-2.2 3.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="hoverShape">
						<path d="M314.1 26c-1.1 1.9-2.1 3.9-2.1 4.5-.1.5-.7-.5-1.5-2.3-1.7-3.9-3-4.1-4-.5l-.7 2.8-1-2.8c-1.2-3.3-2.3-3.5-3.2-.5l-.7 2.3-1.1-2.3c-.6-1.2-1.7-2.2-2.3-2.2-.7 0-1.6 1-1.9 2.2l-.7 2.3-.9-2.3c-1.2-2.7-2.7-2.8-3.5-.2-.9 2.7-2.2 2.5-2.9-.5-.8-3.3-2.2-3.2-4 .2l-1.4 2.8-.7-2.8c-.4-1.5-1.3-2.7-2-2.7s-1.8 1.2-2.3 2.7l-.9 2.8-1.3-2.8c-.7-1.5-1.7-2.7-2.1-2.7-.4 0-1.2 1.2-1.7 2.7-.9 2.7-.9 2.8-1.6.5-.4-1.2-1.2-2.2-1.7-2.2s-1.4 1-1.8 2.2c-.9 2.3-.9 2.3-1.6-.5-.9-3.4-2.1-3.5-2.9-.1-.6 2.4-.8 2.5-2.5 1-2.5-2.2-2.8-2.1-3.7 1.6l-.7 3.3-1.5-2.3c-1.9-2.9-2.6-2.8-4.2.5-1.2 2.8-1.2 2.8-2.1.5-1-2.7-2.9-2.9-3.6-.2-.7 2.5-1.1 2.5-3.3-.3l-1.8-2.2-1.1 2.8c-.6 1.6-1.1 3.3-1.1 3.9 0 .6-.6.6-1.5-.2-1.2-1-1.8-1-3.1.1s-1.7 1-2.5-.4c-1.4-2.5-2.8-2.1-3.5 1-.7 2.5-.8 2.5-2.1.7-1.4-1.8-1.5-1.8-2.8-.1-1.3 1.8-1.4 1.7-2-.2-.9-3.1-2.2-2.6-3.5 1.5-1.2 3.5-1.2 3.5-2.7 1.5-1.9-2.8-3.1-2.6-3.9.4-.7 2.7-1.4 3.1-3.1 1.4-.8-.8-1.5-.5-2.5 1-1.5 2.1-1.5 2.1-2.6-.4-1.6-3.4-2.9-3.1-3.7.7-.7 2.9-.9 3-2.1 1.4-1.1-1.6-1.4-1.5-3.2 2-1.8 3.6-2 3.6-2.7 1.5-.9-3-2.6-2.2-4 2-1.1 3.2-1.2 3.2-1.9 1.1-1-3-2.4-2.8-3.7.5l-1.1 2.8-1.4-2.8c-1.6-3-3.4-2.8-3.4.4 0 1-.4 1.9-1 1.9-.5 0-1-.5-1-1.1 0-.6-.6-.9-1.4-.6-.9.4-2.4-1.4-4.5-5.3-1.8-3.3-3.5-6.2-4-6.4-.4-.3-2.7 2.5-5.1 6.2-3.3 5.1-4.3 7.7-4.4 11.2-.1 4.4-.1 4.4-7.6 9l-7.5 4.6-3.4-4.6c-3.4-4.3-5-6-6.1-6-1.5 0-7 11.5-7 14.5 0 1.9-.4 3.6-.9 3.9-.5.3-1.2 2.3-1.6 4.3-.6 3.4-.8 3.6-2 2-1.8-2.5-3.1-2.1-4.5 1.3-1 2.4-1.5 2.8-2.6 1.9-1.1-.9-1.7-.4-2.9 2.6-1.4 3.1-1.8 3.5-3.1 2.4-1.3-1-1.7-1-2.5.2-.5.8-.9 2.2-.9 3.3 0 1.5-.3 1.6-1.5.6s-1.8-.5-3.4 3.1c-1.1 2.4-2 3.6-2 2.7-.2-3.4-2.2-2.8-4 1.2-1.6 3.6-2 4-2.9 2.5-.5-1-1.4-1.5-2-1.1-1.5.9-3.3 7.6-2.5 9 .4.6 0 .5-.9-.1-1.6-1.4-3.2-.2-5.6 4.2-.6 1.1-1.2 1.4-1.2.7 0-3.1-1.9-.7-3 3.8-1 4.2-1.5 4.9-2.6 3.9-1.1-.9-1.6-.6-2.6 1.7-.7 1.6-1.9 2.9-2.7 2.9-.8 0-1.9 1.5-2.5 3.3-.8 2.4-1.4 3-2.3 2.1-.8-.8-1.4-.1-2.3 2.8-.7 2.1-1.8 4.5-2.4 5.3-.6.8-2.9 5.1-5 9.5-2.2 4.4-4.6 9.1-5.3 10.5-3.2 6-7.3 15.4-7.3 16.9 0 1-.7 2.6-1.5 3.7-.8 1-1.5 3-1.5 4.4 0 1.4-.7 4.6-1.5 7.2-.8 2.7-1.1 4.8-.8 4.8.3 0-.3 1.2-1.2 2.7-1 1.5-1.9 4.3-2.1 6.3-.2 1.9-.6 4.5-1 5.7-.6 2.2-.3 2.3 4 2.3 4.1 0 4.6-.2 4.6-2.3 0-1.2.7-3.1 1.5-4.1.8-1.1 1.5-3.2 1.5-4.8 0-1.8.5-2.8 1.5-2.8 1.1 0 1.5-1.2 1.5-4.4 0-2.6.6-4.8 1.5-5.6.8-.7 1.5-2.7 1.5-4.5 0-2.4.5-3.4 2-3.8 1.5-.4 2-1.4 2-4s.5-3.6 2-4c1.4-.3 2-1.4 2-3.1 0-1.9.5-2.6 2-2.6s2.1-.9 2.6-4c.4-2.4 1.2-4 2-4 .7 0 2-1.3 2.9-2.9.8-1.6 2.4-3.2 3.4-3.6 1.1-.3 2.1-1.6 2.3-2.8.2-1.4 1.1-2.3 2.5-2.5 1.5-.2 2.3-1 2.3-2.4 0-1.1.8-2.2 1.9-2.5 1-.3 2.3-1.9 2.8-3.6.6-2.2 1.6-3.3 3.1-3.5 1.2-.2 2.9-1.6 3.7-3.3.9-1.6 2.2-2.9 3.1-2.9.8 0 1.7-1.3 2-2.9.5-2.3 1.2-3.1 3.3-3.3 1.8-.2 2.7-1 2.9-2.5.2-1.5 1.3-2.4 3.3-2.8 2-.5 2.9-1.3 2.9-2.6s.7-1.9 2.3-1.9c1.3 0 3.2-.9 4.1-1.9 1-1.1 4-2.5 6.7-3.2 3.7-1 5.1-1.9 5.5-3.5.8-3.1 4.2-4.4 7.9-2.8 4.5 2 6.6 1.7 9.7-1.2 1.9-2 2.5-3.1 1.9-4.3-.5-.9-1.1-6.7-1.2-13l-.4-11.5 4.5-2.7c2.5-1.5 5.2-2.8 6.1-2.8.9-.1 1.9-.8 2.3-1.6.3-.8 1-1.5 1.6-1.5 1.4 0 1.3 11.6-.1 14.2-1 1.9-.7 2.5 2.2 4.9 3.4 2.8 7 3.5 11.6 2.3 1.5-.4 2.4-1.8 3-4.2.7-2.8 1.4-3.8 3.2-4 1.4-.2 5.8-2.1 9.9-4.3 10.3-5.4 18.8-8.3 39.2-13.5 9.6-2.5 19.6-5.2 22.2-6 4.8-1.6 19.3-1.5 26.5.1 2.4.5 3.7.3 4.8-.9 1.1-1 3.4-1.6 6.3-1.6 2.6 0 6.5-.5 8.7-1.1 2.9-.8 4-.8 4.3 0 .5 1.6 3.8 1.3 5.3-.5 1.1-1.3 2.6-1.5 7.6-1.1 3.6.4 6.5.2 6.8-.3 1-1.6 13.5-1.1 26.2.9 6.5 1.1 16.3 2.2 21.7 2.6 6.8.4 11.2 1.3 13.7 2.6 2.5 1.2 5.9 1.9 9.8 1.9 5.1 0 6.1.3 6.6 2 .7 2.3 4.3 2.7 5.1.5.5-1.2 1.2-1.1 4.5.5 2.2 1.2 5.7 2 8.1 2 8.8-.2 15.5.7 21.6 2.8 3.5 1.2 8 2.4 10 2.8 2 .4 7.1 2.3 11.2 4.2 4.1 2 8.2 3.8 9 4.1 4.4 1.7 10 5.7 10 7.2 0 5.2 11 7.9 15.1 3.8 1.9-1.9 2.1-3 1.7-10.5-.4-8.5-.1-9.6 2.3-6.8.7.8 3.6 2.7 6.3 4.1 2.8 1.4 5.3 2.7 5.6 3 .3.3.7 7.4 1 16 .5 14.7.7 15.6 2.9 17.4 1.3 1 3.7 1.9 5.5 1.9 1.7 0 4.6.9 6.4 2 1.8 1.1 4.2 2 5.5 2 2.5 0 8.8 3.5 10 5.6.4.8 2 1.4 3.6 1.4 1.9 0 3.4.9 4.9 3 1.2 1.6 2.2 3.3 2.2 3.9 0 .8 4 3.7 9.7 6.9 1.3.7 2.3 2.2 2.3 3.3 0 1.4 1 2.5 3 3.2 2.4.9 3 1.7 2.8 3.7-.3 2.1.3 2.7 3 3.5 2.6.7 3.2 1.4 3 3.4-.2 2 .4 2.9 3.1 4.2 2.1 1 3 1.9 2.2 2.3-1.4.9.4 4.6 2.2 4.6.7 0 2.2.6 3.2 1.4 1.6 1.2 1.7 1.6.6 3-1.2 1.4-1.1 1.9.3 3.4.9 1 1.6 3.3 1.6 5 0 3.3 5.5 12 7.6 12.1.7.1 1.4 1.1 1.7 2.2.4 1.4 1.1 2 2.1 1.6 1.2-.5 1.3-.2.6 1.8-.8 2-.4 2.6 2.4 4.6 2.9 2 3.3 2.6 2.6 5-.6 2.1-.4 3.2 1.2 4.6 1.4 1.2 1.8 2.6 1.4 4.4-.3 1.5.1 3.5 1 4.8.9 1.2 1.3 3 .9 4.2-.4 1.1-.2 2.5.3 3.2 1 1.2 1.9 5.2 2.7 11.7.2 1.9-.1 6.6-.6 10.5-.5 3.8-1.4 10.8-1.9 15.5-1 8.5-4.8 22.1-7.6 27.2l-1.5 2.8h12.8l.7-4.8c.3-2.6 1-7.2 1.6-10.2.6-3 1-8.2 1-11.4-.1-3.3.3-6.2 1-6.6.6-.4 1.3-6.4 1.6-15.1.5-11.1.3-16.3-1-22.8-.9-4.6-1.6-10.1-1.6-12.3 0-2.2-.6-6.1-1.4-8.7-1.3-3.9-5-16.6-7.1-23.8-.5-1.9-.1-2.3 3-2.9 4.1-.7 5.5-2.8 5.5-7.8 0-2 .4-3.6.9-3.6s1.7-2 2.7-4.5c1.3-3.5 1.4-4.9.6-5.7-.7-.7-1.2-2.9-1.2-5 0-2.7-.5-4.1-1.7-4.5-3.1-1.2-4.8-3.9-4.9-7.7-.2-4.3-.8-5.1-4.6-6-2.2-.5-2.8-.3-2.8 1 0 .9-.9 2.7-1.9 4l-2 2.5-1.3-2.8c-1.8-3.7-6.3-4.4-7.1-1.1-.5 2-1.4 2.3-5.6 2.6-4.8.2-5.1.1-5.7-2.5-.3-1.5-.9-3.9-1.4-5.3l-.8-2.5-1.2 3.2c-1.4 3.8-3 3.5-3-.5 0-4.4-1.1-7.4-2.6-6.8-.8.3-2.1-.7-3.1-2.4-1.1-2-2-2.6-2.8-2-1.5 1.2-2.5.3-2.5-2.3 0-3.6-2.3-8.8-3.7-8.3-.7.3-1.3-.1-1.3-1 0-.8-1-2.5-2.3-3.7-1.3-1.2-2.7-3-3-4-.5-1.3-1.3-1.6-2.7-1.2-1.3.4-2 .2-2-.7 0-.8-.7-2.3-1.5-3.4-1.4-1.8-1.6-1.8-2.9-.2-1.2 1.6-1.4 1.4-2.9-2.2-1.7-4.3-3.1-5-4.2-2.3-.5 1.4-1.3.5-3.2-3.7l-2.4-5.5-1.8 2.3-1.7 2.3-1.2-2.6c-.7-1.4-1.2-4.6-1.2-7.1 0-3-.8-5.7-2.5-8.4-1.4-2.2-2.5-4.3-2.5-4.7 0-.5-.9-1.9-1.9-3.3l-1.9-2.4-3.8 7.7-3.8 7.7-3.4-2.1c-1.9-1.1-5.4-3.7-7.8-5.7-3.4-2.9-4.3-4.4-4.4-6.9 0-1.7-.4-3.4-.9-3.7-.5-.4-2.6-3.5-4.6-7.1-2-3.6-4-6.5-4.5-6.5-.4 0-1.9 2.2-3.1 5-1.2 2.7-2.7 5-3.2 5s-1.6.8-2.3 1.7c-1.1 1.6-1.3 1.6-1.9-1-.7-2.7-3.5-3.9-3.5-1.5 0 .7-.9.2-2-1.2-1.7-2.2-1.9-2.3-2.5-.7-1 2.4-2.4 2.1-3.1-.8-.7-2.8-2.2-3.3-3-1-.8 1.9-2.4 2-2.4.1 0-2.1-1.8-3-3.4-1.7-1 .8-1.6.4-2.5-1.6l-1.3-2.7L441 45l-1.8 2.4-1.3-2.7c-1.4-3-2.7-3.5-3.5-1.2-.9 2.2-1.9 1.8-3.3-1.2-1.2-2.6-1.4-2.6-2.5-1-1.6 2.1-2.6 2.2-2.6.3 0-2.1-2.1-3.1-3.1-1.5-.7 1.1-1 .9-1.5-.9-.4-1.2-1-2.2-1.4-2.2-.4 0-1.4 1-2.3 2.2l-1.5 2.3-.7-2.8c-.9-3.2-2.3-3.5-3.3-.6-.8 2-.8 2-2-.5C409.1 35 407 34 407 36c0 .5-.4 1-1 1-.5 0-1-.7-1-1.5 0-1.8-3-2-3.1-.3 0 .7-.5.3-1.1-1-1.2-2.4-2.3-2.8-3.3-1.1-.5.7-1.4.4-2.7-.8-1.9-1.7-2-1.7-3.3 0-1.5 2.1-2.5 2.2-2.5.3 0-2.4-2.2-3.1-3.6-1.2-1.1 1.6-1.3 1.6-1.9-.3-.8-2.6-2.1-2.7-3.4-.4-.9 1.7-1 1.7-1.6 0-.9-2.1-2.8-2.2-4.6-.1-1.1 1.4-1.5 1.3-3.1-.8l-1.8-2.3-1.9 2.2-1.9 2.2L364 29c-1.5-3.7-2.6-3.8-3.5-.3l-.7 2.8-1.5-2.9-1.5-2.9L355 28l-1.8 2.3L352 28l-1.3-2.4-1.8 2.3-1.8 2.2-.7-2.5c-.8-3.2-1.1-3.2-3.5-.4-1.6 2-1.8 2-1.9.5 0-2.4-2.7-2.1-3.6.5-.6 2.1-.7 2.1-2.1-.5-1.6-3.2-2.1-3.3-3.9-1-1.1 1.7-1.3 1.6-2.3-1-.6-1.5-1.3-2.7-1.6-2.7-.3 0-1.1 1.2-1.7 2.7-1 2.5-1.2 2.6-1.6.8-.7-3.3-2.2-4.2-2.8-1.7-.8 2.8-1.8 2.8-3.6 0l-1.5-2.3-2.2 3.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="clickShapeWinter">
						<path d="M277 24c-19.7.7-25.2 1.6-51.7 8.6-7.6 1.9-16.9 4.4-20.8 5.4-3.8 1-10.7 3.2-15.3 4.9-9.5 3.5-14.2 3.9-16.5 1.3-.9-.9-3.6-2.8-6-4.1-4.4-2.4-4.6-2.4-8.9-.8-2.4 1-6.9 3.2-9.9 5.1-3 1.9-8.5 5.3-12.4 7.6-3.8 2.4-7.8 5-8.8 5.8-1.8 1.6-6.7 10.6-6.7 12.3 0 1.3-5.2 9.1-7 10.6-.8.7-4.6 3.1-8.5 5.5C98.1 90 93 94.4 93 96.2c0 .4-.8.9-1.8 1.2-1 .3-3.8 3.1-6.2 6.2-2.5 3.1-10.2 11.8-17.3 19.4-10.6 11.5-13.1 14.8-14.7 19.5-1.1 3.2-3.2 7.6-4.7 10-5.9 8.9-15.7 30.5-17.3 38-.4 2.2-1.3 5.3-1.8 7-1.9 5.6-3.2 12.5-3.2 17v4.5h7.9l5.7-10.8c3.1-5.9 11.5-21.2 18.7-34l13.1-23.4L85 138.6c11.7-10.7 15.2-13.2 26-18.9 6.9-3.6 15.8-7.8 19.8-9.3 4-1.4 7.2-3.2 7.1-3.8-.1-.6-.1-3.6 0-6.6l.2-5.5 6.6-3.8 6.6-3.7 3.8 2.5c5.9 3.8 8.8 3.1 14.9-3.6 3-3.3 6-4.9 20-10.3 22.8-8.8 40.6-14.2 53-15.9 10-1.4 18.8-1.2 21.7.6 1.9 1.2 29.4-2.1 39.8-4.7 8.7-2.1 9.6-2.2 27-1 22.7 1.6 34.2 3.4 48 7.8 12.7 4 27.5 6.4 42.5 7 13.1.5 20.5 2.2 25 5.7 1.9 1.4 9.2 5.9 16.2 9.8 10.5 6 13.8 7.3 18.5 7.8 3.6.4 8.3 1.8 12.5 3.9l6.7 3.4.7 8 .7 8.1 13.1 6.6c13.4 6.7 16.1 8.5 37.6 25.7 15.9 12.7 16.9 13.7 21.2 21.6 2.8 5 5.8 14.7 5.8 18.7 0 .1 2.9 3.5 6.5 7.4 6.1 6.9 6.6 7.8 10.9 20.8 5 15 7 26.7 6.3 35.7-1 13-2.8 25.2-4.6 30.9l-1.9 6 4.6.3c2.8.2 4.8-.1 5.3-.9.4-.7 1-6 1.4-11.8.3-5.8 1-11.4 1.5-12.3.6-1 1.3-7.6 1.6-14.7.5-9.9.3-15.5-1-23.8-3.4-21.1-4.8-27.9-8.2-38.1-1.9-5.6-3.4-10.8-3.4-11.5 0-1.2-3.6-8.4-9.5-19.4-5.1-9.3-24.1-33.5-45.8-58.1-6.9-7.9-13.6-16.4-14.8-18.9-1.2-2.6-4.4-7.1-7.1-10-2.6-2.9-4.8-6.2-4.8-7.2 0-3.2-2.5-4.8-8.4-5.5-7.9-.9-16.1-4.8-22.2-10.6-5.9-5.6-8.7-6.2-16.6-3.6-5.3 1.7-23 2.9-26.1 1.7-2.7-1.1-14.1-4.5-14.4-4.3-.2.1-2.2-.6-4.5-1.4-2.4-.9-5-1.7-5.8-1.9-3.5-.8-19.6-4.6-21-4.9-12.4-3.2-23-4.9-38-6.2-3.6-.3-12.5-1.2-19.8-2-13.6-1.4-30-1.5-63.2-.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						<g class="hoverShapeWinter">
						<path d="M277.5 27.2l-1.3 3.3-1.1-2.8c-1.3-3.3-2.7-3.5-3.7-.5l-.6 2.1L269 27l-1.7-2.3-1 2.9-1.1 2.9-.7-2.8c-.9-3.4-2.1-3.5-3 0l-.7 2.8L259 28l-1.9-2.5-1 2.8c-.6 1.6-1.1 3.4-1.1 4 0 .7-.9.1-1.9-1.2-1.9-2.4-4.1-2.2-4.1.5 0 2.1-1.8 1.7-2.5-.6-.9-2.7-2.3-2.5-3.1.5-.7 3-2 3.2-2.7.5-.8-2.9-2.7-2.4-3.9 1-.8 2.5-1.3 2.8-2.4 1.9-1.2-.9-1.8-.8-2.9.7-1.4 1.8-1.6 1.8-2.6.1-1.4-2.3-2.5-2.2-3.2.5-.5 1.8-.8 1.9-2.1.8-1.3-1-1.9-1-3.1 0-1.2 1-1.6.9-2.1-.4-.9-2.5-2.1-1.9-3.4 1.6-1 3-1.2 3.1-2.4 1.5-1.9-2.4-3.3-2.1-4 .9-.6 2.1-.9 2.4-2.2 1.4-1.1-1-1.8-.9-3.4.5-1.9 1.7-2 1.7-3.1-.8-1.5-3.2-2.4-2.8-3.3 1.5-.6 2.8-.8 3-1.7 1.5-1.5-2.5-2.4-2.1-3.8 2l-1.4 3.7-1.5-3-1.5-2.9-1.3 3c-.7 1.6-1.3 3.7-1.4 4.5 0 .8-.8.1-1.7-1.5l-1.6-3-1.5 3.5-1.4 3.5-1.6-3-1.5-3-1.1 2.5c-1.4 3.5-1.6 3.6-2.7 2-.8-1.3-1-1.3-1.9 0-.8 1.3-1 1.2-1-.4 0-1.1-.8-2.8-1.7-3.9-1.3-1.5-1.5-2.1-.5-2.4 3.1-1.1-.9-7.3-4.9-7.3-3.3 0-6.9 4.7-6.9 9.2 0 1.3-.4 2.8-.9 3.4-.5.5-1.1 2.5-1.3 4.4-.2 2.2-.8 3.4-1.6 3.2-1.7-.3-9.5 5.1-12.2 8.5l-2.1 2.7-2.4-2.9c-1.5-1.9-2.4-4.3-2.5-6.5 0-1.9-.4-3.1-.8-2.5-.4.5-1.8 1-3.2 1-2.1 0-2.7.7-3.9 4.5-.7 2.5-1.8 5.3-2.2 6.2-.5 1-.9 2.8-.9 4 0 1.3-.9 4.8-1.9 7.8-1.7 5.3-1.8 5.4-3.1 3.1l-1.2-2.4-1.9 2.4c-1 1.3-1.9 3-1.9 3.8 0 1.1-.3 1.1-1.5.1-1.3-1-1.8-.6-3.1 2.6s-1.7 3.5-2.5 2.1c-1.5-2.7-2.6-2-3.4 2-.6 3.5-.8 3.6-2.1 1.9-1.3-1.8-1.4-1.8-2.4.8-.5 1.5-1 3.6-1 4.7 0 2.6-1.6 2.4-2.4-.3-.7-2.9-2.3-1.5-4.1 3.8l-1.3 3.9-1.2-3c-1.4-3.6-2.6-2.1-3.6 4.3-.5 3.5-.9 4-2 3.1-1.2-.9-1.8-.8-2.9.7-1.4 1.9-1.9 3.1-1.6 4.6 0 .5-.3.8-.9.8-.5 0-1-.7-1-1.6 0-2.9-1.6-.5-3 4.5-1.1 4-1.6 4.8-2.6 4-1-.8-1.6-.5-2.5 1.5-.7 1.4-1.8 2.6-2.5 2.6-.6 0-1.9 1.7-2.7 3.7-.9 2.1-1.6 3.2-1.6 2.5-.2-2.5-2-1.1-3.1 2.5-.7 2.1-1.8 4.5-2.4 5.3-2 2.5-8.6 15.9-8.6 17.3 0 .8-.9 2.2-2 3.2s-2 2.6-2 3.6-.9 3.3-2 5.1c-1.1 1.8-2 4.1-2 5 0 1-.8 3.2-1.9 5.1-1 1.8-2.1 5-2.5 7-.3 2-.9 5.4-1.2 7.4-.4 2.1-1.2 4.7-1.9 5.8-.7 1.1-1.5 4.6-1.9 7.7L26 219h4c3.3 0 4-.3 4-1.9 0-1.1.7-2.6 1.5-3.5.8-.8 1.5-3.2 1.5-5.5 0-3.1.4-4.1 1.6-4.1 1.2 0 1.5-.8 1.3-3.4-.2-1.9.2-4 .8-4.7.6-.8 1.4-3 1.8-4.9.4-1.9 1.6-4.1 2.6-4.8 1.2-.9 1.9-2.5 1.9-4.8 0-2.7.4-3.4 2-3.4 1.3 0 2-.7 2-1.9 0-3.3 1.1-5.1 3.1-5.1 1.4 0 1.9-.7 1.9-3 0-2.1.5-3 1.6-3 .9 0 2.1-1.4 2.7-3 .8-2.1 1.8-3 3.4-3 2 0 2.3-.5 2.3-3.5 0-2.8.4-3.5 1.9-3.5 1.1 0 2.3-.9 2.6-2 .3-1.1 1.5-2 2.5-2 1.3 0 2.2-.9 2.6-2.8 1-4.9 1.3-5.2 4-5.2 1.7 0 2.8-.6 3.1-2 .3-1.3 1.4-2 2.9-2 1.9 0 2.4-.5 2.4-2.3 0-2.5 2.8-5.7 4.8-5.7.7 0 1.2-.8 1.2-1.8 0-1.6 2.4-3.5 10-7.7 2-1.1 4.8-2.8 6.3-3.8 1.4-.9 4.6-1.7 6.9-1.7 2.7 0 5.2-.8 6.8-2 1.5-1.2 4.1-2 6.5-2 3.1-.1 4.4-.6 5.8-2.6 1.7-2.2 1.7-3.4.8-13.1-1.7-16.6-2-15.5 4.9-19.1 3.3-1.7 6.7-3.8 7.6-4.6.8-.9 1.9-1.6 2.2-1.6.4 0 .4 4.2.1 9.2-.7 9.5-.5 11.3 1.2 10.3.5-.4 1.2 0 1.5.9.3.8 1.9 1.9 3.5 2.5 4.2 1.4 9.4-1.5 9.4-5.3-.1-2 .4-2.6 2.2-2.6 1.2 0 2.5-.5 2.8-1 1.1-1.7 13.3-7 16.2-7 1.5 0 3.9-.9 5.3-2s3.4-2 4.5-2 3.8-.9 6-2 4.7-1.9 5.6-2c.8 0 1.9-.4 2.5-.8.5-.5 5.4-1.9 10.9-3.1 5.5-1.3 10.9-2.8 12.1-3.3 1.1-.5 2.4-.8 3-.7.5.2 2.9-.4 5.2-1.1 3.5-1.1 5.3-1.1 8.8-.2 2.4.7 5.8 1.2 7.4 1.2 1.7 0 3.9.6 5 1.2 1.7 1.1 3.1.9 8.3-1.1 7.8-2.9 13.9-3.7 17.7-2.1 2.4 1 3.8.9 8.9-.9 3.4-1.2 7.7-2.1 9.5-2.1 1.9 0 3.8-.5 4.1-1 .9-1.4 8.5-1.2 19 .5 5 .9 14.8 1.8 21.9 2.2 10.9.5 13.1.9 14.6 2.6 1 1 2.3 1.7 2.8 1.3.5-.3 1.8.1 2.8.9 1 .8 4 1.5 6.5 1.5 3.3 0 5.1.6 6.4 2 1.4 1.5 3 2 6.4 1.9 2.5-.1 7.8.3 11.6 1 3.9.6 11.5 1.6 17 2.1 13 1.2 22.2 3.4 24.9 5.9 2.1 1.9 13.1 6.3 13.9 5.5.4-.4 3.5 1.1 7.5 3.6 1.2.8 2.2 2.3 2.2 3.2 0 2 4.8 4.8 8.4 4.8 1.4 0 3.7-.5 5.2-1l2.7-1.1-.7-9c-.4-5-.3-8.9.1-8.7.4.1 4.1 2.3 8.1 4.7l7.2 4.3.1 10.7c0 5.8.2 13.3.4 16.6.2 3.3.4 6.6.4 7.2.1.7 1.2 1.3 2.4 1.3 1.3 0 5.9 1.7 10.3 3.9 4.3 2.1 8.8 4.1 10 4.5 1.2.3 2.6 1.7 3.2 3 .6 1.3 2.3 2.7 3.9 3 1.5.4 3.7 1.6 4.8 2.6 1.1 1 3.1 2 4.5 2.2 1.8.2 2.6 1 2.8 2.8.5 3.4 1.6 4.3 6.5 5.5 3 .8 4.8 2 6.2 4.3 1.1 1.7 3.7 3.9 5.8 4.9 2.5 1.1 3.7 2.3 3.7 3.7 0 2.5 1.3 4.8 2.6 4.5 2.8-.5 3.6.2 3 2.5-.5 2.1-.1 2.8 2.7 4.1 3.4 1.6 3.4 1.7 3.4 8.3.1 10.3 4.3 18.4 10.4 19.7 3.4.8 5.8 3.3 4.1 4.4-.8.5-.2 1.5 1.9 3 2.7 2 3.1 2.8 2.6 5.7-.4 2.2-.1 3.6.7 4.1 1.2.7 1.6 2.4 3 12.3.2 1.4.6 3.2 1 4 2.5 6.6 2.8 17.4.7 30.5-.6 3.3-1.5 9.1-2 13-.5 3.8-1.7 9.1-2.5 11.7l-1.5 4.8h4.8c3.3 0 5.1-.5 5.4-1.4.3-.8.9-6.3 1.3-12.2.3-6 1-11.4 1.5-12 .5-.6 1.2-7 1.5-14.2.5-10 .3-15.5-1-23.9-3.4-21.1-4.8-27.9-8.2-38.1-1.9-5.6-3.4-11.1-3.4-12.2 0-1.1-.4-2-.9-2-.4 0-1.6-2.1-2.6-4.8-1-2.6-2.9-6.4-4.2-8.4-1.2-2.1-2.3-4.2-2.3-4.8 0-.5-.7-1-1.5-1s-1.5-.9-1.5-2-.4-2-1-2c-.5 0-1-.7-1-1.5 0-1.4-.5-1.6-2.7-1.4-.4 0-1.1-1.5-1.7-3.3-.6-1.8-1.8-3.3-2.6-3.3-2 0-2.8-1.2-4.1-6-1-3.7-1.2-3.8-2-1.8-.9 2.5-2.5 3.1-2.7 1-.7-5.3-1.7-8-3.2-8.4-1-.3-2.3-1.6-2.9-2.9-1.1-2.6-2.7-3.1-3.3-1.3-.2.7-1.2-1.6-2.3-5-1.2-4-2.3-6-3.1-5.7-.7.3-1.8-.8-2.4-2.5-.7-1.6-1.6-2.6-2.1-2.3-.5.3-1.3-.6-1.9-2-1.1-2.9-2-3.2-3.8-1.4-.9.9-1.2.8-1.2-.7-.1-1.1-.7-3.1-1.4-4.4l-1.3-2.4-1.5 2.2c-1.5 2.1-1.6 2-3.3-2.6l-1.8-4.6-1.8 2.2c-1.8 2.1-1.9 2.2-2.5.5-.3-1.1-1.2-3.5-2.1-5.4-1.4-3.3-1.6-3.4-2.9-1.6-.8 1.1-1.5 1.8-1.8 1.5-1.5-1.7-2.6-5.4-2.6-8.7 0-2.4-1-5.7-2.4-8.2-1.7-3-2.3-5.2-1.9-7.4.3-2.2 0-3.6-1.1-4.5-1.4-1.2-2.1-.9-5.1 2-2.4 2.4-3.5 4.3-3.5 6.4 0 4.2-1 7.5-2.3 7.5-.5 0-2.9-1.7-5.3-3.8-2.4-2-5.9-5-7.9-6.5-2.4-2-3.5-3.7-3.5-5.5 0-1.5-.7-3.5-1.5-4.5-.7-1.1-1.1-2.5-.9-3.2.8-1.9-2.2-7.2-4.8-8.6-1.8-1-2.4-.8-4 1.1-1.1 1.3-1.7 2.9-1.4 3.7.7 1.7-1.4 6.4-2.6 5.7-.4-.3-1.1.2-1.4 1-.9 2.3-1.9 2-3.3-1.1-1.2-2.6-1.4-2.6-2.6-1-1.2 1.6-1.3 1.6-2.8-.5-1.5-2.3-1.5-2.3-3-.3-1.5 2-1.6 1.9-2.2-.8-.7-2.8-3.5-3.8-3.5-1.2 0 .8-.4 1.5-1 1.5-.5 0-1-.6-1-1.3 0-.6-.5-1.9-1-2.7-.8-1.3-1.1-1.2-2.3.3-1.2 1.8-1.4 1.7-2.6-1l-1.3-2.8-1.9 2.3-1.9 2.4-.9-2.5c-1.3-3.4-2.8-4.1-3.6-1.7-.7 2.3-2.5 2.7-2.5.6 0-2-3.2-4.5-3.7-2.9-.7 1.9-2.1 1.6-2.8-.7-.8-2.5-2.2-2.6-2.8-.3-.4 1.5-.6 1.5-1.2-.5-.4-1.2-1.1-2.2-1.5-2.2-.4 0-1.4 1-2.3 2.2l-1.5 2.3-.7-2.8c-.8-3-2-3.5-3.3-1.2-.7 1.3-1.1 1.1-2.2-1-1.5-2.8-3-3.3-3-1s-1.7 1.8-2.4-.7c-.4-1.7-.8-2-1.7-1.1-.9.9-1.6.7-3-1-1-1.2-1.8-1.6-1.9-1 0 1.9-2.1 1.6-3.6-.5-1.2-1.6-1.4-1.6-2.8.3-1.4 1.7-1.7 1.8-2.6.5-.5-.8-1.1-2-1.4-2.5-.2-.6-1-.1-1.8.9-1.5 1.9-1.5 1.9-2.7 0-1-1.8-1.2-1.8-2.2-.1-1 1.6-1.2 1.6-3-.5l-2-2.3-1.5 2.2-1.6 2.3-2-2.5-2-2.5-1.8 2.2-1.9 2.3-1.3-3.3c-1.5-3.3-2.8-3.3-2.8 0 0 2.4-1.7 2.3-3-.2-1.4-2.6-2.7-2.5-3.5 0-.6 2-.7 2-2.5-.2s-1.9-2.2-3.2-.5c-1.3 1.8-1.5 1.7-2.8-1l-1.3-2.8-1.1 2.7c-1.2 3.4-2.2 3.5-3 .5l-.6-2.2-1.9 2.5-1.9 2.4-1.3-2.7-1.3-2.8-1.7 2.3-1.8 2.3-1.1-3-1.2-3-1.6 3c-1.4 2.6-1.7 2.8-2 1-.6-3.3-2.2-4.2-2.8-1.7-.8 2.7-1.6 2.8-3.2.2-.7-1.1-1.6-2-2.1-2-.9 0-2.7 4-3.4 7.5-.3 1.6-.7 1.2-2-1.8-1.9-4.4-3.2-4.7-4.2-1l-.7 2.8-1-2.8c-1.2-3.3-2.3-3.5-3.2-.5-.6 2.3-.7 2.2-2.3-.6l-1.6-3-1.5 3-1.5 2.9-.9-2.8c-1.2-3.4-2.6-3.5-3.4-.2-.3 1.4-1 2.5-1.4 2.5-.4 0-1.1-1.1-1.4-2.5-.8-3.3-2.4-3.2-3.7.2l-1.1 2.8-1.4-3.3c-.8-1.7-1.7-3.2-2-3.2-.3 0-1.1 1.5-1.9 3.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
						</g>
						</svg>';
				break;

				case '42Bottom': // 
					$sVG = '<svg class="buildingShape 42Bottom " width="675" height="534" viewBox="0 0 675 534" >
					<g class="clickShape">
					<path d="M29.6 221.8c-2.6 4.2-.9 61.1 2.3 75.9 3.7 17 11.5 36.6 20.4 51.2 2.1 3.5 4.2 7.4 4.7 8.5.5 1.2 4.7 6.7 9.3 12.3 6.1 7.3 9.8 13 13.3 20.4l4.9 10.2 20.4 15.6c11.3 8.6 21.1 16 21.8 16.4.7.5 9.8 3.5 20.2 6.7 10.3 3.3 26.4 9.1 35.7 13 17.9 7.5 27.9 10.7 51.4 16.4 26.6 6.5 50.3 9.3 93 11 14.6.6 55.6-2.6 70.5-5.4 15.2-2.9 33.5-6.9 38.5-8.4 2.5-.8 7.9-2.4 12-3.6 23.5-6.9 26.8-8.1 52-20 8.9-4.2 17.8-7.6 23.2-8.9 8.7-2 8.8-2.1 28-16.7l19.2-14.6 5.4-9.1c2.9-5.1 7-11 9.1-13.2 3.4-3.6 9.8-11.8 14.4-18.5 15.8-23 25.9-47.3 30.2-72.3l.6-3.8-6.2.3-6.1.3-3.7 10.5c-6.6 18.8-19.9 41.5-32.3 55.2l-4.3 4.8-10.1.6-10.1.7-19.9 15.3c-10.9 8.5-20.2 16.2-20.6 17.1-.3 1-.7 4.6-.7 7.9-.1 4.4-.6 6.8-1.8 8.2-2.3 2.7-26.4 14.6-41.3 20.5-86.4 33.9-189.4 35-278 3-13.8-5-33.7-13.9-44.4-19.9-9.8-5.5-11.1-7.6-11.2-18.1l-.1-8.5-22.3-16.5-22.3-16.5-7.9.7-7.9.7-4.4-4.5c-6-6.3-15.8-21.4-21.5-33.2-6.7-13.9-11.5-28.4-14.6-43.8-3-15.1-2.8-21.5 1-38.5l2.3-10.2h-5.8c-3.2 0-6 .3-6.3.8z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="hoverShape">
					<path d="M29.6 221.8c-1.3 2.1-1.6 19.4-.7 41.7 1.1 27.2 2.5 36.3 8.2 52.8 4.8 13.9 17.4 38.7 19.7 38.7.4 0 2 2.4 3.5 5.3 2.6 5.2 11.6 16.6 20 25.4 3.7 3.9 4.1 4.7 3.9 9-.2 4.3.2 5.1 2.9 7.2 2.9 2.3 3.3 2.3 5.7.9 1.5-.8 4-1.8 5.5-2.2 2.8-.7 2.8-.8 2.3-5.9-1.9-17-2-19.2-.5-18.1.8.6 5.7 4.3 10.9 8.1l9.5 7.1-.6 9.3c-.4 5.2-1 12.8-1.4 16.9l-.7 7.5 4.6 3.2c4.3 3 4.7 3.1 7.8 1.9 2.8-1.2 3.7-1.2 7.7.7 2.6 1.1 5.7 2.4 7.1 3 2.7 1 3.1 1.2 9.7 5.1 2.3 1.3 5.9 3 8 3.7 2.1.6 4 1.6 4.3 2 .3.5 1.5 1 2.5 1.2 1.1.1 3.6 1.1 5.5 2 5.6 2.9 21.9 9.2 30 11.7 4.1 1.2 10 3.1 13 4.1 6.7 2.1 29.8 7.6 36 8.5 6.6.9 8.1 1.1 12 1.8 29.3 5.3 81 5.7 117.9 1.1 6.9-.9 12.7-1.8 13-2 .4-.2 2.7-.6 5.1-1 2.5-.3 5.9-1 7.5-1.5 1.7-.5 7.3-1.7 12.5-2.6 5.2-.8 13.1-2.6 17.5-3.9 14.1-4.1 22-6.7 22.5-7.4.3-.3 1.6-.8 3-1 1.4-.1 5.2-1.5 8.5-3.1 3.3-1.5 7.3-3 8.8-3.4 2.7-.6 24.9-11.3 36.7-17.6 4.7-2.6 5.8-2.8 8.5-1.9 2.6 1 3.5.8 6.7-1.2 2.6-1.7 3.5-3 3.3-4.4-.2-1.1-.7-7.9-1-15.1l-.6-13.1 5.8-4.3c3.2-2.4 7.3-5.5 9.2-6.9l3.4-2.6-.5 10.6-.6 10.6 4.9 1.7 4.8 1.8 3.6-2.7c2.9-2.3 3.5-3.4 3.5-6.4 0-3.2.9-4.6 8.5-12.1 4.7-4.7 8.5-8.9 8.5-9.4s1.7-2.7 3.8-5c2-2.3 4.6-5.4 5.7-7 1.1-1.6 2.9-4 4-5.5 12.3-15.8 24.9-46.5 29-70.4l.6-3.8-6.2.3-6.1.3-3.7 10.5c-4.5 12.5-10.4 24.5-17.5 35.5-5.1 7.8-20 26.9-22.9 29.3-1.1.9-2.7.6-7.7-1.4-3.4-1.4-6.8-2.4-7.6-2.2-.7.2-10.4 7.2-21.4 15.7-13.1 10.1-19.7 15.8-19.1 16.5 1.2 1.6 2.3 10.3 1.6 12.5-.7 2.3-4.9 4.9-22.1 13.4-88.4 44-204.7 49.4-302.4 14-15.8-5.7-26.7-10.6-44.9-20.1l-14.4-7.5.7-7.5c.4-5.4 1.1-7.8 2.3-8.8 1.2-.8 1.5-1.6.8-2.2-.5-.5-10.9-8.3-22.9-17.2l-21.9-16.2-6.9 2.6-6.8 2.5-5.6-6.7c-18.5-22.3-31.2-49.2-37-78.5-3-15.1-2.8-21.5 1-38.5l2.3-10.2h-5.8c-3.2 0-6 .3-6.3.8z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="clickShapeWinter">
					<path d="M29.6 221.8c-1.3 2.1-1.6 19.4-.7 41.7 1.1 27.2 2.5 36.3 8.2 52.8 4.8 13.9 17.4 38.7 19.7 38.7.4 0 2 2.4 3.5 5.3 2.6 5.2 11.6 16.6 20 25.4 3.7 3.9 4.1 4.7 3.9 9-.2 4.3.2 5.1 2.9 7.2 2.9 2.3 3.3 2.3 5.7.9 1.5-.8 4-1.8 5.5-2.2 2.8-.7 2.8-.8 2.3-5.9-1.9-17-2-19.2-.5-18.1.8.6 5.7 4.3 10.9 8.1l9.5 7.1-.6 9.3c-.4 5.2-1 12.8-1.4 16.9l-.7 7.5 4.6 3.2c4.3 3 4.7 3.1 7.8 1.9 2.8-1.2 3.7-1.2 7.7.7 2.6 1.1 5.7 2.4 7.1 3 2.7 1 3.1 1.2 9.7 5.1 2.3 1.3 5.9 3 8 3.7 2.1.6 4 1.6 4.3 2 .3.5 1.5 1 2.5 1.2 1.1.1 3.6 1.1 5.5 2 5.6 2.9 21.9 9.2 30 11.7 4.1 1.2 10 3.1 13 4.1 6.7 2.1 29.8 7.6 36 8.5 6.6.9 8.1 1.1 12 1.8 29.3 5.3 81 5.7 117.9 1.1 6.9-.9 12.7-1.8 13-2 .4-.2 2.7-.6 5.1-1 2.5-.3 5.9-1 7.5-1.5 1.7-.5 7.3-1.7 12.5-2.6 5.2-.8 13.1-2.6 17.5-3.9 14.1-4.1 22-6.7 22.5-7.4.3-.3 1.6-.8 3-1 1.4-.1 5.2-1.5 8.5-3.1 3.3-1.5 7.3-3 8.8-3.4 2.7-.6 24.9-11.3 36.7-17.6 4.7-2.6 5.8-2.8 8.5-1.9 2.6 1 3.5.8 6.7-1.2 2.6-1.7 3.5-3 3.3-4.4-.2-1.1-.7-7.9-1-15.1l-.6-13.1 5.8-4.3c3.2-2.4 7.3-5.5 9.2-6.9l3.4-2.6-.5 10.6-.6 10.6 4.9 1.7 4.8 1.8 3.6-2.7c2.9-2.3 3.5-3.4 3.5-6.4 0-3.2.9-4.6 8.5-12.1 4.7-4.7 8.5-8.9 8.5-9.4s1.7-2.7 3.8-5c2-2.3 4.6-5.4 5.7-7 1.1-1.6 2.9-4 4-5.5 12.3-15.8 24.9-46.5 29-70.4l.6-3.8-6.2.3-6.1.3-3.7 10.5c-4.5 12.5-10.4 24.5-17.5 35.5-5.1 7.8-20 26.9-22.9 29.3-1.1.9-2.7.6-7.7-1.4-3.4-1.4-6.8-2.4-7.6-2.2-.7.2-10.4 7.2-21.4 15.7-13.1 10.1-19.7 15.8-19.1 16.5 1.2 1.6 2.3 10.3 1.6 12.5-.7 2.3-4.9 4.9-22.1 13.4-88.4 44-204.7 49.4-302.4 14-15.8-5.7-26.7-10.6-44.9-20.1l-14.4-7.5.7-7.5c.4-5.4 1.1-7.8 2.3-8.8 1.2-.8 1.5-1.6.8-2.2-.5-.5-10.9-8.3-22.9-17.2l-21.9-16.2-6.9 2.6-6.8 2.5-5.6-6.7c-18.5-22.3-31.2-49.2-37-78.5-3-15.1-2.8-21.5 1-38.5l2.3-10.2h-5.8c-3.2 0-6 .3-6.3.8z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="hoverShapeWinter">
					<path d="M29.6 221.8c-1.3 2.1-1.6 19.4-.7 41.7 1.1 27.2 2.5 36.3 8.2 52.8 4.8 13.9 17.4 38.7 19.7 38.7.4 0 2 2.4 3.5 5.3 2.6 5.2 11.6 16.6 20 25.4 3.7 3.9 4.1 4.7 3.9 9-.2 4.3.2 5.1 2.9 7.2 2.9 2.3 3.3 2.3 5.7.9 1.5-.8 4-1.8 5.5-2.2 2.8-.7 2.8-.8 2.3-5.9-1.9-17-2-19.2-.5-18.1.8.6 5.7 4.3 10.9 8.1l9.5 7.1-.6 9.3c-.4 5.2-1 12.8-1.4 16.9l-.7 7.5 4.6 3.2c4.3 3 4.7 3.1 7.8 1.9 2.8-1.2 3.7-1.2 7.7.7 2.6 1.1 5.7 2.4 7.1 3 2.7 1 3.1 1.2 9.7 5.1 2.3 1.3 5.9 3 8 3.7 2.1.6 4 1.6 4.3 2 .3.5 1.5 1 2.5 1.2 1.1.1 3.6 1.1 5.5 2 5.6 2.9 21.9 9.2 30 11.7 4.1 1.2 10 3.1 13 4.1 6.7 2.1 29.8 7.6 36 8.5 6.6.9 8.1 1.1 12 1.8 29.3 5.3 81 5.7 117.9 1.1 6.9-.9 12.7-1.8 13-2 .4-.2 2.7-.6 5.1-1 2.5-.3 5.9-1 7.5-1.5 1.7-.5 7.3-1.7 12.5-2.6 5.2-.8 13.1-2.6 17.5-3.9 14.1-4.1 22-6.7 22.5-7.4.3-.3 1.6-.8 3-1 1.4-.1 5.2-1.5 8.5-3.1 3.3-1.5 7.3-3 8.8-3.4 2.7-.6 24.9-11.3 36.7-17.6 4.7-2.6 5.8-2.8 8.5-1.9 2.6 1 3.5.8 6.7-1.2 2.6-1.7 3.5-3 3.3-4.4-.2-1.1-.7-7.9-1-15.1l-.6-13.1 5.8-4.3c3.2-2.4 7.3-5.5 9.2-6.9l3.4-2.6-.5 10.6-.6 10.6 4.9 1.7 4.8 1.8 3.6-2.7c2.9-2.3 3.5-3.4 3.5-6.4 0-3.2.9-4.6 8.5-12.1 4.7-4.7 8.5-8.9 8.5-9.4s1.7-2.7 3.8-5c2-2.3 4.6-5.4 5.7-7 1.1-1.6 2.9-4 4-5.5 12.3-15.8 24.9-46.5 29-70.4l.6-3.8-6.2.3-6.1.3-3.7 10.5c-4.5 12.5-10.4 24.5-17.5 35.5-5.1 7.8-20 26.9-22.9 29.3-1.1.9-2.7.6-7.7-1.4-3.4-1.4-6.8-2.4-7.6-2.2-.7.2-10.4 7.2-21.4 15.7-13.1 10.1-19.7 15.8-19.1 16.5 1.2 1.6 2.3 10.3 1.6 12.5-.7 2.3-4.9 4.9-22.1 13.4-88.4 44-204.7 49.4-302.4 14-15.8-5.7-26.7-10.6-44.9-20.1l-14.4-7.5.7-7.5c.4-5.4 1.1-7.8 2.3-8.8 1.2-.8 1.5-1.6.8-2.2-.5-.5-10.9-8.3-22.9-17.2l-21.9-16.2-6.9 2.6-6.8 2.5-5.6-6.7c-18.5-22.3-31.2-49.2-37-78.5-3-15.1-2.8-21.5 1-38.5l2.3-10.2h-5.8c-3.2 0-6 .3-6.3.8z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					</svg>';
				break;
				case '42Top': // 
					$sVG = '<svg class="buildingShape 42Top" width="675" height="534" viewBox="0 0 675 534" >
					<g class="clickShape">
					<path d="M306.5 31.1c-35.3 1.8-71 8.5-104.4 19.4l-13.4 4.4-3.8-2c-2.1-1-4.9-3.3-6.1-5-1.3-1.7-4.9-4.4-8.1-6l-5.8-3L143 53.2c-23.2 15.1-24 15.9-24 23.5 0 1.9-.7 6.3-1.6 9.8-1.6 6.3-1.6 6.4-11.2 13.8-39.2 30.3-65.5 68.5-75.3 109.2-2.8 11.9-3 11.5 4.5 11.5h6.3l5.7-13.8c4.3-10.1 8.5-18 16.4-30.2 11.6-17.8 16.3-23.4 34.7-40.5l12.1-11.2 12.3-4.1c8.4-2.8 13.6-5.2 16.4-7.5 2.3-1.9 10.1-7.1 17.2-11.7 7.2-4.5 16.2-10.4 20.1-13.1 8.2-5.5 17.3-9.1 39.1-15.4 34.5-9.9 66.8-15.1 100.3-16 27.5-.8 46.9.4 74 4.5 34.6 5.3 80.1 18.3 89.7 25.6 2 1.5 9.7 6.8 17.2 11.9 7.5 5 17.1 11.5 21.4 14.4 6.4 4.4 9.8 5.9 18.4 8.2 12 3.2 13.2 4 30.6 20.9 27.1 26.1 43.5 53.5 52.5 87.5 3.5 13.3 3.6 30.4.4 44.5-1.2 5.2-2.2 9.5-2.2 9.7 0 .2 2.7.3 5.9.3h5.9l.6-6.8c.4-3.7.7-16.8.8-29.2.1-19.6-.2-23.9-2.1-33.3-3.4-16.9-11-36.8-19.6-51.9-11.6-20.2-33.1-44.7-53.9-61.2-11.1-8.9-13.3-12.4-14.7-23.6-1.1-9.5-1.6-10.3-6.7-13.3-2.6-1.5-12.3-7.7-21.5-13.9l-16.8-11.1-5.5 2.6c-4 2-5.9 3.6-6.9 6-.7 1.7-2.5 4.3-3.9 5.6l-2.6 2.5-18.8-6.2C420.9 38.9 390.7 33.3 349.5 31c-19.7-1.1-21.1-1.1-43 .1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="hoverShape">
					<path d="M306.5 31.1c-35.3 1.8-71 8.5-104.4 19.4l-13.4 4.4-3.8-2c-2.1-1-4.9-3.3-6.1-5-1.3-1.7-4.9-4.4-8.1-6l-5.8-3L143 53.2c-23.2 15.1-24 15.9-24 23.5 0 1.9-.7 6.3-1.6 9.8-1.6 6.3-1.6 6.4-11.2 13.8-39.2 30.3-65.5 68.5-75.3 109.2-2.8 11.9-3 11.5 4.5 11.5h6.3l5.7-13.8c4.3-10.1 8.5-18 16.4-30.2 11.6-17.8 16.3-23.4 34.7-40.5l12.1-11.2 12.3-4.1c8.4-2.8 13.6-5.2 16.4-7.5 2.3-1.9 10.1-7.1 17.2-11.7 7.2-4.5 16.2-10.4 20.1-13.1 8.2-5.5 17.3-9.1 39.1-15.4 34.5-9.9 66.8-15.1 100.3-16 27.5-.8 46.9.4 74 4.5 34.6 5.3 80.1 18.3 89.7 25.6 2 1.5 9.7 6.8 17.2 11.9 7.5 5 17.1 11.5 21.4 14.4 6.4 4.4 9.8 5.9 18.4 8.2 12 3.2 13.2 4 30.6 20.9 27.1 26.1 43.5 53.5 52.5 87.5 3.5 13.3 3.6 30.4.4 44.5-1.2 5.2-2.2 9.5-2.2 9.7 0 .2 2.7.3 5.9.3h5.9l.6-6.8c.4-3.7.7-16.8.8-29.2.1-19.6-.2-23.9-2.1-33.3-3.4-16.9-11-36.8-19.6-51.9-11.6-20.2-33.1-44.7-53.9-61.2-11.1-8.9-13.3-12.4-14.7-23.6-1.1-9.5-1.6-10.3-6.7-13.3-2.6-1.5-12.3-7.7-21.5-13.9l-16.8-11.1-5.5 2.6c-4 2-5.9 3.6-6.9 6-.7 1.7-2.5 4.3-3.9 5.6l-2.6 2.5-18.8-6.2C420.9 38.9 390.7 33.3 349.5 31c-19.7-1.1-21.1-1.1-43 .1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="clickShapeWinter">
					<path d="M306.5 31.1c-35.3 1.8-71 8.5-104.4 19.4l-13.4 4.4-3.8-2c-2.1-1-4.9-3.3-6.1-5-1.3-1.7-4.9-4.4-8.1-6l-5.8-3L143 53.2c-23.2 15.1-24 15.9-24 23.5 0 1.9-.7 6.3-1.6 9.8-1.6 6.3-1.6 6.4-11.2 13.8-39.2 30.3-65.5 68.5-75.3 109.2-2.8 11.9-3 11.5 4.5 11.5h6.3l5.7-13.8c4.3-10.1 8.5-18 16.4-30.2 11.6-17.8 16.3-23.4 34.7-40.5l12.1-11.2 12.3-4.1c8.4-2.8 13.6-5.2 16.4-7.5 2.3-1.9 10.1-7.1 17.2-11.7 7.2-4.5 16.2-10.4 20.1-13.1 8.2-5.5 17.3-9.1 39.1-15.4 34.5-9.9 66.8-15.1 100.3-16 27.5-.8 46.9.4 74 4.5 34.6 5.3 80.1 18.3 89.7 25.6 2 1.5 9.7 6.8 17.2 11.9 7.5 5 17.1 11.5 21.4 14.4 6.4 4.4 9.8 5.9 18.4 8.2 12 3.2 13.2 4 30.6 20.9 27.1 26.1 43.5 53.5 52.5 87.5 3.5 13.3 3.6 30.4.4 44.5-1.2 5.2-2.2 9.5-2.2 9.7 0 .2 2.7.3 5.9.3h5.9l.6-6.8c.4-3.7.7-16.8.8-29.2.1-19.6-.2-23.9-2.1-33.3-3.4-16.9-11-36.8-19.6-51.9-11.6-20.2-33.1-44.7-53.9-61.2-11.1-8.9-13.3-12.4-14.7-23.6-1.1-9.5-1.6-10.3-6.7-13.3-2.6-1.5-12.3-7.7-21.5-13.9l-16.8-11.1-5.5 2.6c-4 2-5.9 3.6-6.9 6-.7 1.7-2.5 4.3-3.9 5.6l-2.6 2.5-18.8-6.2C420.9 38.9 390.7 33.3 349.5 31c-19.7-1.1-21.1-1.1-43 .1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="hoverShapeWinter">
					<path d="M306.5 31.1c-35.3 1.8-71 8.5-104.4 19.4l-13.4 4.4-3.8-2c-2.1-1-4.9-3.3-6.1-5-1.3-1.7-4.9-4.4-8.1-6l-5.8-3L143 53.2c-23.2 15.1-24 15.9-24 23.5 0 1.9-.7 6.3-1.6 9.8-1.6 6.3-1.6 6.4-11.2 13.8-39.2 30.3-65.5 68.5-75.3 109.2-2.8 11.9-3 11.5 4.5 11.5h6.3l5.7-13.8c4.3-10.1 8.5-18 16.4-30.2 11.6-17.8 16.3-23.4 34.7-40.5l12.1-11.2 12.3-4.1c8.4-2.8 13.6-5.2 16.4-7.5 2.3-1.9 10.1-7.1 17.2-11.7 7.2-4.5 16.2-10.4 20.1-13.1 8.2-5.5 17.3-9.1 39.1-15.4 34.5-9.9 66.8-15.1 100.3-16 27.5-.8 46.9.4 74 4.5 34.6 5.3 80.1 18.3 89.7 25.6 2 1.5 9.7 6.8 17.2 11.9 7.5 5 17.1 11.5 21.4 14.4 6.4 4.4 9.8 5.9 18.4 8.2 12 3.2 13.2 4 30.6 20.9 27.1 26.1 43.5 53.5 52.5 87.5 3.5 13.3 3.6 30.4.4 44.5-1.2 5.2-2.2 9.5-2.2 9.7 0 .2 2.7.3 5.9.3h5.9l.6-6.8c.4-3.7.7-16.8.8-29.2.1-19.6-.2-23.9-2.1-33.3-3.4-16.9-11-36.8-19.6-51.9-11.6-20.2-33.1-44.7-53.9-61.2-11.1-8.9-13.3-12.4-14.7-23.6-1.1-9.5-1.6-10.3-6.7-13.3-2.6-1.5-12.3-7.7-21.5-13.9l-16.8-11.1-5.5 2.6c-4 2-5.9 3.6-6.9 6-.7 1.7-2.5 4.3-3.9 5.6l-2.6 2.5-18.8-6.2C420.9 38.9 390.7 33.3 349.5 31c-19.7-1.1-21.1-1.1-43 .1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
				</svg>';
				break;


				case '43Bottom': // 
					$sVG = '<svg class="buildingShape 43Bottom " width="690" height="524" viewBox="0 0 690 524" >
					<g class="clickShape">
					<path d="M24.3 224.2c-.4.7-.7 15.2-.6 32.3.2 25.9.5 32.9 2.2 42.7 3.8 21.8 15.5 51.1 24 60 2.1 2.1 4.3 5.7 5 7.8.7 2.3 3.5 6.5 6.6 9.9 3 3.2 5.5 6.3 5.5 6.8s4.3 5.4 9.5 10.9c5.2 5.4 9.5 10.5 9.5 11.1 0 .8.9 1.1 2.3.9 1.2-.3 3.9-.5 6-.5l3.7-.1v-13.6c0-10.1.3-13.5 1.2-13.2.7.3 1.6 1.8 2 3.4 1.1 4.3 4.1 6.8 7.5 6.1 2.2-.4 4.1.5 9.6 4.5l6.7 5V418c0 20.5.4 22.3 4.1 19.9 2.3-1.4 5.9-1.1 5.9.6 0 .8.9 1.5 1.9 1.5 1 0 2.1.7 2.4 1.5.7 1.7 15.8 9.5 18.5 9.5 1 0 2.7.9 3.6 1.9 1 1.1 3.2 2.3 5 2.7 1.7.3 4.2 1.2 5.6 1.9 17.1 8.7 49.3 18.4 79 23.9 20.7 3.8 39.3 5.6 64.1 6.3 22.4.6 52.1-.8 61.9-2.8 2.5-.5 6.8-1.2 9.5-1.4 2.8-.3 6.1-.9 7.5-1.4 1.4-.5 6.6-1.4 11.7-2 5-.6 11.3-1.8 14-2.5 2.6-.8 8.9-2.4 13.8-3.7 11.9-2.9 32.2-9.6 36.3-12 1.9-1.1 3.7-1.7 4.1-1.4.5.2 1.7-.5 2.7-1.6 1-1.1 2.4-1.7 3.1-1.5 1.8.7 8.3-2.2 9-4 .3-.8 1.2-1.3 2-1 1.7.7 11.6-4.2 19.5-9.5 3.1-2.2 6.6-3.9 7.6-3.9 1.1 0 2.5-.7 3.2-1.5.8-.9 2.1-1.3 3.6-.9 5 1.3 5.4-.2 5.4-21.1v-19.2l6.7-5c5.8-4.4 7.1-4.9 9-4 1.9.8 2.8.6 4.8-1.3 1.4-1.3 2.9-3.7 3.3-5.4.4-1.6 1.3-3.1 2-3.4.9-.3 1.2 3.1 1.2 13.2v13.5l4.3 1.9c3.8 1.7 4.4 1.8 6 .4.9-.8 1.7-2.2 1.7-3.1 0-.9 2.8-4.5 6.1-8.1 3.3-3.6 7.2-7.9 8.7-9.5 4.8-5.2 14.8-18.7 19.2-26 5.8-9.5 14-26.5 14-29.2 0-1.2.5-2.5 1-2.8.6-.3 1-1.5 1-2.6s.6-3 1.4-4.2c2.2-3.3 7.6-27.1 7.6-33.2 0-.3-3.2-.5-7.1-.3l-7.1.3-4.2 10.5c-8.5 21-17.7 36.2-32.1 53-4.7 5.4-8.9 10.2-9.5 10.5-.5.3-1.7-.9-2.6-2.7-.9-1.8-1.8-3.5-2-3.7-.1-.1-4 2.8-8.6 6.6s-9.9 7.9-11.8 9.1c-1.9 1.2-4.8 3.3-6.4 4.7-1.6 1.4-5.7 4.1-9.1 6-7.8 4.3-9.9 7.3-10.5 14.9l-.5 6-17 8.8c-24.2 12.4-54.1 23.2-84 30.3-15.3 3.6-47.9 8.3-67.5 9.6-34.1 2.3-79.9-1.7-114.5-10.1-32.3-7.8-59.1-17.9-86.5-32.6l-9.5-5.1.3-5.6c.1-3.2-.2-5.7-.6-5.7-.5 0-1.4-1-1.9-2.3-.6-1.3-4.6-4.4-8.9-7-8.7-5.2-17.2-11.6-27.7-20.9-3.7-3.3-6.9-5.9-7-5.7-.1.2-1.1 2.1-2.1 4.1-1.1 2.1-2.5 3.8-3.1 3.8-1.4 0-12.7-12.4-20.2-22-13.7-17.8-22.4-35.5-31-63.5-2.7-9-2.4-34.9.6-45.9 1.1-4.4 2.1-8.3 2.1-8.8 0-1.3-12.8-1-13.7.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="hoverShape">
					<path d="M24.3 224.2c-.4.7-.7 15.2-.6 32.3.2 25.9.5 32.9 2.2 42.7 3.8 21.8 15.5 51.1 24 60 2.1 2.1 4.3 5.7 5 7.8.7 2.3 3.5 6.5 6.6 9.9 3 3.2 5.5 6.3 5.5 6.8s4.3 5.4 9.5 10.9c5.2 5.4 9.5 10.5 9.5 11.1 0 .8.9 1.1 2.3.9 1.2-.3 3.9-.5 6-.5l3.7-.1v-13.6c0-10.1.3-13.5 1.2-13.2.7.3 1.6 1.8 2 3.4 1.1 4.3 4.1 6.8 7.5 6.1 2.2-.4 4.1.5 9.6 4.5l6.7 5V418c0 20.5.4 22.3 4.1 19.9 2.3-1.4 5.9-1.1 5.9.6 0 .8.9 1.5 1.9 1.5 1 0 2.1.7 2.4 1.5.7 1.7 15.8 9.5 18.5 9.5 1 0 2.7.9 3.6 1.9 1 1.1 3.2 2.3 5 2.7 1.7.3 4.2 1.2 5.6 1.9 17.1 8.7 49.3 18.4 79 23.9 20.7 3.8 39.3 5.6 64.1 6.3 22.4.6 52.1-.8 61.9-2.8 2.5-.5 6.8-1.2 9.5-1.4 2.8-.3 6.1-.9 7.5-1.4 1.4-.5 6.6-1.4 11.7-2 5-.6 11.3-1.8 14-2.5 2.6-.8 8.9-2.4 13.8-3.7 11.9-2.9 32.2-9.6 36.3-12 1.9-1.1 3.7-1.7 4.1-1.4.5.2 1.7-.5 2.7-1.6 1-1.1 2.4-1.7 3.1-1.5 1.8.7 8.3-2.2 9-4 .3-.8 1.2-1.3 2-1 1.7.7 11.6-4.2 19.5-9.5 3.1-2.2 6.6-3.9 7.6-3.9 1.1 0 2.5-.7 3.2-1.5.8-.9 2.1-1.3 3.6-.9 5 1.3 5.4-.2 5.4-21.1v-19.2l6.7-5c5.8-4.4 7.1-4.9 9-4 1.9.8 2.8.6 4.8-1.3 1.4-1.3 2.9-3.7 3.3-5.4.4-1.6 1.3-3.1 2-3.4.9-.3 1.2 3.1 1.2 13.2v13.5l4.3 1.9c3.8 1.7 4.4 1.8 6 .4.9-.8 1.7-2.2 1.7-3.1 0-.9 2.8-4.5 6.1-8.1 3.3-3.6 7.2-7.9 8.7-9.5 4.8-5.2 14.8-18.7 19.2-26 5.8-9.5 14-26.5 14-29.2 0-1.2.5-2.5 1-2.8.6-.3 1-1.5 1-2.6s.6-3 1.4-4.2c2.2-3.3 7.6-27.1 7.6-33.2 0-.3-3.2-.5-7.1-.3l-7.1.3-4.2 10.5c-8.5 21-17.7 36.2-32.1 53-4.7 5.4-8.9 10.2-9.5 10.5-.5.3-1.7-.9-2.6-2.7-.9-1.8-1.8-3.5-2-3.7-.1-.1-4 2.8-8.6 6.6s-9.9 7.9-11.8 9.1c-1.9 1.2-4.8 3.3-6.4 4.7-1.6 1.4-5.7 4.1-9.1 6-7.8 4.3-9.9 7.3-10.5 14.9l-.5 6-17 8.8c-24.2 12.4-54.1 23.2-84 30.3-15.3 3.6-47.9 8.3-67.5 9.6-34.1 2.3-79.9-1.7-114.5-10.1-32.3-7.8-59.1-17.9-86.5-32.6l-9.5-5.1.3-5.6c.1-3.2-.2-5.7-.6-5.7-.5 0-1.4-1-1.9-2.3-.6-1.3-4.6-4.4-8.9-7-8.7-5.2-17.2-11.6-27.7-20.9-3.7-3.3-6.9-5.9-7-5.7-.1.2-1.1 2.1-2.1 4.1-1.1 2.1-2.5 3.8-3.1 3.8-1.4 0-12.7-12.4-20.2-22-13.7-17.8-22.4-35.5-31-63.5-2.7-9-2.4-34.9.6-45.9 1.1-4.4 2.1-8.3 2.1-8.8 0-1.3-12.8-1-13.7.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="clickShapeWinter">
					<path d="M24.3 224.2c-.4.7-.7 15.2-.6 32.3.2 25.9.5 32.9 2.2 42.7 3.8 21.8 15.5 51.1 24 60 2.1 2.1 4.3 5.7 5 7.8.7 2.3 3.5 6.5 6.6 9.9 3 3.2 5.5 6.3 5.5 6.8s4.3 5.4 9.5 10.9c5.2 5.4 9.5 10.5 9.5 11.1 0 .8.9 1.1 2.3.9 1.2-.3 3.9-.5 6-.5l3.7-.1v-13.6c0-10.1.3-13.5 1.2-13.2.7.3 1.6 1.8 2 3.4 1.1 4.3 4.1 6.8 7.5 6.1 2.2-.4 4.1.5 9.6 4.5l6.7 5V418c0 20.5.4 22.3 4.1 19.9 2.3-1.4 5.9-1.1 5.9.6 0 .8.9 1.5 1.9 1.5 1 0 2.1.7 2.4 1.5.7 1.7 15.8 9.5 18.5 9.5 1 0 2.7.9 3.6 1.9 1 1.1 3.2 2.3 5 2.7 1.7.3 4.2 1.2 5.6 1.9 17.1 8.7 49.3 18.4 79 23.9 20.7 3.8 39.3 5.6 64.1 6.3 22.4.6 52.1-.8 61.9-2.8 2.5-.5 6.8-1.2 9.5-1.4 2.8-.3 6.1-.9 7.5-1.4 1.4-.5 6.6-1.4 11.7-2 5-.6 11.3-1.8 14-2.5 2.6-.8 8.9-2.4 13.8-3.7 11.9-2.9 32.2-9.6 36.3-12 1.9-1.1 3.7-1.7 4.1-1.4.5.2 1.7-.5 2.7-1.6 1-1.1 2.4-1.7 3.1-1.5 1.8.7 8.3-2.2 9-4 .3-.8 1.2-1.3 2-1 1.7.7 11.6-4.2 19.5-9.5 3.1-2.2 6.6-3.9 7.6-3.9 1.1 0 2.5-.7 3.2-1.5.8-.9 2.1-1.3 3.6-.9 5 1.3 5.4-.2 5.4-21.1v-19.2l6.7-5c5.8-4.4 7.1-4.9 9-4 1.9.8 2.8.6 4.8-1.3 1.4-1.3 2.9-3.7 3.3-5.4.4-1.6 1.3-3.1 2-3.4.9-.3 1.2 3.1 1.2 13.2v13.5l4.3 1.9c3.8 1.7 4.4 1.8 6 .4.9-.8 1.7-2.2 1.7-3.1 0-.9 2.8-4.5 6.1-8.1 3.3-3.6 7.2-7.9 8.7-9.5 4.8-5.2 14.8-18.7 19.2-26 5.8-9.5 14-26.5 14-29.2 0-1.2.5-2.5 1-2.8.6-.3 1-1.5 1-2.6s.6-3 1.4-4.2c2.2-3.3 7.6-27.1 7.6-33.2 0-.3-3.2-.5-7.1-.3l-7.1.3-4.2 10.5c-8.5 21-17.7 36.2-32.1 53-4.7 5.4-8.9 10.2-9.5 10.5-.5.3-1.7-.9-2.6-2.7-.9-1.8-1.8-3.5-2-3.7-.1-.1-4 2.8-8.6 6.6s-9.9 7.9-11.8 9.1c-1.9 1.2-4.8 3.3-6.4 4.7-1.6 1.4-5.7 4.1-9.1 6-7.8 4.3-9.9 7.3-10.5 14.9l-.5 6-17 8.8c-24.2 12.4-54.1 23.2-84 30.3-15.3 3.6-47.9 8.3-67.5 9.6-34.1 2.3-79.9-1.7-114.5-10.1-32.3-7.8-59.1-17.9-86.5-32.6l-9.5-5.1.3-5.6c.1-3.2-.2-5.7-.6-5.7-.5 0-1.4-1-1.9-2.3-.6-1.3-4.6-4.4-8.9-7-8.7-5.2-17.2-11.6-27.7-20.9-3.7-3.3-6.9-5.9-7-5.7-.1.2-1.1 2.1-2.1 4.1-1.1 2.1-2.5 3.8-3.1 3.8-1.4 0-12.7-12.4-20.2-22-13.7-17.8-22.4-35.5-31-63.5-2.7-9-2.4-34.9.6-45.9 1.1-4.4 2.1-8.3 2.1-8.8 0-1.3-12.8-1-13.7.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="hoverShapeWinter">
					<path d="M24.3 224.2c-.4.7-.7 15.2-.6 32.3.2 25.9.5 32.9 2.2 42.7 3.8 21.8 15.5 51.1 24 60 2.1 2.1 4.3 5.7 5 7.8.7 2.3 3.5 6.5 6.6 9.9 3 3.2 5.5 6.3 5.5 6.8s4.3 5.4 9.5 10.9c5.2 5.4 9.5 10.5 9.5 11.1 0 .8.9 1.1 2.3.9 1.2-.3 3.9-.5 6-.5l3.7-.1v-13.6c0-10.1.3-13.5 1.2-13.2.7.3 1.6 1.8 2 3.4 1.1 4.3 4.1 6.8 7.5 6.1 2.2-.4 4.1.5 9.6 4.5l6.7 5V418c0 20.5.4 22.3 4.1 19.9 2.3-1.4 5.9-1.1 5.9.6 0 .8.9 1.5 1.9 1.5 1 0 2.1.7 2.4 1.5.7 1.7 15.8 9.5 18.5 9.5 1 0 2.7.9 3.6 1.9 1 1.1 3.2 2.3 5 2.7 1.7.3 4.2 1.2 5.6 1.9 17.1 8.7 49.3 18.4 79 23.9 20.7 3.8 39.3 5.6 64.1 6.3 22.4.6 52.1-.8 61.9-2.8 2.5-.5 6.8-1.2 9.5-1.4 2.8-.3 6.1-.9 7.5-1.4 1.4-.5 6.6-1.4 11.7-2 5-.6 11.3-1.8 14-2.5 2.6-.8 8.9-2.4 13.8-3.7 11.9-2.9 32.2-9.6 36.3-12 1.9-1.1 3.7-1.7 4.1-1.4.5.2 1.7-.5 2.7-1.6 1-1.1 2.4-1.7 3.1-1.5 1.8.7 8.3-2.2 9-4 .3-.8 1.2-1.3 2-1 1.7.7 11.6-4.2 19.5-9.5 3.1-2.2 6.6-3.9 7.6-3.9 1.1 0 2.5-.7 3.2-1.5.8-.9 2.1-1.3 3.6-.9 5 1.3 5.4-.2 5.4-21.1v-19.2l6.7-5c5.8-4.4 7.1-4.9 9-4 1.9.8 2.8.6 4.8-1.3 1.4-1.3 2.9-3.7 3.3-5.4.4-1.6 1.3-3.1 2-3.4.9-.3 1.2 3.1 1.2 13.2v13.5l4.3 1.9c3.8 1.7 4.4 1.8 6 .4.9-.8 1.7-2.2 1.7-3.1 0-.9 2.8-4.5 6.1-8.1 3.3-3.6 7.2-7.9 8.7-9.5 4.8-5.2 14.8-18.7 19.2-26 5.8-9.5 14-26.5 14-29.2 0-1.2.5-2.5 1-2.8.6-.3 1-1.5 1-2.6s.6-3 1.4-4.2c2.2-3.3 7.6-27.1 7.6-33.2 0-.3-3.2-.5-7.1-.3l-7.1.3-4.2 10.5c-8.5 21-17.7 36.2-32.1 53-4.7 5.4-8.9 10.2-9.5 10.5-.5.3-1.7-.9-2.6-2.7-.9-1.8-1.8-3.5-2-3.7-.1-.1-4 2.8-8.6 6.6s-9.9 7.9-11.8 9.1c-1.9 1.2-4.8 3.3-6.4 4.7-1.6 1.4-5.7 4.1-9.1 6-7.8 4.3-9.9 7.3-10.5 14.9l-.5 6-17 8.8c-24.2 12.4-54.1 23.2-84 30.3-15.3 3.6-47.9 8.3-67.5 9.6-34.1 2.3-79.9-1.7-114.5-10.1-32.3-7.8-59.1-17.9-86.5-32.6l-9.5-5.1.3-5.6c.1-3.2-.2-5.7-.6-5.7-.5 0-1.4-1-1.9-2.3-.6-1.3-4.6-4.4-8.9-7-8.7-5.2-17.2-11.6-27.7-20.9-3.7-3.3-6.9-5.9-7-5.7-.1.2-1.1 2.1-2.1 4.1-1.1 2.1-2.5 3.8-3.1 3.8-1.4 0-12.7-12.4-20.2-22-13.7-17.8-22.4-35.5-31-63.5-2.7-9-2.4-34.9.6-45.9 1.1-4.4 2.1-8.3 2.1-8.8 0-1.3-12.8-1-13.7.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					</svg>';
				break;
				case '43Top': // 
					$sVG = '<svg class="buildingShape 43Top" width="690" height="524" viewBox="0 0 690 524" >
					<g class="clickShape">
					<path d="M284.9 32.5c-7.6.7-15.9 1.7-18.5 2.3-2.7.6-6.9 1.4-9.4 1.8-9.9 1.4-40.3 8.6-53 12.6-8.8 2.7-15.3 4.2-18.7 4.2-5-.1-6.9-.9-14.8-6.8-.9-.6-2.5 0-5.1 2-2.2 1.7-7.3 5-11.4 7.4-4.1 2.4-9.6 5.6-12.1 7-2.5 1.5-7.1 3.5-10.1 4.4-6.7 1.9-9.3 4.9-11.5 13.4-.8 3.1-1.9 6.5-2.4 7.4-.5 1-7.1 6.3-14.6 11.9-15.1 11.3-24.6 20.3-37.1 35.4-22.3 26.7-34.7 51.3-39.7 79-2.3 12.3-2.6 11.6 5.2 11.3l6.7-.3 4.2-10c5.6-13.7 12.5-25.9 20.8-36.8 13.9-18.5 28.8-34 41.9-43.8 12.9-9.6 90.9-48.7 107.2-53.7 13.6-4.2 36.8-9.1 52.8-11.2 8.9-1.1 19.5-2.5 23.5-3.1 4.1-.6 17.6-1.2 30-1.4 43.5-.7 87 5.1 124.7 16.6 21.4 6.6 26.5 8.7 32.5 13.6 3.2 2.7 13.7 8.6 25.5 14.5 11 5.5 20.7 10.6 21.5 11.3.8.7 5.8 3.1 11.1 5.2 8.7 3.6 10.8 5 21.9 14.9 6.8 6 15.2 13.9 18.7 17.5 19.1 19.8 33.3 42.9 42 68.6 3.6 10.5 3.7 11.2 3.7 24.3-.1 12.6-1 18.8-4.9 34.2l-.7 2.8H629v-7.3c.2-36.5-.2-56-1.1-61.1-1.2-7.3-6.5-25.9-8.5-30.1-.7-1.7-2.7-6.4-4.4-10.5-10.9-26.5-34.2-54.8-65.9-80.3-6.8-5.5-11.9-12.4-13-17.8-1.7-7.7-5.9-12.4-12.4-13.9-6-1.4-18.8-8-29.5-15.2-3.9-2.7-7.9-5.1-8.8-5.5-1-.3-2.7.9-5 3.5-5 5.8-8.3 6.3-18.7 2.4C435 43.1 392.5 34.4 359 32c-18.9-1.3-56.9-1-74.1.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="hoverShape">
					<path d="M284.9 32.5c-7.6.7-15.9 1.7-18.5 2.3-2.7.6-6.9 1.4-9.4 1.8-9.9 1.4-40.3 8.6-53 12.6-8.8 2.7-15.3 4.2-18.7 4.2-5-.1-6.9-.9-14.8-6.8-.9-.6-2.5 0-5.1 2-2.2 1.7-7.3 5-11.4 7.4-4.1 2.4-9.6 5.6-12.1 7-2.5 1.5-7.1 3.5-10.1 4.4-6.7 1.9-9.3 4.9-11.5 13.4-.8 3.1-1.9 6.5-2.4 7.4-.5 1-7.1 6.3-14.6 11.9-15.1 11.3-24.6 20.3-37.1 35.4-22.3 26.7-34.7 51.3-39.7 79-2.3 12.3-2.6 11.6 5.2 11.3l6.7-.3 4.2-10c5.6-13.7 12.5-25.9 20.8-36.8 13.9-18.5 28.8-34 41.9-43.8 12.9-9.6 90.9-48.7 107.2-53.7 13.6-4.2 36.8-9.1 52.8-11.2 8.9-1.1 19.5-2.5 23.5-3.1 4.1-.6 17.6-1.2 30-1.4 43.5-.7 87 5.1 124.7 16.6 21.4 6.6 26.5 8.7 32.5 13.6 3.2 2.7 13.7 8.6 25.5 14.5 11 5.5 20.7 10.6 21.5 11.3.8.7 5.8 3.1 11.1 5.2 8.7 3.6 10.8 5 21.9 14.9 6.8 6 15.2 13.9 18.7 17.5 19.1 19.8 33.3 42.9 42 68.6 3.6 10.5 3.7 11.2 3.7 24.3-.1 12.6-1 18.8-4.9 34.2l-.7 2.8H629v-7.3c.2-36.5-.2-56-1.1-61.1-1.2-7.3-6.5-25.9-8.5-30.1-.7-1.7-2.7-6.4-4.4-10.5-10.9-26.5-34.2-54.8-65.9-80.3-6.8-5.5-11.9-12.4-13-17.8-1.7-7.7-5.9-12.4-12.4-13.9-6-1.4-18.8-8-29.5-15.2-3.9-2.7-7.9-5.1-8.8-5.5-1-.3-2.7.9-5 3.5-5 5.8-8.3 6.3-18.7 2.4C435 43.1 392.5 34.4 359 32c-18.9-1.3-56.9-1-74.1.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="clickShapeWinter">
					<path d="M284.9 32.5c-7.6.7-15.9 1.7-18.5 2.3-2.7.6-6.9 1.4-9.4 1.8-9.9 1.4-40.3 8.6-53 12.6-8.8 2.7-15.3 4.2-18.7 4.2-5-.1-6.9-.9-14.8-6.8-.9-.6-2.5 0-5.1 2-2.2 1.7-7.3 5-11.4 7.4-4.1 2.4-9.6 5.6-12.1 7-2.5 1.5-7.1 3.5-10.1 4.4-6.7 1.9-9.3 4.9-11.5 13.4-.8 3.1-1.9 6.5-2.4 7.4-.5 1-7.1 6.3-14.6 11.9-15.1 11.3-24.6 20.3-37.1 35.4-22.3 26.7-34.7 51.3-39.7 79-2.3 12.3-2.6 11.6 5.2 11.3l6.7-.3 4.2-10c5.6-13.7 12.5-25.9 20.8-36.8 13.9-18.5 28.8-34 41.9-43.8 12.9-9.6 90.9-48.7 107.2-53.7 13.6-4.2 36.8-9.1 52.8-11.2 8.9-1.1 19.5-2.5 23.5-3.1 4.1-.6 17.6-1.2 30-1.4 43.5-.7 87 5.1 124.7 16.6 21.4 6.6 26.5 8.7 32.5 13.6 3.2 2.7 13.7 8.6 25.5 14.5 11 5.5 20.7 10.6 21.5 11.3.8.7 5.8 3.1 11.1 5.2 8.7 3.6 10.8 5 21.9 14.9 6.8 6 15.2 13.9 18.7 17.5 19.1 19.8 33.3 42.9 42 68.6 3.6 10.5 3.7 11.2 3.7 24.3-.1 12.6-1 18.8-4.9 34.2l-.7 2.8H629v-7.3c.2-36.5-.2-56-1.1-61.1-1.2-7.3-6.5-25.9-8.5-30.1-.7-1.7-2.7-6.4-4.4-10.5-10.9-26.5-34.2-54.8-65.9-80.3-6.8-5.5-11.9-12.4-13-17.8-1.7-7.7-5.9-12.4-12.4-13.9-6-1.4-18.8-8-29.5-15.2-3.9-2.7-7.9-5.1-8.8-5.5-1-.3-2.7.9-5 3.5-5 5.8-8.3 6.3-18.7 2.4C435 43.1 392.5 34.4 359 32c-18.9-1.3-56.9-1-74.1.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="hoverShapeWinter">
					<path d="M284.9 32.5c-7.6.7-15.9 1.7-18.5 2.3-2.7.6-6.9 1.4-9.4 1.8-9.9 1.4-40.3 8.6-53 12.6-8.8 2.7-15.3 4.2-18.7 4.2-5-.1-6.9-.9-14.8-6.8-.9-.6-2.5 0-5.1 2-2.2 1.7-7.3 5-11.4 7.4-4.1 2.4-9.6 5.6-12.1 7-2.5 1.5-7.1 3.5-10.1 4.4-6.7 1.9-9.3 4.9-11.5 13.4-.8 3.1-1.9 6.5-2.4 7.4-.5 1-7.1 6.3-14.6 11.9-15.1 11.3-24.6 20.3-37.1 35.4-22.3 26.7-34.7 51.3-39.7 79-2.3 12.3-2.6 11.6 5.2 11.3l6.7-.3 4.2-10c5.6-13.7 12.5-25.9 20.8-36.8 13.9-18.5 28.8-34 41.9-43.8 12.9-9.6 90.9-48.7 107.2-53.7 13.6-4.2 36.8-9.1 52.8-11.2 8.9-1.1 19.5-2.5 23.5-3.1 4.1-.6 17.6-1.2 30-1.4 43.5-.7 87 5.1 124.7 16.6 21.4 6.6 26.5 8.7 32.5 13.6 3.2 2.7 13.7 8.6 25.5 14.5 11 5.5 20.7 10.6 21.5 11.3.8.7 5.8 3.1 11.1 5.2 8.7 3.6 10.8 5 21.9 14.9 6.8 6 15.2 13.9 18.7 17.5 19.1 19.8 33.3 42.9 42 68.6 3.6 10.5 3.7 11.2 3.7 24.3-.1 12.6-1 18.8-4.9 34.2l-.7 2.8H629v-7.3c.2-36.5-.2-56-1.1-61.1-1.2-7.3-6.5-25.9-8.5-30.1-.7-1.7-2.7-6.4-4.4-10.5-10.9-26.5-34.2-54.8-65.9-80.3-6.8-5.5-11.9-12.4-13-17.8-1.7-7.7-5.9-12.4-12.4-13.9-6-1.4-18.8-8-29.5-15.2-3.9-2.7-7.9-5.1-8.8-5.5-1-.3-2.7.9-5 3.5-5 5.8-8.3 6.3-18.7 2.4C435 43.1 392.5 34.4 359 32c-18.9-1.3-56.9-1-74.1.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					</svg>';
				break;
			}
		}else{
			switch($idBuilding){
				
				case 0:				
					$sVG = '
					<svg class="buildingShape iso" width="120" height="120" viewBox="0 0 120 120" >
					<g class="clickShape">
						<path d="M49.4 70.4c-7.8 1.8-13.5 4.7-16.8 8.6-3.5 4.3-4.1 7.2-2.1 11.3 3.3 7.1 13.9 11.7 28.5 12.4 19.8 1.1 35-6.5 35-17.5 0-4.9-5.8-10.2-14.2-13.1-8.1-2.8-22-3.6-30.4-1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="hoverShape">
						<path d="M49.4 70.4c-7.8 1.8-13.5 4.7-16.8 8.6-3.5 4.3-4.1 7.2-2.1 11.3 3.3 7.1 13.9 11.7 28.5 12.4 19.8 1.1 35-6.5 35-17.5 0-4.9-5.8-10.2-14.2-13.1-8.1-2.8-22-3.6-30.4-1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="clickShapeWinter">
						<path d="M49.4 70.4c-7.8 1.8-13.5 4.7-16.8 8.6-3.5 4.3-4.1 7.2-2.1 11.3 3.3 7.1 13.9 11.7 28.5 12.4 19.8 1.1 35-6.5 35-17.5 0-4.9-5.8-10.2-14.2-13.1-8.1-2.8-22-3.6-30.4-1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="hoverShapeWinter">
						<path d="M49.4 70.4c-7.8 1.8-13.5 4.7-16.8 8.6-3.5 4.3-4.1 7.2-2.1 11.3 3.3 7.1 13.9 11.7 28.5 12.4 19.8 1.1 35-6.5 35-17.5 0-4.9-5.8-10.2-14.2-13.1-8.1-2.8-22-3.6-30.4-1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					</svg>
					';

				break;
				case 5: // 
					$sVG = '<svg class="buildingShape g5" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120">
		<g class="clickShape">
			<path d="M67 20.7c-1.4.3-2.9.9-3.4 1.4-.6.5-1.7.9-2.5.9-.9 0-3.7 1.1-6.4 2.3-4.5 2.2-8.5 6.8-15 17-1.8 2.8-1.8 2.9.2 3.4 1.2.3 2.1 1 2.1 1.5 0 .6-2.5-.4-5.6-2.2-3.1-1.7-6.2-2.9-7-2.6-1.9.7-1.8 3.2.2 4 1.1.4 1.4 1.8 1.2 5.3-.3 4.6-.5 4.9-4.6 6.8-4.2 2-5.4 4.1-2.8 5.1.8.3 4.2-.8 7.6-2.5 3.4-1.7 6.5-3.1 6.9-3.1.6 0 4.1 6.3 4.1 7.4 0 .3-2 1.2-11.8 5.2-3.8 1.6-4.6 2.5-4.3 4.6 0 .4-.8 1.2-1.9 1.8-2.4 1.3-1.3 5.4 1.6 5.5 2.3.1 8.4 2.7 8.4 3.5 0 .3-1.5 1.1-3.4 1.8-6.3 2.3-8.1 6.8-3.6 9.2 1.3.7 4.6-.3 13-4.1 7.9-3.4 11.9-4.7 13.4-4.2 3 1 2.8.9 6.3 1.8 1.7.4 3.4 1.3 3.7 2.1.6 1.6-1 1.9-1.9.4-1.1-1.7-1.9-1.1-5.9 4.5-2.7 3.7-4.2 5.1-4.7 4.3-.6-.9-1.3-.7-2.8.6-2.1 1.9-3 5.3-1.1 4.1.6-.3 1 .3 1 1.5 0 1.7.4 2 2.1 1.5 1.5-.5 1.9-.4 1.5.4-1 1.6 2 1.4 5.4-.4 1.7-.9 4.4-1.3 6.5-1 2.1.2 3.3 0 3-.5-.4-.6 1.5-.9 4.3-.9 4.9.2 9.2-1.3 9.2-3.1 0-.6-.9-1.9-2-3s-2-2.5-2-3c0-1.5 1.9-1.2 3.7.6.9.8 1.9 1.2 2.2.7.3-.5 1.9-1.7 3.5-2.7l3-1.9 2.3 2.8c2.1 2.4 2.7 2.6 4.9 1.6 1.9-.9 2.3-1.5 1.5-2.8-.5-1-1.2-5.6-1.6-10.4-.7-8.1-.6-8.5 1.5-9.5 2.1-.9 1.7-1.3-5.1-6.6-12.1-9.2-12.2-9.4-11.4-17.7.4-3.9 1.3-7.5 2-8.1 1.1-.9.4-2.5-3.4-7.9-2.6-3.8-5.6-9-6.6-11.5-1.1-2.5-2.2-4.5-2.5-4.5-.3.1-1.6.4-3 .6zm-32 33c0 1.3-2 1.9-2 .5 0-.4-.3-1.8-.6-3.2-.5-2.4-.5-2.4 1-.6.9 1.1 1.6 2.6 1.6 3.3zm6-1.2c.6.8 1 1.9.8 2.4-.1.5-1.1-.2-2.1-1.5-1.9-2.6-.8-3.4 1.3-.9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M67 20.7c-1.4.3-2.9.9-3.4 1.4-.6.5-1.7.9-2.5.9-.9 0-3.7 1.1-6.4 2.3-4.5 2.2-8.5 6.8-15 17-1.8 2.8-1.8 2.9.2 3.4 1.2.3 2.1 1 2.1 1.5 0 .6-2.5-.4-5.6-2.2-3.1-1.7-6.2-2.9-7-2.6-1.9.7-1.8 3.2.2 4 1.1.4 1.4 1.8 1.2 5.3-.3 4.6-.5 4.9-4.6 6.8-4.2 2-5.4 4.1-2.8 5.1.8.3 4.2-.8 7.6-2.5 3.4-1.7 6.5-3.1 6.9-3.1.6 0 4.1 6.3 4.1 7.4 0 .3-2 1.2-11.8 5.2-3.8 1.6-4.6 2.5-4.3 4.6 0 .4-.8 1.2-1.9 1.8-2.4 1.3-1.3 5.4 1.6 5.5 2.3.1 8.4 2.7 8.4 3.5 0 .3-1.5 1.1-3.4 1.8-6.3 2.3-8.1 6.8-3.6 9.2 1.3.7 4.6-.3 13-4.1 7.9-3.4 11.9-4.7 13.4-4.2 3 1 2.8.9 6.3 1.8 1.7.4 3.4 1.3 3.7 2.1.6 1.6-1 1.9-1.9.4-1.1-1.7-1.9-1.1-5.9 4.5-2.7 3.7-4.2 5.1-4.7 4.3-.6-.9-1.3-.7-2.8.6-2.1 1.9-3 5.3-1.1 4.1.6-.3 1 .3 1 1.5 0 1.7.4 2 2.1 1.5 1.5-.5 1.9-.4 1.5.4-1 1.6 2 1.4 5.4-.4 1.7-.9 4.4-1.3 6.5-1 2.1.2 3.3 0 3-.5-.4-.6 1.5-.9 4.3-.9 4.9.2 9.2-1.3 9.2-3.1 0-.6-.9-1.9-2-3s-2-2.5-2-3c0-1.5 1.9-1.2 3.7.6.9.8 1.9 1.2 2.2.7.3-.5 1.9-1.7 3.5-2.7l3-1.9 2.3 2.8c2.1 2.4 2.7 2.6 4.9 1.6 1.9-.9 2.3-1.5 1.5-2.8-.5-1-1.2-5.6-1.6-10.4-.7-8.1-.6-8.5 1.5-9.5 2.1-.9 1.7-1.3-5.1-6.6-12.1-9.2-12.2-9.4-11.4-17.7.4-3.9 1.3-7.5 2-8.1 1.1-.9.4-2.5-3.4-7.9-2.6-3.8-5.6-9-6.6-11.5-1.1-2.5-2.2-4.5-2.5-4.5-.3.1-1.6.4-3 .6zm-32 33c0 1.3-2 1.9-2 .5 0-.4-.3-1.8-.6-3.2-.5-2.4-.5-2.4 1-.6.9 1.1 1.6 2.6 1.6 3.3zm6-1.2c.6.8 1 1.9.8 2.4-.1.5-1.1-.2-2.1-1.5-1.9-2.6-.8-3.4 1.3-.9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M67 20.7c-1.4.3-2.9.9-3.4 1.4-.6.5-1.7.9-2.5.9-.9 0-3.7 1.1-6.4 2.3-4.5 2.2-8.5 6.8-15 17-1.8 2.8-1.8 2.9.2 3.4 1.2.3 2.1 1 2.1 1.5 0 .6-2.5-.4-5.6-2.2-3.1-1.7-6.2-2.9-7-2.6-1.9.7-1.8 3.2.2 4 1.1.4 1.4 1.8 1.2 5.3-.3 4.6-.5 4.9-4.6 6.8-4.2 2-5.4 4.1-2.8 5.1.8.3 4.2-.8 7.6-2.5 3.4-1.7 6.5-3.1 6.9-3.1.6 0 4.1 6.3 4.1 7.4 0 .3-2 1.2-11.8 5.2-3.8 1.6-4.6 2.5-4.3 4.6 0 .4-.8 1.2-1.9 1.8-2.4 1.3-1.3 5.4 1.6 5.5 2.3.1 8.4 2.7 8.4 3.5 0 .3-1.5 1.1-3.4 1.8-6.3 2.3-8.1 6.8-3.6 9.2 1.3.7 4.6-.3 13-4.1 7.9-3.4 11.9-4.7 13.4-4.2 3 1 2.8.9 6.3 1.8 1.7.4 3.4 1.3 3.7 2.1.6 1.6-1 1.9-1.9.4-1.1-1.7-1.9-1.1-5.9 4.5-2.7 3.7-4.2 5.1-4.7 4.3-.6-.9-1.3-.7-2.8.6-2.1 1.9-3 5.3-1.1 4.1.6-.3 1 .3 1 1.5 0 1.7.4 2 2.1 1.5 1.5-.5 1.9-.4 1.5.4-1 1.6 2 1.4 5.4-.4 1.7-.9 4.4-1.3 6.5-1 2.1.2 3.3 0 3-.5-.4-.6 1.5-.9 4.3-.9 4.9.2 9.2-1.3 9.2-3.1 0-.6-.9-1.9-2-3s-2-2.5-2-3c0-1.5 1.9-1.2 3.7.6.9.8 1.9 1.2 2.2.7.3-.5 1.9-1.7 3.5-2.7l3-1.9 2.3 2.8c2.1 2.4 2.7 2.6 4.9 1.6 1.9-.9 2.3-1.5 1.5-2.8-.5-1-1.2-5.6-1.6-10.4-.7-8.1-.6-8.5 1.5-9.5 2.1-.9 1.7-1.3-5.1-6.6-12.1-9.2-12.2-9.4-11.4-17.7.4-3.9 1.3-7.5 2-8.1 1.1-.9.4-2.5-3.4-7.9-2.6-3.8-5.6-9-6.6-11.5-1.1-2.5-2.2-4.5-2.5-4.5-.3.1-1.6.4-3 .6zm-32 33c0 1.3-2 1.9-2 .5 0-.4-.3-1.8-.6-3.2-.5-2.4-.5-2.4 1-.6.9 1.1 1.6 2.6 1.6 3.3zm6-1.2c.6.8 1 1.9.8 2.4-.1.5-1.1-.2-2.1-1.5-1.9-2.6-.8-3.4 1.3-.9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M67 20.7c-1.4.3-2.9.9-3.4 1.4-.6.5-1.7.9-2.5.9-.9 0-3.7 1.1-6.4 2.3-4.5 2.2-8.5 6.8-15 17-1.8 2.8-1.8 2.9.2 3.4 1.2.3 2.1 1 2.1 1.5 0 .6-2.5-.4-5.6-2.2-3.1-1.7-6.2-2.9-7-2.6-1.9.7-1.8 3.2.2 4 1.1.4 1.4 1.8 1.2 5.3-.3 4.6-.5 4.9-4.6 6.8-4.2 2-5.4 4.1-2.8 5.1.8.3 4.2-.8 7.6-2.5 3.4-1.7 6.5-3.1 6.9-3.1.6 0 4.1 6.3 4.1 7.4 0 .3-2 1.2-11.8 5.2-3.8 1.6-4.6 2.5-4.3 4.6 0 .4-.8 1.2-1.9 1.8-2.4 1.3-1.3 5.4 1.6 5.5 2.3.1 8.4 2.7 8.4 3.5 0 .3-1.5 1.1-3.4 1.8-6.3 2.3-8.1 6.8-3.6 9.2 1.3.7 4.6-.3 13-4.1 7.9-3.4 11.9-4.7 13.4-4.2 3 1 2.8.9 6.3 1.8 1.7.4 3.4 1.3 3.7 2.1.6 1.6-1 1.9-1.9.4-1.1-1.7-1.9-1.1-5.9 4.5-2.7 3.7-4.2 5.1-4.7 4.3-.6-.9-1.3-.7-2.8.6-2.1 1.9-3 5.3-1.1 4.1.6-.3 1 .3 1 1.5 0 1.7.4 2 2.1 1.5 1.5-.5 1.9-.4 1.5.4-1 1.6 2 1.4 5.4-.4 1.7-.9 4.4-1.3 6.5-1 2.1.2 3.3 0 3-.5-.4-.6 1.5-.9 4.3-.9 4.9.2 9.2-1.3 9.2-3.1 0-.6-.9-1.9-2-3s-2-2.5-2-3c0-1.5 1.9-1.2 3.7.6.9.8 1.9 1.2 2.2.7.3-.5 1.9-1.7 3.5-2.7l3-1.9 2.3 2.8c2.1 2.4 2.7 2.6 4.9 1.6 1.9-.9 2.3-1.5 1.5-2.8-.5-1-1.2-5.6-1.6-10.4-.7-8.1-.6-8.5 1.5-9.5 2.1-.9 1.7-1.3-5.1-6.6-12.1-9.2-12.2-9.4-11.4-17.7.4-3.9 1.3-7.5 2-8.1 1.1-.9.4-2.5-3.4-7.9-2.6-3.8-5.6-9-6.6-11.5-1.1-2.5-2.2-4.5-2.5-4.5-.3.1-1.6.4-3 .6zm-32 33c0 1.3-2 1.9-2 .5 0-.4-.3-1.8-.6-3.2-.5-2.4-.5-2.4 1-.6.9 1.1 1.6 2.6 1.6 3.3zm6-1.2c.6.8 1 1.9.8 2.4-.1.5-1.1-.2-2.1-1.5-1.9-2.6-.8-3.4 1.3-.9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				
				break;
				
				case 6: // 
					$sVG = '<svg class="buildingShape g6" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120">
		<g class="clickShape">
			<path d="M46.3 25.9c-.3 1.2-.8 2.1-1.2 2.1-.7 0-9 11.5-12.9 18.1-1.2 2-2.7 6.3-3.3 9.5-1.9 11.2-4.7 17.6-9.2 21.5l-4.1 3.6 1.3 5.9c.7 3.2 1.5 6.8 1.7 7.9.2 1.1 3.1 4.9 6.5 8.3 5.5 5.7 6.5 6.3 9.2 5.8 1.6-.3 4.2-.6 5.8-.6 1.5 0 3.4-.7 4.2-1.4 1.8-1.8 27-5.6 40.5-6.1 8.3-.3 9.7-.6 13-3.1 2-1.5 3.8-2.8 3.9-2.9.4-.2-3.3-9.5-5.6-14.2C95 78 93.7 72.9 93.4 69c-.7-6.8-.9-7.3-3.8-8.7-1.9-.9-5.1-4.5-8.1-9-6.1-9.3-5.9-8.9-8.3-13.8-1.7-3.5-2.4-4.1-6.3-4.6-2.4-.4-7.9-2.6-12.2-4.9-7.5-4-7.9-4.1-8.4-2.1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M46.3 25.9c-.3 1.2-.8 2.1-1.2 2.1-.7 0-9 11.5-12.9 18.1-1.2 2-2.7 6.3-3.3 9.5-1.9 11.2-4.7 17.6-9.2 21.5l-4.1 3.6 1.3 5.9c.7 3.2 1.5 6.8 1.7 7.9.2 1.1 3.1 4.9 6.5 8.3 5.5 5.7 6.5 6.3 9.2 5.8 1.6-.3 4.2-.6 5.8-.6 1.5 0 3.4-.7 4.2-1.4 1.8-1.8 27-5.6 40.5-6.1 8.3-.3 9.7-.6 13-3.1 2-1.5 3.8-2.8 3.9-2.9.4-.2-3.3-9.5-5.6-14.2C95 78 93.7 72.9 93.4 69c-.7-6.8-.9-7.3-3.8-8.7-1.9-.9-5.1-4.5-8.1-9-6.1-9.3-5.9-8.9-8.3-13.8-1.7-3.5-2.4-4.1-6.3-4.6-2.4-.4-7.9-2.6-12.2-4.9-7.5-4-7.9-4.1-8.4-2.1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M46.3 25.9c-.3 1.2-.8 2.1-1.2 2.1-.7 0-9 11.5-12.9 18.1-1.2 2-2.7 6.3-3.3 9.5-1.9 11.2-4.7 17.6-9.2 21.5l-4.1 3.6 1.3 5.9c.7 3.2 1.5 6.8 1.7 7.9.2 1.1 3.1 4.9 6.5 8.3 5.5 5.7 6.5 6.3 9.2 5.8 1.6-.3 4.2-.6 5.8-.6 1.5 0 3.4-.7 4.2-1.4 1.8-1.8 27-5.6 40.5-6.1 8.3-.3 9.7-.6 13-3.1 2-1.5 3.8-2.8 3.9-2.9.4-.2-3.3-9.5-5.6-14.2C95 78 93.7 72.9 93.4 69c-.7-6.8-.9-7.3-3.8-8.7-1.9-.9-5.1-4.5-8.1-9-6.1-9.3-5.9-8.9-8.3-13.8-1.7-3.5-2.4-4.1-6.3-4.6-2.4-.4-7.9-2.6-12.2-4.9-7.5-4-7.9-4.1-8.4-2.1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M46.3 25.9c-.3 1.2-.8 2.1-1.2 2.1-.7 0-9 11.5-12.9 18.1-1.2 2-2.7 6.3-3.3 9.5-1.9 11.2-4.7 17.6-9.2 21.5l-4.1 3.6 1.3 5.9c.7 3.2 1.5 6.8 1.7 7.9.2 1.1 3.1 4.9 6.5 8.3 5.5 5.7 6.5 6.3 9.2 5.8 1.6-.3 4.2-.6 5.8-.6 1.5 0 3.4-.7 4.2-1.4 1.8-1.8 27-5.6 40.5-6.1 8.3-.3 9.7-.6 13-3.1 2-1.5 3.8-2.8 3.9-2.9.4-.2-3.3-9.5-5.6-14.2C95 78 93.7 72.9 93.4 69c-.7-6.8-.9-7.3-3.8-8.7-1.9-.9-5.1-4.5-8.1-9-6.1-9.3-5.9-8.9-8.3-13.8-1.7-3.5-2.4-4.1-6.3-4.6-2.4-.4-7.9-2.6-12.2-4.9-7.5-4-7.9-4.1-8.4-2.1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				
				break;
				
				case 7: // 
					$sVG = '<svg class="buildingShape g7" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120">
		<g class="clickShape">
			<path d="M36.5 32c-.4.6-.9 5.9-1.2 11.8-.3 5.9-.8 12.9-1.2 15.7l-.6 5-5.5.5c-7.3.7-8.2 1.4-7.7 5.9.3 3.3.1 3.8-1.5 3.3-1.4-.3-1.8.1-1.8 2.1 0 1.4-.7 2.7-1.5 3.1-.8.3-1.5 1.4-1.5 2.4 0 1.1-.7 2.5-1.5 3.2-3.5 2.9 0 7.4 9.3 11.9 7.4 3.7 15.5 5.4 27 5.6 5.4.1 9.6.6 10 1.2.4.6 2.4 1.5 4.4 2.1 4.7 1.3 10.7 0 13-2.9 1-1.1 2.9-2.2 4.3-2.3 1.8-.1 3-1.1 4.1-3.4.9-1.9 2.2-3.2 3.4-3.2 4.6 0 13.1-4.4 17.5-9.1 4.9-5.2 5.3-6.5 3.1-10.8C107 71 100.3 66 97.7 66c-1.3 0-1.7-.9-1.7-4.5 0-2.5.4-4.5 1-4.5.5 0 1.4-.8 1.9-1.8.8-1.5.3-2.7-2.7-5.8-2-2.2-5.7-6.6-8.2-9.8-2.4-3.2-5-6-5.6-6.3-.7-.2-2.7.4-4.6 1.3-1.8 1-4 1.9-5 2.1-.9.1-3.1 1.1-5 2.2-1.8 1-5.1 2.4-7.3 3.1-4.7 1.5-7.7 3.3-12.5 7.4l-3.5 3-.6-3c-.4-1.6-.7-5.8-.8-9.5 0-3.6-.6-7-1.3-7.7-1.5-1.5-4.5-1.6-5.3-.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M36.5 32c-.4.6-.9 5.9-1.2 11.8-.3 5.9-.8 12.9-1.2 15.7l-.6 5-5.5.5c-7.3.7-8.2 1.4-7.7 5.9.3 3.3.1 3.8-1.5 3.3-1.4-.3-1.8.1-1.8 2.1 0 1.4-.7 2.7-1.5 3.1-.8.3-1.5 1.4-1.5 2.4 0 1.1-.7 2.5-1.5 3.2-3.5 2.9 0 7.4 9.3 11.9 7.4 3.7 15.5 5.4 27 5.6 5.4.1 9.6.6 10 1.2.4.6 2.4 1.5 4.4 2.1 4.7 1.3 10.7 0 13-2.9 1-1.1 2.9-2.2 4.3-2.3 1.8-.1 3-1.1 4.1-3.4.9-1.9 2.2-3.2 3.4-3.2 4.6 0 13.1-4.4 17.5-9.1 4.9-5.2 5.3-6.5 3.1-10.8C107 71 100.3 66 97.7 66c-1.3 0-1.7-.9-1.7-4.5 0-2.5.4-4.5 1-4.5.5 0 1.4-.8 1.9-1.8.8-1.5.3-2.7-2.7-5.8-2-2.2-5.7-6.6-8.2-9.8-2.4-3.2-5-6-5.6-6.3-.7-.2-2.7.4-4.6 1.3-1.8 1-4 1.9-5 2.1-.9.1-3.1 1.1-5 2.2-1.8 1-5.1 2.4-7.3 3.1-4.7 1.5-7.7 3.3-12.5 7.4l-3.5 3-.6-3c-.4-1.6-.7-5.8-.8-9.5 0-3.6-.6-7-1.3-7.7-1.5-1.5-4.5-1.6-5.3-.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M36.5 32c-.4.6-.9 5.9-1.2 11.8-.3 5.9-.8 12.9-1.2 15.7l-.6 5-5.5.5c-7.3.7-8.2 1.4-7.7 5.9.3 3.3.1 3.8-1.5 3.3-1.4-.3-1.8.1-1.8 2.1 0 1.4-.7 2.7-1.5 3.1-.8.3-1.5 1.4-1.5 2.4 0 1.1-.7 2.5-1.5 3.2-3.5 2.9 0 7.4 9.3 11.9 7.4 3.7 15.5 5.4 27 5.6 5.4.1 9.6.6 10 1.2.4.6 2.4 1.5 4.4 2.1 4.7 1.3 10.7 0 13-2.9 1-1.1 2.9-2.2 4.3-2.3 1.8-.1 3-1.1 4.1-3.4.9-1.9 2.2-3.2 3.4-3.2 4.6 0 13.1-4.4 17.5-9.1 4.9-5.2 5.3-6.5 3.1-10.8C107 71 100.3 66 97.7 66c-1.3 0-1.7-.9-1.7-4.5 0-2.5.4-4.5 1-4.5.5 0 1.4-.8 1.9-1.8.8-1.5.3-2.7-2.7-5.8-2-2.2-5.7-6.6-8.2-9.8-2.4-3.2-5-6-5.6-6.3-.7-.2-2.7.4-4.6 1.3-1.8 1-4 1.9-5 2.1-.9.1-3.1 1.1-5 2.2-1.8 1-5.1 2.4-7.3 3.1-4.7 1.5-7.7 3.3-12.5 7.4l-3.5 3-.6-3c-.4-1.6-.7-5.8-.8-9.5 0-3.6-.6-7-1.3-7.7-1.5-1.5-4.5-1.6-5.3-.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M36.5 32c-.4.6-.9 5.9-1.2 11.8-.3 5.9-.8 12.9-1.2 15.7l-.6 5-5.5.5c-7.3.7-8.2 1.4-7.7 5.9.3 3.3.1 3.8-1.5 3.3-1.4-.3-1.8.1-1.8 2.1 0 1.4-.7 2.7-1.5 3.1-.8.3-1.5 1.4-1.5 2.4 0 1.1-.7 2.5-1.5 3.2-3.5 2.9 0 7.4 9.3 11.9 7.4 3.7 15.5 5.4 27 5.6 5.4.1 9.6.6 10 1.2.4.6 2.4 1.5 4.4 2.1 4.7 1.3 10.7 0 13-2.9 1-1.1 2.9-2.2 4.3-2.3 1.8-.1 3-1.1 4.1-3.4.9-1.9 2.2-3.2 3.4-3.2 4.6 0 13.1-4.4 17.5-9.1 4.9-5.2 5.3-6.5 3.1-10.8C107 71 100.3 66 97.7 66c-1.3 0-1.7-.9-1.7-4.5 0-2.5.4-4.5 1-4.5.5 0 1.4-.8 1.9-1.8.8-1.5.3-2.7-2.7-5.8-2-2.2-5.7-6.6-8.2-9.8-2.4-3.2-5-6-5.6-6.3-.7-.2-2.7.4-4.6 1.3-1.8 1-4 1.9-5 2.1-.9.1-3.1 1.1-5 2.2-1.8 1-5.1 2.4-7.3 3.1-4.7 1.5-7.7 3.3-12.5 7.4l-3.5 3-.6-3c-.4-1.6-.7-5.8-.8-9.5 0-3.6-.6-7-1.3-7.7-1.5-1.5-4.5-1.6-5.3-.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				
				break;
				
				case 8: // 
					$sVG = '<svg class="buildingShape g8" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
		<g class="clickShape">
			<path d="M56.6 8.2C55 12.8 52.4 19.4 50.9 23l-2.7 6.4-5.8-11.7C39.2 11.3 36.1 6 35.5 6c-.6 0-2.4 1.4-4.1 3.1-1.8 1.8-3.6 2.8-4.7 2.5-2.2-.5-4.4 3.1-2.8 4.7.7.7.4 2.2-1 4.9-3.3 6.4-3.1 6.5 14.4 10.3 4.2.9 7.7 2 7.7 2.5s-4.8 5-10.7 10.1c-6 5-10.9 9.5-11.1 9.9-.1.3.9 1.9 2.2 3.5 1.4 1.6 2.3 3.2 2 3.6-.8 1.3 1.8 2.3 6.8 2.5 4.8.2 4.8.2 6-3.7.8-2.7 1.5-3.6 2.2-2.9.5.5 1.8 1 2.8 1 2.7 0 2.2 2.8-1.3 6.3-3 3-3.1 3.3-2.6 9.5.5 4.6.2 7.2-.8 9.2-.8 1.6-1.2 3.7-.8 4.7.5 1.5.3 1.6-.8.6-1.8-1.8-4.9-1.6-4.9.2 0 .8.8 1.5 1.9 1.5 1 0 3.5.7 5.6 1.5 2.8 1.2 3.4 1.9 2.6 2.9-1.3 1.5-.5 2.6 2 2.6.9 0 1.9.7 2.3 1.5.6 1.8 4.3 2 4.8.4.3-.9.9-.9 2.6 0 1.6.9 3.1.8 6.4-.3 3-1.1 4.4-1.2 4.9-.4.4.6 2 1 3.6 1 8.6-.5 11.2-1.7 9.5-4.7-.5-1-.7-3.7-.4-6 .2-2.6 0-4.5-.7-4.9-.8-.5-.1-1.8 2-3.7 2.9-2.7 3.1-3.3 2.7-8-.4-3.4-1.3-5.8-2.9-7.5-2.4-2.6-3.2-5-2.4-7.3.4-1 .8-1.1 2-.1 4 3.3 4.6-3.2 1-10.5-1.3-2.8-2.5-6-2.5-7.3 0-1.2-.4-2.2-1-2.2-.5 0-1-1.3-1-3 0-2.2-.7-3.3-2.7-4.4l-2.8-1.4h3.7c5 0 5.8-.7 5.8-5.3 0-4.2-1.3-4.9-5.2-2.8-1.8 1-2.4.8-3.9-1.2-2.1-2.7-2.1-2.5-1.9 4.8l.1 5.3h-3.4c-1.9 0-4.5-.8-5.7-1.8l-2.2-1.8 7.1-6.6 7.2-6.7-1.6-3c-.8-1.7-1.5-3.4-1.4-3.8.4-2.4-.3-3.5-1.9-3.1-1.1.3-3-.1-4.3-.8C62.6.7 61 .1 60.5 0c-.6 0-2.3 3.7-3.9 8.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M56.6 8.2C55 12.8 52.4 19.4 50.9 23l-2.7 6.4-5.8-11.7C39.2 11.3 36.1 6 35.5 6c-.6 0-2.4 1.4-4.1 3.1-1.8 1.8-3.6 2.8-4.7 2.5-2.2-.5-4.4 3.1-2.8 4.7.7.7.4 2.2-1 4.9-3.3 6.4-3.1 6.5 14.4 10.3 4.2.9 7.7 2 7.7 2.5s-4.8 5-10.7 10.1c-6 5-10.9 9.5-11.1 9.9-.1.3.9 1.9 2.2 3.5 1.4 1.6 2.3 3.2 2 3.6-.8 1.3 1.8 2.3 6.8 2.5 4.8.2 4.8.2 6-3.7.8-2.7 1.5-3.6 2.2-2.9.5.5 1.8 1 2.8 1 2.7 0 2.2 2.8-1.3 6.3-3 3-3.1 3.3-2.6 9.5.5 4.6.2 7.2-.8 9.2-.8 1.6-1.2 3.7-.8 4.7.5 1.5.3 1.6-.8.6-1.8-1.8-4.9-1.6-4.9.2 0 .8.8 1.5 1.9 1.5 1 0 3.5.7 5.6 1.5 2.8 1.2 3.4 1.9 2.6 2.9-1.3 1.5-.5 2.6 2 2.6.9 0 1.9.7 2.3 1.5.6 1.8 4.3 2 4.8.4.3-.9.9-.9 2.6 0 1.6.9 3.1.8 6.4-.3 3-1.1 4.4-1.2 4.9-.4.4.6 2 1 3.6 1 8.6-.5 11.2-1.7 9.5-4.7-.5-1-.7-3.7-.4-6 .2-2.6 0-4.5-.7-4.9-.8-.5-.1-1.8 2-3.7 2.9-2.7 3.1-3.3 2.7-8-.4-3.4-1.3-5.8-2.9-7.5-2.4-2.6-3.2-5-2.4-7.3.4-1 .8-1.1 2-.1 4 3.3 4.6-3.2 1-10.5-1.3-2.8-2.5-6-2.5-7.3 0-1.2-.4-2.2-1-2.2-.5 0-1-1.3-1-3 0-2.2-.7-3.3-2.7-4.4l-2.8-1.4h3.7c5 0 5.8-.7 5.8-5.3 0-4.2-1.3-4.9-5.2-2.8-1.8 1-2.4.8-3.9-1.2-2.1-2.7-2.1-2.5-1.9 4.8l.1 5.3h-3.4c-1.9 0-4.5-.8-5.7-1.8l-2.2-1.8 7.1-6.6 7.2-6.7-1.6-3c-.8-1.7-1.5-3.4-1.4-3.8.4-2.4-.3-3.5-1.9-3.1-1.1.3-3-.1-4.3-.8C62.6.7 61 .1 60.5 0c-.6 0-2.3 3.7-3.9 8.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M56.6 8.2C55 12.8 52.4 19.4 50.9 23l-2.7 6.4-5.8-11.7C39.2 11.3 36.1 6 35.5 6c-.6 0-2.4 1.4-4.1 3.1-1.8 1.8-3.6 2.8-4.7 2.5-2.2-.5-4.4 3.1-2.8 4.7.7.7.4 2.2-1 4.9-3.3 6.4-3.1 6.5 14.4 10.3 4.2.9 7.7 2 7.7 2.5s-4.8 5-10.7 10.1c-6 5-10.9 9.5-11.1 9.9-.1.3.9 1.9 2.2 3.5 1.4 1.6 2.3 3.2 2 3.6-.8 1.3 1.8 2.3 6.8 2.5 4.8.2 4.8.2 6-3.7.8-2.7 1.5-3.6 2.2-2.9.5.5 1.8 1 2.8 1 2.7 0 2.2 2.8-1.3 6.3-3 3-3.1 3.3-2.6 9.5.5 4.6.2 7.2-.8 9.2-.8 1.6-1.2 3.7-.8 4.7.5 1.5.3 1.6-.8.6-1.8-1.8-4.9-1.6-4.9.2 0 .8.8 1.5 1.9 1.5 1 0 3.5.7 5.6 1.5 2.8 1.2 3.4 1.9 2.6 2.9-1.3 1.5-.5 2.6 2 2.6.9 0 1.9.7 2.3 1.5.6 1.8 4.3 2 4.8.4.3-.9.9-.9 2.6 0 1.6.9 3.1.8 6.4-.3 3-1.1 4.4-1.2 4.9-.4.4.6 2 1 3.6 1 8.6-.5 11.2-1.7 9.5-4.7-.5-1-.7-3.7-.4-6 .2-2.6 0-4.5-.7-4.9-.8-.5-.1-1.8 2-3.7 2.9-2.7 3.1-3.3 2.7-8-.4-3.4-1.3-5.8-2.9-7.5-2.4-2.6-3.2-5-2.4-7.3.4-1 .8-1.1 2-.1 4 3.3 4.6-3.2 1-10.5-1.3-2.8-2.5-6-2.5-7.3 0-1.2-.4-2.2-1-2.2-.5 0-1-1.3-1-3 0-2.2-.7-3.3-2.7-4.4l-2.8-1.4h3.7c5 0 5.8-.7 5.8-5.3 0-4.2-1.3-4.9-5.2-2.8-1.8 1-2.4.8-3.9-1.2-2.1-2.7-2.1-2.5-1.9 4.8l.1 5.3h-3.4c-1.9 0-4.5-.8-5.7-1.8l-2.2-1.8 7.1-6.6 7.2-6.7-1.6-3c-.8-1.7-1.5-3.4-1.4-3.8.4-2.4-.3-3.5-1.9-3.1-1.1.3-3-.1-4.3-.8C62.6.7 61 .1 60.5 0c-.6 0-2.3 3.7-3.9 8.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M56.6 8.2C55 12.8 52.4 19.4 50.9 23l-2.7 6.4-5.8-11.7C39.2 11.3 36.1 6 35.5 6c-.6 0-2.4 1.4-4.1 3.1-1.8 1.8-3.6 2.8-4.7 2.5-2.2-.5-4.4 3.1-2.8 4.7.7.7.4 2.2-1 4.9-3.3 6.4-3.1 6.5 14.4 10.3 4.2.9 7.7 2 7.7 2.5s-4.8 5-10.7 10.1c-6 5-10.9 9.5-11.1 9.9-.1.3.9 1.9 2.2 3.5 1.4 1.6 2.3 3.2 2 3.6-.8 1.3 1.8 2.3 6.8 2.5 4.8.2 4.8.2 6-3.7.8-2.7 1.5-3.6 2.2-2.9.5.5 1.8 1 2.8 1 2.7 0 2.2 2.8-1.3 6.3-3 3-3.1 3.3-2.6 9.5.5 4.6.2 7.2-.8 9.2-.8 1.6-1.2 3.7-.8 4.7.5 1.5.3 1.6-.8.6-1.8-1.8-4.9-1.6-4.9.2 0 .8.8 1.5 1.9 1.5 1 0 3.5.7 5.6 1.5 2.8 1.2 3.4 1.9 2.6 2.9-1.3 1.5-.5 2.6 2 2.6.9 0 1.9.7 2.3 1.5.6 1.8 4.3 2 4.8.4.3-.9.9-.9 2.6 0 1.6.9 3.1.8 6.4-.3 3-1.1 4.4-1.2 4.9-.4.4.6 2 1 3.6 1 8.6-.5 11.2-1.7 9.5-4.7-.5-1-.7-3.7-.4-6 .2-2.6 0-4.5-.7-4.9-.8-.5-.1-1.8 2-3.7 2.9-2.7 3.1-3.3 2.7-8-.4-3.4-1.3-5.8-2.9-7.5-2.4-2.6-3.2-5-2.4-7.3.4-1 .8-1.1 2-.1 4 3.3 4.6-3.2 1-10.5-1.3-2.8-2.5-6-2.5-7.3 0-1.2-.4-2.2-1-2.2-.5 0-1-1.3-1-3 0-2.2-.7-3.3-2.7-4.4l-2.8-1.4h3.7c5 0 5.8-.7 5.8-5.3 0-4.2-1.3-4.9-5.2-2.8-1.8 1-2.4.8-3.9-1.2-2.1-2.7-2.1-2.5-1.9 4.8l.1 5.3h-3.4c-1.9 0-4.5-.8-5.7-1.8l-2.2-1.8 7.1-6.6 7.2-6.7-1.6-3c-.8-1.7-1.5-3.4-1.4-3.8.4-2.4-.3-3.5-1.9-3.1-1.1.3-3-.1-4.3-.8C62.6.7 61 .1 60.5 0c-.6 0-2.3 3.7-3.9 8.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				
				break;
				
				case 9: // 
					$sVG = '<svg class="buildingShape g9" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120">
		<g class="clickShape">
			<path d="M72.7 27.6c-.4.4-.7 2.2-.7 4.1 0 1.8-.3 3.2-.7 3.1-.5-.1-4.3.4-8.5 1.1-6 1.1-8.7 1.1-12.3.2-2.9-.7-5.2-.8-6.3-.2C42 37 38.4 41 39.5 41c.4 0 0 .9-.9 1.9-1 1.1-1.9 2.7-2 3.6-.1.9-2.4 3.7-5 6.1-2.6 2.4-4.5 4.8-4.1 5.4 1.2 1.9-1.4 3.8-6.1 4.5-2.5.3-4.7 1.2-5 2-.7 1.8 1.3 5.5 2.9 5.5.7 0 3.6 1.2 6.5 2.6l5.1 2.5-.1 5.6c-.1 4.5-.5 5.7-2 6.1-1 .2-1.8.8-1.8 1.3 0 1.1 3.6 3 4.3 2.2.4-.3.9.2 1.3 1.1.3.9 1.5 1.6 2.5 1.6 1.3 0 1.9.7 1.9 2.5 0 3.5 2.4 4.3 11.5 3.7 5.6-.3 8.4-.1 8.9.7.4.6 1.7 1.1 2.9 1.1 5.1 0 9.9 2.2 11.5 5.1 1.6 2.9 2 3 6.1 2.5 3.4-.5 5.3-1.5 8.1-4.4 2-2 5.7-4.9 8.2-6.3l4.6-2.7-.6-9c-.4-8.5-.4-9 1.7-9.7 3.4-1.1 2.4-3.3-2.8-5.9-4.2-2.2-4.9-2.9-5.3-6.3-.7-5-6.3-19.2-9.3-23.7-1.5-2.2-2.5-5.3-2.7-8.3-.3-4.5-.5-4.8-3.3-5.1-1.7-.2-3.4 0-3.8.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M72.7 27.6c-.4.4-.7 2.2-.7 4.1 0 1.8-.3 3.2-.7 3.1-.5-.1-4.3.4-8.5 1.1-6 1.1-8.7 1.1-12.3.2-2.9-.7-5.2-.8-6.3-.2C42 37 38.4 41 39.5 41c.4 0 0 .9-.9 1.9-1 1.1-1.9 2.7-2 3.6-.1.9-2.4 3.7-5 6.1-2.6 2.4-4.5 4.8-4.1 5.4 1.2 1.9-1.4 3.8-6.1 4.5-2.5.3-4.7 1.2-5 2-.7 1.8 1.3 5.5 2.9 5.5.7 0 3.6 1.2 6.5 2.6l5.1 2.5-.1 5.6c-.1 4.5-.5 5.7-2 6.1-1 .2-1.8.8-1.8 1.3 0 1.1 3.6 3 4.3 2.2.4-.3.9.2 1.3 1.1.3.9 1.5 1.6 2.5 1.6 1.3 0 1.9.7 1.9 2.5 0 3.5 2.4 4.3 11.5 3.7 5.6-.3 8.4-.1 8.9.7.4.6 1.7 1.1 2.9 1.1 5.1 0 9.9 2.2 11.5 5.1 1.6 2.9 2 3 6.1 2.5 3.4-.5 5.3-1.5 8.1-4.4 2-2 5.7-4.9 8.2-6.3l4.6-2.7-.6-9c-.4-8.5-.4-9 1.7-9.7 3.4-1.1 2.4-3.3-2.8-5.9-4.2-2.2-4.9-2.9-5.3-6.3-.7-5-6.3-19.2-9.3-23.7-1.5-2.2-2.5-5.3-2.7-8.3-.3-4.5-.5-4.8-3.3-5.1-1.7-.2-3.4 0-3.8.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M72.7 27.6c-.4.4-.7 2.2-.7 4.1 0 1.8-.3 3.2-.7 3.1-.5-.1-4.3.4-8.5 1.1-6 1.1-8.7 1.1-12.3.2-2.9-.7-5.2-.8-6.3-.2C42 37 38.4 41 39.5 41c.4 0 0 .9-.9 1.9-1 1.1-1.9 2.7-2 3.6-.1.9-2.4 3.7-5 6.1-2.6 2.4-4.5 4.8-4.1 5.4 1.2 1.9-1.4 3.8-6.1 4.5-2.5.3-4.7 1.2-5 2-.7 1.8 1.3 5.5 2.9 5.5.7 0 3.6 1.2 6.5 2.6l5.1 2.5-.1 5.6c-.1 4.5-.5 5.7-2 6.1-1 .2-1.8.8-1.8 1.3 0 1.1 3.6 3 4.3 2.2.4-.3.9.2 1.3 1.1.3.9 1.5 1.6 2.5 1.6 1.3 0 1.9.7 1.9 2.5 0 3.5 2.4 4.3 11.5 3.7 5.6-.3 8.4-.1 8.9.7.4.6 1.7 1.1 2.9 1.1 5.1 0 9.9 2.2 11.5 5.1 1.6 2.9 2 3 6.1 2.5 3.4-.5 5.3-1.5 8.1-4.4 2-2 5.7-4.9 8.2-6.3l4.6-2.7-.6-9c-.4-8.5-.4-9 1.7-9.7 3.4-1.1 2.4-3.3-2.8-5.9-4.2-2.2-4.9-2.9-5.3-6.3-.7-5-6.3-19.2-9.3-23.7-1.5-2.2-2.5-5.3-2.7-8.3-.3-4.5-.5-4.8-3.3-5.1-1.7-.2-3.4 0-3.8.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M72.7 27.6c-.4.4-.7 2.2-.7 4.1 0 1.8-.3 3.2-.7 3.1-.5-.1-4.3.4-8.5 1.1-6 1.1-8.7 1.1-12.3.2-2.9-.7-5.2-.8-6.3-.2C42 37 38.4 41 39.5 41c.4 0 0 .9-.9 1.9-1 1.1-1.9 2.7-2 3.6-.1.9-2.4 3.7-5 6.1-2.6 2.4-4.5 4.8-4.1 5.4 1.2 1.9-1.4 3.8-6.1 4.5-2.5.3-4.7 1.2-5 2-.7 1.8 1.3 5.5 2.9 5.5.7 0 3.6 1.2 6.5 2.6l5.1 2.5-.1 5.6c-.1 4.5-.5 5.7-2 6.1-1 .2-1.8.8-1.8 1.3 0 1.1 3.6 3 4.3 2.2.4-.3.9.2 1.3 1.1.3.9 1.5 1.6 2.5 1.6 1.3 0 1.9.7 1.9 2.5 0 3.5 2.4 4.3 11.5 3.7 5.6-.3 8.4-.1 8.9.7.4.6 1.7 1.1 2.9 1.1 5.1 0 9.9 2.2 11.5 5.1 1.6 2.9 2 3 6.1 2.5 3.4-.5 5.3-1.5 8.1-4.4 2-2 5.7-4.9 8.2-6.3l4.6-2.7-.6-9c-.4-8.5-.4-9 1.7-9.7 3.4-1.1 2.4-3.3-2.8-5.9-4.2-2.2-4.9-2.9-5.3-6.3-.7-5-6.3-19.2-9.3-23.7-1.5-2.2-2.5-5.3-2.7-8.3-.3-4.5-.5-4.8-3.3-5.1-1.7-.2-3.4 0-3.8.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				
				break;
				
				case 10:
					$sVG ='
					<svg class="buildingShape g10" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
	<g class="clickShape">
			<path d="M80 37.9c-.8.6-1.7.8-2.1.5-.4-.2-1.2.3-1.9 1.1S74.4 41 73.9 41s-3.4 1.2-6.6 2.6c-3.2 1.5-6.7 2.7-7.8 2.9-9.9 1-11.1 1.6-18.8 9.1-4.2 4-7.5 7.9-7.2 8.6.2.7-1.2 2.5-3.3 3.9-3.6 2.5-3.7 2.6-3.4 8.2.3 4.7.8 5.9 2.9 7.2 2.2 1.5 2.5 2.2 2 6.1-.3 3.2 0 4.7 1.1 5.6.8.7 1.7 1.2 1.9 1 .2-.1 2.5.7 5.1 1.8 3.5 1.6 5.7 2 8.7 1.5 14-2.3 33.1-4.6 41.5-4.8l4.5-.2-.3-17.8C94 65.1 94.3 59 95 59c1.6 0 1.2-2.2-.9-4.4-1.1-1.2-2.7-3.5-3.8-5.1-4.1-7-7.8-12.4-8.3-12.5-.3 0-1.2.4-2 .9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M80 37.9c-.8.6-1.7.8-2.1.5-.4-.2-1.2.3-1.9 1.1S74.4 41 73.9 41s-3.4 1.2-6.6 2.6c-3.2 1.5-6.7 2.7-7.8 2.9-9.9 1-11.1 1.6-18.8 9.1-4.2 4-7.5 7.9-7.2 8.6.2.7-1.2 2.5-3.3 3.9-3.6 2.5-3.7 2.6-3.4 8.2.3 4.7.8 5.9 2.9 7.2 2.2 1.5 2.5 2.2 2 6.1-.3 3.2 0 4.7 1.1 5.6.8.7 1.7 1.2 1.9 1 .2-.1 2.5.7 5.1 1.8 3.5 1.6 5.7 2 8.7 1.5 14-2.3 33.1-4.6 41.5-4.8l4.5-.2-.3-17.8C94 65.1 94.3 59 95 59c1.6 0 1.2-2.2-.9-4.4-1.1-1.2-2.7-3.5-3.8-5.1-4.1-7-7.8-12.4-8.3-12.5-.3 0-1.2.4-2 .9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M80 37.9c-.8.6-1.7.8-2.1.5-.4-.2-1.2.3-1.9 1.1S74.4 41 73.9 41s-3.4 1.2-6.6 2.6c-3.2 1.5-6.7 2.7-7.8 2.9-9.9 1-11.1 1.6-18.8 9.1-4.2 4-7.5 7.9-7.2 8.6.2.7-1.2 2.5-3.3 3.9-3.6 2.5-3.7 2.6-3.4 8.2.3 4.7.8 5.9 2.9 7.2 2.2 1.5 2.5 2.2 2 6.1-.3 3.2 0 4.7 1.1 5.6.8.7 1.7 1.2 1.9 1 .2-.1 2.5.7 5.1 1.8 3.5 1.6 5.7 2 8.7 1.5 14-2.3 33.1-4.6 41.5-4.8l4.5-.2-.3-17.8C94 65.1 94.3 59 95 59c1.6 0 1.2-2.2-.9-4.4-1.1-1.2-2.7-3.5-3.8-5.1-4.1-7-7.8-12.4-8.3-12.5-.3 0-1.2.4-2 .9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M80 37.9c-.8.6-1.7.8-2.1.5-.4-.2-1.2.3-1.9 1.1S74.4 41 73.9 41s-3.4 1.2-6.6 2.6c-3.2 1.5-6.7 2.7-7.8 2.9-9.9 1-11.1 1.6-18.8 9.1-4.2 4-7.5 7.9-7.2 8.6.2.7-1.2 2.5-3.3 3.9-3.6 2.5-3.7 2.6-3.4 8.2.3 4.7.8 5.9 2.9 7.2 2.2 1.5 2.5 2.2 2 6.1-.3 3.2 0 4.7 1.1 5.6.8.7 1.7 1.2 1.9 1 .2-.1 2.5.7 5.1 1.8 3.5 1.6 5.7 2 8.7 1.5 14-2.3 33.1-4.6 41.5-4.8l4.5-.2-.3-17.8C94 65.1 94.3 59 95 59c1.6 0 1.2-2.2-.9-4.4-1.1-1.2-2.7-3.5-3.8-5.1-4.1-7-7.8-12.4-8.3-12.5-.3 0-1.2.4-2 .9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				break;

				case 11:
					$sVG = '
					<svg class="buildingShape g11" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
	<g class="clickShape">
			<path d="M51.5 22.5c-3.3 2.5-7.9 5.8-10.2 7.5-3.3 2.4-4.3 3.7-4.3 5.9 0 1.6.7 3.4 1.5 4.1.8.7 1.5 2.3 1.5 3.7 0 2.2-.9 3-4.2 3.7-7.8 1.8-7.9 1.8-7.2 4 .3 1.1 1.3 2.8 2 3.7.8.8 1.4 2.2 1.4 3 0 .8.7 2.3 1.5 3.3.8 1.1 1.5 2.5 1.5 3 0 .6-.4.5-.9-.3-.6-.9-1.1-1-1.5-.2-.8 1.2 2.1 2.8 3.1 1.7.4-.4.8-1.4 1-2.4 1.5-9 3.3-12 3.3-5.5 0 2.1.2 6.8.5 10.5.4 6.7.3 6.8-2.1 6.8-2.1 0-2.4.4-2.4 4 0 3.9-.1 4-2.3 2.8-1.2-.7-2.5-1.8-2.9-2.3-.5-.6-1.4-.4-2.4.7-1.6 1.5-1.6 1.9-.1 4.1.9 1.4 1.3 3.4 1 4.5-.6 2.5 2.7 4.6 7.2 4.4 2.3-.1 3.3.5 4.2 2.5.7 1.4 1 2.9.7 3.4-1 1.7 5.9 4.1 10.3 3.6 3.9-.4 4.5-.2 5.3 1.9 1.6 4.1 11.9 5.8 16.1 2.6 1.3-1 5.7-3.8 9.7-6.2 8.6-5.1 10.9-7.8 9.2-11-1.1-2-1-5.2.2-11.3.2-1.4-1.3-2.4-7.1-4.7-4.1-1.6-7.9-3.4-8.4-3.9-.6-.6-1.2-7.1-1.3-14.8-.2-10.5.1-14.1 1.2-15.7 2.7-3.9 1.8-7.3-2.8-10.6-2.2-1.6-4.6-3-5.3-3-.7 0-3.3-1.8-5.8-4s-4.7-4-4.9-3.9c-.1 0-3 2-6.3 4.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M51.5 22.5c-3.3 2.5-7.9 5.8-10.2 7.5-3.3 2.4-4.3 3.7-4.3 5.9 0 1.6.7 3.4 1.5 4.1.8.7 1.5 2.3 1.5 3.7 0 2.2-.9 3-4.2 3.7-7.8 1.8-7.9 1.8-7.2 4 .3 1.1 1.3 2.8 2 3.7.8.8 1.4 2.2 1.4 3 0 .8.7 2.3 1.5 3.3.8 1.1 1.5 2.5 1.5 3 0 .6-.4.5-.9-.3-.6-.9-1.1-1-1.5-.2-.8 1.2 2.1 2.8 3.1 1.7.4-.4.8-1.4 1-2.4 1.5-9 3.3-12 3.3-5.5 0 2.1.2 6.8.5 10.5.4 6.7.3 6.8-2.1 6.8-2.1 0-2.4.4-2.4 4 0 3.9-.1 4-2.3 2.8-1.2-.7-2.5-1.8-2.9-2.3-.5-.6-1.4-.4-2.4.7-1.6 1.5-1.6 1.9-.1 4.1.9 1.4 1.3 3.4 1 4.5-.6 2.5 2.7 4.6 7.2 4.4 2.3-.1 3.3.5 4.2 2.5.7 1.4 1 2.9.7 3.4-1 1.7 5.9 4.1 10.3 3.6 3.9-.4 4.5-.2 5.3 1.9 1.6 4.1 11.9 5.8 16.1 2.6 1.3-1 5.7-3.8 9.7-6.2 8.6-5.1 10.9-7.8 9.2-11-1.1-2-1-5.2.2-11.3.2-1.4-1.3-2.4-7.1-4.7-4.1-1.6-7.9-3.4-8.4-3.9-.6-.6-1.2-7.1-1.3-14.8-.2-10.5.1-14.1 1.2-15.7 2.7-3.9 1.8-7.3-2.8-10.6-2.2-1.6-4.6-3-5.3-3-.7 0-3.3-1.8-5.8-4s-4.7-4-4.9-3.9c-.1 0-3 2-6.3 4.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M51.5 22.5c-3.3 2.5-7.9 5.8-10.2 7.5-3.3 2.4-4.3 3.7-4.3 5.9 0 1.6.7 3.4 1.5 4.1.8.7 1.5 2.3 1.5 3.7 0 2.2-.9 3-4.2 3.7-7.8 1.8-7.9 1.8-7.2 4 .3 1.1 1.3 2.8 2 3.7.8.8 1.4 2.2 1.4 3 0 .8.7 2.3 1.5 3.3.8 1.1 1.5 2.5 1.5 3 0 .6-.4.5-.9-.3-.6-.9-1.1-1-1.5-.2-.8 1.2 2.1 2.8 3.1 1.7.4-.4.8-1.4 1-2.4 1.5-9 3.3-12 3.3-5.5 0 2.1.2 6.8.5 10.5.4 6.7.3 6.8-2.1 6.8-2.1 0-2.4.4-2.4 4 0 3.9-.1 4-2.3 2.8-1.2-.7-2.5-1.8-2.9-2.3-.5-.6-1.4-.4-2.4.7-1.6 1.5-1.6 1.9-.1 4.1.9 1.4 1.3 3.4 1 4.5-.6 2.5 2.7 4.6 7.2 4.4 2.3-.1 3.3.5 4.2 2.5.7 1.4 1 2.9.7 3.4-1 1.7 5.9 4.1 10.3 3.6 3.9-.4 4.5-.2 5.3 1.9 1.6 4.1 11.9 5.8 16.1 2.6 1.3-1 5.7-3.8 9.7-6.2 8.6-5.1 10.9-7.8 9.2-11-1.1-2-1-5.2.2-11.3.2-1.4-1.3-2.4-7.1-4.7-4.1-1.6-7.9-3.4-8.4-3.9-.6-.6-1.2-7.1-1.3-14.8-.2-10.5.1-14.1 1.2-15.7 2.7-3.9 1.8-7.3-2.8-10.6-2.2-1.6-4.6-3-5.3-3-.7 0-3.3-1.8-5.8-4s-4.7-4-4.9-3.9c-.1 0-3 2-6.3 4.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M51.5 22.5c-3.3 2.5-7.9 5.8-10.2 7.5-3.3 2.4-4.3 3.7-4.3 5.9 0 1.6.7 3.4 1.5 4.1.8.7 1.5 2.3 1.5 3.7 0 2.2-.9 3-4.2 3.7-7.8 1.8-7.9 1.8-7.2 4 .3 1.1 1.3 2.8 2 3.7.8.8 1.4 2.2 1.4 3 0 .8.7 2.3 1.5 3.3.8 1.1 1.5 2.5 1.5 3 0 .6-.4.5-.9-.3-.6-.9-1.1-1-1.5-.2-.8 1.2 2.1 2.8 3.1 1.7.4-.4.8-1.4 1-2.4 1.5-9 3.3-12 3.3-5.5 0 2.1.2 6.8.5 10.5.4 6.7.3 6.8-2.1 6.8-2.1 0-2.4.4-2.4 4 0 3.9-.1 4-2.3 2.8-1.2-.7-2.5-1.8-2.9-2.3-.5-.6-1.4-.4-2.4.7-1.6 1.5-1.6 1.9-.1 4.1.9 1.4 1.3 3.4 1 4.5-.6 2.5 2.7 4.6 7.2 4.4 2.3-.1 3.3.5 4.2 2.5.7 1.4 1 2.9.7 3.4-1 1.7 5.9 4.1 10.3 3.6 3.9-.4 4.5-.2 5.3 1.9 1.6 4.1 11.9 5.8 16.1 2.6 1.3-1 5.7-3.8 9.7-6.2 8.6-5.1 10.9-7.8 9.2-11-1.1-2-1-5.2.2-11.3.2-1.4-1.3-2.4-7.1-4.7-4.1-1.6-7.9-3.4-8.4-3.9-.6-.6-1.2-7.1-1.3-14.8-.2-10.5.1-14.1 1.2-15.7 2.7-3.9 1.8-7.3-2.8-10.6-2.2-1.6-4.6-3-5.3-3-.7 0-3.3-1.8-5.8-4s-4.7-4-4.9-3.9c-.1 0-3 2-6.3 4.4z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				break;


				case 13:
				case 12:
					$sVG = '<svg class="buildingShape g13" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
		<g class="clickShape">
			<path d="M36.1 27.5c-2.9 1.6-4.1 3.6-5.2 8.6-.5 2.1-2.3 6.2-4 9-3 5.2-3.1 5.5-2.4 14 .6 8.1.6 8.8-1.4 10.1-1.7 1.2-2.1 2.7-2.3 7.6-.2 7.3 0 7.7 5 8.1.9 0 2.3 1 3 2.2 1 1.6 1 2 0 1.8-2.9-.4-3.8.2-3.8 2.4 0 3.1 3.5 4.7 7.5 3.5 3.6-1 7-.3 10.6 2.3 1.5 1 3.6 1.9 4.8 1.9 1.1 0 2.9 1 4.1 2.2 1.1 1.2 3 2.3 4.3 2.3 1.2.1 4.2.7 6.7 1.4 5.7 1.7 28.8 1 34.5-1 3.7-1.3 4-1.7 4.3-5.4.2-3.4 1.2-4.9 5.2-8.9 3.2-3.1 5-5.8 5-7.3 0-1.2-.4-2.3-.9-2.3s-1.4-1.4-2-3.1c-.9-2.6-1.3-2.9-3.1-1.9s-2 .8-2-2c0-1.7.7-4.1 1.6-5.5 1.5-2.3 1.5-2.4-7.5-7-8.4-4.3-9.1-4.9-9.1-7.7 0-1.6-1.1-4.9-2.5-7.4s-2.3-5-2-5.5c.4-.5.3-.9-.2-.9-.4 0-.9-2.4-1.1-5.3-.3-5.9-2.1-8-5.5-6.8-1.8.7-2.3 1.8-2.5 5.9-.3 4.8-.4 5.1-2.2 4-1.5-1-2.3-.9-4 .7-1.7 1.6-2.4 1.7-4 .7-1.1-.7-2.9-1.2-4-1.2-2.6 0-7.1-2.2-13.2-6.6-5.7-4-8.5-4.7-11.7-2.9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M36.1 27.5c-2.9 1.6-4.1 3.6-5.2 8.6-.5 2.1-2.3 6.2-4 9-3 5.2-3.1 5.5-2.4 14 .6 8.1.6 8.8-1.4 10.1-1.7 1.2-2.1 2.7-2.3 7.6-.2 7.3 0 7.7 5 8.1.9 0 2.3 1 3 2.2 1 1.6 1 2 0 1.8-2.9-.4-3.8.2-3.8 2.4 0 3.1 3.5 4.7 7.5 3.5 3.6-1 7-.3 10.6 2.3 1.5 1 3.6 1.9 4.8 1.9 1.1 0 2.9 1 4.1 2.2 1.1 1.2 3 2.3 4.3 2.3 1.2.1 4.2.7 6.7 1.4 5.7 1.7 28.8 1 34.5-1 3.7-1.3 4-1.7 4.3-5.4.2-3.4 1.2-4.9 5.2-8.9 3.2-3.1 5-5.8 5-7.3 0-1.2-.4-2.3-.9-2.3s-1.4-1.4-2-3.1c-.9-2.6-1.3-2.9-3.1-1.9s-2 .8-2-2c0-1.7.7-4.1 1.6-5.5 1.5-2.3 1.5-2.4-7.5-7-8.4-4.3-9.1-4.9-9.1-7.7 0-1.6-1.1-4.9-2.5-7.4s-2.3-5-2-5.5c.4-.5.3-.9-.2-.9-.4 0-.9-2.4-1.1-5.3-.3-5.9-2.1-8-5.5-6.8-1.8.7-2.3 1.8-2.5 5.9-.3 4.8-.4 5.1-2.2 4-1.5-1-2.3-.9-4 .7-1.7 1.6-2.4 1.7-4 .7-1.1-.7-2.9-1.2-4-1.2-2.6 0-7.1-2.2-13.2-6.6-5.7-4-8.5-4.7-11.7-2.9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M36.1 27.5c-2.9 1.6-4.1 3.6-5.2 8.6-.5 2.1-2.3 6.2-4 9-3 5.2-3.1 5.5-2.4 14 .6 8.1.6 8.8-1.4 10.1-1.7 1.2-2.1 2.7-2.3 7.6-.2 7.3 0 7.7 5 8.1.9 0 2.3 1 3 2.2 1 1.6 1 2 0 1.8-2.9-.4-3.8.2-3.8 2.4 0 3.1 3.5 4.7 7.5 3.5 3.6-1 7-.3 10.6 2.3 1.5 1 3.6 1.9 4.8 1.9 1.1 0 2.9 1 4.1 2.2 1.1 1.2 3 2.3 4.3 2.3 1.2.1 4.2.7 6.7 1.4 5.7 1.7 28.8 1 34.5-1 3.7-1.3 4-1.7 4.3-5.4.2-3.4 1.2-4.9 5.2-8.9 3.2-3.1 5-5.8 5-7.3 0-1.2-.4-2.3-.9-2.3s-1.4-1.4-2-3.1c-.9-2.6-1.3-2.9-3.1-1.9s-2 .8-2-2c0-1.7.7-4.1 1.6-5.5 1.5-2.3 1.5-2.4-7.5-7-8.4-4.3-9.1-4.9-9.1-7.7 0-1.6-1.1-4.9-2.5-7.4s-2.3-5-2-5.5c.4-.5.3-.9-.2-.9-.4 0-.9-2.4-1.1-5.3-.3-5.9-2.1-8-5.5-6.8-1.8.7-2.3 1.8-2.5 5.9-.3 4.8-.4 5.1-2.2 4-1.5-1-2.3-.9-4 .7-1.7 1.6-2.4 1.7-4 .7-1.1-.7-2.9-1.2-4-1.2-2.6 0-7.1-2.2-13.2-6.6-5.7-4-8.5-4.7-11.7-2.9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M36.1 27.5c-2.9 1.6-4.1 3.6-5.2 8.6-.5 2.1-2.3 6.2-4 9-3 5.2-3.1 5.5-2.4 14 .6 8.1.6 8.8-1.4 10.1-1.7 1.2-2.1 2.7-2.3 7.6-.2 7.3 0 7.7 5 8.1.9 0 2.3 1 3 2.2 1 1.6 1 2 0 1.8-2.9-.4-3.8.2-3.8 2.4 0 3.1 3.5 4.7 7.5 3.5 3.6-1 7-.3 10.6 2.3 1.5 1 3.6 1.9 4.8 1.9 1.1 0 2.9 1 4.1 2.2 1.1 1.2 3 2.3 4.3 2.3 1.2.1 4.2.7 6.7 1.4 5.7 1.7 28.8 1 34.5-1 3.7-1.3 4-1.7 4.3-5.4.2-3.4 1.2-4.9 5.2-8.9 3.2-3.1 5-5.8 5-7.3 0-1.2-.4-2.3-.9-2.3s-1.4-1.4-2-3.1c-.9-2.6-1.3-2.9-3.1-1.9s-2 .8-2-2c0-1.7.7-4.1 1.6-5.5 1.5-2.3 1.5-2.4-7.5-7-8.4-4.3-9.1-4.9-9.1-7.7 0-1.6-1.1-4.9-2.5-7.4s-2.3-5-2-5.5c.4-.5.3-.9-.2-.9-.4 0-.9-2.4-1.1-5.3-.3-5.9-2.1-8-5.5-6.8-1.8.7-2.3 1.8-2.5 5.9-.3 4.8-.4 5.1-2.2 4-1.5-1-2.3-.9-4 .7-1.7 1.6-2.4 1.7-4 .7-1.1-.7-2.9-1.2-4-1.2-2.6 0-7.1-2.2-13.2-6.6-5.7-4-8.5-4.7-11.7-2.9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				break;

				case 14:
					$sVG = '<svg class="buildingShape g14" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
		<g class="clickShape">
			<path d="M65 27c-1.3 3.1-2.4 3.7-3.5 2-.3-.6-1.1-1-1.7-1-.6 0-.3.9.7 2s1.5 2.3 1 2.8c-.4.4-1 .1-1.2-.6-.3-.6-1.1-1.2-1.8-1.2s-1.5-.7-1.9-1.5c-.8-2.1-2.9-1.9-2.2.2.5 1.5.4 1.5-.9-.1-.8-1.1-1.5-1.6-1.5-1 0 .5-.4.4-.8-.3-.4-.6-1.6-1.3-2.7-1.5-1.1-.1-2.3-.6-2.6-1.1-.7-1-7 .3-6.4 1.4.3.4 2.6 1.1 5 1.5 2.5.3 4.5 1 4.5 1.6 0 .5 2.3 2 5.2 3.3 3.9 1.8 5.3 3 5.5 4.8.3 2-.2 2.6-2.3 3.1-2.9.8-5.3-1.4-5.4-4.8 0-1.4-.5-1.7-2.5-1.2-1.6.4-3.4 0-5-1-2.3-1.5-4.5-1.2-4.5.7 0 .5.6.9 1.4.9 1.8 0 4 2.6 2.7 3.4-.5.3-2.1.2-3.5-.4-1.7-.6-2.6-.6-2.6 0 0 1.3 4.9 3.2 6.6 2.6.7-.3 1.5.3 1.7 1.2.3 1.2.5 1.1.6-.6.3-6.3 8 2.4 8.8 10.1.5 3.9.2 4.6-2.2 6.5-2.6 2.1-2.8 2.1-4.1.5-1-1.5-1.5-1.6-2.5-.6-1.9 1.9-4.7 1.6-5.9-.7-2.9-5.4-10-4-10 2 0 3-3.1 7.1-6.4 8.3-1.5.6-1.6 1-.6 2.2.7.8 2.1 1.5 3.1 1.5 1.1 0 1.9.4 1.9.9 0 1.5-2.7 3.2-4.2 2.6-.9-.4-2.2.5-3.3 2.2-1 1.5-2.5 3.4-3.2 4.1-1.5 1.5-1.8 3.6-.4 2.7.5-.3 1.2-.1 1.6.5.4.6 1 .8 1.5.5.5-.3 1.1-.1 1.4.4.4.5-.2 1.2-1.2 1.6-2.9.8-1.9 5.1 1.9 9.1 1.8 1.9 3.8 3.4 4.5 3.4.6 0 1.4.7 1.8 1.5.3.8 1.2 1.5 2.1 1.5.8 0 1.5.4 1.5.9s1.8 1.2 4 1.6c2.2.4 4.2 1.1 4.6 1.6.7 1.2 10.4 1.2 10.4 0 0-.5-.6-1.2-1.2-1.4-.7-.3-.1-.6 1.5-.6 1.5-.1 2.7.3 2.7.8s1.3 1.4 3 2.1c3.7 1.6 9.4.6 12.1-2.1 1.4-1.4 3.6-2.1 7.2-2.3 2.9-.2 6.3-.4 7.6-.5 1.7-.1 2.2-.6 1.9-1.8-.4-1.5.2-1.8 4.3-1.8 6 0 6.7-.7 4-4.1-1.9-2.5-2-2.8-.4-3.9.9-.7 1.3-1.7.9-2.3-.4-.7-.2-1.2.5-1.2.6 0 .9-.8.6-2-.4-1.6 0-2 1.8-2 2.6 0 4.1-1.5 2.6-2.4-.5-.4-1.2-2.4-1.6-4.6-.6-3.5-1-4-2.7-3.4-2.1.6-7.8-1.3-7.8-2.7 0-.5.7-.9 1.5-.9 1.6 0 2.1-2.5.6-3.4-.5-.3-.7-1.4-.5-2.4.3-.9-.2-2.6-1-3.7-1.5-2-1.5-2-1.6.1 0 1.2-.7 2.1-1.5 2.1-1.9 0-1.9-2.1-.1-3.6C88 57.8 89.2 54 88 54c-.4 0-1.8 2-3.1 4.5-2.6 4.9-3.9 5.7-3.9 2.4 0-1.3-.4-1.8-1.2-1.3-.6.4-1.7-.1-2.4-1-1-1.5-1.5-1.5-3.3-.4-2.2 1.4-2.8 2.8-1.1 2.8.6 0 1 .5 1 1.2s-1.6-.5-3.5-2.5c-3.1-3.2-3.4-4-2.4-5.9.9-1.6 1.3-1.8 2-.7s.9 1.1.9-.3c0-.9.9-2 2-2.3 2.7-.9 2.6-2.5-.1-1.8-1.9.5-2 .4-1-1.8 1-2 .8-1.9-1.4.6-1.4 1.7-3.3 2.9-4.2 2.7-1.5-.3-1.5-.2-.3 1.3 1.3 1.5 1.1 2-1.5 4.1-1.6 1.2-3.2 2.1-3.7 1.8-1.4-.9-.9-5.5 1.3-12.8 3-10 3.8-11.3 7.3-13 2.1-1 2.5-1.6 1.3-1.6-1.1 0-1.5-.5-1.1-1.1.4-.8-.1-.9-1.6-.4-1.9.6-2.1.5-1.1-1.3.6-1.2.7-2.3.2-2.6-.5-.3-1.4.8-2.1 2.4zm2.5 4c-.3.5-1.1 1-1.6 1-.6 0-.7-.5-.4-1 .3-.6 1.1-1 1.6-1 .6 0 .7.4.4 1zm11.4 33.9c.7.5 1 1.4.6 2-.5.9-1 .9-2.1 0-2.6-2.2-1.4-3.8 1.5-2zM32 67c0 .5-.5 1-1.1 1-.5 0-.7-.5-.4-1 .3-.6.8-1 1.1-1 .2 0 .4.4.4 1zm52 1.6c0 .8-.6 1.1-1.5.8-.8-.4-1.5-1.2-1.5-2s.6-1.1 1.5-.8c.8.4 1.5 1.2 1.5 2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M65 27c-1.3 3.1-2.4 3.7-3.5 2-.3-.6-1.1-1-1.7-1-.6 0-.3.9.7 2s1.5 2.3 1 2.8c-.4.4-1 .1-1.2-.6-.3-.6-1.1-1.2-1.8-1.2s-1.5-.7-1.9-1.5c-.8-2.1-2.9-1.9-2.2.2.5 1.5.4 1.5-.9-.1-.8-1.1-1.5-1.6-1.5-1 0 .5-.4.4-.8-.3-.4-.6-1.6-1.3-2.7-1.5-1.1-.1-2.3-.6-2.6-1.1-.7-1-7 .3-6.4 1.4.3.4 2.6 1.1 5 1.5 2.5.3 4.5 1 4.5 1.6 0 .5 2.3 2 5.2 3.3 3.9 1.8 5.3 3 5.5 4.8.3 2-.2 2.6-2.3 3.1-2.9.8-5.3-1.4-5.4-4.8 0-1.4-.5-1.7-2.5-1.2-1.6.4-3.4 0-5-1-2.3-1.5-4.5-1.2-4.5.7 0 .5.6.9 1.4.9 1.8 0 4 2.6 2.7 3.4-.5.3-2.1.2-3.5-.4-1.7-.6-2.6-.6-2.6 0 0 1.3 4.9 3.2 6.6 2.6.7-.3 1.5.3 1.7 1.2.3 1.2.5 1.1.6-.6.3-6.3 8 2.4 8.8 10.1.5 3.9.2 4.6-2.2 6.5-2.6 2.1-2.8 2.1-4.1.5-1-1.5-1.5-1.6-2.5-.6-1.9 1.9-4.7 1.6-5.9-.7-2.9-5.4-10-4-10 2 0 3-3.1 7.1-6.4 8.3-1.5.6-1.6 1-.6 2.2.7.8 2.1 1.5 3.1 1.5 1.1 0 1.9.4 1.9.9 0 1.5-2.7 3.2-4.2 2.6-.9-.4-2.2.5-3.3 2.2-1 1.5-2.5 3.4-3.2 4.1-1.5 1.5-1.8 3.6-.4 2.7.5-.3 1.2-.1 1.6.5.4.6 1 .8 1.5.5.5-.3 1.1-.1 1.4.4.4.5-.2 1.2-1.2 1.6-2.9.8-1.9 5.1 1.9 9.1 1.8 1.9 3.8 3.4 4.5 3.4.6 0 1.4.7 1.8 1.5.3.8 1.2 1.5 2.1 1.5.8 0 1.5.4 1.5.9s1.8 1.2 4 1.6c2.2.4 4.2 1.1 4.6 1.6.7 1.2 10.4 1.2 10.4 0 0-.5-.6-1.2-1.2-1.4-.7-.3-.1-.6 1.5-.6 1.5-.1 2.7.3 2.7.8s1.3 1.4 3 2.1c3.7 1.6 9.4.6 12.1-2.1 1.4-1.4 3.6-2.1 7.2-2.3 2.9-.2 6.3-.4 7.6-.5 1.7-.1 2.2-.6 1.9-1.8-.4-1.5.2-1.8 4.3-1.8 6 0 6.7-.7 4-4.1-1.9-2.5-2-2.8-.4-3.9.9-.7 1.3-1.7.9-2.3-.4-.7-.2-1.2.5-1.2.6 0 .9-.8.6-2-.4-1.6 0-2 1.8-2 2.6 0 4.1-1.5 2.6-2.4-.5-.4-1.2-2.4-1.6-4.6-.6-3.5-1-4-2.7-3.4-2.1.6-7.8-1.3-7.8-2.7 0-.5.7-.9 1.5-.9 1.6 0 2.1-2.5.6-3.4-.5-.3-.7-1.4-.5-2.4.3-.9-.2-2.6-1-3.7-1.5-2-1.5-2-1.6.1 0 1.2-.7 2.1-1.5 2.1-1.9 0-1.9-2.1-.1-3.6C88 57.8 89.2 54 88 54c-.4 0-1.8 2-3.1 4.5-2.6 4.9-3.9 5.7-3.9 2.4 0-1.3-.4-1.8-1.2-1.3-.6.4-1.7-.1-2.4-1-1-1.5-1.5-1.5-3.3-.4-2.2 1.4-2.8 2.8-1.1 2.8.6 0 1 .5 1 1.2s-1.6-.5-3.5-2.5c-3.1-3.2-3.4-4-2.4-5.9.9-1.6 1.3-1.8 2-.7s.9 1.1.9-.3c0-.9.9-2 2-2.3 2.7-.9 2.6-2.5-.1-1.8-1.9.5-2 .4-1-1.8 1-2 .8-1.9-1.4.6-1.4 1.7-3.3 2.9-4.2 2.7-1.5-.3-1.5-.2-.3 1.3 1.3 1.5 1.1 2-1.5 4.1-1.6 1.2-3.2 2.1-3.7 1.8-1.4-.9-.9-5.5 1.3-12.8 3-10 3.8-11.3 7.3-13 2.1-1 2.5-1.6 1.3-1.6-1.1 0-1.5-.5-1.1-1.1.4-.8-.1-.9-1.6-.4-1.9.6-2.1.5-1.1-1.3.6-1.2.7-2.3.2-2.6-.5-.3-1.4.8-2.1 2.4zm2.5 4c-.3.5-1.1 1-1.6 1-.6 0-.7-.5-.4-1 .3-.6 1.1-1 1.6-1 .6 0 .7.4.4 1zm11.4 33.9c.7.5 1 1.4.6 2-.5.9-1 .9-2.1 0-2.6-2.2-1.4-3.8 1.5-2zM32 67c0 .5-.5 1-1.1 1-.5 0-.7-.5-.4-1 .3-.6.8-1 1.1-1 .2 0 .4.4.4 1zm52 1.6c0 .8-.6 1.1-1.5.8-.8-.4-1.5-1.2-1.5-2s.6-1.1 1.5-.8c.8.4 1.5 1.2 1.5 2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M65 27c-1.3 3.1-2.4 3.7-3.5 2-.3-.6-1.1-1-1.7-1-.6 0-.3.9.7 2s1.5 2.3 1 2.8c-.4.4-1 .1-1.2-.6-.3-.6-1.1-1.2-1.8-1.2s-1.5-.7-1.9-1.5c-.8-2.1-2.9-1.9-2.2.2.5 1.5.4 1.5-.9-.1-.8-1.1-1.5-1.6-1.5-1 0 .5-.4.4-.8-.3-.4-.6-1.6-1.3-2.7-1.5-1.1-.1-2.3-.6-2.6-1.1-.7-1-7 .3-6.4 1.4.3.4 2.6 1.1 5 1.5 2.5.3 4.5 1 4.5 1.6 0 .5 2.3 2 5.2 3.3 3.9 1.8 5.3 3 5.5 4.8.3 2-.2 2.6-2.3 3.1-2.9.8-5.3-1.4-5.4-4.8 0-1.4-.5-1.7-2.5-1.2-1.6.4-3.4 0-5-1-2.3-1.5-4.5-1.2-4.5.7 0 .5.6.9 1.4.9 1.8 0 4 2.6 2.7 3.4-.5.3-2.1.2-3.5-.4-1.7-.6-2.6-.6-2.6 0 0 1.3 4.9 3.2 6.6 2.6.7-.3 1.5.3 1.7 1.2.3 1.2.5 1.1.6-.6.3-6.3 8 2.4 8.8 10.1.5 3.9.2 4.6-2.2 6.5-2.6 2.1-2.8 2.1-4.1.5-1-1.5-1.5-1.6-2.5-.6-1.9 1.9-4.7 1.6-5.9-.7-2.9-5.4-10-4-10 2 0 3-3.1 7.1-6.4 8.3-1.5.6-1.6 1-.6 2.2.7.8 2.1 1.5 3.1 1.5 1.1 0 1.9.4 1.9.9 0 1.5-2.7 3.2-4.2 2.6-.9-.4-2.2.5-3.3 2.2-1 1.5-2.5 3.4-3.2 4.1-1.5 1.5-1.8 3.6-.4 2.7.5-.3 1.2-.1 1.6.5.4.6 1 .8 1.5.5.5-.3 1.1-.1 1.4.4.4.5-.2 1.2-1.2 1.6-2.9.8-1.9 5.1 1.9 9.1 1.8 1.9 3.8 3.4 4.5 3.4.6 0 1.4.7 1.8 1.5.3.8 1.2 1.5 2.1 1.5.8 0 1.5.4 1.5.9s1.8 1.2 4 1.6c2.2.4 4.2 1.1 4.6 1.6.7 1.2 10.4 1.2 10.4 0 0-.5-.6-1.2-1.2-1.4-.7-.3-.1-.6 1.5-.6 1.5-.1 2.7.3 2.7.8s1.3 1.4 3 2.1c3.7 1.6 9.4.6 12.1-2.1 1.4-1.4 3.6-2.1 7.2-2.3 2.9-.2 6.3-.4 7.6-.5 1.7-.1 2.2-.6 1.9-1.8-.4-1.5.2-1.8 4.3-1.8 6 0 6.7-.7 4-4.1-1.9-2.5-2-2.8-.4-3.9.9-.7 1.3-1.7.9-2.3-.4-.7-.2-1.2.5-1.2.6 0 .9-.8.6-2-.4-1.6 0-2 1.8-2 2.6 0 4.1-1.5 2.6-2.4-.5-.4-1.2-2.4-1.6-4.6-.6-3.5-1-4-2.7-3.4-2.1.6-7.8-1.3-7.8-2.7 0-.5.7-.9 1.5-.9 1.6 0 2.1-2.5.6-3.4-.5-.3-.7-1.4-.5-2.4.3-.9-.2-2.6-1-3.7-1.5-2-1.5-2-1.6.1 0 1.2-.7 2.1-1.5 2.1-1.9 0-1.9-2.1-.1-3.6C88 57.8 89.2 54 88 54c-.4 0-1.8 2-3.1 4.5-2.6 4.9-3.9 5.7-3.9 2.4 0-1.3-.4-1.8-1.2-1.3-.6.4-1.7-.1-2.4-1-1-1.5-1.5-1.5-3.3-.4-2.2 1.4-2.8 2.8-1.1 2.8.6 0 1 .5 1 1.2s-1.6-.5-3.5-2.5c-3.1-3.2-3.4-4-2.4-5.9.9-1.6 1.3-1.8 2-.7s.9 1.1.9-.3c0-.9.9-2 2-2.3 2.7-.9 2.6-2.5-.1-1.8-1.9.5-2 .4-1-1.8 1-2 .8-1.9-1.4.6-1.4 1.7-3.3 2.9-4.2 2.7-1.5-.3-1.5-.2-.3 1.3 1.3 1.5 1.1 2-1.5 4.1-1.6 1.2-3.2 2.1-3.7 1.8-1.4-.9-.9-5.5 1.3-12.8 3-10 3.8-11.3 7.3-13 2.1-1 2.5-1.6 1.3-1.6-1.1 0-1.5-.5-1.1-1.1.4-.8-.1-.9-1.6-.4-1.9.6-2.1.5-1.1-1.3.6-1.2.7-2.3.2-2.6-.5-.3-1.4.8-2.1 2.4zm2.5 4c-.3.5-1.1 1-1.6 1-.6 0-.7-.5-.4-1 .3-.6 1.1-1 1.6-1 .6 0 .7.4.4 1zm11.4 33.9c.7.5 1 1.4.6 2-.5.9-1 .9-2.1 0-2.6-2.2-1.4-3.8 1.5-2zM32 67c0 .5-.5 1-1.1 1-.5 0-.7-.5-.4-1 .3-.6.8-1 1.1-1 .2 0 .4.4.4 1zm52 1.6c0 .8-.6 1.1-1.5.8-.8-.4-1.5-1.2-1.5-2s.6-1.1 1.5-.8c.8.4 1.5 1.2 1.5 2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M65 27c-1.3 3.1-2.4 3.7-3.5 2-.3-.6-1.1-1-1.7-1-.6 0-.3.9.7 2s1.5 2.3 1 2.8c-.4.4-1 .1-1.2-.6-.3-.6-1.1-1.2-1.8-1.2s-1.5-.7-1.9-1.5c-.8-2.1-2.9-1.9-2.2.2.5 1.5.4 1.5-.9-.1-.8-1.1-1.5-1.6-1.5-1 0 .5-.4.4-.8-.3-.4-.6-1.6-1.3-2.7-1.5-1.1-.1-2.3-.6-2.6-1.1-.7-1-7 .3-6.4 1.4.3.4 2.6 1.1 5 1.5 2.5.3 4.5 1 4.5 1.6 0 .5 2.3 2 5.2 3.3 3.9 1.8 5.3 3 5.5 4.8.3 2-.2 2.6-2.3 3.1-2.9.8-5.3-1.4-5.4-4.8 0-1.4-.5-1.7-2.5-1.2-1.6.4-3.4 0-5-1-2.3-1.5-4.5-1.2-4.5.7 0 .5.6.9 1.4.9 1.8 0 4 2.6 2.7 3.4-.5.3-2.1.2-3.5-.4-1.7-.6-2.6-.6-2.6 0 0 1.3 4.9 3.2 6.6 2.6.7-.3 1.5.3 1.7 1.2.3 1.2.5 1.1.6-.6.3-6.3 8 2.4 8.8 10.1.5 3.9.2 4.6-2.2 6.5-2.6 2.1-2.8 2.1-4.1.5-1-1.5-1.5-1.6-2.5-.6-1.9 1.9-4.7 1.6-5.9-.7-2.9-5.4-10-4-10 2 0 3-3.1 7.1-6.4 8.3-1.5.6-1.6 1-.6 2.2.7.8 2.1 1.5 3.1 1.5 1.1 0 1.9.4 1.9.9 0 1.5-2.7 3.2-4.2 2.6-.9-.4-2.2.5-3.3 2.2-1 1.5-2.5 3.4-3.2 4.1-1.5 1.5-1.8 3.6-.4 2.7.5-.3 1.2-.1 1.6.5.4.6 1 .8 1.5.5.5-.3 1.1-.1 1.4.4.4.5-.2 1.2-1.2 1.6-2.9.8-1.9 5.1 1.9 9.1 1.8 1.9 3.8 3.4 4.5 3.4.6 0 1.4.7 1.8 1.5.3.8 1.2 1.5 2.1 1.5.8 0 1.5.4 1.5.9s1.8 1.2 4 1.6c2.2.4 4.2 1.1 4.6 1.6.7 1.2 10.4 1.2 10.4 0 0-.5-.6-1.2-1.2-1.4-.7-.3-.1-.6 1.5-.6 1.5-.1 2.7.3 2.7.8s1.3 1.4 3 2.1c3.7 1.6 9.4.6 12.1-2.1 1.4-1.4 3.6-2.1 7.2-2.3 2.9-.2 6.3-.4 7.6-.5 1.7-.1 2.2-.6 1.9-1.8-.4-1.5.2-1.8 4.3-1.8 6 0 6.7-.7 4-4.1-1.9-2.5-2-2.8-.4-3.9.9-.7 1.3-1.7.9-2.3-.4-.7-.2-1.2.5-1.2.6 0 .9-.8.6-2-.4-1.6 0-2 1.8-2 2.6 0 4.1-1.5 2.6-2.4-.5-.4-1.2-2.4-1.6-4.6-.6-3.5-1-4-2.7-3.4-2.1.6-7.8-1.3-7.8-2.7 0-.5.7-.9 1.5-.9 1.6 0 2.1-2.5.6-3.4-.5-.3-.7-1.4-.5-2.4.3-.9-.2-2.6-1-3.7-1.5-2-1.5-2-1.6.1 0 1.2-.7 2.1-1.5 2.1-1.9 0-1.9-2.1-.1-3.6C88 57.8 89.2 54 88 54c-.4 0-1.8 2-3.1 4.5-2.6 4.9-3.9 5.7-3.9 2.4 0-1.3-.4-1.8-1.2-1.3-.6.4-1.7-.1-2.4-1-1-1.5-1.5-1.5-3.3-.4-2.2 1.4-2.8 2.8-1.1 2.8.6 0 1 .5 1 1.2s-1.6-.5-3.5-2.5c-3.1-3.2-3.4-4-2.4-5.9.9-1.6 1.3-1.8 2-.7s.9 1.1.9-.3c0-.9.9-2 2-2.3 2.7-.9 2.6-2.5-.1-1.8-1.9.5-2 .4-1-1.8 1-2 .8-1.9-1.4.6-1.4 1.7-3.3 2.9-4.2 2.7-1.5-.3-1.5-.2-.3 1.3 1.3 1.5 1.1 2-1.5 4.1-1.6 1.2-3.2 2.1-3.7 1.8-1.4-.9-.9-5.5 1.3-12.8 3-10 3.8-11.3 7.3-13 2.1-1 2.5-1.6 1.3-1.6-1.1 0-1.5-.5-1.1-1.1.4-.8-.1-.9-1.6-.4-1.9.6-2.1.5-1.1-1.3.6-1.2.7-2.3.2-2.6-.5-.3-1.4.8-2.1 2.4zm2.5 4c-.3.5-1.1 1-1.6 1-.6 0-.7-.5-.4-1 .3-.6 1.1-1 1.6-1 .6 0 .7.4.4 1zm11.4 33.9c.7.5 1 1.4.6 2-.5.9-1 .9-2.1 0-2.6-2.2-1.4-3.8 1.5-2zM32 67c0 .5-.5 1-1.1 1-.5 0-.7-.5-.4-1 .3-.6.8-1 1.1-1 .2 0 .4.4.4 1zm52 1.6c0 .8-.6 1.1-1.5.8-.8-.4-1.5-1.2-1.5-2s.6-1.1 1.5-.8c.8.4 1.5 1.2 1.5 2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				break;

				case 15:
					$sVG = '
					<svg class="buildingShape g15" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
			  
			  <g class="clickShape">
			<path d="M66.8 27.7c-1.1.3-1.8 1.4-1.8 2.9v2.5l-3.7-2.6c-3.1-2-5-2.5-10.1-2.5-6.2 0-6.4.1-5.8 2.3.6 2.7-.5 7.6-1.9 7.9-.5.2-.9 1.1-.8 2.1.4 2.7-1.5 5.7-3.7 5.7s-3.5 1.6-2 2.5c2.2 1.4.9 2.6-2 2-2.2-.5-3-.3-3 .7 0 1.7-7.1 8.8-8.8 8.8-.7 0-1.2.6-1.2 1.4 0 .7-1 2-2.1 2.8-1.8 1.2-2 1.7-1 3.5.6 1.2 1.1 4.4 1.1 7.1 0 5.5 1.6 7.5 9 11.2 2.5 1.3 4.8 2.7 5.2 3.3.3.6 1.9 1.2 3.5 1.4 4.4.6 15.3 6 15.3 7.7 0 2.5 3 4.6 6.6 4.6 2 0 5.7 1 8.4 2.1 6.1 2.6 7.4 2.3 17.2-3.9 10-6.4 10.8-7 10.8-9.7 0-2.5 2.4-4.9 5.8-5.9 2.6-.9 3-4.7.7-6.6-.8-.7-1.5-1.9-1.5-2.6 0-.8.7-1.4 1.5-1.4 3 0 1.4-2.6-3-5-4.3-2.3-5.7-4-3.5-4 1.5 0 .9-7.9-.8-11.2-1.8-3.8-2.4-7.6-1.2-8.3 1.5-1 1.2-3.5-.5-3.5-.8 0-1.5-.7-1.5-1.5s-1.2-2.2-2.6-3.1c-1.9-1.3-2.4-2.3-1.9-4.1.6-2.6-2.1-4.5-3.5-2.4-.5 1-.9.9-1.3-.3-.4-.9-1.6-1.6-2.8-1.6-1.9 0-2.1.4-1.5 3.5.6 3.1.4 3.5-1.4 3.5-1.7 0-2-.6-2-3.8 0-5.6-2.3-7.1-8.2-5.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M66.8 27.7c-1.1.3-1.8 1.4-1.8 2.9v2.5l-3.7-2.6c-3.1-2-5-2.5-10.1-2.5-6.2 0-6.4.1-5.8 2.3.6 2.7-.5 7.6-1.9 7.9-.5.2-.9 1.1-.8 2.1.4 2.7-1.5 5.7-3.7 5.7s-3.5 1.6-2 2.5c2.2 1.4.9 2.6-2 2-2.2-.5-3-.3-3 .7 0 1.7-7.1 8.8-8.8 8.8-.7 0-1.2.6-1.2 1.4 0 .7-1 2-2.1 2.8-1.8 1.2-2 1.7-1 3.5.6 1.2 1.1 4.4 1.1 7.1 0 5.5 1.6 7.5 9 11.2 2.5 1.3 4.8 2.7 5.2 3.3.3.6 1.9 1.2 3.5 1.4 4.4.6 15.3 6 15.3 7.7 0 2.5 3 4.6 6.6 4.6 2 0 5.7 1 8.4 2.1 6.1 2.6 7.4 2.3 17.2-3.9 10-6.4 10.8-7 10.8-9.7 0-2.5 2.4-4.9 5.8-5.9 2.6-.9 3-4.7.7-6.6-.8-.7-1.5-1.9-1.5-2.6 0-.8.7-1.4 1.5-1.4 3 0 1.4-2.6-3-5-4.3-2.3-5.7-4-3.5-4 1.5 0 .9-7.9-.8-11.2-1.8-3.8-2.4-7.6-1.2-8.3 1.5-1 1.2-3.5-.5-3.5-.8 0-1.5-.7-1.5-1.5s-1.2-2.2-2.6-3.1c-1.9-1.3-2.4-2.3-1.9-4.1.6-2.6-2.1-4.5-3.5-2.4-.5 1-.9.9-1.3-.3-.4-.9-1.6-1.6-2.8-1.6-1.9 0-2.1.4-1.5 3.5.6 3.1.4 3.5-1.4 3.5-1.7 0-2-.6-2-3.8 0-5.6-2.3-7.1-8.2-5.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M66.8 27.7c-1.1.3-1.8 1.4-1.8 2.9v2.5l-3.7-2.6c-3.1-2-5-2.5-10.1-2.5-6.2 0-6.4.1-5.8 2.3.6 2.7-.5 7.6-1.9 7.9-.5.2-.9 1.1-.8 2.1.4 2.7-1.5 5.7-3.7 5.7s-3.5 1.6-2 2.5c2.2 1.4.9 2.6-2 2-2.2-.5-3-.3-3 .7 0 1.7-7.1 8.8-8.8 8.8-.7 0-1.2.6-1.2 1.4 0 .7-1 2-2.1 2.8-1.8 1.2-2 1.7-1 3.5.6 1.2 1.1 4.4 1.1 7.1 0 5.5 1.6 7.5 9 11.2 2.5 1.3 4.8 2.7 5.2 3.3.3.6 1.9 1.2 3.5 1.4 4.4.6 15.3 6 15.3 7.7 0 2.5 3 4.6 6.6 4.6 2 0 5.7 1 8.4 2.1 6.1 2.6 7.4 2.3 17.2-3.9 10-6.4 10.8-7 10.8-9.7 0-2.5 2.4-4.9 5.8-5.9 2.6-.9 3-4.7.7-6.6-.8-.7-1.5-1.9-1.5-2.6 0-.8.7-1.4 1.5-1.4 3 0 1.4-2.6-3-5-4.3-2.3-5.7-4-3.5-4 1.5 0 .9-7.9-.8-11.2-1.8-3.8-2.4-7.6-1.2-8.3 1.5-1 1.2-3.5-.5-3.5-.8 0-1.5-.7-1.5-1.5s-1.2-2.2-2.6-3.1c-1.9-1.3-2.4-2.3-1.9-4.1.6-2.6-2.1-4.5-3.5-2.4-.5 1-.9.9-1.3-.3-.4-.9-1.6-1.6-2.8-1.6-1.9 0-2.1.4-1.5 3.5.6 3.1.4 3.5-1.4 3.5-1.7 0-2-.6-2-3.8 0-5.6-2.3-7.1-8.2-5.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M66.8 27.7c-1.1.3-1.8 1.4-1.8 2.9v2.5l-3.7-2.6c-3.1-2-5-2.5-10.1-2.5-6.2 0-6.4.1-5.8 2.3.6 2.7-.5 7.6-1.9 7.9-.5.2-.9 1.1-.8 2.1.4 2.7-1.5 5.7-3.7 5.7s-3.5 1.6-2 2.5c2.2 1.4.9 2.6-2 2-2.2-.5-3-.3-3 .7 0 1.7-7.1 8.8-8.8 8.8-.7 0-1.2.6-1.2 1.4 0 .7-1 2-2.1 2.8-1.8 1.2-2 1.7-1 3.5.6 1.2 1.1 4.4 1.1 7.1 0 5.5 1.6 7.5 9 11.2 2.5 1.3 4.8 2.7 5.2 3.3.3.6 1.9 1.2 3.5 1.4 4.4.6 15.3 6 15.3 7.7 0 2.5 3 4.6 6.6 4.6 2 0 5.7 1 8.4 2.1 6.1 2.6 7.4 2.3 17.2-3.9 10-6.4 10.8-7 10.8-9.7 0-2.5 2.4-4.9 5.8-5.9 2.6-.9 3-4.7.7-6.6-.8-.7-1.5-1.9-1.5-2.6 0-.8.7-1.4 1.5-1.4 3 0 1.4-2.6-3-5-4.3-2.3-5.7-4-3.5-4 1.5 0 .9-7.9-.8-11.2-1.8-3.8-2.4-7.6-1.2-8.3 1.5-1 1.2-3.5-.5-3.5-.8 0-1.5-.7-1.5-1.5s-1.2-2.2-2.6-3.1c-1.9-1.3-2.4-2.3-1.9-4.1.6-2.6-2.1-4.5-3.5-2.4-.5 1-.9.9-1.3-.3-.4-.9-1.6-1.6-2.8-1.6-1.9 0-2.1.4-1.5 3.5.6 3.1.4 3.5-1.4 3.5-1.7 0-2-.6-2-3.8 0-5.6-2.3-7.1-8.2-5.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>
					';
				
				break;
				
				case 16:
					$sVG = '
					<svg class="buildingShape g16" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="160" viewBox="0 0 120 160" >
	<g class="clickShape">
			<path d="M55 3.1c-3.2 2.7-3.4 3-1.7 4.3 2.5 2 2.1 3-2.3 6-4.1 2.7-5.3 5.6-2.4 5.6 2.5 0-.1 2.7-4.8 5.1-2.1 1.1-3.8 2.4-3.8 3 0 .6-.8.8-1.7.5-2.6-.8-4.7 1.4-5.8 5.9-.9 3.4-.7 4.2 1 6 3.1 3.3 5.5 6.4 5.5 7.2 0 .4 1.2 1.9 2.6 3.4 1.4 1.5 2.7 3.9 2.9 5.5.1 1.5 1 3.3 1.9 3.9 1 .8 1.6 2.7 1.6 5.3 0 2.2.5 4.2 1.2 4.4.9.3.9 1.2-.1 3.6-.7 1.8-1.1 4.1-.8 5.2.3 1.1-.1 2.7-.9 3.4-.8.8-1.4 2.6-1.4 3.9 0 1.4-.7 3-1.5 3.7-.8.7-1.5 2.3-1.5 3.5 0 1.2-.7 2.8-1.5 3.5-.8.7-1.5 1.8-1.5 2.5 0 .6-1.2 1.8-2.6 2.5-1.4.8-3 2.3-3.5 3.3-.8 1.4-1.7 1.7-3.9 1.2-3.2-.7-8 2.6-8 5.5 0 2.7-4.1 4-6.7 2.2-3.3-2.1-7.3-.8-7.3 2.4 0 1.4-.4 2.3-.9 2-.5-.3-1.9.2-3.2 1.3l-2.3 1.8 5.2 4.8c2.9 2.6 5.2 5.4 5.2 6.1 0 .8.5 1.4 1.2 1.4.6 0 2.8 1.7 4.8 3.7 5.7 5.9 11 10.5 16.7 14.6 3.2 2.4 5 4.3 4.7 5.2-.4.9.6 1.7 3.2 2.4 3.5 1 4.1.8 10.6-3.7 3.8-2.6 7.9-5.2 9.1-5.9 3.8-2 25.2-23.6 26.5-26.8 1.4-3.2 5.9-10.1 7.9-12.3.7-.7 1.3-2.2 1.3-3.3 0-1.5 2.7-11 4.7-16.9.3-.8.7-3.8.8-6.6.2-2.8 1-6.4 1.8-8.1 1.7-3.2 1.4-5.7-1.1-11.3-.9-1.9-1.6-4.2-1.7-5-.5-4.6-2-11.2-3.7-16.5-2.2-6.7-3.1-18-1.6-20.9.7-1.2.6-2.2-.1-2.9-1.8-1.8-1.3-4.7.8-4.7 2.3 0 3.4-2.5 1.8-3.8C98.1 8.9 88.3 5 86.6 5c-.8 0-3.1-.8-5.2-1.7-2.2-.9-4.2-1.6-4.6-1.5-.5.1-.8-.2-.8-.6 0-.5-3.9-.9-8.7-1-8.4-.1-9 0-12.3 2.9zm5.4 2.6c-1.3 1.4-2.7 2.3-3.1 1.9-.4-.4.3-1.6 1.7-2.6 3.6-2.8 4.3-2.4 1.4.7zM87 10.6c0 2-.7 1.7-2-.6-.9-1.6-.8-1.8.5-1.4.8.4 1.5 1.2 1.5 2zm6.8 3.7c.3 2.6.2 2.7-1.7 1.5-2.6-1.6-2.8-5.1-.3-4.6 1 .2 1.8 1.4 2 3.1zm-39.6 2.9c-.7.7-1.2.8-1.2.2 0-1.4 1.2-2.6 1.9-1.9.2.3-.1 1.1-.7 1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M55 3.1c-3.2 2.7-3.4 3-1.7 4.3 2.5 2 2.1 3-2.3 6-4.1 2.7-5.3 5.6-2.4 5.6 2.5 0-.1 2.7-4.8 5.1-2.1 1.1-3.8 2.4-3.8 3 0 .6-.8.8-1.7.5-2.6-.8-4.7 1.4-5.8 5.9-.9 3.4-.7 4.2 1 6 3.1 3.3 5.5 6.4 5.5 7.2 0 .4 1.2 1.9 2.6 3.4 1.4 1.5 2.7 3.9 2.9 5.5.1 1.5 1 3.3 1.9 3.9 1 .8 1.6 2.7 1.6 5.3 0 2.2.5 4.2 1.2 4.4.9.3.9 1.2-.1 3.6-.7 1.8-1.1 4.1-.8 5.2.3 1.1-.1 2.7-.9 3.4-.8.8-1.4 2.6-1.4 3.9 0 1.4-.7 3-1.5 3.7-.8.7-1.5 2.3-1.5 3.5 0 1.2-.7 2.8-1.5 3.5-.8.7-1.5 1.8-1.5 2.5 0 .6-1.2 1.8-2.6 2.5-1.4.8-3 2.3-3.5 3.3-.8 1.4-1.7 1.7-3.9 1.2-3.2-.7-8 2.6-8 5.5 0 2.7-4.1 4-6.7 2.2-3.3-2.1-7.3-.8-7.3 2.4 0 1.4-.4 2.3-.9 2-.5-.3-1.9.2-3.2 1.3l-2.3 1.8 5.2 4.8c2.9 2.6 5.2 5.4 5.2 6.1 0 .8.5 1.4 1.2 1.4.6 0 2.8 1.7 4.8 3.7 5.7 5.9 11 10.5 16.7 14.6 3.2 2.4 5 4.3 4.7 5.2-.4.9.6 1.7 3.2 2.4 3.5 1 4.1.8 10.6-3.7 3.8-2.6 7.9-5.2 9.1-5.9 3.8-2 25.2-23.6 26.5-26.8 1.4-3.2 5.9-10.1 7.9-12.3.7-.7 1.3-2.2 1.3-3.3 0-1.5 2.7-11 4.7-16.9.3-.8.7-3.8.8-6.6.2-2.8 1-6.4 1.8-8.1 1.7-3.2 1.4-5.7-1.1-11.3-.9-1.9-1.6-4.2-1.7-5-.5-4.6-2-11.2-3.7-16.5-2.2-6.7-3.1-18-1.6-20.9.7-1.2.6-2.2-.1-2.9-1.8-1.8-1.3-4.7.8-4.7 2.3 0 3.4-2.5 1.8-3.8C98.1 8.9 88.3 5 86.6 5c-.8 0-3.1-.8-5.2-1.7-2.2-.9-4.2-1.6-4.6-1.5-.5.1-.8-.2-.8-.6 0-.5-3.9-.9-8.7-1-8.4-.1-9 0-12.3 2.9zm5.4 2.6c-1.3 1.4-2.7 2.3-3.1 1.9-.4-.4.3-1.6 1.7-2.6 3.6-2.8 4.3-2.4 1.4.7zM87 10.6c0 2-.7 1.7-2-.6-.9-1.6-.8-1.8.5-1.4.8.4 1.5 1.2 1.5 2zm6.8 3.7c.3 2.6.2 2.7-1.7 1.5-2.6-1.6-2.8-5.1-.3-4.6 1 .2 1.8 1.4 2 3.1zm-39.6 2.9c-.7.7-1.2.8-1.2.2 0-1.4 1.2-2.6 1.9-1.9.2.3-.1 1.1-.7 1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M55 3.1c-3.2 2.7-3.4 3-1.7 4.3 2.5 2 2.1 3-2.3 6-4.1 2.7-5.3 5.6-2.4 5.6 2.5 0-.1 2.7-4.8 5.1-2.1 1.1-3.8 2.4-3.8 3 0 .6-.8.8-1.7.5-2.6-.8-4.7 1.4-5.8 5.9-.9 3.4-.7 4.2 1 6 3.1 3.3 5.5 6.4 5.5 7.2 0 .4 1.2 1.9 2.6 3.4 1.4 1.5 2.7 3.9 2.9 5.5.1 1.5 1 3.3 1.9 3.9 1 .8 1.6 2.7 1.6 5.3 0 2.2.5 4.2 1.2 4.4.9.3.9 1.2-.1 3.6-.7 1.8-1.1 4.1-.8 5.2.3 1.1-.1 2.7-.9 3.4-.8.8-1.4 2.6-1.4 3.9 0 1.4-.7 3-1.5 3.7-.8.7-1.5 2.3-1.5 3.5 0 1.2-.7 2.8-1.5 3.5-.8.7-1.5 1.8-1.5 2.5 0 .6-1.2 1.8-2.6 2.5-1.4.8-3 2.3-3.5 3.3-.8 1.4-1.7 1.7-3.9 1.2-3.2-.7-8 2.6-8 5.5 0 2.7-4.1 4-6.7 2.2-3.3-2.1-7.3-.8-7.3 2.4 0 1.4-.4 2.3-.9 2-.5-.3-1.9.2-3.2 1.3l-2.3 1.8 5.2 4.8c2.9 2.6 5.2 5.4 5.2 6.1 0 .8.5 1.4 1.2 1.4.6 0 2.8 1.7 4.8 3.7 5.7 5.9 11 10.5 16.7 14.6 3.2 2.4 5 4.3 4.7 5.2-.4.9.6 1.7 3.2 2.4 3.5 1 4.1.8 10.6-3.7 3.8-2.6 7.9-5.2 9.1-5.9 3.8-2 25.2-23.6 26.5-26.8 1.4-3.2 5.9-10.1 7.9-12.3.7-.7 1.3-2.2 1.3-3.3 0-1.5 2.7-11 4.7-16.9.3-.8.7-3.8.8-6.6.2-2.8 1-6.4 1.8-8.1 1.7-3.2 1.4-5.7-1.1-11.3-.9-1.9-1.6-4.2-1.7-5-.5-4.6-2-11.2-3.7-16.5-2.2-6.7-3.1-18-1.6-20.9.7-1.2.6-2.2-.1-2.9-1.8-1.8-1.3-4.7.8-4.7 2.3 0 3.4-2.5 1.8-3.8C98.1 8.9 88.3 5 86.6 5c-.8 0-3.1-.8-5.2-1.7-2.2-.9-4.2-1.6-4.6-1.5-.5.1-.8-.2-.8-.6 0-.5-3.9-.9-8.7-1-8.4-.1-9 0-12.3 2.9zm5.4 2.6c-1.3 1.4-2.7 2.3-3.1 1.9-.4-.4.3-1.6 1.7-2.6 3.6-2.8 4.3-2.4 1.4.7zM87 10.6c0 2-.7 1.7-2-.6-.9-1.6-.8-1.8.5-1.4.8.4 1.5 1.2 1.5 2zm6.8 3.7c.3 2.6.2 2.7-1.7 1.5-2.6-1.6-2.8-5.1-.3-4.6 1 .2 1.8 1.4 2 3.1zm-39.6 2.9c-.7.7-1.2.8-1.2.2 0-1.4 1.2-2.6 1.9-1.9.2.3-.1 1.1-.7 1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M55 3.1c-3.2 2.7-3.4 3-1.7 4.3 2.5 2 2.1 3-2.3 6-4.1 2.7-5.3 5.6-2.4 5.6 2.5 0-.1 2.7-4.8 5.1-2.1 1.1-3.8 2.4-3.8 3 0 .6-.8.8-1.7.5-2.6-.8-4.7 1.4-5.8 5.9-.9 3.4-.7 4.2 1 6 3.1 3.3 5.5 6.4 5.5 7.2 0 .4 1.2 1.9 2.6 3.4 1.4 1.5 2.7 3.9 2.9 5.5.1 1.5 1 3.3 1.9 3.9 1 .8 1.6 2.7 1.6 5.3 0 2.2.5 4.2 1.2 4.4.9.3.9 1.2-.1 3.6-.7 1.8-1.1 4.1-.8 5.2.3 1.1-.1 2.7-.9 3.4-.8.8-1.4 2.6-1.4 3.9 0 1.4-.7 3-1.5 3.7-.8.7-1.5 2.3-1.5 3.5 0 1.2-.7 2.8-1.5 3.5-.8.7-1.5 1.8-1.5 2.5 0 .6-1.2 1.8-2.6 2.5-1.4.8-3 2.3-3.5 3.3-.8 1.4-1.7 1.7-3.9 1.2-3.2-.7-8 2.6-8 5.5 0 2.7-4.1 4-6.7 2.2-3.3-2.1-7.3-.8-7.3 2.4 0 1.4-.4 2.3-.9 2-.5-.3-1.9.2-3.2 1.3l-2.3 1.8 5.2 4.8c2.9 2.6 5.2 5.4 5.2 6.1 0 .8.5 1.4 1.2 1.4.6 0 2.8 1.7 4.8 3.7 5.7 5.9 11 10.5 16.7 14.6 3.2 2.4 5 4.3 4.7 5.2-.4.9.6 1.7 3.2 2.4 3.5 1 4.1.8 10.6-3.7 3.8-2.6 7.9-5.2 9.1-5.9 3.8-2 25.2-23.6 26.5-26.8 1.4-3.2 5.9-10.1 7.9-12.3.7-.7 1.3-2.2 1.3-3.3 0-1.5 2.7-11 4.7-16.9.3-.8.7-3.8.8-6.6.2-2.8 1-6.4 1.8-8.1 1.7-3.2 1.4-5.7-1.1-11.3-.9-1.9-1.6-4.2-1.7-5-.5-4.6-2-11.2-3.7-16.5-2.2-6.7-3.1-18-1.6-20.9.7-1.2.6-2.2-.1-2.9-1.8-1.8-1.3-4.7.8-4.7 2.3 0 3.4-2.5 1.8-3.8C98.1 8.9 88.3 5 86.6 5c-.8 0-3.1-.8-5.2-1.7-2.2-.9-4.2-1.6-4.6-1.5-.5.1-.8-.2-.8-.6 0-.5-3.9-.9-8.7-1-8.4-.1-9 0-12.3 2.9zm5.4 2.6c-1.3 1.4-2.7 2.3-3.1 1.9-.4-.4.3-1.6 1.7-2.6 3.6-2.8 4.3-2.4 1.4.7zM87 10.6c0 2-.7 1.7-2-.6-.9-1.6-.8-1.8.5-1.4.8.4 1.5 1.2 1.5 2zm6.8 3.7c.3 2.6.2 2.7-1.7 1.5-2.6-1.6-2.8-5.1-.3-4.6 1 .2 1.8 1.4 2 3.1zm-39.6 2.9c-.7.7-1.2.8-1.2.2 0-1.4 1.2-2.6 1.9-1.9.2.3-.1 1.1-.7 1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>
					';
				
				break;
				
				case 17:
					$sVG = '
					<svg class="buildingShape g17" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
		 <g class="clickShape">
			<path d="M46.5 37.1c-2.7 1.5-6.9 3.1-9.2 3.5-6.7 1-11 6.1-10.5 12.5.1 1-1.4 2.3-3.8 3.4-2.6 1.2-4 2.5-4 3.7 0 1.9-1.5 2.4-2.5.8-1.8-2.9-2.5 0-2.5 9.8 0 9.7.2 10.8 2 12 1.5.9 1.9 1.9 1.4 3.6-.4 1.9-.1 2.6 1.6 3.1l2.2.7-2.1 2.1-2 2.2 2.2 2.1c5.7 5.4 10.3 7.3 17.1 7.2 5.1-.1 7.1.3 8.6 1.7s3 1.7 6.8 1.2c3.8-.4 5.5-.1 7.8 1.4 3.5 2.3 7.5 2.4 11.3.4 1.6-.8 5.1-1.9 7.7-2.5 2.7-.6 5.6-1.7 6.4-2.5.8-.7 2.6-1.9 4-2.5 6.3-2.8 12.5-6.9 12.8-8.4.2-1 0-2.5-.4-3.4-1.1-2.6-.5-4.4 2.7-7.4 1.6-1.6 2.9-3.5 2.9-4.3 0-2.5-2.7-8.2-4.9-10.4-1.7-1.7-1.8-2.1-.5-2.1s1.3-.4-.4-2.8c-1-1.5-1.7-3.3-1.5-4 .3-.6 0-1.2-.5-1.2-.6 0-1.2.5-1.4 1.2-.7 2.1-9.3-3.4-9.6-6.2-.2-1.4-1-3-1.8-3.7-.9-.6-1.1-1.4-.5-1.7.5-.3 2.2.7 3.8 2.3 2.9 2.9 2.9 2.9 5.4 1 2-1.6 2.2-2.1.9-2.9-.8-.5-2.1-1.4-2.9-1.9-.7-.5-1.3-2-1.3-3.4 0-1.8-.5-2.4-1.4-2-1 .4-1.2 0-.8-1.1.3-.9.2-1.6-.3-1.6-1.1 0-3.6 4-4 6.5-.6 3.5-2 3.7-4.6.5-1.5-1.7-3.1-3.8-3.6-4.7-.8-1.4-1.1-1.5-2.3-.2-.8.8-1.7 2.2-2.1 3.2-.4 1.1-1.6 2.1-2.7 2.4-1.1.3-2 1.3-2 2.3 0 1-.7 2.3-1.5 3-1.3 1.1-1.4 1-.9-.4.4-1-.5-2.9-2.5-5.1-1.7-2-3.1-4.2-3.1-5 0-2.3-3.7-5.5-6.2-5.4-1.3.1-4.5 1.4-7.3 3zm16.6 20.6c-.8 1.5-.9 1.2-.7-1.2.5-3.9.7-4.4 1.3-2.3.2 1 0 2.5-.6 3.5zM28 65c0 1.1-.7 2-1.5 2s-1.5.6-1.5 1.4c0 .9-.8 1.2-2.5.8-2-.3-2.4-.1-1.9 1.1.4 1.2.1 1.4-1.4 1-1.4-.3-2.2.1-2.5 1.4-.3 1-.5.5-.6-1.4-.1-2.3.5-3.5 1.9-4.3 1.1-.6 2-.8 2-.5 0 .3 1.7-.4 3.7-1.5 4.6-2.4 4.3-2.4 4.3 0z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M46.5 37.1c-2.7 1.5-6.9 3.1-9.2 3.5-6.7 1-11 6.1-10.5 12.5.1 1-1.4 2.3-3.8 3.4-2.6 1.2-4 2.5-4 3.7 0 1.9-1.5 2.4-2.5.8-1.8-2.9-2.5 0-2.5 9.8 0 9.7.2 10.8 2 12 1.5.9 1.9 1.9 1.4 3.6-.4 1.9-.1 2.6 1.6 3.1l2.2.7-2.1 2.1-2 2.2 2.2 2.1c5.7 5.4 10.3 7.3 17.1 7.2 5.1-.1 7.1.3 8.6 1.7s3 1.7 6.8 1.2c3.8-.4 5.5-.1 7.8 1.4 3.5 2.3 7.5 2.4 11.3.4 1.6-.8 5.1-1.9 7.7-2.5 2.7-.6 5.6-1.7 6.4-2.5.8-.7 2.6-1.9 4-2.5 6.3-2.8 12.5-6.9 12.8-8.4.2-1 0-2.5-.4-3.4-1.1-2.6-.5-4.4 2.7-7.4 1.6-1.6 2.9-3.5 2.9-4.3 0-2.5-2.7-8.2-4.9-10.4-1.7-1.7-1.8-2.1-.5-2.1s1.3-.4-.4-2.8c-1-1.5-1.7-3.3-1.5-4 .3-.6 0-1.2-.5-1.2-.6 0-1.2.5-1.4 1.2-.7 2.1-9.3-3.4-9.6-6.2-.2-1.4-1-3-1.8-3.7-.9-.6-1.1-1.4-.5-1.7.5-.3 2.2.7 3.8 2.3 2.9 2.9 2.9 2.9 5.4 1 2-1.6 2.2-2.1.9-2.9-.8-.5-2.1-1.4-2.9-1.9-.7-.5-1.3-2-1.3-3.4 0-1.8-.5-2.4-1.4-2-1 .4-1.2 0-.8-1.1.3-.9.2-1.6-.3-1.6-1.1 0-3.6 4-4 6.5-.6 3.5-2 3.7-4.6.5-1.5-1.7-3.1-3.8-3.6-4.7-.8-1.4-1.1-1.5-2.3-.2-.8.8-1.7 2.2-2.1 3.2-.4 1.1-1.6 2.1-2.7 2.4-1.1.3-2 1.3-2 2.3 0 1-.7 2.3-1.5 3-1.3 1.1-1.4 1-.9-.4.4-1-.5-2.9-2.5-5.1-1.7-2-3.1-4.2-3.1-5 0-2.3-3.7-5.5-6.2-5.4-1.3.1-4.5 1.4-7.3 3zm16.6 20.6c-.8 1.5-.9 1.2-.7-1.2.5-3.9.7-4.4 1.3-2.3.2 1 0 2.5-.6 3.5zM28 65c0 1.1-.7 2-1.5 2s-1.5.6-1.5 1.4c0 .9-.8 1.2-2.5.8-2-.3-2.4-.1-1.9 1.1.4 1.2.1 1.4-1.4 1-1.4-.3-2.2.1-2.5 1.4-.3 1-.5.5-.6-1.4-.1-2.3.5-3.5 1.9-4.3 1.1-.6 2-.8 2-.5 0 .3 1.7-.4 3.7-1.5 4.6-2.4 4.3-2.4 4.3 0z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M46.5 37.1c-2.7 1.5-6.9 3.1-9.2 3.5-6.7 1-11 6.1-10.5 12.5.1 1-1.4 2.3-3.8 3.4-2.6 1.2-4 2.5-4 3.7 0 1.9-1.5 2.4-2.5.8-1.8-2.9-2.5 0-2.5 9.8 0 9.7.2 10.8 2 12 1.5.9 1.9 1.9 1.4 3.6-.4 1.9-.1 2.6 1.6 3.1l2.2.7-2.1 2.1-2 2.2 2.2 2.1c5.7 5.4 10.3 7.3 17.1 7.2 5.1-.1 7.1.3 8.6 1.7s3 1.7 6.8 1.2c3.8-.4 5.5-.1 7.8 1.4 3.5 2.3 7.5 2.4 11.3.4 1.6-.8 5.1-1.9 7.7-2.5 2.7-.6 5.6-1.7 6.4-2.5.8-.7 2.6-1.9 4-2.5 6.3-2.8 12.5-6.9 12.8-8.4.2-1 0-2.5-.4-3.4-1.1-2.6-.5-4.4 2.7-7.4 1.6-1.6 2.9-3.5 2.9-4.3 0-2.5-2.7-8.2-4.9-10.4-1.7-1.7-1.8-2.1-.5-2.1s1.3-.4-.4-2.8c-1-1.5-1.7-3.3-1.5-4 .3-.6 0-1.2-.5-1.2-.6 0-1.2.5-1.4 1.2-.7 2.1-9.3-3.4-9.6-6.2-.2-1.4-1-3-1.8-3.7-.9-.6-1.1-1.4-.5-1.7.5-.3 2.2.7 3.8 2.3 2.9 2.9 2.9 2.9 5.4 1 2-1.6 2.2-2.1.9-2.9-.8-.5-2.1-1.4-2.9-1.9-.7-.5-1.3-2-1.3-3.4 0-1.8-.5-2.4-1.4-2-1 .4-1.2 0-.8-1.1.3-.9.2-1.6-.3-1.6-1.1 0-3.6 4-4 6.5-.6 3.5-2 3.7-4.6.5-1.5-1.7-3.1-3.8-3.6-4.7-.8-1.4-1.1-1.5-2.3-.2-.8.8-1.7 2.2-2.1 3.2-.4 1.1-1.6 2.1-2.7 2.4-1.1.3-2 1.3-2 2.3 0 1-.7 2.3-1.5 3-1.3 1.1-1.4 1-.9-.4.4-1-.5-2.9-2.5-5.1-1.7-2-3.1-4.2-3.1-5 0-2.3-3.7-5.5-6.2-5.4-1.3.1-4.5 1.4-7.3 3zm16.6 20.6c-.8 1.5-.9 1.2-.7-1.2.5-3.9.7-4.4 1.3-2.3.2 1 0 2.5-.6 3.5zM28 65c0 1.1-.7 2-1.5 2s-1.5.6-1.5 1.4c0 .9-.8 1.2-2.5.8-2-.3-2.4-.1-1.9 1.1.4 1.2.1 1.4-1.4 1-1.4-.3-2.2.1-2.5 1.4-.3 1-.5.5-.6-1.4-.1-2.3.5-3.5 1.9-4.3 1.1-.6 2-.8 2-.5 0 .3 1.7-.4 3.7-1.5 4.6-2.4 4.3-2.4 4.3 0z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M46.5 37.1c-2.7 1.5-6.9 3.1-9.2 3.5-6.7 1-11 6.1-10.5 12.5.1 1-1.4 2.3-3.8 3.4-2.6 1.2-4 2.5-4 3.7 0 1.9-1.5 2.4-2.5.8-1.8-2.9-2.5 0-2.5 9.8 0 9.7.2 10.8 2 12 1.5.9 1.9 1.9 1.4 3.6-.4 1.9-.1 2.6 1.6 3.1l2.2.7-2.1 2.1-2 2.2 2.2 2.1c5.7 5.4 10.3 7.3 17.1 7.2 5.1-.1 7.1.3 8.6 1.7s3 1.7 6.8 1.2c3.8-.4 5.5-.1 7.8 1.4 3.5 2.3 7.5 2.4 11.3.4 1.6-.8 5.1-1.9 7.7-2.5 2.7-.6 5.6-1.7 6.4-2.5.8-.7 2.6-1.9 4-2.5 6.3-2.8 12.5-6.9 12.8-8.4.2-1 0-2.5-.4-3.4-1.1-2.6-.5-4.4 2.7-7.4 1.6-1.6 2.9-3.5 2.9-4.3 0-2.5-2.7-8.2-4.9-10.4-1.7-1.7-1.8-2.1-.5-2.1s1.3-.4-.4-2.8c-1-1.5-1.7-3.3-1.5-4 .3-.6 0-1.2-.5-1.2-.6 0-1.2.5-1.4 1.2-.7 2.1-9.3-3.4-9.6-6.2-.2-1.4-1-3-1.8-3.7-.9-.6-1.1-1.4-.5-1.7.5-.3 2.2.7 3.8 2.3 2.9 2.9 2.9 2.9 5.4 1 2-1.6 2.2-2.1.9-2.9-.8-.5-2.1-1.4-2.9-1.9-.7-.5-1.3-2-1.3-3.4 0-1.8-.5-2.4-1.4-2-1 .4-1.2 0-.8-1.1.3-.9.2-1.6-.3-1.6-1.1 0-3.6 4-4 6.5-.6 3.5-2 3.7-4.6.5-1.5-1.7-3.1-3.8-3.6-4.7-.8-1.4-1.1-1.5-2.3-.2-.8.8-1.7 2.2-2.1 3.2-.4 1.1-1.6 2.1-2.7 2.4-1.1.3-2 1.3-2 2.3 0 1-.7 2.3-1.5 3-1.3 1.1-1.4 1-.9-.4.4-1-.5-2.9-2.5-5.1-1.7-2-3.1-4.2-3.1-5 0-2.3-3.7-5.5-6.2-5.4-1.3.1-4.5 1.4-7.3 3zm16.6 20.6c-.8 1.5-.9 1.2-.7-1.2.5-3.9.7-4.4 1.3-2.3.2 1 0 2.5-.6 3.5zM28 65c0 1.1-.7 2-1.5 2s-1.5.6-1.5 1.4c0 .9-.8 1.2-2.5.8-2-.3-2.4-.1-1.9 1.1.4 1.2.1 1.4-1.4 1-1.4-.3-2.2.1-2.5 1.4-.3 1-.5.5-.6-1.4-.1-2.3.5-3.5 1.9-4.3 1.1-.6 2-.8 2-.5 0 .3 1.7-.4 3.7-1.5 4.6-2.4 4.3-2.4 4.3 0z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>
					';
				break;
				
				
				case 18:
					$sVG = '
					<svg class="buildingShape g18" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
		<g class="clickShape">
			<path d="M81.6 19.8c-.2.4-.3 5.6-.1 11.7l.3 10.9-2-2.7c-1.8-2.5-2.3-2.6-10.4-2.5-4.6.1-8.6-.1-8.8-.3-.3-.2-.7-2.3-1-4.7-.6-5.5-1.2-6.2-4.5-6.2-3.2 0-5.1 1.6-5.1 4.3 0 1.2-.5 1.7-1.4 1.4-.8-.3-3.1-.1-5 .4-4.2 1.2-6.6 5-6.6 10.6 0 2.8-1.2 5.7-4.5 10.7-4.8 7.3-5.4 10.4-3.1 14.8 1.3 2.4 1.3 2.8.1 2.8-.8 0-2.5 1.1-3.8 2.6-2 2.2-2.3 3.3-2 8.5.3 5.7 2.4 10.9 4.4 10.9.5 0 2.6 1.5 4.6 3.3 9.8 8.5 18 10.7 39.2 10.5 14.4-.1 16.7-.3 20.4-2.2l4.2-2.1.9-9.3c.8-9.1.8-9.4-1.9-13.5-1.6-2.3-3.4-4.6-4.2-5-.7-.4-1.3-1.7-1.3-2.7 0-1-.4-2.2-1-2.5-.5-.3-1-1.7-1-3s-.4-2.7-1-3c-.5-.3-1-4.4-1-8.9v-8.3l4.2-.7c4.5-.7 7.1-2.3 6.2-3.7-.3-.5-.5-5.5-.5-11V20.7l-4.8.6c-3.8.4-5.1.2-5.5-.9-.6-1.5-3.2-1.9-4-.6z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M81.6 19.8c-.2.4-.3 5.6-.1 11.7l.3 10.9-2-2.7c-1.8-2.5-2.3-2.6-10.4-2.5-4.6.1-8.6-.1-8.8-.3-.3-.2-.7-2.3-1-4.7-.6-5.5-1.2-6.2-4.5-6.2-3.2 0-5.1 1.6-5.1 4.3 0 1.2-.5 1.7-1.4 1.4-.8-.3-3.1-.1-5 .4-4.2 1.2-6.6 5-6.6 10.6 0 2.8-1.2 5.7-4.5 10.7-4.8 7.3-5.4 10.4-3.1 14.8 1.3 2.4 1.3 2.8.1 2.8-.8 0-2.5 1.1-3.8 2.6-2 2.2-2.3 3.3-2 8.5.3 5.7 2.4 10.9 4.4 10.9.5 0 2.6 1.5 4.6 3.3 9.8 8.5 18 10.7 39.2 10.5 14.4-.1 16.7-.3 20.4-2.2l4.2-2.1.9-9.3c.8-9.1.8-9.4-1.9-13.5-1.6-2.3-3.4-4.6-4.2-5-.7-.4-1.3-1.7-1.3-2.7 0-1-.4-2.2-1-2.5-.5-.3-1-1.7-1-3s-.4-2.7-1-3c-.5-.3-1-4.4-1-8.9v-8.3l4.2-.7c4.5-.7 7.1-2.3 6.2-3.7-.3-.5-.5-5.5-.5-11V20.7l-4.8.6c-3.8.4-5.1.2-5.5-.9-.6-1.5-3.2-1.9-4-.6z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M81.6 19.8c-.2.4-.3 5.6-.1 11.7l.3 10.9-2-2.7c-1.8-2.5-2.3-2.6-10.4-2.5-4.6.1-8.6-.1-8.8-.3-.3-.2-.7-2.3-1-4.7-.6-5.5-1.2-6.2-4.5-6.2-3.2 0-5.1 1.6-5.1 4.3 0 1.2-.5 1.7-1.4 1.4-.8-.3-3.1-.1-5 .4-4.2 1.2-6.6 5-6.6 10.6 0 2.8-1.2 5.7-4.5 10.7-4.8 7.3-5.4 10.4-3.1 14.8 1.3 2.4 1.3 2.8.1 2.8-.8 0-2.5 1.1-3.8 2.6-2 2.2-2.3 3.3-2 8.5.3 5.7 2.4 10.9 4.4 10.9.5 0 2.6 1.5 4.6 3.3 9.8 8.5 18 10.7 39.2 10.5 14.4-.1 16.7-.3 20.4-2.2l4.2-2.1.9-9.3c.8-9.1.8-9.4-1.9-13.5-1.6-2.3-3.4-4.6-4.2-5-.7-.4-1.3-1.7-1.3-2.7 0-1-.4-2.2-1-2.5-.5-.3-1-1.7-1-3s-.4-2.7-1-3c-.5-.3-1-4.4-1-8.9v-8.3l4.2-.7c4.5-.7 7.1-2.3 6.2-3.7-.3-.5-.5-5.5-.5-11V20.7l-4.8.6c-3.8.4-5.1.2-5.5-.9-.6-1.5-3.2-1.9-4-.6z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M81.6 19.8c-.2.4-.3 5.6-.1 11.7l.3 10.9-2-2.7c-1.8-2.5-2.3-2.6-10.4-2.5-4.6.1-8.6-.1-8.8-.3-.3-.2-.7-2.3-1-4.7-.6-5.5-1.2-6.2-4.5-6.2-3.2 0-5.1 1.6-5.1 4.3 0 1.2-.5 1.7-1.4 1.4-.8-.3-3.1-.1-5 .4-4.2 1.2-6.6 5-6.6 10.6 0 2.8-1.2 5.7-4.5 10.7-4.8 7.3-5.4 10.4-3.1 14.8 1.3 2.4 1.3 2.8.1 2.8-.8 0-2.5 1.1-3.8 2.6-2 2.2-2.3 3.3-2 8.5.3 5.7 2.4 10.9 4.4 10.9.5 0 2.6 1.5 4.6 3.3 9.8 8.5 18 10.7 39.2 10.5 14.4-.1 16.7-.3 20.4-2.2l4.2-2.1.9-9.3c.8-9.1.8-9.4-1.9-13.5-1.6-2.3-3.4-4.6-4.2-5-.7-.4-1.3-1.7-1.3-2.7 0-1-.4-2.2-1-2.5-.5-.3-1-1.7-1-3s-.4-2.7-1-3c-.5-.3-1-4.4-1-8.9v-8.3l4.2-.7c4.5-.7 7.1-2.3 6.2-3.7-.3-.5-.5-5.5-.5-11V20.7l-4.8.6c-3.8.4-5.1.2-5.5-.9-.6-1.5-3.2-1.9-4-.6z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>
					';
				break;
				
				case 19:
					$sVG = '
					<svg class="buildingShape g19" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120">
	<g class="clickShape">
			<path d="M47 29.5c0 1.6-.5 2.3-1.3 1.9-2.1-.8-4.2 10.4-2.6 13.9 1.3 2.9 2.4 3.4 3.2 1.4.3-.6.6 6.1.6 15 .1 10-.3 16.3-.9 16.3-.5 0-1.9-1.8-3-4.1-1.8-3.4-3.3-4.6-8.7-7-7.4-3.4-6.4-3.9-12.5 5.5-2.2 3.3-2.8 5.1-2 5.4.7.2 1.2 1.8 1.2 3.6 0 2.7-.6 3.6-3.5 5.2-4.9 2.6-5.1 4.2-1.1 7.8 6.8 6.2 6.3 6 21.6 6.9 4.8.3 5.5.6 5.2 2.3-.2 1.8-.2 1.8 1.1.1 2-2.5 6.7-2.3 6.7.3 0 1.1.4 2 1 2 .5 0 .7.8.4 1.7-.5 1.7-.5 1.7.9 0 1.3-1.5 1.5-1.6 2.1-.2.6 1.7 3.6 2.1 3.6.5 0-.6.8-1 1.8-1s4.3-1.8 7.2-4c2.9-2.2 5.6-4 5.9-4 .3 0 1.9 1.1 3.5 2.5 4.5 3.9 8.8 4.6 14.9 2.8 2.9-.9 5.5-1.6 5.9-1.5.3.1 2.3-1.4 4.3-3.2 2.7-2.5 3.8-4.4 4.2-7.5.5-3.9.3-4.4-3.6-8-3.3-3.1-4.2-4.7-4.7-8.5-.6-4-1.4-5.3-6-9.6-6.1-5.5-7.2-8.9-2.6-8.2 2.1.3 2.9 0 2.9-1.2 0-1-.9-1.6-2.2-1.6-1.1 0-2.6-.5-3.3-1.2-.9-.9-1.2-.3-1.2 2.9 0 3.7-.4 4.4-4.7 7.6-2.7 2-4.9 3.2-5.1 2.7-.2-.4-2.8-3.2-5.7-6.2-3.2-3.2-5.5-6.4-5.5-7.6 0-1.3.5-1.9 1.3-1.6.7.2 2.1-.3 3.2-1.2 1.5-1.2 1.6-1.4.4-1.1-.9.3-2.6-.3-3.8-1.4l-2.1-2V50c0 5.2-1.8 8.7-8.1 15.6-2.8 3-4.9 6.3-4.8 7.2.3 2.9-.1 4.2-1.1 4.2-1.6 0-1.3-30.7.4-35.5.7-2.2 1.6-5.5 2-7.4.5-3 .4-3.3-1.4-2.8-1.6.4-2 0-2-1.9 0-1.3-.4-2.4-1-2.4-.5 0-1 1.1-1 2.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M47 29.5c0 1.6-.5 2.3-1.3 1.9-2.1-.8-4.2 10.4-2.6 13.9 1.3 2.9 2.4 3.4 3.2 1.4.3-.6.6 6.1.6 15 .1 10-.3 16.3-.9 16.3-.5 0-1.9-1.8-3-4.1-1.8-3.4-3.3-4.6-8.7-7-7.4-3.4-6.4-3.9-12.5 5.5-2.2 3.3-2.8 5.1-2 5.4.7.2 1.2 1.8 1.2 3.6 0 2.7-.6 3.6-3.5 5.2-4.9 2.6-5.1 4.2-1.1 7.8 6.8 6.2 6.3 6 21.6 6.9 4.8.3 5.5.6 5.2 2.3-.2 1.8-.2 1.8 1.1.1 2-2.5 6.7-2.3 6.7.3 0 1.1.4 2 1 2 .5 0 .7.8.4 1.7-.5 1.7-.5 1.7.9 0 1.3-1.5 1.5-1.6 2.1-.2.6 1.7 3.6 2.1 3.6.5 0-.6.8-1 1.8-1s4.3-1.8 7.2-4c2.9-2.2 5.6-4 5.9-4 .3 0 1.9 1.1 3.5 2.5 4.5 3.9 8.8 4.6 14.9 2.8 2.9-.9 5.5-1.6 5.9-1.5.3.1 2.3-1.4 4.3-3.2 2.7-2.5 3.8-4.4 4.2-7.5.5-3.9.3-4.4-3.6-8-3.3-3.1-4.2-4.7-4.7-8.5-.6-4-1.4-5.3-6-9.6-6.1-5.5-7.2-8.9-2.6-8.2 2.1.3 2.9 0 2.9-1.2 0-1-.9-1.6-2.2-1.6-1.1 0-2.6-.5-3.3-1.2-.9-.9-1.2-.3-1.2 2.9 0 3.7-.4 4.4-4.7 7.6-2.7 2-4.9 3.2-5.1 2.7-.2-.4-2.8-3.2-5.7-6.2-3.2-3.2-5.5-6.4-5.5-7.6 0-1.3.5-1.9 1.3-1.6.7.2 2.1-.3 3.2-1.2 1.5-1.2 1.6-1.4.4-1.1-.9.3-2.6-.3-3.8-1.4l-2.1-2V50c0 5.2-1.8 8.7-8.1 15.6-2.8 3-4.9 6.3-4.8 7.2.3 2.9-.1 4.2-1.1 4.2-1.6 0-1.3-30.7.4-35.5.7-2.2 1.6-5.5 2-7.4.5-3 .4-3.3-1.4-2.8-1.6.4-2 0-2-1.9 0-1.3-.4-2.4-1-2.4-.5 0-1 1.1-1 2.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M47 29.5c0 1.6-.5 2.3-1.3 1.9-2.1-.8-4.2 10.4-2.6 13.9 1.3 2.9 2.4 3.4 3.2 1.4.3-.6.6 6.1.6 15 .1 10-.3 16.3-.9 16.3-.5 0-1.9-1.8-3-4.1-1.8-3.4-3.3-4.6-8.7-7-7.4-3.4-6.4-3.9-12.5 5.5-2.2 3.3-2.8 5.1-2 5.4.7.2 1.2 1.8 1.2 3.6 0 2.7-.6 3.6-3.5 5.2-4.9 2.6-5.1 4.2-1.1 7.8 6.8 6.2 6.3 6 21.6 6.9 4.8.3 5.5.6 5.2 2.3-.2 1.8-.2 1.8 1.1.1 2-2.5 6.7-2.3 6.7.3 0 1.1.4 2 1 2 .5 0 .7.8.4 1.7-.5 1.7-.5 1.7.9 0 1.3-1.5 1.5-1.6 2.1-.2.6 1.7 3.6 2.1 3.6.5 0-.6.8-1 1.8-1s4.3-1.8 7.2-4c2.9-2.2 5.6-4 5.9-4 .3 0 1.9 1.1 3.5 2.5 4.5 3.9 8.8 4.6 14.9 2.8 2.9-.9 5.5-1.6 5.9-1.5.3.1 2.3-1.4 4.3-3.2 2.7-2.5 3.8-4.4 4.2-7.5.5-3.9.3-4.4-3.6-8-3.3-3.1-4.2-4.7-4.7-8.5-.6-4-1.4-5.3-6-9.6-6.1-5.5-7.2-8.9-2.6-8.2 2.1.3 2.9 0 2.9-1.2 0-1-.9-1.6-2.2-1.6-1.1 0-2.6-.5-3.3-1.2-.9-.9-1.2-.3-1.2 2.9 0 3.7-.4 4.4-4.7 7.6-2.7 2-4.9 3.2-5.1 2.7-.2-.4-2.8-3.2-5.7-6.2-3.2-3.2-5.5-6.4-5.5-7.6 0-1.3.5-1.9 1.3-1.6.7.2 2.1-.3 3.2-1.2 1.5-1.2 1.6-1.4.4-1.1-.9.3-2.6-.3-3.8-1.4l-2.1-2V50c0 5.2-1.8 8.7-8.1 15.6-2.8 3-4.9 6.3-4.8 7.2.3 2.9-.1 4.2-1.1 4.2-1.6 0-1.3-30.7.4-35.5.7-2.2 1.6-5.5 2-7.4.5-3 .4-3.3-1.4-2.8-1.6.4-2 0-2-1.9 0-1.3-.4-2.4-1-2.4-.5 0-1 1.1-1 2.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M47 29.5c0 1.6-.5 2.3-1.3 1.9-2.1-.8-4.2 10.4-2.6 13.9 1.3 2.9 2.4 3.4 3.2 1.4.3-.6.6 6.1.6 15 .1 10-.3 16.3-.9 16.3-.5 0-1.9-1.8-3-4.1-1.8-3.4-3.3-4.6-8.7-7-7.4-3.4-6.4-3.9-12.5 5.5-2.2 3.3-2.8 5.1-2 5.4.7.2 1.2 1.8 1.2 3.6 0 2.7-.6 3.6-3.5 5.2-4.9 2.6-5.1 4.2-1.1 7.8 6.8 6.2 6.3 6 21.6 6.9 4.8.3 5.5.6 5.2 2.3-.2 1.8-.2 1.8 1.1.1 2-2.5 6.7-2.3 6.7.3 0 1.1.4 2 1 2 .5 0 .7.8.4 1.7-.5 1.7-.5 1.7.9 0 1.3-1.5 1.5-1.6 2.1-.2.6 1.7 3.6 2.1 3.6.5 0-.6.8-1 1.8-1s4.3-1.8 7.2-4c2.9-2.2 5.6-4 5.9-4 .3 0 1.9 1.1 3.5 2.5 4.5 3.9 8.8 4.6 14.9 2.8 2.9-.9 5.5-1.6 5.9-1.5.3.1 2.3-1.4 4.3-3.2 2.7-2.5 3.8-4.4 4.2-7.5.5-3.9.3-4.4-3.6-8-3.3-3.1-4.2-4.7-4.7-8.5-.6-4-1.4-5.3-6-9.6-6.1-5.5-7.2-8.9-2.6-8.2 2.1.3 2.9 0 2.9-1.2 0-1-.9-1.6-2.2-1.6-1.1 0-2.6-.5-3.3-1.2-.9-.9-1.2-.3-1.2 2.9 0 3.7-.4 4.4-4.7 7.6-2.7 2-4.9 3.2-5.1 2.7-.2-.4-2.8-3.2-5.7-6.2-3.2-3.2-5.5-6.4-5.5-7.6 0-1.3.5-1.9 1.3-1.6.7.2 2.1-.3 3.2-1.2 1.5-1.2 1.6-1.4.4-1.1-.9.3-2.6-.3-3.8-1.4l-2.1-2V50c0 5.2-1.8 8.7-8.1 15.6-2.8 3-4.9 6.3-4.8 7.2.3 2.9-.1 4.2-1.1 4.2-1.6 0-1.3-30.7.4-35.5.7-2.2 1.6-5.5 2-7.4.5-3 .4-3.3-1.4-2.8-1.6.4-2 0-2-1.9 0-1.3-.4-2.4-1-2.4-.5 0-1 1.1-1 2.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				break;
				
				case 20:
					$sVG = '<svg class="buildingShape g20" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
		<g class="clickShape">
			<path d="M49 33.1c-1.4.6-2.8 1.7-3.3 2.5-.6 1-1.3 1.2-2.7.4-3.2-1.7-6-1.2-6 1 0 1.1.5 2 1.1 2 1.9 0 .5 4.5-1.6 4.8-5.5 1-7.1 6.8-3.4 11.6 1.2 1.5 2 2.9 1.7 3.1-.2.2-2.5.7-5.2 1.2-5.6.9-6.8 2.4-3.2 4.1 2.3 1 2.5 1.5 2.2 8.4-.2 4-.4 7.6-.5 7.9 0 .3-2.4 1.1-5.2 1.8-5.5 1.4-7.2 3.6-4.5 5.7 1.4 1 1.5 1.6.6 3.3-1 2-.9 2.1 2.2 1.7 4.4-.6 10.8-2.3 11.5-3 .3-.3.1-2.9-.4-5.8-1.3-7.4-.6-16.8 1.3-16.1 1.1.4 1.4 2.2 1.2 8-.2 7.3-.2 7.5 3 9.9 1.8 1.4 3.2 2.3 3.2 1.9 0-.4.7 0 1.5.9 2.3 2.2 1.8 2.8-1.6 2.1-3-.7-3.2-.6-2.6 1.6.4 1.3.7 3.4.7 4.6 0 1.9.4 2.1 4.1 1.6l4.1-.6-.6 3c-.8 3.7 1.6 6.3 4.2 4.6 1.3-.8 1.6-2.2 1.4-5.9-.3-4.8-.3-4.9 1.5-2.7 1 1.2 3.5 2.6 5.5 3.1 2 .6 5.5 2.5 7.8 4.4 3.6 2.8 4.9 3.3 9.4 3.2 9.6-.1 26.7-7 26.6-10.7-.1-1.9-4.6-6.6-7.5-7.7-2.4-.9-2.5-1.2-2.5-10.9 0-8.5.3-10.2 1.8-11 1.6-.9 1.6-1.4-.5-7.5-1.5-4.2-2.1-7.5-1.7-9.5.6-2.8.4-3.1-2-3.1-1.9 0-2.6-.5-2.6-1.9 0-1.1-.5-2.3-1.1-2.7-.8-.4-.7-.9.2-1.6.9-.6-2.3-.7-8.7-.3-11.2.7-13.8 0-22-5.5-2.5-1.6-4.6-3-4.7-2.9-.1 0-1.3.5-2.7 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M49 33.1c-1.4.6-2.8 1.7-3.3 2.5-.6 1-1.3 1.2-2.7.4-3.2-1.7-6-1.2-6 1 0 1.1.5 2 1.1 2 1.9 0 .5 4.5-1.6 4.8-5.5 1-7.1 6.8-3.4 11.6 1.2 1.5 2 2.9 1.7 3.1-.2.2-2.5.7-5.2 1.2-5.6.9-6.8 2.4-3.2 4.1 2.3 1 2.5 1.5 2.2 8.4-.2 4-.4 7.6-.5 7.9 0 .3-2.4 1.1-5.2 1.8-5.5 1.4-7.2 3.6-4.5 5.7 1.4 1 1.5 1.6.6 3.3-1 2-.9 2.1 2.2 1.7 4.4-.6 10.8-2.3 11.5-3 .3-.3.1-2.9-.4-5.8-1.3-7.4-.6-16.8 1.3-16.1 1.1.4 1.4 2.2 1.2 8-.2 7.3-.2 7.5 3 9.9 1.8 1.4 3.2 2.3 3.2 1.9 0-.4.7 0 1.5.9 2.3 2.2 1.8 2.8-1.6 2.1-3-.7-3.2-.6-2.6 1.6.4 1.3.7 3.4.7 4.6 0 1.9.4 2.1 4.1 1.6l4.1-.6-.6 3c-.8 3.7 1.6 6.3 4.2 4.6 1.3-.8 1.6-2.2 1.4-5.9-.3-4.8-.3-4.9 1.5-2.7 1 1.2 3.5 2.6 5.5 3.1 2 .6 5.5 2.5 7.8 4.4 3.6 2.8 4.9 3.3 9.4 3.2 9.6-.1 26.7-7 26.6-10.7-.1-1.9-4.6-6.6-7.5-7.7-2.4-.9-2.5-1.2-2.5-10.9 0-8.5.3-10.2 1.8-11 1.6-.9 1.6-1.4-.5-7.5-1.5-4.2-2.1-7.5-1.7-9.5.6-2.8.4-3.1-2-3.1-1.9 0-2.6-.5-2.6-1.9 0-1.1-.5-2.3-1.1-2.7-.8-.4-.7-.9.2-1.6.9-.6-2.3-.7-8.7-.3-11.2.7-13.8 0-22-5.5-2.5-1.6-4.6-3-4.7-2.9-.1 0-1.3.5-2.7 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M49 33.1c-1.4.6-2.8 1.7-3.3 2.5-.6 1-1.3 1.2-2.7.4-3.2-1.7-6-1.2-6 1 0 1.1.5 2 1.1 2 1.9 0 .5 4.5-1.6 4.8-5.5 1-7.1 6.8-3.4 11.6 1.2 1.5 2 2.9 1.7 3.1-.2.2-2.5.7-5.2 1.2-5.6.9-6.8 2.4-3.2 4.1 2.3 1 2.5 1.5 2.2 8.4-.2 4-.4 7.6-.5 7.9 0 .3-2.4 1.1-5.2 1.8-5.5 1.4-7.2 3.6-4.5 5.7 1.4 1 1.5 1.6.6 3.3-1 2-.9 2.1 2.2 1.7 4.4-.6 10.8-2.3 11.5-3 .3-.3.1-2.9-.4-5.8-1.3-7.4-.6-16.8 1.3-16.1 1.1.4 1.4 2.2 1.2 8-.2 7.3-.2 7.5 3 9.9 1.8 1.4 3.2 2.3 3.2 1.9 0-.4.7 0 1.5.9 2.3 2.2 1.8 2.8-1.6 2.1-3-.7-3.2-.6-2.6 1.6.4 1.3.7 3.4.7 4.6 0 1.9.4 2.1 4.1 1.6l4.1-.6-.6 3c-.8 3.7 1.6 6.3 4.2 4.6 1.3-.8 1.6-2.2 1.4-5.9-.3-4.8-.3-4.9 1.5-2.7 1 1.2 3.5 2.6 5.5 3.1 2 .6 5.5 2.5 7.8 4.4 3.6 2.8 4.9 3.3 9.4 3.2 9.6-.1 26.7-7 26.6-10.7-.1-1.9-4.6-6.6-7.5-7.7-2.4-.9-2.5-1.2-2.5-10.9 0-8.5.3-10.2 1.8-11 1.6-.9 1.6-1.4-.5-7.5-1.5-4.2-2.1-7.5-1.7-9.5.6-2.8.4-3.1-2-3.1-1.9 0-2.6-.5-2.6-1.9 0-1.1-.5-2.3-1.1-2.7-.8-.4-.7-.9.2-1.6.9-.6-2.3-.7-8.7-.3-11.2.7-13.8 0-22-5.5-2.5-1.6-4.6-3-4.7-2.9-.1 0-1.3.5-2.7 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M49 33.1c-1.4.6-2.8 1.7-3.3 2.5-.6 1-1.3 1.2-2.7.4-3.2-1.7-6-1.2-6 1 0 1.1.5 2 1.1 2 1.9 0 .5 4.5-1.6 4.8-5.5 1-7.1 6.8-3.4 11.6 1.2 1.5 2 2.9 1.7 3.1-.2.2-2.5.7-5.2 1.2-5.6.9-6.8 2.4-3.2 4.1 2.3 1 2.5 1.5 2.2 8.4-.2 4-.4 7.6-.5 7.9 0 .3-2.4 1.1-5.2 1.8-5.5 1.4-7.2 3.6-4.5 5.7 1.4 1 1.5 1.6.6 3.3-1 2-.9 2.1 2.2 1.7 4.4-.6 10.8-2.3 11.5-3 .3-.3.1-2.9-.4-5.8-1.3-7.4-.6-16.8 1.3-16.1 1.1.4 1.4 2.2 1.2 8-.2 7.3-.2 7.5 3 9.9 1.8 1.4 3.2 2.3 3.2 1.9 0-.4.7 0 1.5.9 2.3 2.2 1.8 2.8-1.6 2.1-3-.7-3.2-.6-2.6 1.6.4 1.3.7 3.4.7 4.6 0 1.9.4 2.1 4.1 1.6l4.1-.6-.6 3c-.8 3.7 1.6 6.3 4.2 4.6 1.3-.8 1.6-2.2 1.4-5.9-.3-4.8-.3-4.9 1.5-2.7 1 1.2 3.5 2.6 5.5 3.1 2 .6 5.5 2.5 7.8 4.4 3.6 2.8 4.9 3.3 9.4 3.2 9.6-.1 26.7-7 26.6-10.7-.1-1.9-4.6-6.6-7.5-7.7-2.4-.9-2.5-1.2-2.5-10.9 0-8.5.3-10.2 1.8-11 1.6-.9 1.6-1.4-.5-7.5-1.5-4.2-2.1-7.5-1.7-9.5.6-2.8.4-3.1-2-3.1-1.9 0-2.6-.5-2.6-1.9 0-1.1-.5-2.3-1.1-2.7-.8-.4-.7-.9.2-1.6.9-.6-2.3-.7-8.7-.3-11.2.7-13.8 0-22-5.5-2.5-1.6-4.6-3-4.7-2.9-.1 0-1.3.5-2.7 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				break;
				
				case 21:
					$sVG = '<svg class="buildingShape g21" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
		<g class="clickShape">
			<path d="M69.7 26.6c-.4.4-1 2.9-1.3 5.5-.6 4.3-.9 4.7-2.5 3.9-2.9-1.6-3.4-1.3-7.8 4.9L54 46.8l-4-1.9c-2.2-1.1-4-2.4-4-2.9 0-.6-.6-1-1.3-1-.7 0-3-.9-5.2-2s-4.2-2-4.6-2c-.4 0-2 1.8-3.6 4-1.5 2.2-3.4 4-4 4-.7 0-1.3.8-1.3 1.7 0 .9-1.9 4.6-4.2 8.1l-4.2 6.5 2.4 1.9c2 1.6 2.4 2.9 2.4 7.4l.1 5.5-4 1.1c-5.6 1.6-6.7 2.6-6 5.9.3 1.5.1 3-.4 3.4-2.1 1.2-1.1 3.3 1.8 4 2 .4 2.8 1.1 2.4 2.1-.7 1.8.7 1.8 3.1-.1 2.2-1.6 5.6-2 5.6-.6 0 .5-.9 1.1-2 1.4-3 .8-2.4 2.2 1.3 3.3 2 .7 4.4.7 6.5.1 2.5-.8 3.6-.6 5 .5.9.9 2.8 1.9 4.1 2.3 1.4.4 3.2 1.5 4 2.5 1 1 4.6 2.3 9 3.1 4.8.8 8.8 2.3 11.5 4.1 3.3 2.2 5.2 2.8 9.9 2.8 7.1 0 9.3-1.1 11.2-5.5 1.1-2.7 2-3.5 3.9-3.5 2.6 0 3.6-1.5 1.4-2.2-2.6-.9 3.1-4.8 7-4.8 4.4 0 6.2-1.4 6.2-4.9 0-1.8.9-3.1 3-4.3 3.5-2 3.8-3.8 1-6.3-1.7-1.5-2-3.1-2-9.2 0-5.1.3-7.2 1.1-6.7 3 1.8-.3-3.2-5.9-9-4.4-4.5-7.7-7-11-8.1-2.6-.9-5.4-2.1-6.2-2.6-.8-.5-2.9-1.5-4.6-2.3-3-1.3-3.2-1.8-4-8.8-.8-6.2-1.2-7.3-2.9-7.6-1.2-.2-2.4 0-2.8.4zM17 86c0 .5-.2 1-.4 1-.3 0-.8-.5-1.1-1-.3-.6-.1-1 .4-1 .6 0 1.1.4 1.1 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M69.7 26.6c-.4.4-1 2.9-1.3 5.5-.6 4.3-.9 4.7-2.5 3.9-2.9-1.6-3.4-1.3-7.8 4.9L54 46.8l-4-1.9c-2.2-1.1-4-2.4-4-2.9 0-.6-.6-1-1.3-1-.7 0-3-.9-5.2-2s-4.2-2-4.6-2c-.4 0-2 1.8-3.6 4-1.5 2.2-3.4 4-4 4-.7 0-1.3.8-1.3 1.7 0 .9-1.9 4.6-4.2 8.1l-4.2 6.5 2.4 1.9c2 1.6 2.4 2.9 2.4 7.4l.1 5.5-4 1.1c-5.6 1.6-6.7 2.6-6 5.9.3 1.5.1 3-.4 3.4-2.1 1.2-1.1 3.3 1.8 4 2 .4 2.8 1.1 2.4 2.1-.7 1.8.7 1.8 3.1-.1 2.2-1.6 5.6-2 5.6-.6 0 .5-.9 1.1-2 1.4-3 .8-2.4 2.2 1.3 3.3 2 .7 4.4.7 6.5.1 2.5-.8 3.6-.6 5 .5.9.9 2.8 1.9 4.1 2.3 1.4.4 3.2 1.5 4 2.5 1 1 4.6 2.3 9 3.1 4.8.8 8.8 2.3 11.5 4.1 3.3 2.2 5.2 2.8 9.9 2.8 7.1 0 9.3-1.1 11.2-5.5 1.1-2.7 2-3.5 3.9-3.5 2.6 0 3.6-1.5 1.4-2.2-2.6-.9 3.1-4.8 7-4.8 4.4 0 6.2-1.4 6.2-4.9 0-1.8.9-3.1 3-4.3 3.5-2 3.8-3.8 1-6.3-1.7-1.5-2-3.1-2-9.2 0-5.1.3-7.2 1.1-6.7 3 1.8-.3-3.2-5.9-9-4.4-4.5-7.7-7-11-8.1-2.6-.9-5.4-2.1-6.2-2.6-.8-.5-2.9-1.5-4.6-2.3-3-1.3-3.2-1.8-4-8.8-.8-6.2-1.2-7.3-2.9-7.6-1.2-.2-2.4 0-2.8.4zM17 86c0 .5-.2 1-.4 1-.3 0-.8-.5-1.1-1-.3-.6-.1-1 .4-1 .6 0 1.1.4 1.1 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M69.7 26.6c-.4.4-1 2.9-1.3 5.5-.6 4.3-.9 4.7-2.5 3.9-2.9-1.6-3.4-1.3-7.8 4.9L54 46.8l-4-1.9c-2.2-1.1-4-2.4-4-2.9 0-.6-.6-1-1.3-1-.7 0-3-.9-5.2-2s-4.2-2-4.6-2c-.4 0-2 1.8-3.6 4-1.5 2.2-3.4 4-4 4-.7 0-1.3.8-1.3 1.7 0 .9-1.9 4.6-4.2 8.1l-4.2 6.5 2.4 1.9c2 1.6 2.4 2.9 2.4 7.4l.1 5.5-4 1.1c-5.6 1.6-6.7 2.6-6 5.9.3 1.5.1 3-.4 3.4-2.1 1.2-1.1 3.3 1.8 4 2 .4 2.8 1.1 2.4 2.1-.7 1.8.7 1.8 3.1-.1 2.2-1.6 5.6-2 5.6-.6 0 .5-.9 1.1-2 1.4-3 .8-2.4 2.2 1.3 3.3 2 .7 4.4.7 6.5.1 2.5-.8 3.6-.6 5 .5.9.9 2.8 1.9 4.1 2.3 1.4.4 3.2 1.5 4 2.5 1 1 4.6 2.3 9 3.1 4.8.8 8.8 2.3 11.5 4.1 3.3 2.2 5.2 2.8 9.9 2.8 7.1 0 9.3-1.1 11.2-5.5 1.1-2.7 2-3.5 3.9-3.5 2.6 0 3.6-1.5 1.4-2.2-2.6-.9 3.1-4.8 7-4.8 4.4 0 6.2-1.4 6.2-4.9 0-1.8.9-3.1 3-4.3 3.5-2 3.8-3.8 1-6.3-1.7-1.5-2-3.1-2-9.2 0-5.1.3-7.2 1.1-6.7 3 1.8-.3-3.2-5.9-9-4.4-4.5-7.7-7-11-8.1-2.6-.9-5.4-2.1-6.2-2.6-.8-.5-2.9-1.5-4.6-2.3-3-1.3-3.2-1.8-4-8.8-.8-6.2-1.2-7.3-2.9-7.6-1.2-.2-2.4 0-2.8.4zM17 86c0 .5-.2 1-.4 1-.3 0-.8-.5-1.1-1-.3-.6-.1-1 .4-1 .6 0 1.1.4 1.1 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M69.7 26.6c-.4.4-1 2.9-1.3 5.5-.6 4.3-.9 4.7-2.5 3.9-2.9-1.6-3.4-1.3-7.8 4.9L54 46.8l-4-1.9c-2.2-1.1-4-2.4-4-2.9 0-.6-.6-1-1.3-1-.7 0-3-.9-5.2-2s-4.2-2-4.6-2c-.4 0-2 1.8-3.6 4-1.5 2.2-3.4 4-4 4-.7 0-1.3.8-1.3 1.7 0 .9-1.9 4.6-4.2 8.1l-4.2 6.5 2.4 1.9c2 1.6 2.4 2.9 2.4 7.4l.1 5.5-4 1.1c-5.6 1.6-6.7 2.6-6 5.9.3 1.5.1 3-.4 3.4-2.1 1.2-1.1 3.3 1.8 4 2 .4 2.8 1.1 2.4 2.1-.7 1.8.7 1.8 3.1-.1 2.2-1.6 5.6-2 5.6-.6 0 .5-.9 1.1-2 1.4-3 .8-2.4 2.2 1.3 3.3 2 .7 4.4.7 6.5.1 2.5-.8 3.6-.6 5 .5.9.9 2.8 1.9 4.1 2.3 1.4.4 3.2 1.5 4 2.5 1 1 4.6 2.3 9 3.1 4.8.8 8.8 2.3 11.5 4.1 3.3 2.2 5.2 2.8 9.9 2.8 7.1 0 9.3-1.1 11.2-5.5 1.1-2.7 2-3.5 3.9-3.5 2.6 0 3.6-1.5 1.4-2.2-2.6-.9 3.1-4.8 7-4.8 4.4 0 6.2-1.4 6.2-4.9 0-1.8.9-3.1 3-4.3 3.5-2 3.8-3.8 1-6.3-1.7-1.5-2-3.1-2-9.2 0-5.1.3-7.2 1.1-6.7 3 1.8-.3-3.2-5.9-9-4.4-4.5-7.7-7-11-8.1-2.6-.9-5.4-2.1-6.2-2.6-.8-.5-2.9-1.5-4.6-2.3-3-1.3-3.2-1.8-4-8.8-.8-6.2-1.2-7.3-2.9-7.6-1.2-.2-2.4 0-2.8.4zM17 86c0 .5-.2 1-.4 1-.3 0-.8-.5-1.1-1-.3-.6-.1-1 .4-1 .6 0 1.1.4 1.1 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				break;
				
				case 22:
					$sVG = '
					<svg class="buildingShape g22" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120">
		<g class="clickShape">
			<path d="M39 40.5c-3 5.2-7.3 11.8-9.7 14.7-4.6 5.6-4.8 6.7-1.7 8.3 1 .5 1.9 1.9 2 3 .1 1.1.4 3.1.9 4.4l.7 2.4-2.3-2.2C27.7 70 25.8 69 24.8 69c-2.5 0-5.2 4.4-4.4 7.5.5 2 .3 2.5-1.4 2.5-2.1 0-2.1.1-2.5 6.3-.2 3.8 1.6 5.9 4.2 5 1.3-.5 11.5 4.6 12.1 6 1.3 2.8 3.4 3.3 13 3.2l10.1-.2-1.3 2.6c-3.1 6.1.6 10.1 9.3 10.1 5.3 0 6.2-.3 8-2.7 1.6-1.9 2-3.4 1.5-5.8-.4-2.3-.2-3.4.9-3.8 1.6-.6 7.7 2.1 7.7 3.4 0 3.4 13.8-1.8 18.3-6.9 3-3.3 1.8-11.8-1.8-13.2-3.2-1.2-3.6-2.2-2.3-5.8.9-2.4.9-4.2 0-7.9-.6-2.6-1.4-7.1-1.7-9.8-.3-2.8-.7-5.9-1-7-.2-1.1-.4-2.8-.4-3.8-.2-3.8-1.8-1.4-2.7 3.9l-.9 5.6-3.2-4.6c-2.5-3.5-2.9-4.6-1.7-4.6 1.1 0 1.4-.5 1-1.6-.3-.9-.6-1.8-.6-2 0-.2-1.1-.4-2.4-.4-3.3 0-4-1.4-2.3-4.7.8-1.5 1.2-3.4 1-4.1-.7-1.8-3-1.5-4.3.5-.8 1.3-1.3 1.4-1.6.5-.8-1.7-4.3-1.5-5.1.4-.3.9-1.2 1.3-2.2.8-.9-.3-3.4-1-5.6-1.5-2.2-.5-5.1-1.2-6.5-1.5-1.4-.3-4.5-1.4-6.9-2.5-2.4-1-4.4-1.9-4.5-1.9-.1 0-2.6 4.3-5.6 9.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M39 40.5c-3 5.2-7.3 11.8-9.7 14.7-4.6 5.6-4.8 6.7-1.7 8.3 1 .5 1.9 1.9 2 3 .1 1.1.4 3.1.9 4.4l.7 2.4-2.3-2.2C27.7 70 25.8 69 24.8 69c-2.5 0-5.2 4.4-4.4 7.5.5 2 .3 2.5-1.4 2.5-2.1 0-2.1.1-2.5 6.3-.2 3.8 1.6 5.9 4.2 5 1.3-.5 11.5 4.6 12.1 6 1.3 2.8 3.4 3.3 13 3.2l10.1-.2-1.3 2.6c-3.1 6.1.6 10.1 9.3 10.1 5.3 0 6.2-.3 8-2.7 1.6-1.9 2-3.4 1.5-5.8-.4-2.3-.2-3.4.9-3.8 1.6-.6 7.7 2.1 7.7 3.4 0 3.4 13.8-1.8 18.3-6.9 3-3.3 1.8-11.8-1.8-13.2-3.2-1.2-3.6-2.2-2.3-5.8.9-2.4.9-4.2 0-7.9-.6-2.6-1.4-7.1-1.7-9.8-.3-2.8-.7-5.9-1-7-.2-1.1-.4-2.8-.4-3.8-.2-3.8-1.8-1.4-2.7 3.9l-.9 5.6-3.2-4.6c-2.5-3.5-2.9-4.6-1.7-4.6 1.1 0 1.4-.5 1-1.6-.3-.9-.6-1.8-.6-2 0-.2-1.1-.4-2.4-.4-3.3 0-4-1.4-2.3-4.7.8-1.5 1.2-3.4 1-4.1-.7-1.8-3-1.5-4.3.5-.8 1.3-1.3 1.4-1.6.5-.8-1.7-4.3-1.5-5.1.4-.3.9-1.2 1.3-2.2.8-.9-.3-3.4-1-5.6-1.5-2.2-.5-5.1-1.2-6.5-1.5-1.4-.3-4.5-1.4-6.9-2.5-2.4-1-4.4-1.9-4.5-1.9-.1 0-2.6 4.3-5.6 9.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M39 40.5c-3 5.2-7.3 11.8-9.7 14.7-4.6 5.6-4.8 6.7-1.7 8.3 1 .5 1.9 1.9 2 3 .1 1.1.4 3.1.9 4.4l.7 2.4-2.3-2.2C27.7 70 25.8 69 24.8 69c-2.5 0-5.2 4.4-4.4 7.5.5 2 .3 2.5-1.4 2.5-2.1 0-2.1.1-2.5 6.3-.2 3.8 1.6 5.9 4.2 5 1.3-.5 11.5 4.6 12.1 6 1.3 2.8 3.4 3.3 13 3.2l10.1-.2-1.3 2.6c-3.1 6.1.6 10.1 9.3 10.1 5.3 0 6.2-.3 8-2.7 1.6-1.9 2-3.4 1.5-5.8-.4-2.3-.2-3.4.9-3.8 1.6-.6 7.7 2.1 7.7 3.4 0 3.4 13.8-1.8 18.3-6.9 3-3.3 1.8-11.8-1.8-13.2-3.2-1.2-3.6-2.2-2.3-5.8.9-2.4.9-4.2 0-7.9-.6-2.6-1.4-7.1-1.7-9.8-.3-2.8-.7-5.9-1-7-.2-1.1-.4-2.8-.4-3.8-.2-3.8-1.8-1.4-2.7 3.9l-.9 5.6-3.2-4.6c-2.5-3.5-2.9-4.6-1.7-4.6 1.1 0 1.4-.5 1-1.6-.3-.9-.6-1.8-.6-2 0-.2-1.1-.4-2.4-.4-3.3 0-4-1.4-2.3-4.7.8-1.5 1.2-3.4 1-4.1-.7-1.8-3-1.5-4.3.5-.8 1.3-1.3 1.4-1.6.5-.8-1.7-4.3-1.5-5.1.4-.3.9-1.2 1.3-2.2.8-.9-.3-3.4-1-5.6-1.5-2.2-.5-5.1-1.2-6.5-1.5-1.4-.3-4.5-1.4-6.9-2.5-2.4-1-4.4-1.9-4.5-1.9-.1 0-2.6 4.3-5.6 9.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M39 40.5c-3 5.2-7.3 11.8-9.7 14.7-4.6 5.6-4.8 6.7-1.7 8.3 1 .5 1.9 1.9 2 3 .1 1.1.4 3.1.9 4.4l.7 2.4-2.3-2.2C27.7 70 25.8 69 24.8 69c-2.5 0-5.2 4.4-4.4 7.5.5 2 .3 2.5-1.4 2.5-2.1 0-2.1.1-2.5 6.3-.2 3.8 1.6 5.9 4.2 5 1.3-.5 11.5 4.6 12.1 6 1.3 2.8 3.4 3.3 13 3.2l10.1-.2-1.3 2.6c-3.1 6.1.6 10.1 9.3 10.1 5.3 0 6.2-.3 8-2.7 1.6-1.9 2-3.4 1.5-5.8-.4-2.3-.2-3.4.9-3.8 1.6-.6 7.7 2.1 7.7 3.4 0 3.4 13.8-1.8 18.3-6.9 3-3.3 1.8-11.8-1.8-13.2-3.2-1.2-3.6-2.2-2.3-5.8.9-2.4.9-4.2 0-7.9-.6-2.6-1.4-7.1-1.7-9.8-.3-2.8-.7-5.9-1-7-.2-1.1-.4-2.8-.4-3.8-.2-3.8-1.8-1.4-2.7 3.9l-.9 5.6-3.2-4.6c-2.5-3.5-2.9-4.6-1.7-4.6 1.1 0 1.4-.5 1-1.6-.3-.9-.6-1.8-.6-2 0-.2-1.1-.4-2.4-.4-3.3 0-4-1.4-2.3-4.7.8-1.5 1.2-3.4 1-4.1-.7-1.8-3-1.5-4.3.5-.8 1.3-1.3 1.4-1.6.5-.8-1.7-4.3-1.5-5.1.4-.3.9-1.2 1.3-2.2.8-.9-.3-3.4-1-5.6-1.5-2.2-.5-5.1-1.2-6.5-1.5-1.4-.3-4.5-1.4-6.9-2.5-2.4-1-4.4-1.9-4.5-1.9-.1 0-2.6 4.3-5.6 9.5z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>
					';
				break;

				

				case 23:
					$sVG = '
					<svg class="buildingShape g23" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
		<g class="clickShape">
			<path d="M90.5 20c.3.5 1.1 1 1.6 1 .6 0 .7-.5.4-1-.3-.6-1.1-1-1.6-1-.6 0-.7.4-.4 1zM85.8 20.7c.7.3 1.6.2 1.9-.1.4-.3-.2-.6-1.3-.5-1.1 0-1.4.3-.6.6zM72 23.6c-1.4.8-1.8 1.4-1 1.4.8-.1 2.4-.7 3.4-1.5 2.4-1.9 1-1.8-2.4.1zM77 26c-1.9 1.1-4.3 1.9-5.3 2-.9 0-3.5.6-5.7 1.2-4.2 1.4-5.5 3.3-5.2 8.1.1 1.5-.4 4.1-1.3 5.7-2.1 4-1.9 5.4 1.1 8.9C62.7 54.4 64 55 67 55c2.1 0 4.1-.4 4.4-.8.3-.4 2-.6 3.9-.4 2.7.3 3.7 1.1 5.1 3.8 2.2 4.7 2 8-.6 10.6-1.7 1.7-2.3 1.9-2.6.7-.3-.8-1.6-2.4-2.9-3.6-1.2-1.2-2.3-3.1-2.3-4.1 0-1.5-1.2-2.2-5.2-3.2C53.6 54.7 38 58 38 64c0 1-1.9 2.5-5.1 3.8-4.2 1.9-4.9 2.6-4.4 4.3.5 1.5.1 2.5-1.4 3.5-1.2.9-2.1 2.6-2.1 4.2-.1 2.7-4 8.2-6 8.2-1.7 0-1.1 2 .8 2.7.9.3 2.5 1.4 3.5 2.5 1 1 3.1 1.8 4.7 1.8 2 0 3 .5 3 1.5 0 2.4 7.9 7.5 11.7 7.5 3.7 0 8.2-1.9 10.2-4.4 1.4-1.7 3.6-2.3 2.7-.7-.3.4.4 1.7 1.5 2.7 1.6 1.4 3.6 1.9 8 1.9 5.4 0 6.2-.3 9-3.3 2-2.1 3.9-3.2 5.8-3.2 1.6 0 4-.9 5.4-2 1.5-1.2 4.1-2 6.4-2 2.2 0 4.8-.9 6.3-1.9l2.4-1.9-2-5.9C96 76.8 94 75.7 90.5 79l-2.2 2.1-2.2-4.1c-2.7-5.2-2.7-5.8.6-7.1 1.5-.6 3.4-1.5 4.1-2.2.8-.6 2.6-1.2 4.1-1.3 1.4-.1 2.7-.3 2.9-.4.1-.1.6-.3 1.1-.4.5 0 1.3-.4 1.7-.9.4-.4 2-.7 3.5-.7 1.9 0 3.4-.9 4.8-2.8 1.9-2.8 1.9-3 .3-6.2-.9-1.8-2.6-3.8-3.7-4.4-1.1-.6-2.4-1.7-3.1-2.5-1.1-1.4-7.1-1.2-10.8.4-1.4.6-1.7.5-1.2-.3.4-.6 2.4-1.6 4.5-2.2 3.1-.8 4.2-1.7 5-4.2.6-1.7 1.1-3.9 1.1-4.8 0-2.6-5.6-9.7-8.9-11.5-4-2-10.8-1.8-15.1.5zm9 24.1c0 2.1-2.8 3.3-4.6 1.8-1.9-1.6-.6-3.9 2.2-3.7 1.5.2 2.4.9 2.4 1.9zm3.7 15.6c.2.6-.5 1.4-1.7 1.8-2.6.8-3.3.3-2.4-1.9.7-1.9 3.5-1.8 4.1.1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M90.5 20c.3.5 1.1 1 1.6 1 .6 0 .7-.5.4-1-.3-.6-1.1-1-1.6-1-.6 0-.7.4-.4 1zM85.8 20.7c.7.3 1.6.2 1.9-.1.4-.3-.2-.6-1.3-.5-1.1 0-1.4.3-.6.6zM72 23.6c-1.4.8-1.8 1.4-1 1.4.8-.1 2.4-.7 3.4-1.5 2.4-1.9 1-1.8-2.4.1zM77 26c-1.9 1.1-4.3 1.9-5.3 2-.9 0-3.5.6-5.7 1.2-4.2 1.4-5.5 3.3-5.2 8.1.1 1.5-.4 4.1-1.3 5.7-2.1 4-1.9 5.4 1.1 8.9C62.7 54.4 64 55 67 55c2.1 0 4.1-.4 4.4-.8.3-.4 2-.6 3.9-.4 2.7.3 3.7 1.1 5.1 3.8 2.2 4.7 2 8-.6 10.6-1.7 1.7-2.3 1.9-2.6.7-.3-.8-1.6-2.4-2.9-3.6-1.2-1.2-2.3-3.1-2.3-4.1 0-1.5-1.2-2.2-5.2-3.2C53.6 54.7 38 58 38 64c0 1-1.9 2.5-5.1 3.8-4.2 1.9-4.9 2.6-4.4 4.3.5 1.5.1 2.5-1.4 3.5-1.2.9-2.1 2.6-2.1 4.2-.1 2.7-4 8.2-6 8.2-1.7 0-1.1 2 .8 2.7.9.3 2.5 1.4 3.5 2.5 1 1 3.1 1.8 4.7 1.8 2 0 3 .5 3 1.5 0 2.4 7.9 7.5 11.7 7.5 3.7 0 8.2-1.9 10.2-4.4 1.4-1.7 3.6-2.3 2.7-.7-.3.4.4 1.7 1.5 2.7 1.6 1.4 3.6 1.9 8 1.9 5.4 0 6.2-.3 9-3.3 2-2.1 3.9-3.2 5.8-3.2 1.6 0 4-.9 5.4-2 1.5-1.2 4.1-2 6.4-2 2.2 0 4.8-.9 6.3-1.9l2.4-1.9-2-5.9C96 76.8 94 75.7 90.5 79l-2.2 2.1-2.2-4.1c-2.7-5.2-2.7-5.8.6-7.1 1.5-.6 3.4-1.5 4.1-2.2.8-.6 2.6-1.2 4.1-1.3 1.4-.1 2.7-.3 2.9-.4.1-.1.6-.3 1.1-.4.5 0 1.3-.4 1.7-.9.4-.4 2-.7 3.5-.7 1.9 0 3.4-.9 4.8-2.8 1.9-2.8 1.9-3 .3-6.2-.9-1.8-2.6-3.8-3.7-4.4-1.1-.6-2.4-1.7-3.1-2.5-1.1-1.4-7.1-1.2-10.8.4-1.4.6-1.7.5-1.2-.3.4-.6 2.4-1.6 4.5-2.2 3.1-.8 4.2-1.7 5-4.2.6-1.7 1.1-3.9 1.1-4.8 0-2.6-5.6-9.7-8.9-11.5-4-2-10.8-1.8-15.1.5zm9 24.1c0 2.1-2.8 3.3-4.6 1.8-1.9-1.6-.6-3.9 2.2-3.7 1.5.2 2.4.9 2.4 1.9zm3.7 15.6c.2.6-.5 1.4-1.7 1.8-2.6.8-3.3.3-2.4-1.9.7-1.9 3.5-1.8 4.1.1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M90.5 20c.3.5 1.1 1 1.6 1 .6 0 .7-.5.4-1-.3-.6-1.1-1-1.6-1-.6 0-.7.4-.4 1zM85.8 20.7c.7.3 1.6.2 1.9-.1.4-.3-.2-.6-1.3-.5-1.1 0-1.4.3-.6.6zM72 23.6c-1.4.8-1.8 1.4-1 1.4.8-.1 2.4-.7 3.4-1.5 2.4-1.9 1-1.8-2.4.1zM77 26c-1.9 1.1-4.3 1.9-5.3 2-.9 0-3.5.6-5.7 1.2-4.2 1.4-5.5 3.3-5.2 8.1.1 1.5-.4 4.1-1.3 5.7-2.1 4-1.9 5.4 1.1 8.9C62.7 54.4 64 55 67 55c2.1 0 4.1-.4 4.4-.8.3-.4 2-.6 3.9-.4 2.7.3 3.7 1.1 5.1 3.8 2.2 4.7 2 8-.6 10.6-1.7 1.7-2.3 1.9-2.6.7-.3-.8-1.6-2.4-2.9-3.6-1.2-1.2-2.3-3.1-2.3-4.1 0-1.5-1.2-2.2-5.2-3.2C53.6 54.7 38 58 38 64c0 1-1.9 2.5-5.1 3.8-4.2 1.9-4.9 2.6-4.4 4.3.5 1.5.1 2.5-1.4 3.5-1.2.9-2.1 2.6-2.1 4.2-.1 2.7-4 8.2-6 8.2-1.7 0-1.1 2 .8 2.7.9.3 2.5 1.4 3.5 2.5 1 1 3.1 1.8 4.7 1.8 2 0 3 .5 3 1.5 0 2.4 7.9 7.5 11.7 7.5 3.7 0 8.2-1.9 10.2-4.4 1.4-1.7 3.6-2.3 2.7-.7-.3.4.4 1.7 1.5 2.7 1.6 1.4 3.6 1.9 8 1.9 5.4 0 6.2-.3 9-3.3 2-2.1 3.9-3.2 5.8-3.2 1.6 0 4-.9 5.4-2 1.5-1.2 4.1-2 6.4-2 2.2 0 4.8-.9 6.3-1.9l2.4-1.9-2-5.9C96 76.8 94 75.7 90.5 79l-2.2 2.1-2.2-4.1c-2.7-5.2-2.7-5.8.6-7.1 1.5-.6 3.4-1.5 4.1-2.2.8-.6 2.6-1.2 4.1-1.3 1.4-.1 2.7-.3 2.9-.4.1-.1.6-.3 1.1-.4.5 0 1.3-.4 1.7-.9.4-.4 2-.7 3.5-.7 1.9 0 3.4-.9 4.8-2.8 1.9-2.8 1.9-3 .3-6.2-.9-1.8-2.6-3.8-3.7-4.4-1.1-.6-2.4-1.7-3.1-2.5-1.1-1.4-7.1-1.2-10.8.4-1.4.6-1.7.5-1.2-.3.4-.6 2.4-1.6 4.5-2.2 3.1-.8 4.2-1.7 5-4.2.6-1.7 1.1-3.9 1.1-4.8 0-2.6-5.6-9.7-8.9-11.5-4-2-10.8-1.8-15.1.5zm9 24.1c0 2.1-2.8 3.3-4.6 1.8-1.9-1.6-.6-3.9 2.2-3.7 1.5.2 2.4.9 2.4 1.9zm3.7 15.6c.2.6-.5 1.4-1.7 1.8-2.6.8-3.3.3-2.4-1.9.7-1.9 3.5-1.8 4.1.1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M90.5 20c.3.5 1.1 1 1.6 1 .6 0 .7-.5.4-1-.3-.6-1.1-1-1.6-1-.6 0-.7.4-.4 1zM85.8 20.7c.7.3 1.6.2 1.9-.1.4-.3-.2-.6-1.3-.5-1.1 0-1.4.3-.6.6zM72 23.6c-1.4.8-1.8 1.4-1 1.4.8-.1 2.4-.7 3.4-1.5 2.4-1.9 1-1.8-2.4.1zM77 26c-1.9 1.1-4.3 1.9-5.3 2-.9 0-3.5.6-5.7 1.2-4.2 1.4-5.5 3.3-5.2 8.1.1 1.5-.4 4.1-1.3 5.7-2.1 4-1.9 5.4 1.1 8.9C62.7 54.4 64 55 67 55c2.1 0 4.1-.4 4.4-.8.3-.4 2-.6 3.9-.4 2.7.3 3.7 1.1 5.1 3.8 2.2 4.7 2 8-.6 10.6-1.7 1.7-2.3 1.9-2.6.7-.3-.8-1.6-2.4-2.9-3.6-1.2-1.2-2.3-3.1-2.3-4.1 0-1.5-1.2-2.2-5.2-3.2C53.6 54.7 38 58 38 64c0 1-1.9 2.5-5.1 3.8-4.2 1.9-4.9 2.6-4.4 4.3.5 1.5.1 2.5-1.4 3.5-1.2.9-2.1 2.6-2.1 4.2-.1 2.7-4 8.2-6 8.2-1.7 0-1.1 2 .8 2.7.9.3 2.5 1.4 3.5 2.5 1 1 3.1 1.8 4.7 1.8 2 0 3 .5 3 1.5 0 2.4 7.9 7.5 11.7 7.5 3.7 0 8.2-1.9 10.2-4.4 1.4-1.7 3.6-2.3 2.7-.7-.3.4.4 1.7 1.5 2.7 1.6 1.4 3.6 1.9 8 1.9 5.4 0 6.2-.3 9-3.3 2-2.1 3.9-3.2 5.8-3.2 1.6 0 4-.9 5.4-2 1.5-1.2 4.1-2 6.4-2 2.2 0 4.8-.9 6.3-1.9l2.4-1.9-2-5.9C96 76.8 94 75.7 90.5 79l-2.2 2.1-2.2-4.1c-2.7-5.2-2.7-5.8.6-7.1 1.5-.6 3.4-1.5 4.1-2.2.8-.6 2.6-1.2 4.1-1.3 1.4-.1 2.7-.3 2.9-.4.1-.1.6-.3 1.1-.4.5 0 1.3-.4 1.7-.9.4-.4 2-.7 3.5-.7 1.9 0 3.4-.9 4.8-2.8 1.9-2.8 1.9-3 .3-6.2-.9-1.8-2.6-3.8-3.7-4.4-1.1-.6-2.4-1.7-3.1-2.5-1.1-1.4-7.1-1.2-10.8.4-1.4.6-1.7.5-1.2-.3.4-.6 2.4-1.6 4.5-2.2 3.1-.8 4.2-1.7 5-4.2.6-1.7 1.1-3.9 1.1-4.8 0-2.6-5.6-9.7-8.9-11.5-4-2-10.8-1.8-15.1.5zm9 24.1c0 2.1-2.8 3.3-4.6 1.8-1.9-1.6-.6-3.9 2.2-3.7 1.5.2 2.4.9 2.4 1.9zm3.7 15.6c.2.6-.5 1.4-1.7 1.8-2.6.8-3.3.3-2.4-1.9.7-1.9 3.5-1.8 4.1.1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>
					';
				break;

				case 24:
					$sVG = '<svg class="buildingShape g24" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
		<g class="clickShape">
			<path d="M47.3 29.2c-.4.7-.6 2-.5 2.8.4 2.5-12.2 22.4-15.2 23.8-3.4 1.7-3.8 7.7-.6 8.7 1.7.5 2 1.5 2 6.1 0 5.1-.2 5.5-2 4.9-1.3-.4-2.2-.1-2.6.8-.6 1.6-4.9 3.2-7.3 2.8-.7-.1-2.2.7-3.2 1.8-2.3 2.4-1.9 6.7.5 6.5 2-.2 2 .3 0 2.3-.8.9-1.3 2.4-1.1 3.4.3 1.1.1 2.1-.4 2.5-1.1.7 1.9 5.4 3.5 5.4.7 0 3 .9 5.2 1.9 2.1 1 7.2 2.4 11.1 3 4 .6 9.6 2 12.5 3 8.6 3.1 19.8 4.3 28.1 3 11-1.7 28.7-11.1 28.7-15.2 0-.7-1.6-2.8-3.5-4.7-3.4-3.5-4.7-8.2-2.4-9.5.7-.5.5-1.2-.5-2.4-.8-.9-1.9-2.9-2.5-4.4-1.3-3.3-3.5-3.1-3.9.4-.2 1.3-.8 2.8-1.4 3.4-.7.7-.8-.8-.3-4.5.4-3 1.1-5.7 1.5-6 1.4-1 1.7-6 .4-7.1-.9-.8-1.4-3.4-1.4-7.6v-6.5l2.8.6c1.5.3 4.1-.1 5.7-.9 3-1.3 3-1.3.5-1.5l-2.5-.1 2.8-1.5c3.3-1.8 3.5-2.9.5-2-3.5.8-9.8-.4-9.8-2 0-.8-.4-1.4-1-1.4s-1 4.1-1 10.2v10.2l-4.5-7.3c-3.3-5.4-4.3-7.9-3.9-9.7.8-3.2-2.3-4.6-4.2-1.9-1.3 1.8-1.7 1.7-9.1-.7-12.4-4.1-17.6-6.6-17-8.2.5-1.3-1-3.6-2.4-3.6-.4 0-1.1.6-1.6 1.2zM27 85c0 .5-.5 1-1.1 1-.5 0-.7-.5-.4-1 .3-.6.8-1 1.1-1 .2 0 .4.4.4 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M47.3 29.2c-.4.7-.6 2-.5 2.8.4 2.5-12.2 22.4-15.2 23.8-3.4 1.7-3.8 7.7-.6 8.7 1.7.5 2 1.5 2 6.1 0 5.1-.2 5.5-2 4.9-1.3-.4-2.2-.1-2.6.8-.6 1.6-4.9 3.2-7.3 2.8-.7-.1-2.2.7-3.2 1.8-2.3 2.4-1.9 6.7.5 6.5 2-.2 2 .3 0 2.3-.8.9-1.3 2.4-1.1 3.4.3 1.1.1 2.1-.4 2.5-1.1.7 1.9 5.4 3.5 5.4.7 0 3 .9 5.2 1.9 2.1 1 7.2 2.4 11.1 3 4 .6 9.6 2 12.5 3 8.6 3.1 19.8 4.3 28.1 3 11-1.7 28.7-11.1 28.7-15.2 0-.7-1.6-2.8-3.5-4.7-3.4-3.5-4.7-8.2-2.4-9.5.7-.5.5-1.2-.5-2.4-.8-.9-1.9-2.9-2.5-4.4-1.3-3.3-3.5-3.1-3.9.4-.2 1.3-.8 2.8-1.4 3.4-.7.7-.8-.8-.3-4.5.4-3 1.1-5.7 1.5-6 1.4-1 1.7-6 .4-7.1-.9-.8-1.4-3.4-1.4-7.6v-6.5l2.8.6c1.5.3 4.1-.1 5.7-.9 3-1.3 3-1.3.5-1.5l-2.5-.1 2.8-1.5c3.3-1.8 3.5-2.9.5-2-3.5.8-9.8-.4-9.8-2 0-.8-.4-1.4-1-1.4s-1 4.1-1 10.2v10.2l-4.5-7.3c-3.3-5.4-4.3-7.9-3.9-9.7.8-3.2-2.3-4.6-4.2-1.9-1.3 1.8-1.7 1.7-9.1-.7-12.4-4.1-17.6-6.6-17-8.2.5-1.3-1-3.6-2.4-3.6-.4 0-1.1.6-1.6 1.2zM27 85c0 .5-.5 1-1.1 1-.5 0-.7-.5-.4-1 .3-.6.8-1 1.1-1 .2 0 .4.4.4 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M47.3 29.2c-.4.7-.6 2-.5 2.8.4 2.5-12.2 22.4-15.2 23.8-3.4 1.7-3.8 7.7-.6 8.7 1.7.5 2 1.5 2 6.1 0 5.1-.2 5.5-2 4.9-1.3-.4-2.2-.1-2.6.8-.6 1.6-4.9 3.2-7.3 2.8-.7-.1-2.2.7-3.2 1.8-2.3 2.4-1.9 6.7.5 6.5 2-.2 2 .3 0 2.3-.8.9-1.3 2.4-1.1 3.4.3 1.1.1 2.1-.4 2.5-1.1.7 1.9 5.4 3.5 5.4.7 0 3 .9 5.2 1.9 2.1 1 7.2 2.4 11.1 3 4 .6 9.6 2 12.5 3 8.6 3.1 19.8 4.3 28.1 3 11-1.7 28.7-11.1 28.7-15.2 0-.7-1.6-2.8-3.5-4.7-3.4-3.5-4.7-8.2-2.4-9.5.7-.5.5-1.2-.5-2.4-.8-.9-1.9-2.9-2.5-4.4-1.3-3.3-3.5-3.1-3.9.4-.2 1.3-.8 2.8-1.4 3.4-.7.7-.8-.8-.3-4.5.4-3 1.1-5.7 1.5-6 1.4-1 1.7-6 .4-7.1-.9-.8-1.4-3.4-1.4-7.6v-6.5l2.8.6c1.5.3 4.1-.1 5.7-.9 3-1.3 3-1.3.5-1.5l-2.5-.1 2.8-1.5c3.3-1.8 3.5-2.9.5-2-3.5.8-9.8-.4-9.8-2 0-.8-.4-1.4-1-1.4s-1 4.1-1 10.2v10.2l-4.5-7.3c-3.3-5.4-4.3-7.9-3.9-9.7.8-3.2-2.3-4.6-4.2-1.9-1.3 1.8-1.7 1.7-9.1-.7-12.4-4.1-17.6-6.6-17-8.2.5-1.3-1-3.6-2.4-3.6-.4 0-1.1.6-1.6 1.2zM27 85c0 .5-.5 1-1.1 1-.5 0-.7-.5-.4-1 .3-.6.8-1 1.1-1 .2 0 .4.4.4 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M47.3 29.2c-.4.7-.6 2-.5 2.8.4 2.5-12.2 22.4-15.2 23.8-3.4 1.7-3.8 7.7-.6 8.7 1.7.5 2 1.5 2 6.1 0 5.1-.2 5.5-2 4.9-1.3-.4-2.2-.1-2.6.8-.6 1.6-4.9 3.2-7.3 2.8-.7-.1-2.2.7-3.2 1.8-2.3 2.4-1.9 6.7.5 6.5 2-.2 2 .3 0 2.3-.8.9-1.3 2.4-1.1 3.4.3 1.1.1 2.1-.4 2.5-1.1.7 1.9 5.4 3.5 5.4.7 0 3 .9 5.2 1.9 2.1 1 7.2 2.4 11.1 3 4 .6 9.6 2 12.5 3 8.6 3.1 19.8 4.3 28.1 3 11-1.7 28.7-11.1 28.7-15.2 0-.7-1.6-2.8-3.5-4.7-3.4-3.5-4.7-8.2-2.4-9.5.7-.5.5-1.2-.5-2.4-.8-.9-1.9-2.9-2.5-4.4-1.3-3.3-3.5-3.1-3.9.4-.2 1.3-.8 2.8-1.4 3.4-.7.7-.8-.8-.3-4.5.4-3 1.1-5.7 1.5-6 1.4-1 1.7-6 .4-7.1-.9-.8-1.4-3.4-1.4-7.6v-6.5l2.8.6c1.5.3 4.1-.1 5.7-.9 3-1.3 3-1.3.5-1.5l-2.5-.1 2.8-1.5c3.3-1.8 3.5-2.9.5-2-3.5.8-9.8-.4-9.8-2 0-.8-.4-1.4-1-1.4s-1 4.1-1 10.2v10.2l-4.5-7.3c-3.3-5.4-4.3-7.9-3.9-9.7.8-3.2-2.3-4.6-4.2-1.9-1.3 1.8-1.7 1.7-9.1-.7-12.4-4.1-17.6-6.6-17-8.2.5-1.3-1-3.6-2.4-3.6-.4 0-1.1.6-1.6 1.2zM27 85c0 .5-.5 1-1.1 1-.5 0-.7-.5-.4-1 .3-.6.8-1 1.1-1 .2 0 .4.4.4 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				break;

				case 25:
					$sVG = '
					<svg class="buildingShape g25" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
		<g class="clickShape">
			<path d="M75.6 13.7c.1 6.6-1.4 9.7-6 12-7.1 3.6-9.3 7.7-6.1 11.9.8 1 1.5 3.5 1.5 5.4 0 3.5-.1 3.6-3.7 3.7-2.1.1-5.5.4-7.5.8-3.5.7-3.8.5-3.9-1.6-.2-7.1-1.4-9.2-5.4-9.2-3 0-4.5 1.7-4.5 5 0 2.2-.1 2.2-3 .5-2.3-1.3-3-2.4-3-4.9 0-3.1.1-3.1 4-2.6 4.4.6 5.3-.2 1.8-1.7l-2.3-.9h2.3c1.3-.1 2-.5 1.7-1.1-.4-.6-1.4-.8-2.4-.5-1 .3-2.7-.4-3.9-1.5-1.2-1.2-2.2-1.9-2.2-1.8-.1.2-.2 3.1-.4 6.5-.2 5.9-.5 6.5-4.1 9.6-3.3 2.9-3.9 4-4.1 8-.2 2.9-1.6 7.1-3.8 11.5-4.2 7.9-4.7 13.5-1.6 15.2 2.8 1.5 2.7 5.8-.1 6.2-1.2.2-2.6 1-3.2 1.9-1.5 2.3 0 6.7 2.5 7.5 2 .6 2 .8.5 1.9-.9.6-1.3 1.5-.9 1.9.3.4.7 1.5.7 2.4 0 .9.5 1.7 1.2 1.7s2.3.9 3.5 2c1.3 1.1 2.6 1.9 3 1.7.4-.1 1.2.7 1.8 1.8.6 1.1 1.7 2 2.5 2 .7 0 2.1.8 3.1 1.7 2.4 2.4 3.7 2.7 13.9 2.7 17.2 0 24.1-2.7 31.1-11.9 2.3-2.9 4.3-4.5 5.7-4.5 1.2 0 4-.7 6.2-1.4 3.6-1.3 4.1-1.8 4.7-5.7.7-3.8.5-4.6-1.9-7.1-1.5-1.5-3.7-2.8-5-2.8-2.3 0-2.3-.1-2.2-12.8 0-7 .4-14.7.8-17.2.8-4.4.9-4.3 1 2.3.1 5 .4 6.8 1.4 6.4.6-.2 2.4 0 4 .4 2.7.8 2.7.7 2.7-4.1 0-5.4 1.1-6.8 3.3-4.5.9.8 2.2 1.2 3.1.9 2.3-.9-.3-4.6-2.7-3.8-1.6.5-1.8.2-1.2-1.5.6-2 .3-2.1-5.3-2.1-3.2 0-6.1-.3-6.5-.6-.3-.3.3-2.2 1.3-4.2 3.1-5.8 1-10.6-6.1-14.3-2-1-3.9-2.5-4.3-3.4-.9-2.6-.7-6.5.4-6.5.6 0 1 .4 1 1 0 .5 1.4 1 3 1 3.1 0 4.3-1.9 1.3-2.1-1.6 0-1.6-.1.1-.8 2.3-.9 1.4-1.6-3.1-2.6-1.9-.4-3.3-1.3-3.3-2.1S76.7 8 76.3 8c-.5 0-.8 2.6-.7 5.7zM102 50.1c0 .6-.4.7-1 .4-.5-.3-1-1.1-1-1.6 0-.6.5-.7 1-.4.6.3 1 1.1 1 1.6z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M75.6 13.7c.1 6.6-1.4 9.7-6 12-7.1 3.6-9.3 7.7-6.1 11.9.8 1 1.5 3.5 1.5 5.4 0 3.5-.1 3.6-3.7 3.7-2.1.1-5.5.4-7.5.8-3.5.7-3.8.5-3.9-1.6-.2-7.1-1.4-9.2-5.4-9.2-3 0-4.5 1.7-4.5 5 0 2.2-.1 2.2-3 .5-2.3-1.3-3-2.4-3-4.9 0-3.1.1-3.1 4-2.6 4.4.6 5.3-.2 1.8-1.7l-2.3-.9h2.3c1.3-.1 2-.5 1.7-1.1-.4-.6-1.4-.8-2.4-.5-1 .3-2.7-.4-3.9-1.5-1.2-1.2-2.2-1.9-2.2-1.8-.1.2-.2 3.1-.4 6.5-.2 5.9-.5 6.5-4.1 9.6-3.3 2.9-3.9 4-4.1 8-.2 2.9-1.6 7.1-3.8 11.5-4.2 7.9-4.7 13.5-1.6 15.2 2.8 1.5 2.7 5.8-.1 6.2-1.2.2-2.6 1-3.2 1.9-1.5 2.3 0 6.7 2.5 7.5 2 .6 2 .8.5 1.9-.9.6-1.3 1.5-.9 1.9.3.4.7 1.5.7 2.4 0 .9.5 1.7 1.2 1.7s2.3.9 3.5 2c1.3 1.1 2.6 1.9 3 1.7.4-.1 1.2.7 1.8 1.8.6 1.1 1.7 2 2.5 2 .7 0 2.1.8 3.1 1.7 2.4 2.4 3.7 2.7 13.9 2.7 17.2 0 24.1-2.7 31.1-11.9 2.3-2.9 4.3-4.5 5.7-4.5 1.2 0 4-.7 6.2-1.4 3.6-1.3 4.1-1.8 4.7-5.7.7-3.8.5-4.6-1.9-7.1-1.5-1.5-3.7-2.8-5-2.8-2.3 0-2.3-.1-2.2-12.8 0-7 .4-14.7.8-17.2.8-4.4.9-4.3 1 2.3.1 5 .4 6.8 1.4 6.4.6-.2 2.4 0 4 .4 2.7.8 2.7.7 2.7-4.1 0-5.4 1.1-6.8 3.3-4.5.9.8 2.2 1.2 3.1.9 2.3-.9-.3-4.6-2.7-3.8-1.6.5-1.8.2-1.2-1.5.6-2 .3-2.1-5.3-2.1-3.2 0-6.1-.3-6.5-.6-.3-.3.3-2.2 1.3-4.2 3.1-5.8 1-10.6-6.1-14.3-2-1-3.9-2.5-4.3-3.4-.9-2.6-.7-6.5.4-6.5.6 0 1 .4 1 1 0 .5 1.4 1 3 1 3.1 0 4.3-1.9 1.3-2.1-1.6 0-1.6-.1.1-.8 2.3-.9 1.4-1.6-3.1-2.6-1.9-.4-3.3-1.3-3.3-2.1S76.7 8 76.3 8c-.5 0-.8 2.6-.7 5.7zM102 50.1c0 .6-.4.7-1 .4-.5-.3-1-1.1-1-1.6 0-.6.5-.7 1-.4.6.3 1 1.1 1 1.6z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M75.6 13.7c.1 6.6-1.4 9.7-6 12-7.1 3.6-9.3 7.7-6.1 11.9.8 1 1.5 3.5 1.5 5.4 0 3.5-.1 3.6-3.7 3.7-2.1.1-5.5.4-7.5.8-3.5.7-3.8.5-3.9-1.6-.2-7.1-1.4-9.2-5.4-9.2-3 0-4.5 1.7-4.5 5 0 2.2-.1 2.2-3 .5-2.3-1.3-3-2.4-3-4.9 0-3.1.1-3.1 4-2.6 4.4.6 5.3-.2 1.8-1.7l-2.3-.9h2.3c1.3-.1 2-.5 1.7-1.1-.4-.6-1.4-.8-2.4-.5-1 .3-2.7-.4-3.9-1.5-1.2-1.2-2.2-1.9-2.2-1.8-.1.2-.2 3.1-.4 6.5-.2 5.9-.5 6.5-4.1 9.6-3.3 2.9-3.9 4-4.1 8-.2 2.9-1.6 7.1-3.8 11.5-4.2 7.9-4.7 13.5-1.6 15.2 2.8 1.5 2.7 5.8-.1 6.2-1.2.2-2.6 1-3.2 1.9-1.5 2.3 0 6.7 2.5 7.5 2 .6 2 .8.5 1.9-.9.6-1.3 1.5-.9 1.9.3.4.7 1.5.7 2.4 0 .9.5 1.7 1.2 1.7s2.3.9 3.5 2c1.3 1.1 2.6 1.9 3 1.7.4-.1 1.2.7 1.8 1.8.6 1.1 1.7 2 2.5 2 .7 0 2.1.8 3.1 1.7 2.4 2.4 3.7 2.7 13.9 2.7 17.2 0 24.1-2.7 31.1-11.9 2.3-2.9 4.3-4.5 5.7-4.5 1.2 0 4-.7 6.2-1.4 3.6-1.3 4.1-1.8 4.7-5.7.7-3.8.5-4.6-1.9-7.1-1.5-1.5-3.7-2.8-5-2.8-2.3 0-2.3-.1-2.2-12.8 0-7 .4-14.7.8-17.2.8-4.4.9-4.3 1 2.3.1 5 .4 6.8 1.4 6.4.6-.2 2.4 0 4 .4 2.7.8 2.7.7 2.7-4.1 0-5.4 1.1-6.8 3.3-4.5.9.8 2.2 1.2 3.1.9 2.3-.9-.3-4.6-2.7-3.8-1.6.5-1.8.2-1.2-1.5.6-2 .3-2.1-5.3-2.1-3.2 0-6.1-.3-6.5-.6-.3-.3.3-2.2 1.3-4.2 3.1-5.8 1-10.6-6.1-14.3-2-1-3.9-2.5-4.3-3.4-.9-2.6-.7-6.5.4-6.5.6 0 1 .4 1 1 0 .5 1.4 1 3 1 3.1 0 4.3-1.9 1.3-2.1-1.6 0-1.6-.1.1-.8 2.3-.9 1.4-1.6-3.1-2.6-1.9-.4-3.3-1.3-3.3-2.1S76.7 8 76.3 8c-.5 0-.8 2.6-.7 5.7zM102 50.1c0 .6-.4.7-1 .4-.5-.3-1-1.1-1-1.6 0-.6.5-.7 1-.4.6.3 1 1.1 1 1.6z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M75.6 13.7c.1 6.6-1.4 9.7-6 12-7.1 3.6-9.3 7.7-6.1 11.9.8 1 1.5 3.5 1.5 5.4 0 3.5-.1 3.6-3.7 3.7-2.1.1-5.5.4-7.5.8-3.5.7-3.8.5-3.9-1.6-.2-7.1-1.4-9.2-5.4-9.2-3 0-4.5 1.7-4.5 5 0 2.2-.1 2.2-3 .5-2.3-1.3-3-2.4-3-4.9 0-3.1.1-3.1 4-2.6 4.4.6 5.3-.2 1.8-1.7l-2.3-.9h2.3c1.3-.1 2-.5 1.7-1.1-.4-.6-1.4-.8-2.4-.5-1 .3-2.7-.4-3.9-1.5-1.2-1.2-2.2-1.9-2.2-1.8-.1.2-.2 3.1-.4 6.5-.2 5.9-.5 6.5-4.1 9.6-3.3 2.9-3.9 4-4.1 8-.2 2.9-1.6 7.1-3.8 11.5-4.2 7.9-4.7 13.5-1.6 15.2 2.8 1.5 2.7 5.8-.1 6.2-1.2.2-2.6 1-3.2 1.9-1.5 2.3 0 6.7 2.5 7.5 2 .6 2 .8.5 1.9-.9.6-1.3 1.5-.9 1.9.3.4.7 1.5.7 2.4 0 .9.5 1.7 1.2 1.7s2.3.9 3.5 2c1.3 1.1 2.6 1.9 3 1.7.4-.1 1.2.7 1.8 1.8.6 1.1 1.7 2 2.5 2 .7 0 2.1.8 3.1 1.7 2.4 2.4 3.7 2.7 13.9 2.7 17.2 0 24.1-2.7 31.1-11.9 2.3-2.9 4.3-4.5 5.7-4.5 1.2 0 4-.7 6.2-1.4 3.6-1.3 4.1-1.8 4.7-5.7.7-3.8.5-4.6-1.9-7.1-1.5-1.5-3.7-2.8-5-2.8-2.3 0-2.3-.1-2.2-12.8 0-7 .4-14.7.8-17.2.8-4.4.9-4.3 1 2.3.1 5 .4 6.8 1.4 6.4.6-.2 2.4 0 4 .4 2.7.8 2.7.7 2.7-4.1 0-5.4 1.1-6.8 3.3-4.5.9.8 2.2 1.2 3.1.9 2.3-.9-.3-4.6-2.7-3.8-1.6.5-1.8.2-1.2-1.5.6-2 .3-2.1-5.3-2.1-3.2 0-6.1-.3-6.5-.6-.3-.3.3-2.2 1.3-4.2 3.1-5.8 1-10.6-6.1-14.3-2-1-3.9-2.5-4.3-3.4-.9-2.6-.7-6.5.4-6.5.6 0 1 .4 1 1 0 .5 1.4 1 3 1 3.1 0 4.3-1.9 1.3-2.1-1.6 0-1.6-.1.1-.8 2.3-.9 1.4-1.6-3.1-2.6-1.9-.4-3.3-1.3-3.3-2.1S76.7 8 76.3 8c-.5 0-.8 2.6-.7 5.7zM102 50.1c0 .6-.4.7-1 .4-.5-.3-1-1.1-1-1.6 0-.6.5-.7 1-.4.6.3 1 1.1 1 1.6z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>
					';
				break;
				
				case 26:
					$sVG = '<svg class="buildingShape g26" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
		<g class="clickShape">
			<path d="M59.7 12.2c.8 7-1.3 16-4.2 18.1-3.9 2.9-4.9 5.3-3 7.9 2.2 3.3 1.9 5.1-.8 4.4-1.9-.4-2.8.3-5.4 4.7-2.9 4.6-3.2 4.9-3.3 2.6 0-1.5.7-3.2 1.5-3.9 3.5-2.9.3-9.6-6-12.6-1.9-.8-4.1-2.6-4.9-3.9-1.7-2.6-1.1-7.1.8-5.6.8.7 2.3.7 4.2.2 1.6-.5 2.1-1 1.2-1-1-.1-1.8-.5-1.8-1s.8-1.2 1.8-1.4c1.1-.3.7-.5-1-.6C35 20 33 19 33 17.4c0-.8-.4-1.4-1-1.4-.5 0-1 2.5-1 5.5 0 6.9-1.2 8.8-7.7 13.1-6.4 4.1-7.6 7.2-4.4 11.4 1.8 2.6 2.1 4.3 2.1 13.6 0 8.1-.3 10.9-1.4 11.8-1.3.9-2.1 7.6-1.7 13.3.1.6 2.2 1.9 4.9 2.9 5.1 2 5.3 2.1 9.6 5.9l2.8 2.5-1.7 3.7c-1.6 3.3-1.6 4-.4 6.3 2.6 4.4 8.2 6.3 16.8 5.5 13-1.2 17.7-1 20.5.8 3.1 2 7.5 2.2 10.1.3 1.8-1.3 1.8-2.6 0-7-.7-1.6-.5-1.8 2.1-1.3 9.9 2.1 8.9 2.3 15.4-3.4 5.8-5.2 6.1-5.6 5.4-8.9-.3-1.9-.8-12.6-1.1-23.8-.3-17.1-.1-20.6 1.2-22.4 2.8-4.2 1.2-9.3-3.8-12.3-4.8-2.9-7.7-6.5-7.7-9.6 0-2.9 1.5-4 2.2-1.8.3.9 1.2.9 3.4.1 1.7-.6 2.2-1.1 1.2-1.1-1-.1-1.8-.5-1.8-1s.8-1.2 1.8-1.4c1.1-.3.7-.5-1-.6C94 18 92 17 92 15.4c0-.8-.3-1.4-.7-1.4-.5 0-.8 2.1-.9 4.8-.1 8.2-.9 9.8-6.8 13.1-7.2 4.1-8.8 7.8-5.9 13.2 1.1 2 1.7 4.3 1.3 4.9-.5.9-.9.9-1.4.1-.4-.6-1.4-1.1-2.3-1.1-1.1 0-1.4.6-.9 2.4.5 2 .3 2.3-1.2 1.9-4.6-1.2-5.4-2.8-4.8-9.5.3-3.5.6-7.2.6-8.3.1-1.1-1.3-3.1-3-4.5-3.1-2.5-5-7.3-5-12.2 0-3.1.3-3.3 2.8-1.7 1.4.9 2.7.9 4.8.1 2.5-.9 2.6-1.1.7-1.1C68 16 67 15.5 67 15c0-.6 1-1 2.3-1 1.7 0 1.9-.2.7-1-.8-.5-2.5-.7-3.6-.4-1.2.3-2.5.1-2.9-.5-.3-.6-1.1-.9-1.6-.5-.6.3-.9-.2-.7-1.1.2-1-.2-2-.8-2.2-.8-.3-1 1-.7 3.9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M59.7 12.2c.8 7-1.3 16-4.2 18.1-3.9 2.9-4.9 5.3-3 7.9 2.2 3.3 1.9 5.1-.8 4.4-1.9-.4-2.8.3-5.4 4.7-2.9 4.6-3.2 4.9-3.3 2.6 0-1.5.7-3.2 1.5-3.9 3.5-2.9.3-9.6-6-12.6-1.9-.8-4.1-2.6-4.9-3.9-1.7-2.6-1.1-7.1.8-5.6.8.7 2.3.7 4.2.2 1.6-.5 2.1-1 1.2-1-1-.1-1.8-.5-1.8-1s.8-1.2 1.8-1.4c1.1-.3.7-.5-1-.6C35 20 33 19 33 17.4c0-.8-.4-1.4-1-1.4-.5 0-1 2.5-1 5.5 0 6.9-1.2 8.8-7.7 13.1-6.4 4.1-7.6 7.2-4.4 11.4 1.8 2.6 2.1 4.3 2.1 13.6 0 8.1-.3 10.9-1.4 11.8-1.3.9-2.1 7.6-1.7 13.3.1.6 2.2 1.9 4.9 2.9 5.1 2 5.3 2.1 9.6 5.9l2.8 2.5-1.7 3.7c-1.6 3.3-1.6 4-.4 6.3 2.6 4.4 8.2 6.3 16.8 5.5 13-1.2 17.7-1 20.5.8 3.1 2 7.5 2.2 10.1.3 1.8-1.3 1.8-2.6 0-7-.7-1.6-.5-1.8 2.1-1.3 9.9 2.1 8.9 2.3 15.4-3.4 5.8-5.2 6.1-5.6 5.4-8.9-.3-1.9-.8-12.6-1.1-23.8-.3-17.1-.1-20.6 1.2-22.4 2.8-4.2 1.2-9.3-3.8-12.3-4.8-2.9-7.7-6.5-7.7-9.6 0-2.9 1.5-4 2.2-1.8.3.9 1.2.9 3.4.1 1.7-.6 2.2-1.1 1.2-1.1-1-.1-1.8-.5-1.8-1s.8-1.2 1.8-1.4c1.1-.3.7-.5-1-.6C94 18 92 17 92 15.4c0-.8-.3-1.4-.7-1.4-.5 0-.8 2.1-.9 4.8-.1 8.2-.9 9.8-6.8 13.1-7.2 4.1-8.8 7.8-5.9 13.2 1.1 2 1.7 4.3 1.3 4.9-.5.9-.9.9-1.4.1-.4-.6-1.4-1.1-2.3-1.1-1.1 0-1.4.6-.9 2.4.5 2 .3 2.3-1.2 1.9-4.6-1.2-5.4-2.8-4.8-9.5.3-3.5.6-7.2.6-8.3.1-1.1-1.3-3.1-3-4.5-3.1-2.5-5-7.3-5-12.2 0-3.1.3-3.3 2.8-1.7 1.4.9 2.7.9 4.8.1 2.5-.9 2.6-1.1.7-1.1C68 16 67 15.5 67 15c0-.6 1-1 2.3-1 1.7 0 1.9-.2.7-1-.8-.5-2.5-.7-3.6-.4-1.2.3-2.5.1-2.9-.5-.3-.6-1.1-.9-1.6-.5-.6.3-.9-.2-.7-1.1.2-1-.2-2-.8-2.2-.8-.3-1 1-.7 3.9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M59.7 12.2c.8 7-1.3 16-4.2 18.1-3.9 2.9-4.9 5.3-3 7.9 2.2 3.3 1.9 5.1-.8 4.4-1.9-.4-2.8.3-5.4 4.7-2.9 4.6-3.2 4.9-3.3 2.6 0-1.5.7-3.2 1.5-3.9 3.5-2.9.3-9.6-6-12.6-1.9-.8-4.1-2.6-4.9-3.9-1.7-2.6-1.1-7.1.8-5.6.8.7 2.3.7 4.2.2 1.6-.5 2.1-1 1.2-1-1-.1-1.8-.5-1.8-1s.8-1.2 1.8-1.4c1.1-.3.7-.5-1-.6C35 20 33 19 33 17.4c0-.8-.4-1.4-1-1.4-.5 0-1 2.5-1 5.5 0 6.9-1.2 8.8-7.7 13.1-6.4 4.1-7.6 7.2-4.4 11.4 1.8 2.6 2.1 4.3 2.1 13.6 0 8.1-.3 10.9-1.4 11.8-1.3.9-2.1 7.6-1.7 13.3.1.6 2.2 1.9 4.9 2.9 5.1 2 5.3 2.1 9.6 5.9l2.8 2.5-1.7 3.7c-1.6 3.3-1.6 4-.4 6.3 2.6 4.4 8.2 6.3 16.8 5.5 13-1.2 17.7-1 20.5.8 3.1 2 7.5 2.2 10.1.3 1.8-1.3 1.8-2.6 0-7-.7-1.6-.5-1.8 2.1-1.3 9.9 2.1 8.9 2.3 15.4-3.4 5.8-5.2 6.1-5.6 5.4-8.9-.3-1.9-.8-12.6-1.1-23.8-.3-17.1-.1-20.6 1.2-22.4 2.8-4.2 1.2-9.3-3.8-12.3-4.8-2.9-7.7-6.5-7.7-9.6 0-2.9 1.5-4 2.2-1.8.3.9 1.2.9 3.4.1 1.7-.6 2.2-1.1 1.2-1.1-1-.1-1.8-.5-1.8-1s.8-1.2 1.8-1.4c1.1-.3.7-.5-1-.6C94 18 92 17 92 15.4c0-.8-.3-1.4-.7-1.4-.5 0-.8 2.1-.9 4.8-.1 8.2-.9 9.8-6.8 13.1-7.2 4.1-8.8 7.8-5.9 13.2 1.1 2 1.7 4.3 1.3 4.9-.5.9-.9.9-1.4.1-.4-.6-1.4-1.1-2.3-1.1-1.1 0-1.4.6-.9 2.4.5 2 .3 2.3-1.2 1.9-4.6-1.2-5.4-2.8-4.8-9.5.3-3.5.6-7.2.6-8.3.1-1.1-1.3-3.1-3-4.5-3.1-2.5-5-7.3-5-12.2 0-3.1.3-3.3 2.8-1.7 1.4.9 2.7.9 4.8.1 2.5-.9 2.6-1.1.7-1.1C68 16 67 15.5 67 15c0-.6 1-1 2.3-1 1.7 0 1.9-.2.7-1-.8-.5-2.5-.7-3.6-.4-1.2.3-2.5.1-2.9-.5-.3-.6-1.1-.9-1.6-.5-.6.3-.9-.2-.7-1.1.2-1-.2-2-.8-2.2-.8-.3-1 1-.7 3.9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M59.7 12.2c.8 7-1.3 16-4.2 18.1-3.9 2.9-4.9 5.3-3 7.9 2.2 3.3 1.9 5.1-.8 4.4-1.9-.4-2.8.3-5.4 4.7-2.9 4.6-3.2 4.9-3.3 2.6 0-1.5.7-3.2 1.5-3.9 3.5-2.9.3-9.6-6-12.6-1.9-.8-4.1-2.6-4.9-3.9-1.7-2.6-1.1-7.1.8-5.6.8.7 2.3.7 4.2.2 1.6-.5 2.1-1 1.2-1-1-.1-1.8-.5-1.8-1s.8-1.2 1.8-1.4c1.1-.3.7-.5-1-.6C35 20 33 19 33 17.4c0-.8-.4-1.4-1-1.4-.5 0-1 2.5-1 5.5 0 6.9-1.2 8.8-7.7 13.1-6.4 4.1-7.6 7.2-4.4 11.4 1.8 2.6 2.1 4.3 2.1 13.6 0 8.1-.3 10.9-1.4 11.8-1.3.9-2.1 7.6-1.7 13.3.1.6 2.2 1.9 4.9 2.9 5.1 2 5.3 2.1 9.6 5.9l2.8 2.5-1.7 3.7c-1.6 3.3-1.6 4-.4 6.3 2.6 4.4 8.2 6.3 16.8 5.5 13-1.2 17.7-1 20.5.8 3.1 2 7.5 2.2 10.1.3 1.8-1.3 1.8-2.6 0-7-.7-1.6-.5-1.8 2.1-1.3 9.9 2.1 8.9 2.3 15.4-3.4 5.8-5.2 6.1-5.6 5.4-8.9-.3-1.9-.8-12.6-1.1-23.8-.3-17.1-.1-20.6 1.2-22.4 2.8-4.2 1.2-9.3-3.8-12.3-4.8-2.9-7.7-6.5-7.7-9.6 0-2.9 1.5-4 2.2-1.8.3.9 1.2.9 3.4.1 1.7-.6 2.2-1.1 1.2-1.1-1-.1-1.8-.5-1.8-1s.8-1.2 1.8-1.4c1.1-.3.7-.5-1-.6C94 18 92 17 92 15.4c0-.8-.3-1.4-.7-1.4-.5 0-.8 2.1-.9 4.8-.1 8.2-.9 9.8-6.8 13.1-7.2 4.1-8.8 7.8-5.9 13.2 1.1 2 1.7 4.3 1.3 4.9-.5.9-.9.9-1.4.1-.4-.6-1.4-1.1-2.3-1.1-1.1 0-1.4.6-.9 2.4.5 2 .3 2.3-1.2 1.9-4.6-1.2-5.4-2.8-4.8-9.5.3-3.5.6-7.2.6-8.3.1-1.1-1.3-3.1-3-4.5-3.1-2.5-5-7.3-5-12.2 0-3.1.3-3.3 2.8-1.7 1.4.9 2.7.9 4.8.1 2.5-.9 2.6-1.1.7-1.1C68 16 67 15.5 67 15c0-.6 1-1 2.3-1 1.7 0 1.9-.2.7-1-.8-.5-2.5-.7-3.6-.4-1.2.3-2.5.1-2.9-.5-.3-.6-1.1-.9-1.6-.5-.6.3-.9-.2-.7-1.1.2-1-.2-2-.8-2.2-.8-.3-1 1-.7 3.9z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				break;
				
				case 27:
					$sVG = '
					<svg class="buildingShape g27" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
		<g class="clickShape">
			<path d="M53.5 22.1c-4.2 1.2-10.2 6.4-12.4 10.7C38.4 38.1 37 45.9 37 55c0 4.3-.4 8.1-.8 8.4-.5.3-.7 1.8-.4 3.5.2 1.9-.3 3.9-1.4 5.5-2.3 3.1-4 3.3-4.7.5-.4-1.3-1.2-2-2.4-1.7-1.4.3-1.9 1.5-2.1 6.1-.2 3.5.1 5.7.7 5.7s1.7 1.5 2.6 3.2c.8 1.8 2 4.1 2.7 5.1 1.1 1.4 1 2.3-.4 4.7l-1.7 3 3.9 1c3.8 1 7 .5 7-1.1 0-.4 1.6-.1 3.5.7 1.9.7 5.2 1.4 7.5 1.4 3.5 0 3.9.3 3.7 2.2-.3 3 5.6 4.9 7.9 2.6 1.4-1.4 2.2-1.3 7.7.4 4.5 1.4 8 1.8 12.4 1.5 12-.9 16.9-6.6 15.7-18.1-.4-4.2-1.3-7.4-2.5-8.8-1.1-1.1-1.9-3-1.9-4 0-1.2-.9-2.2-2.1-2.5-2-.6-2-1-1.4-10.8.9-12.6.3-14.8-3.4-14-3.4.7-6.5-3.1-7.5-9.4-2.1-13.1-14.2-21.5-26.1-18zm-8.7 38.1c1.6 1.6 1.5 3.5-.2 4.1-1 .4-1.5-.4-1.8-2.4-.5-3.1.1-3.6 2-1.7zM86 70c0 4.9-1.2 6.6-2.4 3.4-.8-2.1-.8-6.9.1-7.7C85.3 64 86 65.4 86 70zm-54 7c0 .5-.5 1-1.1 1-.5 0-.7-.5-.4-1 .3-.6.8-1 1.1-1 .2 0 .4.4.4 1zm0 11.4c0 .5-.7.3-1.5-.4S29 86 29 85.2c0-1.3.3-1.2 1.5.4.8 1 1.5 2.3 1.5 2.8z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M53.5 22.1c-4.2 1.2-10.2 6.4-12.4 10.7C38.4 38.1 37 45.9 37 55c0 4.3-.4 8.1-.8 8.4-.5.3-.7 1.8-.4 3.5.2 1.9-.3 3.9-1.4 5.5-2.3 3.1-4 3.3-4.7.5-.4-1.3-1.2-2-2.4-1.7-1.4.3-1.9 1.5-2.1 6.1-.2 3.5.1 5.7.7 5.7s1.7 1.5 2.6 3.2c.8 1.8 2 4.1 2.7 5.1 1.1 1.4 1 2.3-.4 4.7l-1.7 3 3.9 1c3.8 1 7 .5 7-1.1 0-.4 1.6-.1 3.5.7 1.9.7 5.2 1.4 7.5 1.4 3.5 0 3.9.3 3.7 2.2-.3 3 5.6 4.9 7.9 2.6 1.4-1.4 2.2-1.3 7.7.4 4.5 1.4 8 1.8 12.4 1.5 12-.9 16.9-6.6 15.7-18.1-.4-4.2-1.3-7.4-2.5-8.8-1.1-1.1-1.9-3-1.9-4 0-1.2-.9-2.2-2.1-2.5-2-.6-2-1-1.4-10.8.9-12.6.3-14.8-3.4-14-3.4.7-6.5-3.1-7.5-9.4-2.1-13.1-14.2-21.5-26.1-18zm-8.7 38.1c1.6 1.6 1.5 3.5-.2 4.1-1 .4-1.5-.4-1.8-2.4-.5-3.1.1-3.6 2-1.7zM86 70c0 4.9-1.2 6.6-2.4 3.4-.8-2.1-.8-6.9.1-7.7C85.3 64 86 65.4 86 70zm-54 7c0 .5-.5 1-1.1 1-.5 0-.7-.5-.4-1 .3-.6.8-1 1.1-1 .2 0 .4.4.4 1zm0 11.4c0 .5-.7.3-1.5-.4S29 86 29 85.2c0-1.3.3-1.2 1.5.4.8 1 1.5 2.3 1.5 2.8z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M53.5 22.1c-4.2 1.2-10.2 6.4-12.4 10.7C38.4 38.1 37 45.9 37 55c0 4.3-.4 8.1-.8 8.4-.5.3-.7 1.8-.4 3.5.2 1.9-.3 3.9-1.4 5.5-2.3 3.1-4 3.3-4.7.5-.4-1.3-1.2-2-2.4-1.7-1.4.3-1.9 1.5-2.1 6.1-.2 3.5.1 5.7.7 5.7s1.7 1.5 2.6 3.2c.8 1.8 2 4.1 2.7 5.1 1.1 1.4 1 2.3-.4 4.7l-1.7 3 3.9 1c3.8 1 7 .5 7-1.1 0-.4 1.6-.1 3.5.7 1.9.7 5.2 1.4 7.5 1.4 3.5 0 3.9.3 3.7 2.2-.3 3 5.6 4.9 7.9 2.6 1.4-1.4 2.2-1.3 7.7.4 4.5 1.4 8 1.8 12.4 1.5 12-.9 16.9-6.6 15.7-18.1-.4-4.2-1.3-7.4-2.5-8.8-1.1-1.1-1.9-3-1.9-4 0-1.2-.9-2.2-2.1-2.5-2-.6-2-1-1.4-10.8.9-12.6.3-14.8-3.4-14-3.4.7-6.5-3.1-7.5-9.4-2.1-13.1-14.2-21.5-26.1-18zm-8.7 38.1c1.6 1.6 1.5 3.5-.2 4.1-1 .4-1.5-.4-1.8-2.4-.5-3.1.1-3.6 2-1.7zM86 70c0 4.9-1.2 6.6-2.4 3.4-.8-2.1-.8-6.9.1-7.7C85.3 64 86 65.4 86 70zm-54 7c0 .5-.5 1-1.1 1-.5 0-.7-.5-.4-1 .3-.6.8-1 1.1-1 .2 0 .4.4.4 1zm0 11.4c0 .5-.7.3-1.5-.4S29 86 29 85.2c0-1.3.3-1.2 1.5.4.8 1 1.5 2.3 1.5 2.8z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M53.5 22.1c-4.2 1.2-10.2 6.4-12.4 10.7C38.4 38.1 37 45.9 37 55c0 4.3-.4 8.1-.8 8.4-.5.3-.7 1.8-.4 3.5.2 1.9-.3 3.9-1.4 5.5-2.3 3.1-4 3.3-4.7.5-.4-1.3-1.2-2-2.4-1.7-1.4.3-1.9 1.5-2.1 6.1-.2 3.5.1 5.7.7 5.7s1.7 1.5 2.6 3.2c.8 1.8 2 4.1 2.7 5.1 1.1 1.4 1 2.3-.4 4.7l-1.7 3 3.9 1c3.8 1 7 .5 7-1.1 0-.4 1.6-.1 3.5.7 1.9.7 5.2 1.4 7.5 1.4 3.5 0 3.9.3 3.7 2.2-.3 3 5.6 4.9 7.9 2.6 1.4-1.4 2.2-1.3 7.7.4 4.5 1.4 8 1.8 12.4 1.5 12-.9 16.9-6.6 15.7-18.1-.4-4.2-1.3-7.4-2.5-8.8-1.1-1.1-1.9-3-1.9-4 0-1.2-.9-2.2-2.1-2.5-2-.6-2-1-1.4-10.8.9-12.6.3-14.8-3.4-14-3.4.7-6.5-3.1-7.5-9.4-2.1-13.1-14.2-21.5-26.1-18zm-8.7 38.1c1.6 1.6 1.5 3.5-.2 4.1-1 .4-1.5-.4-1.8-2.4-.5-3.1.1-3.6 2-1.7zM86 70c0 4.9-1.2 6.6-2.4 3.4-.8-2.1-.8-6.9.1-7.7C85.3 64 86 65.4 86 70zm-54 7c0 .5-.5 1-1.1 1-.5 0-.7-.5-.4-1 .3-.6.8-1 1.1-1 .2 0 .4.4.4 1zm0 11.4c0 .5-.7.3-1.5-.4S29 86 29 85.2c0-1.3.3-1.2 1.5.4.8 1 1.5 2.3 1.5 2.8z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>
					';
				break;
			case 31:
			$sVG = '<svg class="buildingShape a40 wallBottom" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="628" height="464" viewBox="0 0 628 464" >
				<g class="clickShape">
					<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
				</g>
				<g class="hoverShape">
					<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
				</g>
				<g class="clickShapeWinter">
					<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
				</g>
				<g class="hoverShapeWinter">
					<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
				</g>
			</svg>
			<svg class="buildingShape a40 wallTop" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="628" height="464" viewBox="0 0 628 464" >
				<g class="clickShape">
					<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
				</g>
				<g class="hoverShape">
					<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
				</g>
				<g class="clickShapeWinter">
					<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
				</g>
				<g class="hoverShapeWinter">
					<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
				</g>
			</svg>';
				break;
						
						case 32:
						$sVG = '<svg class="buildingShape a40 wallBottom" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="628" height="464" viewBox="0 0 628 464" >
	<g class="clickShape">
			<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>
		<svg class="buildingShape a40 wallTop" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="628" height="464" viewBox="0 0 628 464" >
	<g class="clickShape">
			<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
						break;
						
					case 33:
					$sVG = '<svg class="buildingShape a40 wallBottom" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="628" height="464" viewBox="0 0 628 464" >
						<g class="clickShape">
								<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShape">
								<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="clickShapeWinter">
								<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShapeWinter">
								<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							</svg>
							<svg class="buildingShape a40 wallTop" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="628" height="464" viewBox="0 0 628 464" >
						<g class="clickShape">
								<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShape">
								<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="clickShapeWinter">
								<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShapeWinter">
								<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
					</svg>';
					break;
					case 42:
						$sVG = '<svg class="buildingShape a40 wallBottom" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="628" height="464" viewBox="0 0 628 464" >
						<g class="clickShape">
								<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShape">
								<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="clickShapeWinter">
								<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShapeWinter">
								<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							</svg>
							<svg class="buildingShape a40 wallTop" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="628" height="464" viewBox="0 0 628 464" >
						<g class="clickShape">
								<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShape">
								<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="clickShapeWinter">
								<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShapeWinter">
								<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							</svg>';
						break;

					case 43:
						$sVG = '<svg class="buildingShape a40 wallBottom" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="628" height="464" viewBox="0 0 628 464" >
						<g class="clickShape">
								<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShape">
								<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="clickShapeWinter">
								<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShapeWinter">
								<path d="M623.57 239.09c0-2.94.26-5.46.28-7.92 0-2.7-.94-5.49-1.87-8.1-.54-1.52-.38-2.28 1-2.75-1.58-4.19 1.47-7.18 2.1-10.48-.56-3-2.85-5.68-1.16-8.81.14-1.07-1.23-.81-1.26-1.68.95-1.92.95-1.92.22-3.3.73-6 1.44-12 2.62-17.88a1.74 1.74 0 0 0 .24-.91 1.94 1.94 0 0 0 0-.22c.2-1 .4-1.88.55-2.46.28-1.09.57-2.17.76-2.73a4.29 4.29 0 0 0 .15-.54 6.29 6.29 0 0 0 .1-.62c-.14.48-.25 1-.38 1.44a1.74 1.74 0 0 1-1.72 1H624.55a2 2 0 0 1-.79-.58 1.24 1.24 0 0 1-.21-.41 1.35 1.35 0 0 1 0-.51c.27-2.71-.87-5.1-1.59-7.59.22-.77.89-1.58.5-2.32-2-3.84-1.62-8.57-4.54-12.08L616 150a4.94 4.94 0 0 1-.73-.88 2.06 2.06 0 0 1-.21-.46 1.76 1.76 0 0 1 0-1l2-1.33a9.05 9.05 0 0 1-1.84-4.13 12.06 12.06 0 0 0-3.54-7.39 39 39 0 0 1-6.2-8.28 44.64 44.64 0 0 0-7.54-10.45 11.27 11.27 0 0 1-2.55-4.5 7.42 7.42 0 0 0-3.5-4.29 21 21 0 0 1-5-4c-3.51-3.76-5.76-8.53-9.7-11.91a12.21 12.21 0 0 1-2.34-2.87 4.82 4.82 0 0 0-1.49-1.51 2.81 2.81 0 0 0-1.06-.39h-.14a3.56 3.56 0 0 0-2.15.54 4 4 0 0 1-1-.36 1.14 1.14 0 0 1-.6-.72 3 3 0 0 0-3.08-2.52.92.92 0 0 1-.55-.35c-.18-.19-.35-.42-.53-.58s-.06-.47-.08-.72c0-.42-.08-.81-.27-.9-3.13-1.52-4.51-4.68-6.75-7l-.26-.27c-.64-.64-1.43-1.25-2.44-.27a2.6 2.6 0 0 1-2.5-2.25v-.13a3.14 3.14 0 0 0 .24-1.14 1.72 1.72 0 0 0-1.19-1.68 6.15 6.15 0 0 0-1.4-.34c-4-.57-6.11-4.61-10-5.21-.3 0-.5-.63-.79-.93s-.68-.82-.87-.76c-2.22.72-3.09-1.12-4.32-2.24a6.39 6.39 0 0 0-3.65-1.88 6 6 0 0 1-1-.13 7.39 7.39 0 0 1-1.16-.37c-1.65-.66-3.07-1.78-4.78-2.25-.3-.27-.6-.57-.89-.87l-.05-.05-.63-.65c-.23-.23-.45-.45-.69-.66a5.55 5.55 0 0 0-1-.7 3.46 3.46 0 0 0-.52-.24c-2.8-1-5.37-2.37-8-3.59-4.9-2.23-10-4.17-14.05-7.85a4.43 4.43 0 0 0-.53-.41 4.26 4.26 0 0 0-2.25-.68 11 11 0 0 0-1.81.11 11.89 11.89 0 0 0-3.88-2 4.82 4.82 0 0 0-.65-.08 2.5 2.5 0 0 0-2-2 34.2 34.2 0 0 1-4.52-1.75c-2.27-1-4.72-1.49-7.06-2.28a16.5 16.5 0 0 1-4-1.9 2.09 2.09 0 0 0-.21-.46 1.49 1.49 0 0 0-1.18-.7c-2.16-.71-3.64-3.22-5.65-3.31-3-.13-5.3-1.36-7.7-2.8-3.63-2.18-8.08-1.71-11.84-3.56a6.6 6.6 0 0 0-4.82-.59 6.88 6.88 0 0 1-4.92-.85c-4-2.25-8.52-2.38-12.67-3.89-6-2.2-12.74-1.17-18.77-3.6a5.77 5.77 0 0 0-1.93-.24c-3.67-.14-7.3.65-11-.52-2.32-.72-4.7-1.29-7-2.09-5.2-1.78-10.78-2.18-16.18-2.95-6.54-.93-13.4-.32-20-.82a39.92 39.92 0 0 0-9.14.41c-2.31.36-3.95.31-5.63-1a5.25 5.25 0 0 0-4.41-1.1A12.39 12.39 0 0 1 324 .7c-3.1-.33-6.69-1.23-9.57 0-3.58 1.49-7.3 1.65-11 2a72.31 72.31 0 0 1-7.37.11c-5.12 0-10.28.35-15.35-.15-2.53-.25-4.41 1.06-6.66 1.36-2.79.37-5.64.43-8.36 1-2.07.43-4.42-.43-6 .58-2.15 1.38-4.26 1.12-6.45 1.25a13 13 0 0 0-2.93.37c-3.74 1.07-7.35 3.15-11.47 1.69-.29-.1-.89-.1-1 0-.84 1.76-2.59.85-3.84 1.37s-3.14.16-4.24 1.26c-1.61 1.62-3.55 1.47-5.36 1.1a5.55 5.55 0 0 0-4.28.58 6.75 6.75 0 0 1-1.87.6c-3.59.87-7.52.55-10.7 2.84-2 1.45-4.25 1.09-6.49 1.2a12.62 12.62 0 0 0-2.18.28 8.51 8.51 0 0 0-1.69.58A6.7 6.7 0 0 0 194 22l-1 .31-.6-.31c0 .19-.1.38-.15.56-2.95-.86-5.81-1.2-8.33.89q-.25.21-.5.45a12.84 12.84 0 0 1-6.05 3.51c-1.83.43-3.82.28-5.41 1.54-.82 0-1.78-.26-2.44.08-2.32 1.19-4.77 1.31-7.26 1.76a9.08 9.08 0 0 0-2.09.67 17.24 17.24 0 0 0-1.75.93l-1 .59-.49.31c-.6.39-1.19.78-1.79 1.15-.33.2-.67.4-1 .59l-2.09 1.67c-.62-.3-1-.25-1.23 0-.42.35-.5 1.14-.86 1.57l-1.4-.14h-.2l-.32.06a21.1 21.1 0 0 0-3.4 1.64h-.11a4.69 4.69 0 0 1-1.89.49 3.34 3.34 0 0 1-1.08-.16 4.41 4.41 0 0 1-1.16-.59c-.79 2-1.06 2.77-3.06 3.67-6.68 3-13 7.36-20.88 6.5a9.38 9.38 0 0 0-1-.06 3 3 0 0 0-2.58 1.32h-3.66l.75 4.63a5.53 5.53 0 0 1-.94.17c-2.14.19-3.84-1-5.52-2.23A18.4 18.4 0 0 1 105 55.7c.61 1.17.13 1.69-1.41 3.34a11.13 11.13 0 0 1-.92.72.6.6 0 0 1-.3.08c-2.81-.14-4.1 2-5.94 3.52-3.09 2.57-6.34 5.21-10 6.77-2.68 1.14-5.08 2.14-6 4.87-.81 2.33-3.16 2.14-4.59 3.44-2 1.83-2.92 4.56-4.91 6.16-3.14 2.53-6.14 5.24-9.38 7.62a16.84 16.84 0 0 0-3.69 4.28c-1.64 2.42-4.16 4.19-5.12 7.19a12.16 12.16 0 0 1-4.3 6c-2 1.53-3.05 4.13-5.51 5.24-.47.21-.35.9-.56 1.31-1.5 3-2.3 6.54-5.36 8.45-3.54 2.22-5 6-6.86 9.27-2.18 3.82-4.74 7.38-6.62 11.47-2.69 5.85-7 10.94-9.55 17-.16.37-.39.88-.71 1-2.14.73-2 2.61-2.25 4.3a19.48 19.48 0 0 1-1 3.83c-.54 1.69-.27.84-.77 2.69l31.91 1.56c1.5-3.37 4.14-6.22 5.21-8.61 3.19-7.12 7.25-13.82 10-21.19a37.88 37.88 0 0 1 10.34-15.31c4-3.58 6.56-8.31 9.78-12.46 2.94-3.79 5.46-7.62 10.48-9.78l-2.3-.45c.15-.43.16-.83.37-1 1.85-1.69 4-3 5.62-5 .35-.42 1.09-.84 1.56-.42 1 .91 1.45 0 2-.5 2.43-2.4 4.91-4.75 7.23-7.25.89-1 2.45-1.08 2.9-2.57.6-2 2.36-3 4.07-3.69a9.81 9.81 0 0 0 2.68-1.65c4.32-3.62 9.52-5.89 14.14-9.06 1.55-1.06 3.55-1.51 5-2.67 4.61-3.64 10-5.7 15.22-8.23l2.51-.51.91-.26a3 3 0 0 0 2.17-1.65 1.56 1.56 0 0 0 .42-.12c.38-.13.87-.14 1.12-.4a9.35 9.35 0 0 1 5.55-2.94 3.73 3.73 0 0 0 1.23-.39 3.58 3.58 0 0 0 1.75-3.06l1.38-.18c.11.15.21.28.32.4.72.8 1.27.72 1.57-.74 3-1.07 6-2.41 9.23-2.78.46-.05.93-.09 1.4-.1a2.84 2.84 0 0 0 1.82-.88c2.87-3.34 7.26-3.09 10.84-4.72 3.24-1.47 6.82-1.89 10-3.65 1.9-1.06 3.87-2.41 6.24-2.27a9.84 9.84 0 0 0 5.23-1.33c3.18-1.65 6.64-2.22 10.15-3 4.08-.84 8.24-1.4 12.27-2.44 2-.51 3.82-1.42 6-1.39a5.12 5.12 0 0 0 2.8-.92 7.58 7.58 0 0 1 4.27-1c5.42-.29 10.54-2.73 16.08-2.33a3 3 0 0 0 1.62-.59 9.21 9.21 0 0 1 5-1.1c2.73-.05 5.64.73 8.13-.6s5.24-1.4 8-1.38h9.75c.7 0 1.45.24 2.08-.46 1.4-1.55 3.56-1.21 5-.94a42.92 42.92 0 0 0 12.32.19 109.38 109.38 0 0 1 20.1-.3c2.9.24 5.82.41 8.73.59 4.85.29 9.71.59 14.53 1.25a1.9 1.9 0 0 0 1.14 1.22 5.12 5.12 0 0 0 1.69.22c3.22.06 6.59-.66 9.61.78a11.64 11.64 0 0 0 5.51 1.19c2.66-.07 5.35-.2 7.83 1 3.24 1.56 7 .42 10.21 2a11.15 11.15 0 0 0 3.05 1 82.79 82.79 0 0 1 11.22 2.8c3.77 1.19 7.76 1.35 11.44 3 1.81.82 3.76 1.94 5.8 2.08 5.17.36 9.81 2.29 14.33 4.52a59 59 0 0 0 9.57 3.39c4.84 1.42 9.11 4.21 14.21 5.11 3.67.65 7 2.91 10.63 4.18l2 .7 2.78 1q.88.35 1.74.73a26.41 26.41 0 0 1 8.42 5.5 3.57 3.57 0 0 0 2.3 1.66l.79.25a10.7 10.7 0 0 1 1.5.66 274.62 274.62 0 0 1 29 18.53 238.77 238.77 0 0 1 20.7 16.51c7.43 6.68 13.73 14.42 19.66 22.46a181 181 0 0 1 14 22.34c3.1 5.81 5.41 11.92 8 17.91.83 1.9 2.11 3.54 2.81 5.55 3.45 10 6.43 20.05 7.81 30.55.45 3.44 1.51 7 1.11 10.31a93.5 93.5 0 0 0-.55 10.17 43.1 43.1 0 0 1-.83 9.65 45.82 45.82 0 0 0-.86 6.16" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							</svg>
							<svg class="buildingShape a40 wallTop" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="628" height="464" viewBox="0 0 628 464" >
						<g class="clickShape">
								<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShape">
								<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="clickShapeWinter">
								<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							<g class="hoverShapeWinter">
								<path d="M9.25 174.12c-.14.08-.26.68-.3.73a2 2 0 0 0-.39 1.65l-2.1.66c-.12.66.43 1.56-.44 2-2 1-1.69 2.28-.66 3.66l-.24.73-2.67 1.8c1.17 1.09 1.59 2.19.38 3.52a1.41 1.41 0 0 0-.13 1.83 2.57 2.57 0 0 1 .57.65v.83c-.28.39-.89.67-1.26 1 .25.74 1.64 1.19.9 2-1.37 1.55-.29 3.67-1.43 5.17a2.08 2.08 0 0 0 0 2.45 2.11 2.11 0 0 1-.71 3.07c-.19 1.12 1.38 1.1 1.08 1.58-1.29 2 .37 4.22-.45 6.15-.46 1.08 0 1.64.57 2.31A2.16 2.16 0 0 1 0 218.36c.9.81 1.75 1.36 1.36 2.86-.14.54-1.07 2.65 1.11 2.86 1.1.82-.42 1.74.08 2.58l-.46.08h.48l.42.28c-.63.91-1.89 1.36-1.92 2.69 2.25 1.54 2.39 2.16 1 5.09 1.41-1.1 1.71.12 1.86.72.28 1.12.92 2.41.24 3.46-1 1.47-1 4 .29 5.12v1.45l-.46.48c-2.31 2.3-2.67 4.87-.93 7.8-1.48.33-1.6 1.1-1.25 2.42.29 1.13 1.28 2.43.42 3.56-1.77 2.33-1.1 5-.93 7.4.1 1.46-.27 3.69 2.41 4.45-1.34.52-1.25 1.41-1.25 2.33 0 1.49.16 3 .16 4.44a9.32 9.32 0 0 0 .3 3.28c.86 2.43 1.81 4.76.64 7.55-1.5 3.55-.49 6.53 2.26 8.3a3.26 3.26 0 0 0 .35 3 6 6 0 0 0 1.35 2.29 13.09 13.09 0 0 1 4.22 6.15c1 3 4.35 3.78 5.25 6.66a4.48 4.48 0 0 0 1.56 2.15 11.72 11.72 0 0 1 4 5c.64.47 2-.17 2 .52 0 3 3.25 2.81 4.41 4.79a10.45 10.45 0 0 0 3 3.25c1.82 1.21 2.83 3.69 4.68 4.8s2.26 3.75 4.75 3.84c1.39 2.67 2.27 5.83 6 6.23a.29.29 0 0 0 .28.31 11.5 11.5 0 0 0 1.84 2.88c1.4 2.06 3.5 3.13 5.39 4.53l.22 1.48c.19 2.26 2.11 3.26 3.49 4.58 5 4.79 9.52 10.11 14.95 14.46v2a56.44 56.44 0 0 1-1 9.54c-.46 1.22 0 1.57 1.24 1.56h11.13l3 .39c1.49 1 3.26.93 4.92 1.25a4.5 4.5 0 0 0 2.52.81l.49.5A9 9 0 0 0 99 393a2.87 2.87 0 0 0 1.81 1.81c1.14.33.54 1.77 1.57 2.34 1.37.76 1.06-2.17 2.47-.88.26.33.08.71 0 1.11-1.09 4.75.62 6.36 5.51 8.08a2.78 2.78 0 0 1 1.43 1.23c1.74 2.48 4 4.06 7.14 3.89a1.56 1.56 0 0 1 1.55.6c1.82 3.08 4.85 4.08 8.06 4.81l.48.48c.52 1 1.45 1.19 2.46 1.6 2.3.94 4.92-1.05 7 .94.19 1.34-2.72.89-1.47 3 1-.28 2.12-.56 3 .49a7.19 7.19 0 0 0 2.58 1.48c.76 1 2.11 1.08 3 2a11.77 11.77 0 0 0 7.64 3.45c2.34.22 5 .19 7.07.78 3 .89 5.85-.22 8.78.47 3.33.78 6.37 2.94 10 2.34a.51.51 0 0 1 .39.25c1 2 3.19 1.72 4.91 2.23 2 .6 4.41 1.73 6.21 1.19a19.22 19.22 0 0 1 11.2-.4c4 1.19 7.94.52 11.92.32.58 0 1.17-.24 1.68.15 1.72 1.29 3.28.53 4.74-.45a3.77 3.77 0 0 1 1.95-.91c3.45-.21 6.95-1 10.34-.64 3.66.34 7.19 2.49 10.75-.37.88 2.4-.11 4.37.76 6.15.17 2.49 2 4.21 2.91 6.28 1.48 3.36 4 2 6.2 2.58.24.06.49.06.73.1 3.39.7 6.17 3.17 10.13 2.54 2.61-.42 5.42.92 8.32.65 1.26-.12 3.2-.35 4.28 1.2 2.78 2.49 6.15 3.8 9.66 4.86 4.26 1.3 8.4 3.09 13 3.2 1.59 0 3.11-.41 4.79.11a19.63 19.63 0 0 0 10.75.33c1.93 1.1 3.88 2 6 .51h.91c1.6-.14 3.14.38 4.82.21a27.53 27.53 0 0 1 4.85-.31 27.58 27.58 0 0 0 6.89-.19 41.85 41.85 0 0 1 8.36-.58c4.57.05 8.15-2 11.64-4.55.41-.45 1.47-.71.49-1.59 0-.43-.08-.86.46-1a193.24 193.24 0 0 0 28-4.53c2.2 1.48 2.2 1.48 4-.83a4.22 4.22 0 0 0 4.16.33c3.32-1.44 7-1.72 10.3-3.25 2.46-1.72 5.51-2.06 8.22-2.67 5-1.14 10.07-2.55 15.25-3.13 3.15-.36 6.19-.85 8.64-3.46a5.89 5.89 0 0 1 3.57-1.61c3.62.22 6.2-2.51 9.67-3 2.32-.35 4.93-.86 7.28-1.19a8.22 8.22 0 0 0 4.79-2.15 11.45 11.45 0 0 1 5.07-2.73c2.05-.38 3.71-1.51 5.64-2s3.84-1.71 5.85-2.43c1.5-.53 3.06.5 4.41-.77a11 11 0 0 1 6-2.67 4.47 4.47 0 0 0 2.78-1.41c1.69-1.62 4.08-2.52 6.19-3.68a.78.78 0 0 1 .71.09c1.7 1.48 3.12.73 4.67-.37a6.16 6.16 0 0 1 3.55-.88 6 6 0 0 0 4.42-1.93 3.45 3.45 0 0 1 1.4-1 28.5 28.5 0 0 0 12.06-6.52c1.46-1.33 3.36-2.42 4.26-4.06a7.31 7.31 0 0 1 5.6-4.24 2.14 2.14 0 0 0 1.39-.8l1.54-1a1.8 1.8 0 0 0 1.46-.58c5.09-2.29 10.58-2.78 16-3.28.06-1.19.16-2-.44-2.7-1.43-3.2-4.37-5.67-4.56-9.47v-.5l8-8c2.05-2 4.16-4 6.16-6.09 1-1.05 1.19-2.05-.71-2.35-1.14-.18-2.26-.44-3.39-.67l-.54-.41v-1c1.36-.75 1.12-3 3-3.23s2-1.6 2.27-3c.47-2.8 1.75-4.86 4.59-5.92a7.44 7.44 0 0 0 3.67-2.51c2.35-3.82 5.29-7.18 7.84-10.83 1.7-2.43 3.95-4.69 4.9-7.39 1.8-5.09 5.2-9.21 8.69-12.73 2.72-2.74 3.5-6.93 7.3-8.77 1.38-.67.6-3 1.29-4.5 1.74-.71 2.17-2.63 3.4-3.86 2.66-2.65 6.79-4.6 6.13-9.39.73-.28 1.55.06 1.95-.63 1.14-2-2-2.77-1.31-4.61 2.24-.6 2.94-1.68 1.86-2.87-2-2.15-1.6-3.48 1.25-4.15 1.14-.27 2.24-.67 2.44-1.77.44-2.39 1.69-4.52 1.73-7.09.08-6.34 2.66-12.49 1.4-19-.38-1.95.6-4.51.68-6.7h-31c-.31.39-.42.82-.67 1-2.35 5.44-3.43 11.28-5.34 16.86-4.18 12.16-10 23.52-16.5 34.57a174.48 174.48 0 0 1-25.85 33.75c-5.76 5.91-12.41 10.76-18.73 16-2.95 2.44-6.8 3.94-8.54 7.73a3.69 3.69 0 0 0-3.22.5c-5.22 3.8-10.39 7.72-16.32 10.43a2.4 2.4 0 0 0-1.49 2.1l-.89.6a5.52 5.52 0 0 0-3.29.81c-3.66 2.14-7.4 4.16-11.17 6.09-17.49 9-35.81 16-54.45 22.14a307.49 307.49 0 0 1-32.72 8.42c-6.56 1.39-13.1 2.93-19.73 3.86-6.13.86-12.39 1.29-18.45 2.37s-12.16-.17-18 1.75c-21.16-.44-42.35 1.06-63.48-1.81-8.18-1.11-16.38-2.26-24.56-3.36-6.87-.92-13.62-2.83-20.41-4.35a334.15 334.15 0 0 1-46.13-14c-5.74-2.18-11.37-4.57-17-7-9-3.88-17.69-8.46-26.37-13-3.72-2-7.24-4.29-10.86-6.45a7.89 7.89 0 0 0-5.87-3.25c-1.61-1.81-4.13-1.79-6.08-3.11-5.19-3.54-9.92-7.72-15.37-10.88l-.53-.39a1.16 1.16 0 0 0-.12-.47c0-1.09-.89-1.48-1.59-2.06-3.44-2.83-7.23-5.27-10.58-8.13a129.67 129.67 0 0 1-19.75-21.75C52.69 286.66 41.57 263.12 36.17 237a95.51 95.51 0 0 1-1.59-31.2 187 187 0 0 1 6-27.94c0-.07.13-.68.37-1.53.06-.27.2-.59.19-.61" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
							</g>
							</svg>';
						break;
	
				/*
				case 32:
					$sVG ='
					<svg class="buildingShape 32Bottom" width="675" height="534" viewBox="0 0 675 534">
							<g class="clickShape">
									<path d="M20.7 224.2c-2.4 5-3.2 9.4-5.7 28.3-.8 6.4-.7 9.1.5 13.5 1.2 4.7 1.2 5.7 0 7.6-1 1.6-1.3 4.9-1.1 13 .2 6-.1 12-.6 13.4-1.4 3.5-1.8 20.1-.6 22.3.7 1.4.5 2.4-.7 3.7-2.2 2.4-1.3 8.6 3.6 25.9 3.7 12.9 4.8 15.1 7.7 15.1.7 0 2.1 1.9 3.1 4.2 1 2.4 3.3 5.6 5 7.2 1.7 1.7 3.1 4 3.1 5.3 0 1.7.6 2.3 2.3 2.3 1.7 0 2.8 1.1 4.5 4.6 2.1 4.2 2.5 4.6 5.2 4.1 2.6-.6 3.2-.1 6 4.3 4.7 7.3 4.7 7.3 13.1 5.1 8.4-2.3 27.6-5.1 34.7-5.1 4 0 5.3-.6 9.2-3.9l4.5-3.9 8 6.7c4.4 3.6 7.3 5.7 6.6 4.6-1.4-1.9-1.4-1.9.2-.4 1.6 1.4 1.5 2-1.9 9.4-2.8 6.1-5.3 9.5-10.8 15-3.9 3.8-8.9 9.2-11.1 11.9l-3.9 4.8 1.9 3.6c1.2 2.2 3.8 4.6 6.3 6 2.9 1.6 4.2 3 4.2 4.5.1 4.2 7 10 10.8 9 2.7-.7 12.2 2.5 12.2 4.1 0 .7 2.8 2.8 6.1 4.6 3.5 1.8 6.8 4.5 7.8 6.1 2.2 4 5.4 5.2 14.6 5.9 7.5.5 8.1.7 9.3 3.2 1.2 2.5 1.9 2.8 6.1 2.8 3.1 0 5.3.5 6.1 1.5 1.6 1.9 5.8 1.9 10.1.1 2.9-1.2 3.7-1.2 6.3.1 1.6.8 3.1 1.1 3.5.6.3-.5 1.8-.8 3.4-.8 1.5.1 2.7-.3 2.7-.8s.7-.7 1.5-.3c1 .4 1.5-.1 1.5-1.5 0-1.7.3-1.8 2.2-.8 1.7.9 2.3.9 2.6 0 .3-.9.7-.9 1.8 0 1.2 1 1.6.8 2.1-.9.6-2.4 1.8-2.8 2.8-1.1.4.6 1.3.8 2.1.5.7-.3 1.9.4 2.5 1.5.8 1.6 1.4 1.8 2.4.9.9-.8 1.5-.8 2 0 .3.5 1.7 1 3.1 1 1.9 0 2.4-.5 2.4-2.6 0-2.5.1-2.6 2.4-1.1 1.3.9 2.7 1.3 3 1 1-1 4.6-1.1 4.6-.1 0 .4.7.8 1.5.8s1.5.4 1.5.8c0 .5 1.1 1.5 2.5 2.2 1.4.7 2.5 1.7 2.5 2.1 0 1.1 9.8 10.2 11.8 11.1.9.4 2.9.9 4.5 1.2 1.5.4 2.7 1.3 2.7 2.1 0 1.5 3.2 2.1 9.5 1.7 2.4-.2 3.4.4 4.8 2.8 2 3.2 8.3 7 10.5 6.2.8-.2 3 .3 5 1.1 7.9 3.3 17.2 2.6 17.2-1.3 0-2.5 3.5-3.1 8.8-1.4 11.2 3.5 38.4 2.2 43.1-2 1.1-1 2.1-1.2 3.3-.5 2 1.1 6.2-1.2 7.9-4.2.8-1.6 2.2-1.9 8.2-1.9 4 0 7.8.4 8.5.9 2 1.2 6 .1 6.5-1.9s8.5-5 13.4-5c1.9 0 4.7-.7 6.2-1.5 1.8-.9 4.2-1.2 6.5-.8 2.5.4 4.2.1 5.4-.9 2.1-1.9 8.2-5.1 8.2-4.3 0 .3-1 1.9-2.2 3.6-1.8 2.3-2 3.3-1.1 4.7.8 1.4 2.4 1.7 7.9 1.5 5.7-.1 7.1-.5 8.6-2.3 2.5-3 2.3-3.7-1.7-6.2-4.5-2.9-4.4-3.8.3-3.8 2.4 0 5.1-.9 7.6-2.6 3.5-2.4 4.4-2.6 10.1-2 4.9.5 6.4.3 6.8-.8.3-.7 2.5-1.6 5-2 2.4-.4 5.3-1.3 6.3-2.1 1.1-.8 2.9-1.5 4-1.5 1.6 0 2.3-.8 2.7-3 .8-3.8 6.8-6.7 10.5-5 1.8.8 2.8.7 4.5-.5 1.2-.8 2.9-1.5 3.7-1.5.9 0 3.6-2 6-4.5 2.7-2.8 5.3-4.5 6.7-4.5 3.6 0 8.5-2.4 11.7-5.9 1.7-1.7 4-3.1 5.3-3.1 3.2 0 5.7-5.2 5.7-12.3l.1-5.8-6.7-6c-3.6-3.2-8.5-9-10.8-12.7-2.3-3.7-5.9-8.4-7.8-10.5l-3.6-3.7 4.4-4.5c4.1-4.2 4.5-4.4 5.2-2.5.4 1.1 1.6 2 2.8 2 1.1 0 3.8 1.1 5.9 2.5 2.2 1.5 4.5 2.3 5.5 1.9.9-.3 2.3-.1 3.1.6 1.1.9 4.5 1.1 12.3.8 8.5-.4 12.1-.1 17.4 1.3 8.1 2.3 7.8 2.2 7.8.6 0-.8.9-2.2 2-3.2s2-2.5 2-3.3c0-.8 1.1-3.4 2.5-5.8 1.8-3.1 3.9-4.9 7.7-6.8 6.9-3.2 13.2-9.6 12.4-12.4-.9-2.7 2.9-8.9 6.4-10.7 4.1-2.1 9.1-10 9.4-14.9.2-2.3 1.7-6.9 3.4-10.3 1.8-3.5 3.2-7.8 3.2-9.7 0-2 .9-5.9 2-8.8 2-5.5 5-18.5 5-21.9 0-1.8-.9-1.9-24.4-1.9h-24.4l-.6 2.4c-.3 1.3-1.7 3.1-3 4-3.9 2.5-9.9 12.2-9.4 15.1.5 2.5-1.6 5.2-5.8 7.3-1.1.6-2.9 2.8-4 4.8-2.5 5-10.3 15.4-11.5 15.4-.5 0-.9.7-.9 1.5s-1.3 2.1-2.9 2.9c-1.6.9-3.4 2.9-3.9 4.6-.6 1.7-1.8 3.3-2.6 3.6-2.5.9-9.5 11.2-10.9 15.9-1 3.5-3.4 6.6-11.2 14.5-8.7 8.8-10 9.8-11.4 8.6-.9-.7-4-1.9-7-2.5-5.7-1.2-7.5-.6-12.5 4.1-1.1 1-2.9 1.8-4.1 1.8-1.1 0-4.2 2-6.8 4.4-2.7 2.4-6.1 4.7-7.5 5.1-1.5.3-5.5 1.7-8.9 3.1-3.4 1.3-7.5 2.4-9.2 2.4-1.7 0-3.4.7-3.9 1.6-1.1 2-15.6 8.4-19.1 8.4-4.5 0-12.7 2.7-16.1 5.1-2.4 1.8-4.6 2.4-9.3 2.6-3.4.1-9.3.9-13.2 1.8-3.8.9-9.7 1.8-12.9 2-3.4.3-6.6 1.1-7.6 2-1.8 1.6-6.5 2.1-7.4.7-.3-.4-6-.7-12.8-.6-9.4.1-13.7.6-18.3 2.2-6.8 2.3-17.7 2.3-27 .1-4.5-1.1-12.3-1.2-30.5-.5-4.9.2-7.5-.2-10.8-1.8-3.5-1.7-4.6-1.8-6.2-.8-2.4 1.5-6.5 1.6-9 .2-1.2-.7-2.8-.7-4.4 0-2.1.7-3.6.4-8.3-2.2-5.3-2.8-6.6-3.1-16-3.2-9.7-.1-10.2-.2-12.2-2.8-1.7-2.2-2.9-2.6-6.6-2.6-2.5.1-5.1-.4-5.7-1-.7-.7-1.9-1.2-2.9-1.2-.9 0-3.7-1.1-6.3-2.5-2.6-1.4-6.1-2.5-7.8-2.5-2.4 0-4.2-1.2-8-4.9-4.7-4.7-7.9-6.2-13.5-6.5-1.2-.1-3.2-1.1-4.5-2.3-1.3-1.2-4.7-3.1-7.6-4.2-3.4-1.2-6.8-3.5-9.1-6-4.2-4.6-7.4-5.2-12.1-2.4-2.8 1.6-2.9 1.6-8.4-2.2-5.9-4-6.4-4.5-14-12.4-2.7-2.8-5.3-5.1-5.8-5.1-.4 0-.8-.7-.8-1.5 0-3.2-5.3-13.2-8.8-16.6-2-1.9-5.7-5.9-8.2-8.8-7.7-9-8.9-10.1-10.7-10.1-1 0-3.9-2.5-6.5-5.4-4.3-4.9-4.8-5.9-4.8-10.3 0-4.2-.5-5.4-3-7.8-1.6-1.6-3-3.7-3-4.8 0-1.1-1.8-4.1-4-6.7-2.2-2.6-4-5.6-4-6.6s-.9-2.7-1.9-3.8c-1.2-1.2-2.8-5.9-4-11.2-1.1-5-3.2-12.4-4.7-16.5-2.5-6.9-2.6-8-2-19.4.3-6.6 1.2-15.5 1.9-19.8l1.4-7.7H23.1l-2.4 5.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
								</g>
								<g class="hoverShape">
									<path d="M20.7 224.2c-2.4 5-3.2 9.4-5.7 28.3-.8 6.4-.7 9.1.5 13.5 1.2 4.7 1.2 5.7 0 7.6-1 1.6-1.3 4.9-1.1 13 .2 6-.1 12-.6 13.4-1.4 3.5-1.8 20.1-.6 22.3.7 1.4.5 2.4-.7 3.7-2.2 2.4-1.3 8.6 3.6 25.9 3.7 12.9 4.8 15.1 7.7 15.1.7 0 2.1 1.9 3.1 4.2 1 2.4 3.3 5.6 5 7.2 1.7 1.7 3.1 4 3.1 5.3 0 1.7.6 2.3 2.3 2.3 1.7 0 2.8 1.1 4.5 4.6 2.1 4.2 2.5 4.6 5.2 4.1 2.6-.6 3.2-.1 6 4.3 4.7 7.3 4.7 7.3 13.1 5.1 8.4-2.3 27.6-5.1 34.7-5.1 4 0 5.3-.6 9.2-3.9l4.5-3.9 8 6.7c4.4 3.6 7.3 5.7 6.6 4.6-1.4-1.9-1.4-1.9.2-.4 1.6 1.4 1.5 2-1.9 9.4-2.8 6.1-5.3 9.5-10.8 15-3.9 3.8-8.9 9.2-11.1 11.9l-3.9 4.8 1.9 3.6c1.2 2.2 3.8 4.6 6.3 6 2.9 1.6 4.2 3 4.2 4.5.1 4.2 7 10 10.8 9 2.7-.7 12.2 2.5 12.2 4.1 0 .7 2.8 2.8 6.1 4.6 3.5 1.8 6.8 4.5 7.8 6.1 2.2 4 5.4 5.2 14.6 5.9 7.5.5 8.1.7 9.3 3.2 1.2 2.5 1.9 2.8 6.1 2.8 3.1 0 5.3.5 6.1 1.5 1.6 1.9 5.8 1.9 10.1.1 2.9-1.2 3.7-1.2 6.3.1 1.6.8 3.1 1.1 3.5.6.3-.5 1.8-.8 3.4-.8 1.5.1 2.7-.3 2.7-.8s.7-.7 1.5-.3c1 .4 1.5-.1 1.5-1.5 0-1.7.3-1.8 2.2-.8 1.7.9 2.3.9 2.6 0 .3-.9.7-.9 1.8 0 1.2 1 1.6.8 2.1-.9.6-2.4 1.8-2.8 2.8-1.1.4.6 1.3.8 2.1.5.7-.3 1.9.4 2.5 1.5.8 1.6 1.4 1.8 2.4.9.9-.8 1.5-.8 2 0 .3.5 1.7 1 3.1 1 1.9 0 2.4-.5 2.4-2.6 0-2.5.1-2.6 2.4-1.1 1.3.9 2.7 1.3 3 1 1-1 4.6-1.1 4.6-.1 0 .4.7.8 1.5.8s1.5.4 1.5.8c0 .5 1.1 1.5 2.5 2.2 1.4.7 2.5 1.7 2.5 2.1 0 1.1 9.8 10.2 11.8 11.1.9.4 2.9.9 4.5 1.2 1.5.4 2.7 1.3 2.7 2.1 0 1.5 3.2 2.1 9.5 1.7 2.4-.2 3.4.4 4.8 2.8 2 3.2 8.3 7 10.5 6.2.8-.2 3 .3 5 1.1 7.9 3.3 17.2 2.6 17.2-1.3 0-2.5 3.5-3.1 8.8-1.4 11.2 3.5 38.4 2.2 43.1-2 1.1-1 2.1-1.2 3.3-.5 2 1.1 6.2-1.2 7.9-4.2.8-1.6 2.2-1.9 8.2-1.9 4 0 7.8.4 8.5.9 2 1.2 6 .1 6.5-1.9s8.5-5 13.4-5c1.9 0 4.7-.7 6.2-1.5 1.8-.9 4.2-1.2 6.5-.8 2.5.4 4.2.1 5.4-.9 2.1-1.9 8.2-5.1 8.2-4.3 0 .3-1 1.9-2.2 3.6-1.8 2.3-2 3.3-1.1 4.7.8 1.4 2.4 1.7 7.9 1.5 5.7-.1 7.1-.5 8.6-2.3 2.5-3 2.3-3.7-1.7-6.2-4.5-2.9-4.4-3.8.3-3.8 2.4 0 5.1-.9 7.6-2.6 3.5-2.4 4.4-2.6 10.1-2 4.9.5 6.4.3 6.8-.8.3-.7 2.5-1.6 5-2 2.4-.4 5.3-1.3 6.3-2.1 1.1-.8 2.9-1.5 4-1.5 1.6 0 2.3-.8 2.7-3 .8-3.8 6.8-6.7 10.5-5 1.8.8 2.8.7 4.5-.5 1.2-.8 2.9-1.5 3.7-1.5.9 0 3.6-2 6-4.5 2.7-2.8 5.3-4.5 6.7-4.5 3.6 0 8.5-2.4 11.7-5.9 1.7-1.7 4-3.1 5.3-3.1 3.2 0 5.7-5.2 5.7-12.3l.1-5.8-6.7-6c-3.6-3.2-8.5-9-10.8-12.7-2.3-3.7-5.9-8.4-7.8-10.5l-3.6-3.7 4.4-4.5c4.1-4.2 4.5-4.4 5.2-2.5.4 1.1 1.6 2 2.8 2 1.1 0 3.8 1.1 5.9 2.5 2.2 1.5 4.5 2.3 5.5 1.9.9-.3 2.3-.1 3.1.6 1.1.9 4.5 1.1 12.3.8 8.5-.4 12.1-.1 17.4 1.3 8.1 2.3 7.8 2.2 7.8.6 0-.8.9-2.2 2-3.2s2-2.5 2-3.3c0-.8 1.1-3.4 2.5-5.8 1.8-3.1 3.9-4.9 7.7-6.8 6.9-3.2 13.2-9.6 12.4-12.4-.9-2.7 2.9-8.9 6.4-10.7 4.1-2.1 9.1-10 9.4-14.9.2-2.3 1.7-6.9 3.4-10.3 1.8-3.5 3.2-7.8 3.2-9.7 0-2 .9-5.9 2-8.8 2-5.5 5-18.5 5-21.9 0-1.8-.9-1.9-24.4-1.9h-24.4l-.6 2.4c-.3 1.3-1.7 3.1-3 4-3.9 2.5-9.9 12.2-9.4 15.1.5 2.5-1.6 5.2-5.8 7.3-1.1.6-2.9 2.8-4 4.8-2.5 5-10.3 15.4-11.5 15.4-.5 0-.9.7-.9 1.5s-1.3 2.1-2.9 2.9c-1.6.9-3.4 2.9-3.9 4.6-.6 1.7-1.8 3.3-2.6 3.6-2.5.9-9.5 11.2-10.9 15.9-1 3.5-3.4 6.6-11.2 14.5-8.7 8.8-10 9.8-11.4 8.6-.9-.7-4-1.9-7-2.5-5.7-1.2-7.5-.6-12.5 4.1-1.1 1-2.9 1.8-4.1 1.8-1.1 0-4.2 2-6.8 4.4-2.7 2.4-6.1 4.7-7.5 5.1-1.5.3-5.5 1.7-8.9 3.1-3.4 1.3-7.5 2.4-9.2 2.4-1.7 0-3.4.7-3.9 1.6-1.1 2-15.6 8.4-19.1 8.4-4.5 0-12.7 2.7-16.1 5.1-2.4 1.8-4.6 2.4-9.3 2.6-3.4.1-9.3.9-13.2 1.8-3.8.9-9.7 1.8-12.9 2-3.4.3-6.6 1.1-7.6 2-1.8 1.6-6.5 2.1-7.4.7-.3-.4-6-.7-12.8-.6-9.4.1-13.7.6-18.3 2.2-6.8 2.3-17.7 2.3-27 .1-4.5-1.1-12.3-1.2-30.5-.5-4.9.2-7.5-.2-10.8-1.8-3.5-1.7-4.6-1.8-6.2-.8-2.4 1.5-6.5 1.6-9 .2-1.2-.7-2.8-.7-4.4 0-2.1.7-3.6.4-8.3-2.2-5.3-2.8-6.6-3.1-16-3.2-9.7-.1-10.2-.2-12.2-2.8-1.7-2.2-2.9-2.6-6.6-2.6-2.5.1-5.1-.4-5.7-1-.7-.7-1.9-1.2-2.9-1.2-.9 0-3.7-1.1-6.3-2.5-2.6-1.4-6.1-2.5-7.8-2.5-2.4 0-4.2-1.2-8-4.9-4.7-4.7-7.9-6.2-13.5-6.5-1.2-.1-3.2-1.1-4.5-2.3-1.3-1.2-4.7-3.1-7.6-4.2-3.4-1.2-6.8-3.5-9.1-6-4.2-4.6-7.4-5.2-12.1-2.4-2.8 1.6-2.9 1.6-8.4-2.2-5.9-4-6.4-4.5-14-12.4-2.7-2.8-5.3-5.1-5.8-5.1-.4 0-.8-.7-.8-1.5 0-3.2-5.3-13.2-8.8-16.6-2-1.9-5.7-5.9-8.2-8.8-7.7-9-8.9-10.1-10.7-10.1-1 0-3.9-2.5-6.5-5.4-4.3-4.9-4.8-5.9-4.8-10.3 0-4.2-.5-5.4-3-7.8-1.6-1.6-3-3.7-3-4.8 0-1.1-1.8-4.1-4-6.7-2.2-2.6-4-5.6-4-6.6s-.9-2.7-1.9-3.8c-1.2-1.2-2.8-5.9-4-11.2-1.1-5-3.2-12.4-4.7-16.5-2.5-6.9-2.6-8-2-19.4.3-6.6 1.2-15.5 1.9-19.8l1.4-7.7H23.1l-2.4 5.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
								</g>
								<g class="clickShapeWinter">
									<path d="M16.8 233.2c-3.6 7.7-4.8 23.3-5 68.8-.1 36.6 1 60.6 3.3 66.7.4 1.2 3.8 4.1 7.6 6.6 3.7 2.5 9.3 6.8 12.3 9.7 3 2.9 7.3 6.5 9.6 8.1 2.3 1.5 5.9 5.1 8 7.9 3.1 4 4.3 5 5.9 4.5 5.7-1.8 13-3.6 17-4.4l4.6-.9 5.7 5.7c7.6 7.5 13.4 12.1 20.7 16.5 3.3 1.9 6.6 4 7.4 4.5 1.1.7.1 2.6-5.3 8.9-6.6 8-6.6 8.1-5.7 12.1 1.1 4.9 5.1 10.5 9 12.5 1.5.8 4.2 3.1 5.9 5 1.7 2 3.4 3.6 3.7 3.6.4 0 3.2 1.2 6.3 2.6 3.1 1.5 7.3 3.4 9.2 4.2 1.9.8 4.7 2.1 6.2 2.9 1.4.7 3.3 1.3 4.2 1.3.9 0 2.4.8 3.5 1.9 2.1 2.1 5.7 3.3 15.1 5.2 3.6.7 7.4 1.8 8.5 2.5 1.1.7 4.3 1.8 7 2.4 2.8.6 5.7 1.3 6.5 1.6.8.3 5.3.7 9.9.9 9.4.5 23.2 3 27.3 5.1 1.6.8 5.8 1.4 10.2 1.4 6.8 0 14.2 1.4 35.6 6.6 7.9 2 19.3 6.8 25.4 10.9 4.3 2.9 10.4 3.4 42.6 4 23.4.5 32.2.3 42.5-1 20.5-2.5 24.4-3.1 35.2-5.9 5.6-1.4 13-2.6 16.5-2.6 3.5-.1 8.1-.8 10.3-1.6 5.7-2.2 11.8-4 20.9-6 4.3-1 9.1-2.7 10.5-3.8 3.9-3.1 6.8-4.6 9.1-4.6 1.1 0 3.9-1.7 6.3-3.9 3.2-2.9 5.3-4 9-4.4 2.8-.4 7.4-2.2 10.8-4.2 3.3-1.9 6.6-3.5 7.3-3.5 1.8 0 12-6.3 16.7-10.3 2.2-1.9 6.8-4.9 10.2-6.7 8.9-4.7 15.5-11.3 16.2-16.4.8-5-.7-9.1-4.6-13.2l-3-3.1 3.4-3.4c1.8-1.9 3.8-4.1 4.3-5 .5-.8 2.9-2.9 5.4-4.6 2.5-1.7 6.3-5 8.5-7.2l4-4.2 6.5.7c5.8.5 6.7.4 8.4-1.5 1.1-1.2 2.8-2.1 3.8-2.1 1.1 0 2.3-1.1 2.8-2.6 1.3-3.4 13.1-16.4 14.9-16.4 1.3 0 6.1-4.2 14.5-12.5 1.6-1.7 2.6-2.3 2.1-1.5-.4.8.4.4 1.9-1 2.5-2.2 2.7-3 2.3-7-.4-2.9 0-5.4.8-7 .8-1.4 1.4-4.6 1.5-7.2 0-2.9 1-7.1 2.6-10.5 7.8-17.6 9.3-22.9 9.9-34.8l.6-11.5h-46.9l-3.1 2.6c-1.9 1.6-3.6 4.4-4.5 7.5-.8 2.7-2.6 6.4-3.9 8.1-1.4 1.8-3.2 5-4.1 7.2-.9 2.1-2.9 5.4-4.6 7.3-1.7 1.8-4.1 5.6-5.5 8.3-1.3 2.7-3.3 5.1-4.4 5.5-1.2.3-2.7 2.5-3.7 5.2-1.3 3.3-4.2 7-10.1 12.7-24.4 23.7-32 30-41 34-2.3 1-5.2 2.9-6.4 4.2-1.3 1.4-4.4 3.1-7 3.9-2.7.7-7.6 3.3-11.1 5.7-6.9 4.7-11.1 6.8-13.8 6.8-1.6 0-5.9 1.6-14.7 5.6-1.7.8-3.6 1.4-4.3 1.4-.7 0-3.8 1.6-7 3.6-4.2 2.6-7.3 3.7-11.6 4.1-3.3.3-7 1.2-8.4 1.9-2.9 1.5-23.3 6.6-33.2 8.3-3.8.6-9.8 1.6-13.5 2.3-3.8.6-14 1.2-22.9 1.3-13.3 0-16.5.4-18.7 1.8-2.3 1.5-3.9 1.5-16.4.6-15.6-1.3-18.9-1.4-32.3-1.2-14.8.3-28.9-.5-32.6-1.7-1.9-.6-4.6-1-6.1-.9-1.6.2-3.3 0-4-.4-2.5-1.6-10.9-3.7-14.8-3.7-5.9-.1-14.3-2.3-20.2-5.4-3.8-2-6-2.6-8.1-2.1-2 .4-3.6 0-5.3-1.3-2.2-1.8-5.5-3.2-8.4-3.6-1.7-.2-18.2-9.7-21.5-12.3-1.5-1.2-2.9-2-3.2-1.8-1 1-7.4-1.9-11.1-5.1-4-3.4-9.2-5.5-19.2-8-4.1-1.1-7.1-2.6-10-5.2-2.2-2.1-5.8-4.8-8-6.2-5.9-3.5-13.5-10-13.5-11.5 0-.7-1.9-3.7-4.2-6.5-4.7-5.8-7.5-9.5-11.4-15.4-2.8-4.2-18.2-19.6-19.6-19.6-.5 0-1.8-2.1-2.9-4.8-1.2-2.6-3.2-6.7-4.5-9.2-1.3-2.5-4-7.9-6-12s-5-10.2-6.7-13.4c-1.7-3.3-3.6-8.4-4.3-11.5-.7-3.1-2.8-10.8-4.5-17.1-2.5-8.9-3.3-14-3.6-22.8l-.4-11.2H19.3l-2.5 5.2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
								</g>
								<g class="hoverShapeWinter">
									<path d="M18 230.7c-.7 1.6-1.5 4.4-1.6 6.3-.9 11-1.4 14-2.3 15.8-1.1 2.1-.7 10.5.9 16 .7 2.7.6 4.1-.7 6.3-1.3 2.2-1.4 3.2-.4 5.6.8 1.9.9 4.3.3 7.4-.6 2.8-.5 5.4 0 6.3.7 1.2.5 2.4-.5 3.9-1.9 3-2.7 7.8-1.6 9.9.6 1.2.6 3.2-.1 5.7-.8 3-.7 4.6.5 7.4 1.1 2.6 1.2 3.8.4 4.3-1.6.9-.3 16.5 1.6 20.1.8 1.6 1.5 3.6 1.5 4.6 0 1 .8 4.8 1.7 8.4 1.2 5 1.3 6.8.5 7.1-.7.2-1.7 1.2-2.2 2.2-.7 1.3-.4 2.2 1.2 3.6 1.4 1.2 1.8 1.4 1.3.4-.5-.8.3-.4 1.7 1s5 3.8 7.9 5.3c5 2.8 12.9 9.9 12.9 11.8 0 .5 2.1 1.9 4.7 3.1 2.8 1.4 5 3.1 5.3 4.4.3 1.1 1.7 3.5 3.2 5.4 2.4 3.1 2.8 3.2 5.5 2.2 8-3.2 25.5-6.2 37.2-6.4 6.7-.1 7.9-.4 11.7-3.2 4-2.9 4.2-3.3 3.7-7.1l-.6-4 7.9 7.6c4.4 4.1 9 8.3 10.4 9.2l2.4 1.7-4.2 8.5c-3.1 6.1-6.1 10.3-10.6 14.8-9.4 9.3-15.9 17.5-15.3 19.2.4.8 3.1 3.4 6.2 5.7 4.1 3.2 5.5 4.9 5.5 6.9 0 1.6 1 3.3 3 4.7 1.7 1.3 3 3.1 3 4 0 2.5 8.1 5.1 15.2 4.9 4.9-.1 6.4.3 10 2.8 2.4 1.7 5 4.5 5.8 6.2 1.8 3.8 7.7 5.9 13.9 4.8 5.1-.9 10.4.9 12.5 4.1 1.2 1.7 2.6 2.4 4.9 2.4 1.8 0 4.2.7 5.5 1.5 1.7 1.3 2.6 1.4 4.4.4 3.2-1.7 18.5-.4 25.6 2.2 3.1 1.1 6.6 2 7.7 2 21.2-1.5 23.3-1.2 31 4.5 1.8 1.2 5.2 2.4 8 2.7 7.8.8 13.4 2.3 15.4 4.1.9.9 3.4 1.6 5.3 1.6 5 0 6.6.8 8.4 4.1.8 1.6 2.7 3.5 4.2 4.2 3.7 1.9 23.1 4.3 24.7 3.1 5.6-4.1 6-4.3 11-3.4 17.6 2.9 22.2 3.2 30.7 2.2 4.9-.6 9.8-1.7 11-2.3 2.4-1.3 7.4-1.9 14.2-1.9 2.2.1 4.2-.3 4.5-.9.9-1.3 3.2-1.6 7-.7 2.7.7 3.6.5 4.3-.8.5-.9 2-1.6 3.2-1.6 1.7 0 2.6-.7 3-2.5 1-3.8 4.3-5.2 13.4-5.9 4.6-.3 8.8-.9 9.5-1.3.6-.4 3-.6 5.3-.4 3.3.2 4.8-.3 7.4-2.6 2.8-2.3 3.2-2.4 2.6-.8-.5 1.1-1.1 3.3-1.4 4.9l-.5 2.9 8.3-.7c6.1-.5 8.7-1.1 9.6-2.4 2.1-2.7 1.6-4.1-2.1-5.9-4.4-2.1-4.4-2.8-.2-3 1.7 0 4.3-.4 5.7-.7 1.4-.3 5.7-1.3 9.5-2.1 3.9-.9 10.6-2.9 15-4.6 6.6-2.5 8.9-4 13.2-8.5l5.2-5.6 6.5.7 6.6.7 4.5-4.6c2.7-2.7 5.5-4.6 6.8-4.6 3.3 0 12.4-4.6 13.9-6.9.7-1.2 2-2.1 2.9-2.1 2.5 0 6.7-5.2 7.4-9.2 1.1-5.9-1-10.2-7.6-15.9-3.2-2.9-8.1-8.6-10.8-12.8-2.7-4.2-5.9-8.5-7.1-9.6-4.4-4-4.3-4.8 2.1-11.1 5.9-5.8 5.9-5.9 5.3-2.6-.6 2.9-.4 3.2 1.6 3.2 1.3 0 3.8 1.1 5.6 2.5 3.6 2.8 3.2 2.7 19.9 2.9 9.2.2 13.1.6 16.7 2.1 4.7 1.9 4.7 1.9 7-.2 1.3-1.2 3.1-2.3 4.2-2.5 1.1-.2 2.6-2.3 4-5.4 1.1-2.8 2.8-5.9 3.6-7 2.5-3.3 7.1-6.4 9.5-6.4 2.3 0 9.8-7.7 8.9-9.2-.3-.4.6-.8 2-.8 1.5 0 3.3-1.1 4.6-2.8 1.2-1.5 1.8-2 1.4-1.2-.4.8.5.4 2-1 2.5-2.2 2.7-3 2.3-7-.4-2.9 0-5.4.8-7 .8-1.4 1.4-4.6 1.5-7.2 0-2.9 1-7.1 2.6-10.5 7.8-17.6 9.3-22.9 9.9-34.8l.6-11.5h-45.8l-1.2 5.5c-.9 4.1-2 6-4.1 7.5-4.3 3.1-9.2 10.8-9.2 14.7 0 3.2-1.2 4.9-5.2 6.8-1.1.5-3 2.9-4.1 5.2-1.4 2.9-3.1 4.7-5.7 5.9-2.1.9-3.8 1.7-3.8 1.8-2 6.9-4.1 11.3-5.8 12.2-2.5 1.2-9.2 8.1-9.2 9.4 0 .5-1.6 3-3.6 5.5-2 2.6-4.2 6.6-5 8.8-.8 2.4-3.6 6.2-6.7 9.2-2.8 2.8-7.3 7.5-9.8 10.5l-4.7 5.5-2.8-1.6c-1.6-.9-5-2-7.6-2.3-5-.7-7.9.2-11.1 3.5-1 1.1-2.9 1.9-4.3 1.9-1.6 0-3.9 1.5-6.7 4.5-2.8 2.9-5.2 4.5-6.8 4.5-1.3 0-4.4.6-6.9 1.4-2.5.7-5.1 1.1-5.9.8-.9-.3-1.1 0-.6.8.6 1 .4 1.2-.8.7-2.7-1-6.5.9-8.1 4.1-1 2-2.8 3.2-5.7 4.1-2.3.7-5.8 2.1-7.6 3.1-2.5 1.3-4.2 1.6-6.3 1-5.5-1.6-10-.5-12.8 3-2.5 3.2-2.6 3.2-8.3 2.6-4.4-.5-7.5-.2-12.6 1.3-3.8 1.1-9.6 2.5-13.1 3-3.4.5-8.4 2.2-11.1 3.6l-4.8 2.6-5.2-1.7c-7.3-2.4-19.3-1.5-29.5 2.1-7.4 2.6-7.8 2.6-11.6 1.1-2.2-.9-6.1-1.6-8.6-1.6s-6.8-.7-9.5-1.6c-3.4-1.1-6.3-1.4-9.2-.9-2.3.3-5.8.9-7.7 1.2-1.9.3-4 1-4.7 1.5-.7.6-1.9.5-3.3-.2-3.6-1.9-6.1-2.3-16.5-2.7-13.1-.5-13.5-.6-13.5-1.6 0-.5-1.9-.7-4.2-.5-3.2.4-5.7-.2-9.7-2-5-2.3-6.1-2.4-15-1.9l-9.6.7-2-3c-2.7-4-7.6-6.3-11.8-5.5-2.5.5-3.7.2-5.1-1.4-1.5-1.6-3.8-2.6-8.6-3.6-.3 0-2.4-1.1-4.7-2.3-2.3-1.2-5.1-2.2-6.3-2.2-1.1 0-2.5-.9-3.2-2-.7-1.1-2.3-2-3.5-2-1.3 0-2.3-.6-2.3-1.4 0-1.9-2.6-3.9-5.6-4.2-1.4-.2-4.4-1.2-6.7-2.2-2.3-1.1-6.3-2.8-8.9-3.7-2.7-1-4.8-2.1-4.8-2.6 0-3.5-9.5-5.4-12-2.4-2.5 3-3.5 2.2-19.8-14.8-1.5-1.6-3.9-3.5-5.4-4.3-1.8-.9-3.9-3.9-6.3-8.9-2.2-4.6-4.9-8.5-6.9-10s-3.8-4.1-4.5-6.5c-.9-2.9-3.7-6.5-10.3-13-5-5-9.4-9-9.7-9-1.7 0-5.1-5.3-5.1-7.8 0-1.5-1.3-4.6-2.9-6.8-1.6-2.1-3.2-5-3.6-6.4-.4-1.4-1.8-3.9-3.1-5.7-1.3-1.7-2.4-3.7-2.4-4.3 0-.6-1.5-2.7-3.2-4.6-1.8-2-3.4-4.6-3.4-5.7-.2-3.7-5.1-21.3-6.8-24.6-1.8-3.4-2.3-8.3-2.5-25.4L44 228H19.3l-1.3 2.7zM205.5 425c.3.5.2 1-.4 1-.5 0-1.3-.5-1.6-1-.3-.6-.2-1 .4-1 .5 0 1.3.4 1.6 1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
								</g>
							</svg>';
				break;
				*/
				case 34:
					$sVG ='
							<svg class="buildingShape g34" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
					<g class="clickShape">
						<path d="M67.1 11.4c-.7.8-1 2-.6 2.5 1.3 2.2-1.8 5.1-7.8 7.3-3.4 1.2-6.4 2.1-6.7 2-.2-.1-.5-1.2-.5-2.5 0-1.7-.6-2.2-2.5-2.2-2.5 0-2.6.2-2.9 6.6-.1 1.5-2.9 6.4-6.5 11.5-4.7 6.6-6 9.1-5.1 9.7.8.4 2.5.3 3.9-.3l2.6-1v15.1l-4.5 2.7c-4.7 2.9-5.5 4.5-2.9 6 1 .6 1.4 2 1.2 4.3l-.3 3.4-2-2.3c-2.6-3-6.2-2.9-7.7.3-.6 1.4-1.5 2.5-2.1 2.5-.5 0-.7 2-.5 4.5.2 3.1-.1 4.5-.9 4.5-1.8 0-1.6 2.5.4 4.1 1.7 1.5 4.4 2 10.3 2 1.9 0 4 .5 4.7 1 .8.7 1.8.7 3.2-.1 2.8-1.4 2.6-2.4-.6-5-1.6-1.2-2.3-1.9-1.7-1.6.7.3 1.4 0 1.7-.8 1.2-3 6.7.4 6.4 3.9-.1 1 .4 2 1.1 2.2.9.3 1.2-1.3 1.2-6.2 0-6.3.1-6.5 2-5.5 1.8 1 2 1.7 1.4 5.9-.6 4.7-.5 4.9 3 7 1.9 1.2 4 2 4.5 1.6.6-.3 1.9 0 3 .7 3 1.9 4.9-.2 3.3-3.7-1.1-2.4-1-2.5 1.7-2.5 1.6 0 3.9 1.1 5.5 2.5 1.5 1.4 3.4 2.5 4.2 2.5.7 0 1.4.6 1.4 1.4 0 1.6 2.2 2.7 3.7 1.8.5-.4 1.4-2.1 2-3.7.5-1.7 1.6-3 2.4-3.1 3.5-.1 8.4-1.8 10.1-3.3 1.9-1.8 2-6.3.3-9.1-1.5-2.3-.5-9 1.6-11.1 1-1 1.9-2.2 1.9-2.7-.1-.4-2.9-2.5-6.4-4.6l-6.4-3.9-.6-5.9c-.5-3.9-.3-6.4.4-7.3.7-.8.8-2 .3-2.7-.4-.7-1-4.7-1.3-8.8-.5-7.4-.5-7.5-3.4-7.8-1.9-.2-3.2.2-3.8 1.2-1.5 2.7-6.1-3.7-6.8-9.3-.5-4.9-2.6-6.5-4.9-3.7zM40 73.5c0 1.8.9 3.1 3 4.3 2.4 1.3 3 2.4 3 5 0 2.1-.4 3.1-1.1 2.7-.6-.4-.8-1.3-.5-2 .7-1.9-2-3.8-4.4-3-1.3.4-2 .2-2-.7-.1-.7-.3-3-.6-5.1-.5-2.9-.2-3.7 1-3.7 1 0 1.6.9 1.6 2.5zM47.8 100.6c-5.9 3.2-2.1 8.3 4.5 6 5-1.7 5.2-2 2.6-5s-3.4-3.1-7.1-1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="hoverShape">
						<path d="M67.1 11.4c-.7.8-1 2-.6 2.5 1.3 2.2-1.8 5.1-7.8 7.3-3.4 1.2-6.4 2.1-6.7 2-.2-.1-.5-1.2-.5-2.5 0-1.7-.6-2.2-2.5-2.2-2.5 0-2.6.2-2.9 6.6-.1 1.5-2.9 6.4-6.5 11.5-4.7 6.6-6 9.1-5.1 9.7.8.4 2.5.3 3.9-.3l2.6-1v15.1l-4.5 2.7c-4.7 2.9-5.5 4.5-2.9 6 1 .6 1.4 2 1.2 4.3l-.3 3.4-2-2.3c-2.6-3-6.2-2.9-7.7.3-.6 1.4-1.5 2.5-2.1 2.5-.5 0-.7 2-.5 4.5.2 3.1-.1 4.5-.9 4.5-1.8 0-1.6 2.5.4 4.1 1.7 1.5 4.4 2 10.3 2 1.9 0 4 .5 4.7 1 .8.7 1.8.7 3.2-.1 2.8-1.4 2.6-2.4-.6-5-1.6-1.2-2.3-1.9-1.7-1.6.7.3 1.4 0 1.7-.8 1.2-3 6.7.4 6.4 3.9-.1 1 .4 2 1.1 2.2.9.3 1.2-1.3 1.2-6.2 0-6.3.1-6.5 2-5.5 1.8 1 2 1.7 1.4 5.9-.6 4.7-.5 4.9 3 7 1.9 1.2 4 2 4.5 1.6.6-.3 1.9 0 3 .7 3 1.9 4.9-.2 3.3-3.7-1.1-2.4-1-2.5 1.7-2.5 1.6 0 3.9 1.1 5.5 2.5 1.5 1.4 3.4 2.5 4.2 2.5.7 0 1.4.6 1.4 1.4 0 1.6 2.2 2.7 3.7 1.8.5-.4 1.4-2.1 2-3.7.5-1.7 1.6-3 2.4-3.1 3.5-.1 8.4-1.8 10.1-3.3 1.9-1.8 2-6.3.3-9.1-1.5-2.3-.5-9 1.6-11.1 1-1 1.9-2.2 1.9-2.7-.1-.4-2.9-2.5-6.4-4.6l-6.4-3.9-.6-5.9c-.5-3.9-.3-6.4.4-7.3.7-.8.8-2 .3-2.7-.4-.7-1-4.7-1.3-8.8-.5-7.4-.5-7.5-3.4-7.8-1.9-.2-3.2.2-3.8 1.2-1.5 2.7-6.1-3.7-6.8-9.3-.5-4.9-2.6-6.5-4.9-3.7zM40 73.5c0 1.8.9 3.1 3 4.3 2.4 1.3 3 2.4 3 5 0 2.1-.4 3.1-1.1 2.7-.6-.4-.8-1.3-.5-2 .7-1.9-2-3.8-4.4-3-1.3.4-2 .2-2-.7-.1-.7-.3-3-.6-5.1-.5-2.9-.2-3.7 1-3.7 1 0 1.6.9 1.6 2.5zM47.8 100.6c-5.9 3.2-2.1 8.3 4.5 6 5-1.7 5.2-2 2.6-5s-3.4-3.1-7.1-1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="clickShapeWinter">
						<path d="M67.1 11.4c-.7.8-1 2-.6 2.5 1.3 2.2-1.8 5.1-7.8 7.3-3.4 1.2-6.4 2.1-6.7 2-.2-.1-.5-1.2-.5-2.5 0-1.7-.6-2.2-2.5-2.2-2.5 0-2.6.2-2.9 6.6-.1 1.5-2.9 6.4-6.5 11.5-4.7 6.6-6 9.1-5.1 9.7.8.4 2.5.3 3.9-.3l2.6-1v15.1l-4.5 2.7c-4.7 2.9-5.5 4.5-2.9 6 1 .6 1.4 2 1.2 4.3l-.3 3.4-2-2.3c-2.6-3-6.2-2.9-7.7.3-.6 1.4-1.5 2.5-2.1 2.5-.5 0-.7 2-.5 4.5.2 3.1-.1 4.5-.9 4.5-1.8 0-1.6 2.5.4 4.1 1.7 1.5 4.4 2 10.3 2 1.9 0 4 .5 4.7 1 .8.7 1.8.7 3.2-.1 2.8-1.4 2.6-2.4-.6-5-1.6-1.2-2.3-1.9-1.7-1.6.7.3 1.4 0 1.7-.8 1.2-3 6.7.4 6.4 3.9-.1 1 .4 2 1.1 2.2.9.3 1.2-1.3 1.2-6.2 0-6.3.1-6.5 2-5.5 1.8 1 2 1.7 1.4 5.9-.6 4.7-.5 4.9 3 7 1.9 1.2 4 2 4.5 1.6.6-.3 1.9 0 3 .7 3 1.9 4.9-.2 3.3-3.7-1.1-2.4-1-2.5 1.7-2.5 1.6 0 3.9 1.1 5.5 2.5 1.5 1.4 3.4 2.5 4.2 2.5.7 0 1.4.6 1.4 1.4 0 1.6 2.2 2.7 3.7 1.8.5-.4 1.4-2.1 2-3.7.5-1.7 1.6-3 2.4-3.1 3.5-.1 8.4-1.8 10.1-3.3 1.9-1.8 2-6.3.3-9.1-1.5-2.3-.5-9 1.6-11.1 1-1 1.9-2.2 1.9-2.7-.1-.4-2.9-2.5-6.4-4.6l-6.4-3.9-.6-5.9c-.5-3.9-.3-6.4.4-7.3.7-.8.8-2 .3-2.7-.4-.7-1-4.7-1.3-8.8-.5-7.4-.5-7.5-3.4-7.8-1.9-.2-3.2.2-3.8 1.2-1.5 2.7-6.1-3.7-6.8-9.3-.5-4.9-2.6-6.5-4.9-3.7zM40 73.5c0 1.8.9 3.1 3 4.3 2.4 1.3 3 2.4 3 5 0 2.1-.4 3.1-1.1 2.7-.6-.4-.8-1.3-.5-2 .7-1.9-2-3.8-4.4-3-1.3.4-2 .2-2-.7-.1-.7-.3-3-.6-5.1-.5-2.9-.2-3.7 1-3.7 1 0 1.6.9 1.6 2.5zM47.8 100.6c-5.9 3.2-2.1 8.3 4.5 6 5-1.7 5.2-2 2.6-5s-3.4-3.1-7.1-1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					<g class="hoverShapeWinter">
						<path d="M67.1 11.4c-.7.8-1 2-.6 2.5 1.3 2.2-1.8 5.1-7.8 7.3-3.4 1.2-6.4 2.1-6.7 2-.2-.1-.5-1.2-.5-2.5 0-1.7-.6-2.2-2.5-2.2-2.5 0-2.6.2-2.9 6.6-.1 1.5-2.9 6.4-6.5 11.5-4.7 6.6-6 9.1-5.1 9.7.8.4 2.5.3 3.9-.3l2.6-1v15.1l-4.5 2.7c-4.7 2.9-5.5 4.5-2.9 6 1 .6 1.4 2 1.2 4.3l-.3 3.4-2-2.3c-2.6-3-6.2-2.9-7.7.3-.6 1.4-1.5 2.5-2.1 2.5-.5 0-.7 2-.5 4.5.2 3.1-.1 4.5-.9 4.5-1.8 0-1.6 2.5.4 4.1 1.7 1.5 4.4 2 10.3 2 1.9 0 4 .5 4.7 1 .8.7 1.8.7 3.2-.1 2.8-1.4 2.6-2.4-.6-5-1.6-1.2-2.3-1.9-1.7-1.6.7.3 1.4 0 1.7-.8 1.2-3 6.7.4 6.4 3.9-.1 1 .4 2 1.1 2.2.9.3 1.2-1.3 1.2-6.2 0-6.3.1-6.5 2-5.5 1.8 1 2 1.7 1.4 5.9-.6 4.7-.5 4.9 3 7 1.9 1.2 4 2 4.5 1.6.6-.3 1.9 0 3 .7 3 1.9 4.9-.2 3.3-3.7-1.1-2.4-1-2.5 1.7-2.5 1.6 0 3.9 1.1 5.5 2.5 1.5 1.4 3.4 2.5 4.2 2.5.7 0 1.4.6 1.4 1.4 0 1.6 2.2 2.7 3.7 1.8.5-.4 1.4-2.1 2-3.7.5-1.7 1.6-3 2.4-3.1 3.5-.1 8.4-1.8 10.1-3.3 1.9-1.8 2-6.3.3-9.1-1.5-2.3-.5-9 1.6-11.1 1-1 1.9-2.2 1.9-2.7-.1-.4-2.9-2.5-6.4-4.6l-6.4-3.9-.6-5.9c-.5-3.9-.3-6.4.4-7.3.7-.8.8-2 .3-2.7-.4-.7-1-4.7-1.3-8.8-.5-7.4-.5-7.5-3.4-7.8-1.9-.2-3.2.2-3.8 1.2-1.5 2.7-6.1-3.7-6.8-9.3-.5-4.9-2.6-6.5-4.9-3.7zM40 73.5c0 1.8.9 3.1 3 4.3 2.4 1.3 3 2.4 3 5 0 2.1-.4 3.1-1.1 2.7-.6-.4-.8-1.3-.5-2 .7-1.9-2-3.8-4.4-3-1.3.4-2 .2-2-.7-.1-.7-.3-3-.6-5.1-.5-2.9-.2-3.7 1-3.7 1 0 1.6.9 1.6 2.5zM47.8 100.6c-5.9 3.2-2.1 8.3 4.5 6 5-1.7 5.2-2 2.6-5s-3.4-3.1-7.1-1z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
					</g>
					</svg>';
				break;
		
				case 35:
					$sVG ='<svg class="buildingShape g35" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
	<g class="clickShape">
			<path d="M49.4 70.4c-7.8 1.8-13.5 4.7-16.8 8.6-3.5 4.3-4.1 7.2-2.1 11.3 3.3 7.1 13.9 11.7 28.5 12.4 19.8 1.1 35-6.5 35-17.5 0-4.9-5.8-10.2-14.2-13.1-8.1-2.8-22-3.6-30.4-1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M49.4 70.4c-7.8 1.8-13.5 4.7-16.8 8.6-3.5 4.3-4.1 7.2-2.1 11.3 3.3 7.1 13.9 11.7 28.5 12.4 19.8 1.1 35-6.5 35-17.5 0-4.9-5.8-10.2-14.2-13.1-8.1-2.8-22-3.6-30.4-1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M49.4 70.4c-7.8 1.8-13.5 4.7-16.8 8.6-3.5 4.3-4.1 7.2-2.1 11.3 3.3 7.1 13.9 11.7 28.5 12.4 19.8 1.1 35-6.5 35-17.5 0-4.9-5.8-10.2-14.2-13.1-8.1-2.8-22-3.6-30.4-1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M49.4 70.4c-7.8 1.8-13.5 4.7-16.8 8.6-3.5 4.3-4.1 7.2-2.1 11.3 3.3 7.1 13.9 11.7 28.5 12.4 19.8 1.1 35-6.5 35-17.5 0-4.9-5.8-10.2-14.2-13.1-8.1-2.8-22-3.6-30.4-1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>
	';
				break;
		
		
				case 35:
					$sVG ='<svg class="buildingShape g35" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120" >
	<g class="clickShape">
        <path d="M45 26.5c-.6 1.4-1 3.1-1 3.6s-.6.9-1.4.9c-.8 0-2.8 1.2-4.5 2.6-2.6 2.2-3.1 3.3-3.1 7.1 0 3.9-.5 5-4.5 9.1-4.8 4.9-5.8 8.8-2.9 11.6.8.9 1.9 2.8 2.3 4.3 1.1 3.9 1.4 14.3.4 14.4-6.5.7-6.7.8-5.4 4.1.7 1.8 3.2 3.3 10.7 6.4 5.3 2.1 9 4 8.1 4.2-2.4.5-2.2 4.2.2 4.2 1.1 0 2.2.4 2.5.9.3.5 2 1.1 3.8 1.4 1.8.3 5.5 1.1 8.2 1.7 3.8 1 5.8 1 8.9 0 2.4-.7 4.2-.8 4.6-.2 2.3 3.4 9.6 4.2 13.3 1.3 2-1.6 2.5-3 2.7-7.4.1-3.8-.3-5.7-1.4-6.7-.8-.7-1.5-2.1-1.5-3.1 0-1.8.1-1.8 2.1.2s2.3 2 3.1.5c.5-.9 1.4-1.6 2-1.6 1.1 0 .5 9-.8 12.3-.6 1.7 2.2 2.7 4.9 1.8 1.6-.6 1.6-.8.3-2.6-3-4-1.2-17.3 2.5-18.2 2.1-.6 2.7-3.3.7-3.3-.7 0-2.2-1.2-3.4-2.8-2-2.4-2.8-2.7-8.5-2.7-4.3 0-6.4-.4-6.7-1.3-.3-1 .5-1.2 3.3-.6 9.2 1.7 14.3-8 9.8-18.6-1.2-2.6-2.4-4-3.6-4-1.4 0-1.7-.6-1.4-2.8.7-4.5-3.7-8.2-10.7-8.9-4-.4-5.6-.2-5.6.6 0 .7-.5.9-1.1.6-.6-.4-1.7.2-2.5 1.4-1.8 2.5-9.4 3-11.7.6-.8-.8-1.7-1.2-2-.9-.3.4-.8-1.1-1.2-3.2-1.2-7.7-1.8-8.9-5.3-9.2-2.7-.3-3.4.1-4.2 2.3zm39.9 56.2c.1.7-.3 1.3-.9 1.3-.5 0-1-1.7-.9-3.8.1-3.3.2-3.4.9-1.2.4 1.4.8 3.1.9 3.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
    </g>
    <g class="hoverShape">
        <path d="M45 26.5c-.6 1.4-1 3.1-1 3.6s-.6.9-1.4.9c-.8 0-2.8 1.2-4.5 2.6-2.6 2.2-3.1 3.3-3.1 7.1 0 3.9-.5 5-4.5 9.1-4.8 4.9-5.8 8.8-2.9 11.6.8.9 1.9 2.8 2.3 4.3 1.1 3.9 1.4 14.3.4 14.4-6.5.7-6.7.8-5.4 4.1.7 1.8 3.2 3.3 10.7 6.4 5.3 2.1 9 4 8.1 4.2-2.4.5-2.2 4.2.2 4.2 1.1 0 2.2.4 2.5.9.3.5 2 1.1 3.8 1.4 1.8.3 5.5 1.1 8.2 1.7 3.8 1 5.8 1 8.9 0 2.4-.7 4.2-.8 4.6-.2 2.3 3.4 9.6 4.2 13.3 1.3 2-1.6 2.5-3 2.7-7.4.1-3.8-.3-5.7-1.4-6.7-.8-.7-1.5-2.1-1.5-3.1 0-1.8.1-1.8 2.1.2s2.3 2 3.1.5c.5-.9 1.4-1.6 2-1.6 1.1 0 .5 9-.8 12.3-.6 1.7 2.2 2.7 4.9 1.8 1.6-.6 1.6-.8.3-2.6-3-4-1.2-17.3 2.5-18.2 2.1-.6 2.7-3.3.7-3.3-.7 0-2.2-1.2-3.4-2.8-2-2.4-2.8-2.7-8.5-2.7-4.3 0-6.4-.4-6.7-1.3-.3-1 .5-1.2 3.3-.6 9.2 1.7 14.3-8 9.8-18.6-1.2-2.6-2.4-4-3.6-4-1.4 0-1.7-.6-1.4-2.8.7-4.5-3.7-8.2-10.7-8.9-4-.4-5.6-.2-5.6.6 0 .7-.5.9-1.1.6-.6-.4-1.7.2-2.5 1.4-1.8 2.5-9.4 3-11.7.6-.8-.8-1.7-1.2-2-.9-.3.4-.8-1.1-1.2-3.2-1.2-7.7-1.8-8.9-5.3-9.2-2.7-.3-3.4.1-4.2 2.3zm39.9 56.2c.1.7-.3 1.3-.9 1.3-.5 0-1-1.7-.9-3.8.1-3.3.2-3.4.9-1.2.4 1.4.8 3.1.9 3.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
    </g>
    <g class="clickShapeWinter">
        <path d="M45 26.5c-.6 1.4-1 3.1-1 3.6s-.6.9-1.4.9c-.8 0-2.8 1.2-4.5 2.6-2.6 2.2-3.1 3.3-3.1 7.1 0 3.9-.5 5-4.5 9.1-4.8 4.9-5.8 8.8-2.9 11.6.8.9 1.9 2.8 2.3 4.3 1.1 3.9 1.4 14.3.4 14.4-6.5.7-6.7.8-5.4 4.1.7 1.8 3.2 3.3 10.7 6.4 5.3 2.1 9 4 8.1 4.2-2.4.5-2.2 4.2.2 4.2 1.1 0 2.2.4 2.5.9.3.5 2 1.1 3.8 1.4 1.8.3 5.5 1.1 8.2 1.7 3.8 1 5.8 1 8.9 0 2.4-.7 4.2-.8 4.6-.2 2.3 3.4 9.6 4.2 13.3 1.3 2-1.6 2.5-3 2.7-7.4.1-3.8-.3-5.7-1.4-6.7-.8-.7-1.5-2.1-1.5-3.1 0-1.8.1-1.8 2.1.2s2.3 2 3.1.5c.5-.9 1.4-1.6 2-1.6 1.1 0 .5 9-.8 12.3-.6 1.7 2.2 2.7 4.9 1.8 1.6-.6 1.6-.8.3-2.6-3-4-1.2-17.3 2.5-18.2 2.1-.6 2.7-3.3.7-3.3-.7 0-2.2-1.2-3.4-2.8-2-2.4-2.8-2.7-8.5-2.7-4.3 0-6.4-.4-6.7-1.3-.3-1 .5-1.2 3.3-.6 9.2 1.7 14.3-8 9.8-18.6-1.2-2.6-2.4-4-3.6-4-1.4 0-1.7-.6-1.4-2.8.7-4.5-3.7-8.2-10.7-8.9-4-.4-5.6-.2-5.6.6 0 .7-.5.9-1.1.6-.6-.4-1.7.2-2.5 1.4-1.8 2.5-9.4 3-11.7.6-.8-.8-1.7-1.2-2-.9-.3.4-.8-1.1-1.2-3.2-1.2-7.7-1.8-8.9-5.3-9.2-2.7-.3-3.4.1-4.2 2.3zm39.9 56.2c.1.7-.3 1.3-.9 1.3-.5 0-1-1.7-.9-3.8.1-3.3.2-3.4.9-1.2.4 1.4.8 3.1.9 3.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
    </g>
    <g class="hoverShapeWinter">
        <path d="M45 26.5c-.6 1.4-1 3.1-1 3.6s-.6.9-1.4.9c-.8 0-2.8 1.2-4.5 2.6-2.6 2.2-3.1 3.3-3.1 7.1 0 3.9-.5 5-4.5 9.1-4.8 4.9-5.8 8.8-2.9 11.6.8.9 1.9 2.8 2.3 4.3 1.1 3.9 1.4 14.3.4 14.4-6.5.7-6.7.8-5.4 4.1.7 1.8 3.2 3.3 10.7 6.4 5.3 2.1 9 4 8.1 4.2-2.4.5-2.2 4.2.2 4.2 1.1 0 2.2.4 2.5.9.3.5 2 1.1 3.8 1.4 1.8.3 5.5 1.1 8.2 1.7 3.8 1 5.8 1 8.9 0 2.4-.7 4.2-.8 4.6-.2 2.3 3.4 9.6 4.2 13.3 1.3 2-1.6 2.5-3 2.7-7.4.1-3.8-.3-5.7-1.4-6.7-.8-.7-1.5-2.1-1.5-3.1 0-1.8.1-1.8 2.1.2s2.3 2 3.1.5c.5-.9 1.4-1.6 2-1.6 1.1 0 .5 9-.8 12.3-.6 1.7 2.2 2.7 4.9 1.8 1.6-.6 1.6-.8.3-2.6-3-4-1.2-17.3 2.5-18.2 2.1-.6 2.7-3.3.7-3.3-.7 0-2.2-1.2-3.4-2.8-2-2.4-2.8-2.7-8.5-2.7-4.3 0-6.4-.4-6.7-1.3-.3-1 .5-1.2 3.3-.6 9.2 1.7 14.3-8 9.8-18.6-1.2-2.6-2.4-4-3.6-4-1.4 0-1.7-.6-1.4-2.8.7-4.5-3.7-8.2-10.7-8.9-4-.4-5.6-.2-5.6.6 0 .7-.5.9-1.1.6-.6-.4-1.7.2-2.5 1.4-1.8 2.5-9.4 3-11.7.6-.8-.8-1.7-1.2-2-.9-.3.4-.8-1.1-1.2-3.2-1.2-7.7-1.8-8.9-5.3-9.2-2.7-.3-3.4.1-4.2 2.3zm39.9 56.2c.1.7-.3 1.3-.9 1.3-.5 0-1-1.7-.9-3.8.1-3.3.2-3.4.9-1.2.4 1.4.8 3.1.9 3.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
    </g>
		</svg>
	';
				break;
		
				
				case 37:
					$sVG ='
							<svg class="buildingShape g37" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120">
		<g class="clickShape">
			<path d="M20.4 42.7c.1 40.3 0 42.3-2.5 44.9-1.9 2-1.9 2.1.1 3.9 1.1 1 1.7 2.1 1.5 2.5-.8 1.4 4.1 3.8 11.1 5.7 3.8 1 7.1 2 7.4 2.4.5.5 3.9 2.1 5 2.3.3.1 1.9 1 3.5 2.2 4.7 3.2 11.1 5.4 16 5.4 3.8 0 4.8-.4 6.7-3 1.2-1.7 3-3 4-3s3.2-1.3 5-3c1.7-1.6 3.7-3 4.5-3 .7 0 3.4-1.9 5.8-4.1 4.1-3.8 4.4-4.3 3-5.9-2.3-2.6-1.9-6.5 1-8.4 2.6-1.7 3.4-5.9 1.5-7.1-.6-.4-.8-1-.6-1.3.2-.4-.6-3-2-5.7-1.3-2.8-2.4-6.3-2.4-7.8 0-2.8-3.4-8.8-5.4-9.6-.5-.2-2-1.5-3.2-2.8-2.8-3.1-5.9-2.4-6.7 1.5-.4 2-1.8 3.3-5.2 5C66 55 63.5 56 63 56c-.6 0-1-2.2-1-4.9 0-3.9.4-5.1 2.2-6.3l2.2-1.5-4.8-7c-4.1-6-4.6-7.4-3.7-9.1 1.2-2.2-.2-4.1-1.5-2.1-.5.8-.9.8-1.4-.1-1-1.6-3.1-.4-2.4 1.3.7 1.8-5.7 4.1-6.8 2.4-.6-.9-1.4-.6-2.8 1.2-1.7 2-2.1 2.1-2.6.7-.3-.9-1.2-1.6-2-1.6-1.7 0-1.7.2-.2 3.4 1 2.3.7 3.2-2.5 8.1-4.2 6.2-4.6 7.9-1.7 7 2-.6 2-.3 1.8 15.2-.3 13.8-.5 15.8-2 16.1-1.3.2-1.5-.2-1.1-1.9.5-1.9.3-2.1-2-1.7-2.2.4-2.7.2-2.7-1.6-.1-2.1-.1-2.1-1.5-.2-2.9 3.9-3.7.4-4.3-18.3L21.7 37l2.3.2c1.5.2 2.4-.3 2.5-1.5.2-1.2-.4-1.7-2.1-1.7-1.8 0-2.4-.5-2.4-2.1 0-1.7.4-2 2.4-1.5 1.4.3 2.8.2 3.1-.4.3-.5 0-1-.7-1-.9 0-.9-.3.2-1s.7-1-1.7-1c-2.6 0-3.3-.4-3.3-2 0-2.1 2-2.6 4.8-1.2 1.1.6 3.8.2 7.1-.8 5.2-1.6 5.2-3 .1-3-1 0-2.2-.5-2.5-1-1-1.7 1.5-4 4.4-4 2.4-.1 2.4-.2.7-1.2-1.3-.7-2.9-.7-5.5 0-4.7 1.3-10.2.5-9.4-1.4.3-.8.1-1.4-.4-1.4-.6 0-.9 12.5-.9 31.7zm5.7 42c-.9 1.6-1 1.4-1.1-1.2 0-2.1.3-2.6 1.1-1.8s.8 1.6 0 3zM37 83c0 1.1-.4 2-1 2-.5 0-1-.9-1-2s.5-2 1-2c.6 0 1 .9 1 2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShape">
			<path d="M20.4 42.7c.1 40.3 0 42.3-2.5 44.9-1.9 2-1.9 2.1.1 3.9 1.1 1 1.7 2.1 1.5 2.5-.8 1.4 4.1 3.8 11.1 5.7 3.8 1 7.1 2 7.4 2.4.5.5 3.9 2.1 5 2.3.3.1 1.9 1 3.5 2.2 4.7 3.2 11.1 5.4 16 5.4 3.8 0 4.8-.4 6.7-3 1.2-1.7 3-3 4-3s3.2-1.3 5-3c1.7-1.6 3.7-3 4.5-3 .7 0 3.4-1.9 5.8-4.1 4.1-3.8 4.4-4.3 3-5.9-2.3-2.6-1.9-6.5 1-8.4 2.6-1.7 3.4-5.9 1.5-7.1-.6-.4-.8-1-.6-1.3.2-.4-.6-3-2-5.7-1.3-2.8-2.4-6.3-2.4-7.8 0-2.8-3.4-8.8-5.4-9.6-.5-.2-2-1.5-3.2-2.8-2.8-3.1-5.9-2.4-6.7 1.5-.4 2-1.8 3.3-5.2 5C66 55 63.5 56 63 56c-.6 0-1-2.2-1-4.9 0-3.9.4-5.1 2.2-6.3l2.2-1.5-4.8-7c-4.1-6-4.6-7.4-3.7-9.1 1.2-2.2-.2-4.1-1.5-2.1-.5.8-.9.8-1.4-.1-1-1.6-3.1-.4-2.4 1.3.7 1.8-5.7 4.1-6.8 2.4-.6-.9-1.4-.6-2.8 1.2-1.7 2-2.1 2.1-2.6.7-.3-.9-1.2-1.6-2-1.6-1.7 0-1.7.2-.2 3.4 1 2.3.7 3.2-2.5 8.1-4.2 6.2-4.6 7.9-1.7 7 2-.6 2-.3 1.8 15.2-.3 13.8-.5 15.8-2 16.1-1.3.2-1.5-.2-1.1-1.9.5-1.9.3-2.1-2-1.7-2.2.4-2.7.2-2.7-1.6-.1-2.1-.1-2.1-1.5-.2-2.9 3.9-3.7.4-4.3-18.3L21.7 37l2.3.2c1.5.2 2.4-.3 2.5-1.5.2-1.2-.4-1.7-2.1-1.7-1.8 0-2.4-.5-2.4-2.1 0-1.7.4-2 2.4-1.5 1.4.3 2.8.2 3.1-.4.3-.5 0-1-.7-1-.9 0-.9-.3.2-1s.7-1-1.7-1c-2.6 0-3.3-.4-3.3-2 0-2.1 2-2.6 4.8-1.2 1.1.6 3.8.2 7.1-.8 5.2-1.6 5.2-3 .1-3-1 0-2.2-.5-2.5-1-1-1.7 1.5-4 4.4-4 2.4-.1 2.4-.2.7-1.2-1.3-.7-2.9-.7-5.5 0-4.7 1.3-10.2.5-9.4-1.4.3-.8.1-1.4-.4-1.4-.6 0-.9 12.5-.9 31.7zm5.7 42c-.9 1.6-1 1.4-1.1-1.2 0-2.1.3-2.6 1.1-1.8s.8 1.6 0 3zM37 83c0 1.1-.4 2-1 2-.5 0-1-.9-1-2s.5-2 1-2c.6 0 1 .9 1 2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="clickShapeWinter">
			<path d="M20.4 42.7c.1 40.3 0 42.3-2.5 44.9-1.9 2-1.9 2.1.1 3.9 1.1 1 1.7 2.1 1.5 2.5-.8 1.4 4.1 3.8 11.1 5.7 3.8 1 7.1 2 7.4 2.4.5.5 3.9 2.1 5 2.3.3.1 1.9 1 3.5 2.2 4.7 3.2 11.1 5.4 16 5.4 3.8 0 4.8-.4 6.7-3 1.2-1.7 3-3 4-3s3.2-1.3 5-3c1.7-1.6 3.7-3 4.5-3 .7 0 3.4-1.9 5.8-4.1 4.1-3.8 4.4-4.3 3-5.9-2.3-2.6-1.9-6.5 1-8.4 2.6-1.7 3.4-5.9 1.5-7.1-.6-.4-.8-1-.6-1.3.2-.4-.6-3-2-5.7-1.3-2.8-2.4-6.3-2.4-7.8 0-2.8-3.4-8.8-5.4-9.6-.5-.2-2-1.5-3.2-2.8-2.8-3.1-5.9-2.4-6.7 1.5-.4 2-1.8 3.3-5.2 5C66 55 63.5 56 63 56c-.6 0-1-2.2-1-4.9 0-3.9.4-5.1 2.2-6.3l2.2-1.5-4.8-7c-4.1-6-4.6-7.4-3.7-9.1 1.2-2.2-.2-4.1-1.5-2.1-.5.8-.9.8-1.4-.1-1-1.6-3.1-.4-2.4 1.3.7 1.8-5.7 4.1-6.8 2.4-.6-.9-1.4-.6-2.8 1.2-1.7 2-2.1 2.1-2.6.7-.3-.9-1.2-1.6-2-1.6-1.7 0-1.7.2-.2 3.4 1 2.3.7 3.2-2.5 8.1-4.2 6.2-4.6 7.9-1.7 7 2-.6 2-.3 1.8 15.2-.3 13.8-.5 15.8-2 16.1-1.3.2-1.5-.2-1.1-1.9.5-1.9.3-2.1-2-1.7-2.2.4-2.7.2-2.7-1.6-.1-2.1-.1-2.1-1.5-.2-2.9 3.9-3.7.4-4.3-18.3L21.7 37l2.3.2c1.5.2 2.4-.3 2.5-1.5.2-1.2-.4-1.7-2.1-1.7-1.8 0-2.4-.5-2.4-2.1 0-1.7.4-2 2.4-1.5 1.4.3 2.8.2 3.1-.4.3-.5 0-1-.7-1-.9 0-.9-.3.2-1s.7-1-1.7-1c-2.6 0-3.3-.4-3.3-2 0-2.1 2-2.6 4.8-1.2 1.1.6 3.8.2 7.1-.8 5.2-1.6 5.2-3 .1-3-1 0-2.2-.5-2.5-1-1-1.7 1.5-4 4.4-4 2.4-.1 2.4-.2.7-1.2-1.3-.7-2.9-.7-5.5 0-4.7 1.3-10.2.5-9.4-1.4.3-.8.1-1.4-.4-1.4-.6 0-.9 12.5-.9 31.7zm5.7 42c-.9 1.6-1 1.4-1.1-1.2 0-2.1.3-2.6 1.1-1.8s.8 1.6 0 3zM37 83c0 1.1-.4 2-1 2-.5 0-1-.9-1-2s.5-2 1-2c.6 0 1 .9 1 2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		<g class="hoverShapeWinter">
			<path d="M20.4 42.7c.1 40.3 0 42.3-2.5 44.9-1.9 2-1.9 2.1.1 3.9 1.1 1 1.7 2.1 1.5 2.5-.8 1.4 4.1 3.8 11.1 5.7 3.8 1 7.1 2 7.4 2.4.5.5 3.9 2.1 5 2.3.3.1 1.9 1 3.5 2.2 4.7 3.2 11.1 5.4 16 5.4 3.8 0 4.8-.4 6.7-3 1.2-1.7 3-3 4-3s3.2-1.3 5-3c1.7-1.6 3.7-3 4.5-3 .7 0 3.4-1.9 5.8-4.1 4.1-3.8 4.4-4.3 3-5.9-2.3-2.6-1.9-6.5 1-8.4 2.6-1.7 3.4-5.9 1.5-7.1-.6-.4-.8-1-.6-1.3.2-.4-.6-3-2-5.7-1.3-2.8-2.4-6.3-2.4-7.8 0-2.8-3.4-8.8-5.4-9.6-.5-.2-2-1.5-3.2-2.8-2.8-3.1-5.9-2.4-6.7 1.5-.4 2-1.8 3.3-5.2 5C66 55 63.5 56 63 56c-.6 0-1-2.2-1-4.9 0-3.9.4-5.1 2.2-6.3l2.2-1.5-4.8-7c-4.1-6-4.6-7.4-3.7-9.1 1.2-2.2-.2-4.1-1.5-2.1-.5.8-.9.8-1.4-.1-1-1.6-3.1-.4-2.4 1.3.7 1.8-5.7 4.1-6.8 2.4-.6-.9-1.4-.6-2.8 1.2-1.7 2-2.1 2.1-2.6.7-.3-.9-1.2-1.6-2-1.6-1.7 0-1.7.2-.2 3.4 1 2.3.7 3.2-2.5 8.1-4.2 6.2-4.6 7.9-1.7 7 2-.6 2-.3 1.8 15.2-.3 13.8-.5 15.8-2 16.1-1.3.2-1.5-.2-1.1-1.9.5-1.9.3-2.1-2-1.7-2.2.4-2.7.2-2.7-1.6-.1-2.1-.1-2.1-1.5-.2-2.9 3.9-3.7.4-4.3-18.3L21.7 37l2.3.2c1.5.2 2.4-.3 2.5-1.5.2-1.2-.4-1.7-2.1-1.7-1.8 0-2.4-.5-2.4-2.1 0-1.7.4-2 2.4-1.5 1.4.3 2.8.2 3.1-.4.3-.5 0-1-.7-1-.9 0-.9-.3.2-1s.7-1-1.7-1c-2.6 0-3.3-.4-3.3-2 0-2.1 2-2.6 4.8-1.2 1.1.6 3.8.2 7.1-.8 5.2-1.6 5.2-3 .1-3-1 0-2.2-.5-2.5-1-1-1.7 1.5-4 4.4-4 2.4-.1 2.4-.2.7-1.2-1.3-.7-2.9-.7-5.5 0-4.7 1.3-10.2.5-9.4-1.4.3-.8.1-1.4-.4-1.4-.6 0-.9 12.5-.9 31.7zm5.7 42c-.9 1.6-1 1.4-1.1-1.2 0-2.1.3-2.6 1.1-1.8s.8 1.6 0 3zM37 83c0 1.1-.4 2-1 2-.5 0-1-.9-1-2s.5-2 1-2c.6 0 1 .9 1 2z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
		</g>
		</svg>';
				break;
				
				case 41:
					$sVG ='
							<svg class="buildingShape g37" '.(DIRECTION == 'rtl' ? 'style="transform: scale(-1, 1);"' : '').' width="120" height="120" viewBox="0 0 120 120">
		<g class="clickShape">
        <path d="M49.4 70.4c-7.8 1.8-13.5 4.7-16.8 8.6-3.5 4.3-4.1 7.2-2.1 11.3 3.3 7.1 13.9 11.7 28.5 12.4 19.8 1.1 35-6.5 35-17.5 0-4.9-5.8-10.2-14.2-13.1-8.1-2.8-22-3.6-30.4-1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
    </g>
    <g class="hoverShape">
        <path d="M49.4 70.4c-7.8 1.8-13.5 4.7-16.8 8.6-3.5 4.3-4.1 7.2-2.1 11.3 3.3 7.1 13.9 11.7 28.5 12.4 19.8 1.1 35-6.5 35-17.5 0-4.9-5.8-10.2-14.2-13.1-8.1-2.8-22-3.6-30.4-1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
    </g>
    <g class="clickShapeWinter">
        <path d="M49.4 70.4c-7.8 1.8-13.5 4.7-16.8 8.6-3.5 4.3-4.1 7.2-2.1 11.3 3.3 7.1 13.9 11.7 28.5 12.4 19.8 1.1 35-6.5 35-17.5 0-4.9-5.8-10.2-14.2-13.1-8.1-2.8-22-3.6-30.4-1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
    </g>
    <g class="hoverShapeWinter">
        <path d="M49.4 70.4c-7.8 1.8-13.5 4.7-16.8 8.6-3.5 4.3-4.1 7.2-2.1 11.3 3.3 7.1 13.9 11.7 28.5 12.4 19.8 1.1 35-6.5 35-17.5 0-4.9-5.8-10.2-14.2-13.1-8.1-2.8-22-3.6-30.4-1.7z" onclick="window.location.href=\'build.php?id='.$t.'&fastUP=0\'"></path>
    </g>
		</svg>';
				break;
		
				default:
					$sVG = FALSE;
				break;
			}
			}
		return $sVG;
	}
	
	public function getTitle($i){
		global $building,$village,$lang;
		//global $village;
		
		
		if($village->resarray['f' . ($i) . 't'] == 5 
			|| $village->resarray['f' . ($i) . 't'] == 6
			|| $village->resarray['f' . ($i) . 't'] == 7
			|| $village->resarray['f' . ($i) . 't'] == 7
			|| $village->resarray['f' . ($i) . 't'] == 9
		){
			$maxLevel = 5;
		}elseif($village->resarray['f' . ($i) . 't'] == 23
			|| $village->resarray['f' . ($i) . 't'] == 35){
			$maxLevel = 10;
		}else{
			$maxLevel = 20;
		}
		//if($village->resarray['f'.$i.'t'] == 0){
			//if($i == 40){
				//return "حائط المدينة||";
			//}else{
				//return "<b>مكان</b> فارغ";
			//}
		//}else{
			if($village->resarray['f'.$i] != $maxLevel){
				$loopsame[$i] = $building->isCurrent($i) ? 1 : 0;
				$doublebuild[$i] = 0;
				if ($loopsame[$i] > 0 && $building->isLoop($i)) {
					$doublebuild[$i] = 1;
				}
				$uprequire[$i] = $building->resourceRequired($i, $village->resarray['f' . $i . 't'], ($loopsame[$i] > 0 ? 2 : 1) + $doublebuild[$i]);
				$nextLevel[$i] = $village->resarray['f' . $i] + ($loopsame[$i] > 0 ? 2 : 1);
				/*return constant('B'.$village->resarray['f'.$i.'t'])."&amp;nbsp;&lt;span class=&quot;".BD_LEVEL."&quot;&gt;".BD_LEVEL." ".$village->resarray['f'.$i]."&lt;/span&gt;||".COSTS_TONEXT." ".$nextLevel[$i].":
						&lt;br /&gt;
						&lt;div class=&quot;showCosts&quot;&gt;
						&lt;span class=&quot;resources r1&quot;&gt;
						&lt;img class=&quot;r1&quot; src=&quot;img/x.gif&quot; /&gt;".$uprequire[$i]['wood']."
						&lt;/span&gt;
						&lt;span class=&quot;resources r2&quot;&gt;
						&lt;img class=&quot;r2&quot; src=&quot;img/x.gif&quot; /&gt;".$uprequire[$i]['clay']."
						&lt;/span&gt;
						&lt;span class=&quot;resources r3&quot;&gt;
						&lt;img class=&quot;r3&quot; src=&quot;img/x.gif&quot; /&gt;".$uprequire[$i]['iron']."
						&lt;/span&gt;
						&lt;span class=&quot;resources r4&quot;&gt;
						&lt;img class=&quot;r4&quot; src=&quot;img/x.gif&quot; /&gt;".$uprequire[$i]['crop']."
						&lt;/span&gt; &lt;div class=&quot;clear&quot;&gt;&lt;/div&gt;
				";*/
				
				return htmlentities($lang['buildings'][$village->resarray['f'.$i.'t']]." <span class=\"level\">".$lang['Build']['Level']." ".$village->resarray['f'.$i]."</span>||".$lang['Build']['Costs']." ".$nextLevel[$i].":
                <br/>
	            <div class=\"inlineIconList resourceWrapper\">
                    <div class=\"inlineIcon resources\" title=''>
                        <i class=\"r1\"></i>
						<span class=\"value\">".$uprequire[$i]['wood']."</span>
                    </div>
                    <div class=\"inlineIcon resources\" title=''>
                        <i class=\"r2\"></i>
                        <span class=\"value\">".$uprequire[$i]['clay']."</span>
                    </div>
                    <div class=\"inlineIcon resources\" title=''>
                        <i class=\"r3\"></i>
                        <span class=\"value\">".$uprequire[$i]['iron']."</span>
                    </div>
	                <div class=\"inlineIcon resources\" title=''>
	                    <i class=\"r4\"></i>
	                    <span class=\"value\">".$uprequire[$i]['crop']."</span>
	                </div>
	            </div>
				");				
			}else{
				return $lang['buildings'][$village->resarray['f'.$i.'t']]."&amp;nbsp;&lt;span class=&quot;".BD_LEVEL."&quot;&gt;".BD_LEVEL." ".$village->resarray['f'.$i]."&lt;/span&gt;||&lt;span class=&quot;notice&quot;&gt;".constant('B'.$village->resarray['f'.$i.'t'])." ".MAXLEVEL.".&lt;/span&gt;";
			}
			//return "<b>".constant('B'.$village->resarray['f'.$i.'t'])."</b> ".BD_LEVEL." ".$village->resarray['f'.$i]."\"/>";
		//}
	}
	
	public function getStatus($i){
		$i = $i+18;
		global $building,$village;

		$bindicate[$i] = $building->canBuild($i, $village->resarray['f' . $i . 't']);
		if (in_array($bindicate[$i], array(1,2,3,4,5,6,7,10,11,20,21,22,88))) {
			return 'notNow';
		}else{
			return 'good';
		}
	}	
	
	public function heroStatus(){
		global $session, $database;
		// 1 -> in village , 2 -> in adventure , 3 -> returning to village , 4 -> support to a village 5 trapped 6 attacking
		$hero = $database->getHeroData($uid);
		
		foreach ($session->villages as $myvill) {
			
			if($hero['dead'] == 1){
				return array(
					'status' => 7,
					'atVillage' => $database->getHeroTrain($myvill)['vref']
				);

			}
			// In one of player villages
			$q3 = $database->query("SELECT * from " . TB_PREFIX . "units where `vref` = '" . $myvill . "' AND hero = 1");
			$d3 = mysqli_fetch_assoc($q3);
			
			if(mysqli_num_rows($q3) != 0){
				return array(
					'status' => 1,
					'atVillage' => $d3['vref']
				);
				break;
			}
			
			// In adventure
			if (!empty($database->getMovement(9, $myvill, 0))) {
				foreach($database->getMovement(9, $myvill, 0) as $Adventure) {					
					return array(
						'status' => 2,
						'atVillage' => $Adventure['to']
					);				
					break;
				}
			}
			
			// Returning from adventure
			if (!empty($database->getMovement(10, $myvill, 1))) {
				foreach($database->getMovement(10, $myvill, 1) as $Adventure) {
					return array(
						'status' => 3,
						'atVillage' => $Adventure['to'],
						'endTime' => $Adventure['endtime']
					);				
					break;
				}
			}
			
			// Returning from village
			$q9 = $database->query("SELECT * from " . TB_PREFIX . "movement where `to` = '" . $myvill . "' AND isHero = 1 AND proc = 0");
			$d9 = mysqli_fetch_assoc($q9);
			
			if(mysqli_num_rows($q9) != 0){
				return array(
					'status' => 9,
					'atVillage' => $d9['to'],
					'endTime' => $d9['endtime']
				);
				break;
			}
			
			// attacking a village
			$q10 = $database->query("SELECT * from " . TB_PREFIX . "movement where `from` = '" . $myvill . "' AND isHero = 1 AND proc = 0");
			$d10 = mysqli_fetch_assoc($q10);
			
			if(mysqli_num_rows($q10) != 0){
				return array(
					'status' => 10,
					'atVillage' => $d10['to'],
					'endTime' => $d10['endtime']
				);
				break;
			}
			
			
			// Support to a village
			$q1 = $database->query("SELECT * from " . TB_PREFIX . "enforcement where `from` = '" . $myvill . "' AND hero = 1");
			$d1 = mysqli_fetch_assoc($q1);
			
			if(mysqli_num_rows($q1) != 0){
				return array(
					'status' => 4,
					'atVillage' => $d1['vref']
				);
				break;
			}
			
			// Trapped
			$q2 = $database->query("SELECT * from " . TB_PREFIX . "trapped where `from` = '" . $myvill . "'  AND hero = 1");
			$d2 = mysqli_fetch_assoc($q2);
			if(mysqli_num_rows($q2) != 0){
				return array(
					'status' => 6,
					'atVillage' => $d2['vref']
				);
				break;
			}
			
			// Hero in revive
			if($database->getHeroTrain($myvill)){
				return array(
					'status' => 8,
					'atVillage' => $database->getHeroTrain($myvill)['vref'],
					'checkT' => $database->getHeroTrain($myvill)
				);		
				break;
			}
		}
	}
	
	public function adventureData($id){
		global $database, $session, $generator;
		
		
		$Query = $database->query("SELECT * from " . TB_PREFIX . "adventure where `wref` = '" . $id . "' AND end = 0");
		$Result = mysqli_fetch_assoc($Query);
		
		
		if(mysqli_num_rows($Query) == 0){
			header('Location: hero.php?t=3'); exit();
		}else{
			
			$heroData = $database->getHero($session->uid);
			
			$eigen = $database->getCoor($heroData['wref']);
			$coor = $database->getCoor($id);
			$from = array('x' => $eigen['x'], 'y' => $eigen['y']);
			$to = array('x' => $coor['x'], 'y' => $coor['y']);
			$speed = $heroData['speed'] + $heroData['itemspeed'];
			$time = $generator->procDistanceTime($from, $to, $speed, 1);
			
			return array(
				'time' => $generator->getTimeFormat($time),
				'back' => $generator->getTimeFormat($time*2)
			);
		}
	}
	
	public function getAllpop($type, $level){		
		global $database;
		
		$name = "bid".$type;
		global $$name;
		$dataarray = $$name;
		$pop=0; $cp=0;
		for($i=1;$i<=$level;$i++){			
			$pop = $pop + $dataarray[($i)]['pop'];
			$cp = $cp+ $dataarray[($i)]['cp'];
		}

		return array($pop,$cp);
	}

	public function array_flatten($array) { 
		if (!is_array($array)) { 
		  return FALSE; 
		} 
		$result = array(); 
		foreach ($array as $key => $value) { 
		  if (is_array($value)) { 
			$result = array_merge($result, array_flatten($value)); 
		  } 
		  else { 
			$result[$key] = $value; 
		  } 
		} 
		return $result; 
	  } 

	  public function getItemData($btype, $type){
		if($btype==1){
    
			if($type==1){
				$name = ITEM0;
				$title = IEFF0;
				$item = 1;
				$effect = 15;
			}elseif($type==2){
				$name = ITEM1;
				$title = IEFF1;
				$item = 2;
				$effect = 20;
			}elseif($type==3){
				$name = ITEM2;
				$title = IEFF2;
				$item = 3;
				$effect = 25;
			}
			if($type==4){
				$name = ITEM3;
				$title = IEFF3;
				$item = 4;
				$effect = 10;
			}elseif($type==5){
				$name = ITEM4;
				$title = IEFF4;
				$item = 5;
				$effect = 15;
			}elseif($type==6){
				$name = ITEM5;
				$title = IEFF5;
				$item = 6;
				$effect = 20;
			}
			if($type==7){
				$name = ITEM6;
				$title = IEFF6;
				$item = 7;
				$effect = 100;
			}elseif($type==8){
				$name = ITEM7;
				$title = IEFF7;
				$item = 8;
				$effect = 400;
			}elseif($type==9){
				$name = ITEM8;
				$title = IEFF8;
				$item = 9;
				$effect = 800;
			}
			if($type==10){
				$name = ITEM9;
				$title = IEFF9;
				$item = 10;
				$effect = 10;
			}elseif($type==11){
				$name = ITEM10;
				$title = IEFF10;
				$item = 11;
				$effect = 15;
			}elseif($type==12){
				$name = ITEM11;
				$title = IEFF11;
				$item = 12;
				$effect = 20;
			}
			if($type==13){
				$name = ITEM12;
				$title = IEFF12;
				$item = 13;
				$effect = 10;
			}elseif($type==14){
				$name = ITEM13;
				$title = IEFF13;
				$item = 14;
				$effect = 15;
			}elseif($type==15){
				$name = ITEM14;
				$title = IEFF14;
				$item = 15;
				$effect = 20;
			}
			
		}elseif($btype==2){
	
			if($type==82){
				$name = ITEM15;
				$title = IEFF15;
				$item = 82;
				$effect = 20;
			}elseif($type==83){
				$name = ITEM16;
				$title = IEFF16;
				$item = 83;
				$effect = 30;
			}elseif($type==84){
				$name = ITEM17;
				$title = IEFF17;
				$item = 84;
				$effect = 40;
			}
			if($type==85){
				$name = ITEM18;
				$title = IEFF18;
				$item = 85;
				$effect = 10;
			}elseif($type==86){
				$name = ITEM19;
				$title = IEFF19;
				$item = 86;
				$effect = 15;
			}elseif($type==87){
				$name = ITEM20;
				$title = IEFF20;
				$item = 87;
				$effect = 20;
			}
			if($type==88){
				$name = ITEM21;
				$title = IEFF21;
				$item = 88;
				$effect = 500;
			}elseif($type==89){
				$name = ITEM22;
				$title = IEFF22;
				$item = 89;
				$effect = 1000;
			}elseif($type==90){
				$name = ITEM23;
				$title = IEFF23;
				$item = 90;
				$effect = 1500;
			}
			if($type==91){
				$name = ITEM24;
				$title = IEFF24;
				$item = 91;
				$effect = 3;
			}elseif($type==92){
				$name = ITEM25;
				$title = IEFF25;
				$item = 92;
				$effect = 4;
			}elseif($type==93){
				$name = ITEM26;
				$title = IEFF26;
				$item = 93;
				$effect = 5;
			}
			
		}elseif($btype==3){
		
			if($type==61){
				$name = ITEM27;
				$title = IEFF27;
				$item = 61;
				$effect = 30;
			}elseif($type==62){
				$name = ITEM28;
				$title = IEFF28;
				$item = 62;
				$effect = 40;
			}elseif($type==63){
				$name = ITEM29;
				$title = IEFF29;
				$item = 63;
				$effect = 50;
			}
			if($type==64){
				$name = ITEM30;
				$title = IEFF30;
				$item = 64;
				$effect = 30;
			}elseif($type==65){
				$name = ITEM31;
				$title = IEFF31;
				$item = 65;
				$effect = 40;
			}elseif($type==66){
				$name = ITEM32;
				$title = IEFF32s;
				$item = 66;
				$effect = 50;
			}
			if($type==67){
				$name = ITEM33;
				$title = IEFF33;
				$item = 67;
				$effect = 15;
			}elseif($type==68){
				$name = ITEM34;
				$title = IEFF34;
				$item = 68;
				$effect = 20;
			}elseif($type==69){
				$name = ITEM35;
				$title = IEFF35;
				$item = 69;
				$effect = 25;
			}
			if($type==73){
				$name = ITEM36;
				$title = IEFF36;
				$item = 73;
				$effect = 10;
			}elseif($type==74){
				$name = ITEM37;
				$title = IEFF37;
				$item = 74;
				$effect = 15;
			}elseif($type==75){
				$name = ITEM38;
				$title = IEFF38;
				$item = 75;
				$effect = 20;
			}
			if($type==76){
				$name = ITEM39;
				$title = IEFF39;
				$item = 76;
				$effect = 500;
			}elseif($type==77){
				$name = ITEM40;
				$title = IEFF40;
				$item = 77;
				$effect = 1000;
			}elseif($type==78){
				$name = ITEM41;
				$title = IEFF41;
				$item = 78;
				$effect = 1500;
			}
			if($type==79){
				$name = ITEM42;
				$title = IEFF42;
				$item = 79;
				$effect = 20;
			}elseif($type==80){
				$name = ITEM43;
				$title = IEFF43;
				$item = 80;
				$effect = 25;
			}elseif($type==81){
				$name = ITEM44;
				$title = IEFF44;
				$item = 81;
				$effect = 30;
			}
			
		}elseif($btype==4){
		
			if($type==16){
				$name = ITEM45;
				$title = IEFF45;
				$item = 16;
				$effect = 500;
			}elseif($type==17){
				$name = ITEM46;
				$title = IEFF46;
				$item = 17;
				$effect = 1000;
			}elseif($type==18){
				$name = ITEM47;
				$title = IEFF47;
				$item = 18;
				$effect = 1500;
			}
			if($type==19){
				$name = ITEM48;
				$title = IEFF48;
				$item = 19;
				$effect = 500;
			}elseif($type==20){
				$name = ITEM49;
				$title = IEFF49;
				$item = 20;
				$effect = 1000;
			}elseif($type==21){
				$name = ITEM50;
				$title = IEFF50;
				$item = 21;
				$effect = 1500;
			}
			if($type==22){
				$name = ITEM51;
				$title = IEFF51;
				$item = 22;
				$effect = 500;
			}elseif($type==23){
				$name = ITEM52;
				$title = IEFF52;
				$item = 23;
				$effect = 1000;
			}elseif($type==24){
				$name = ITEM53;
				$title = IEFF53;
				$item = 24;
				$effect = 1500;
			}
			if($type==25){
				$name = ITEM54;
				$title = IEFF54;
				$item = 25;
				$effect = 500;
			}elseif($type==26){
				$name = ITEM55;
				$title = IEFF55;
				$item = 26;
				$effect = 1000;
			}elseif($type==27){
				$name = ITEM56;
				$title = IEFF56;
				$item = 27;
				$effect = 1500;
			}
			if($type==28){
				$name = ITEM57;
				$title = IEFF57;
				$item = 28;
				$effect = 500;
			}elseif($type==29){
				$name = ITEM58;
				$title = IEFF58;
				$item = 29;
				$effect = 1000;
			}elseif($type==30){
				$name = ITEM59;
				$title = IEFF59;
				$item = 30;
				$effect = 1500;
			}
			if($type==31){
				$name = ITEM60;
				$title = IEFF60;
				$item = 31;
				$effect = 500;
			}elseif($type==32){
				$name = ITEM61;
				$title = IEFF61;
				$item = 32;
				$effect = 1000;
			}elseif($type==33){
				$name = ITEM62;
				$title = IEFF62;
				$item = 33;
				$effect = 1500;
			}
			if($type==34){
				$name = ITEM63;
				$title = IEFF63;
				$item = 34;
				$effect = 500;
			}elseif($type==35){
				$name = ITEM64;
				$title = IEFF64;
				$item = 35;
				$effect = 1000;
			}elseif($type==36){
				$name = ITEM65;
				$title = IEFF65;
				$item = 36;
				$effect = 1500;
			}
			if($type==37){
				$name = ITEM66;
				$title = IEFF66;
				$item = 37;
				$effect = 500;
			}elseif($type==38){
				$name = ITEM67;
				$title = IEFF67;
				$item = 38;
				$effect = 1000;
			}elseif($type==39){
				$name = ITEM68;
				$title = IEFF68;
				$item = 39;
				$effect = 1500;
			}
			if($type==40){
				$name = ITEM69;
				$title = IEFF69;
				$item = 40;
				$effect = 500;
			}elseif($type==41){
				$name = ITEM70;
				$title = IEFF70;
				$item = 41;
				$effect = 1000;
			}elseif($type==42){
				$name = ITEM71;
				$title = IEFF71;
				$item = 42;
				$effect = 1500;
			}
			if($type==43){
				$name = ITEM72;
				$title = IEFF72;
				$item = 43;
				$effect = 500;
			}elseif($type==44){
				$name = ITEM73;
				$title = IEFF73;
				$item = 44;
				$effect = 1000;
			}elseif($type==45){
				$name = ITEM74;
				$title = IEFF74;
				$item = 45;
				$effect = 1500;
			}
			if($type==46){
				$name = ITEM75;
				$title = IEFF75;
				$item = 46;
				$effect = 500;
			}elseif($type==47){
				$name = ITEM76;
				$title = IEFF76;
				$item = 47;
				$effect = 1000;
			}elseif($type==48){
				$name = ITEM77;
				$title = IEFF77;
				$item = 48;
				$effect = 1500;
			}
			if($type==49){
				$name = ITEM78;
				$title = IEFF78;
				$item = 49;
				$effect = 500;
			}elseif($type==50){
				$name = ITEM79;
				$title = IEFF79;
				$item = 50;
				$effect = 1000;
			}elseif($type==51){
				$name = ITEM80;
				$title = IEFF80;
				$item = 51;
				$effect = 1500;
			}
			if($type==52){
				$name = ITEM81;
				$title = IEFF81;
				$item = 52;
				$effect = 500;
			}elseif($type==53){
				$name = ITEM82;
				$title = IEFF82;
				$item = 53;
				$effect = 1000;
			}elseif($type==54){
				$name = ITEM83;
				$title = IEFF83;
				$item = 54;
				$effect = 1500;
			}
			if($type==55){
				$name = ITEM84;
				$title = IEFF84;
				$item = 55;
			}elseif($type==56){
				$name = ITEM85;
				$title = IEFF85;
				$item = 56;
			}elseif($type==57){
				$name = ITEM86;
				$title = IEFF86;
				$item = 57;
			}
			if($type==58){
				$name = ITEM87;
				$title = IEFF87;
				$item = 58;
				$effect = 500;
			}elseif($type==59){
				$name = ITEM88;
				$title = IEFF88;
				$item = 59;
				$effect = 1000;
			}elseif($type==60){
				$name = ITEM89;
				$title = IEFF89;
				$item = 60;
				$effect = 1500;
			}
			

			// الفراعنة
			if($type==115){
				$name = ITEM115;
				$title = IEFF115;
				$item = 115;
				$effect = 500;
			}elseif($type==116){
				$name = ITEM116;
				$title = IEFF116;
				$item = 116;
				$effect = 1000;
			}elseif($type==117){
				$name = ITEM117;
				$title = IEFF117;
				$item = 117;
				$effect = 1500;
			}

			if($type==118){
				$name = ITEM118;
				$title = IEFF118;
				$item = 118;
				$effect = 500;
			}elseif($type==119){
				$name = ITEM119;
				$title = IEFF119;
				$item = 119;
				$effect = 1000;
			}elseif($type==120){
				$name = ITEM120;
				$title = IEFF120;
				$item = 120;
				$effect = 1500;
			}
			if($type==121){
				$name = ITEM121;
				$title = IEFF121;
				$item = 121;
				$effect = 500;
			}elseif($type==122){
				$name = ITEM122;
				$title = IEFF122;
				$item = 122;
				$effect = 1000;
			}elseif($type==123){
				$name = ITEM123;
				$title = IEFF123;
				$item = 123;
				$effect = 1500;
			}
			if($type==124){
				$name = ITEM124;
				$title = IEFF124;
				$item = 124;
				$effect = 500;
			}elseif($type==125){
				$name = ITEM125;
				$title = IEFF125;
				$item = 125;
				$effect = 1000;
			}elseif($type==126){
				$name = ITEM126;
				$title = IEFF126;
				$item = 126;
				$effect = 1500;
			}
			if($type==127){
				$name = ITEM127;
				$title = IEFF127;
				$item = 127;
				$effect = 500;
			}elseif($type==128){
				$name = ITEM128;
				$title = IEFF128;
				$item = 128;
				$effect = 1000;
			}elseif($type==129){
				$name = ITEM129;
				$title = IEFF129;
				$item = 129;
				$effect = 1500;
			}

		}elseif($btype==5){
		
			if($type==94){
				$name = ITEM90;
				$title = IEFF90;
				$item = 94;
				$effect = 10;
			}elseif($type==95){
				$name = ITEM91;
				$title = IEFF91;
				$item = 95;
				$effect = 15;
			}elseif($type==96){
				$name = ITEM92;
				$title = IEFF92;
				$item = 96;
				$effect = 20;
			}
			if($type==97){
				$name = ITEM93;
				$title = IEFF93;
				$item = 97;
				$effect = 25;
			}elseif($type==98){
				$name = ITEM94;
				$title = IEFF94;
				$item = 98;
				$effect = 30;
			}elseif($type==99){
				$name = ITEM95;
				$title = IEFF95;
				$item = 99;
				$effect = 35;
			}
			if($type==100){
				$name = ITEM96;
				$title = IEFF96;
				$item = 100;
				$effect = 3;
			}elseif($type==101){
				$name = ITEM97;
				$title = IEFF97;
				$item = 101;
				$effect = 4;
			}elseif($type==102){
				$name = ITEM98;
				$title = IEFF98;
				$item = 102;
				$effect = 5;
			}
			
		}elseif($btype==6){
			if($type==103){
				$name = ITEM99;
				$title = IEFF99;
				$item = 103;
				$effect = 14;
			}elseif($type==104){
				$name = ITEM100;
				$title = IEFF100;
				$item = 104;
				$effect = 17;
			}elseif($type==105){
				$name = ITEM101;
				$title = IEFF101;
				$item = 105;
				$effect = 20;
			}
			
		}elseif($btype==7){
			$name =  ITEM102;
			$title =  IEFF102;
			$item = 112;
		}elseif($btype==8){
			$name = ITEM103;
			$title =  IEFF103;
			$item = 113;
		}elseif($btype==9){
			$name = ITEM104;
			$title =  IEFF104;
			$item = 114;
		}elseif($btype==10){
			$name = ITEM105;
			$title = IEFF105;
			$item = 107;
		}elseif($btype==11){
			$name = ITEM106;
			$title = IEFF106;
			$item = 106;
		}elseif($btype==12){
			$name = ITEM107;
			$title = IEFF107;
			$item = 108;
		}elseif($btype==13){
			$name = ITEM108;
			$title = IEFF108;
			$item = 110;
		}elseif($btype==14){
			$name = ITEM109;
			$title = IEFF109;
			$item = 109;
		}elseif($btype==15){
			$name = ITEM110;
			$title = IEFF110;
			$item = 111;
		}	
		
		return array(
			'name' =>$name,
			'title'=>$title,
			'item' => $item
		);
	  }
	  
	  public function getAutoRenewal($type){
		global $database, $session;
		$q = $database->query("SELECT * FROM autorenewals WHERE userid = ".$session->uid."");

		foreach($q as $auto){
			return $auto[$type];
		}
	  }
}

$Travian = new newTravian;

if(isset($_GET['lang'])){
	$Travian->setLang($_GET['lang']);
}
?>