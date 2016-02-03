<?php
class Teep{
    public function __construct(){
		ob_start();
		session_start();
		require_once "./constant.php";
        $this->host        = HOST;
        $this->username    = USER;
        $this->password    = DBPASSWORD;
        $this->database    = DBNAME;
        $this->link = mysql_connect($this->host, $this->username, $this->password)
            OR die("There was a problem connecting to the database.");
        mysql_select_db($this->database, $this->link)
            OR die("There was a problem selecting the database.");
		$this->sessionCheck();
		date_default_timezone_set("Africa/Lagos");
        return true;
    }
	public function siteurl(){
		$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https';
		$host = $protocol.'://'.$_SERVER['HTTP_HOST'].'/dms';
		return $host;
	}
	public function MailSend($to,$subject,$message,$from,$cc){
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		// More headers
		if(!empty($from)){
			$headers .= 'From: '.$from. "\r\n";
		} if(!empty($cc)){
			$headers .= 'Cc: '.$cc . "\r\n";
		}
		$row = mail($to,$subject,$message,$headers);
		return $row;
	}
	public function query($sql){
		$query = mysql_query($sql);
		if($query){
			return $query;
		} else {
			return false;
		}
	}
	public function rows($sql){
		if($sql){
			$count = mysql_num_rows($sql);
			return $count;
		} else {
			return false;
		}
	}
	public function findOne($sql){
		if($sql){
			$row = mysql_fetch_assoc($sql);
			return $row;
		} else {
			return false;
		}
	}
	public function findAll($sql){
		if($sql){
			$array = array();
			while($row = mysql_fetch_assoc($sql)){
				$array[] = $row;
			}
			return $array;
		} else {
			return false;
		}
	}
	public function getCountByCol($tbl,$wherecol,$whereval,$where2){
			if($where2!=""){
				$andwhere = "AND ".$where2;
			} else {
				$andwhere = "";
			}
			$sql = $this->query("SELECT * FROM $tbl WHERE $wherecol='".$whereval."' $andwhere");
			$count = mysql_num_rows($sql);
			return $count;
	}
	public function filesGet($uid){
		if($uid){
			$array = array();
			$sql = $this->query("SELECT * FROM uploads WHERE uid='".$uid."' and type!=5 order by type");
			while($row = mysql_fetch_assoc($sql)){
				$array[] = $row;
			}
			return $array;
		} else {
			return false;
		}
	}
	public function fieldData($fId,$user){
		$q = $this->query("SELECT field_id ,value FROM `profile_fields_value` WHERE field_id='".$fId."' AND uid='".$user."'");
		return $this->findOne($q);
	}
	public function getRecords($uid){
		if($uid){
			$array = array();
			$sql = $this->query("SELECT * FROM uploads WHERE uid='".$uid."'");
			while($row = mysql_fetch_assoc($sql)){
				$array[] = $row;
			}
			return $array;
		} else {
			return false;
		}
	}
	public function getRecordsbytype($col,$table,$wherecol,$whereval,$orderby){
		if($table){
			if(!$orderby){
				$orderby = "";
			}
			$array = array();
			$sql = $this->query("SELECT $col FROM $table WHERE $wherecol='".$whereval."' $orderby");
			while($row = mysql_fetch_assoc($sql)){
				$array[] = $row;
			}
			return $array;
		} else {
			return false;
		}
	}
	public function findOnebytype($col,$table,$wherecol,$whereval){
		if($table){
			$sql = $this->query("SELECT $col FROM $table WHERE $wherecol='".$whereval."'");
			$row = mysql_fetch_assoc($sql);
			return $row;
		} else {
			return false;
		}
	}
	public function userRedirect($id){
		// if(isset($_SESSION['user']) && $_SESSION['user']['user_type']!=$id){
		if(isset($_SESSION['user']) && !in_array($_SESSION['user']['user_type'],$id)){
			header("location:".$this->siteurl()."/index.php");
			return false;
		}
	}
	public function getFiles(){
			$array = array();
			$sql = $this->query("SELECT users.teepid,users.id FROM uploads LEFT JOIN users ON users.id=uploads.uid group by uploads.uid");
			while($row = mysql_fetch_assoc($sql)){
				$array[] = $row;
			}
			return $array;
	}
	public function sessionCheck(){
		$file = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);
		if(!empty($_SESSION['user'])){
			$u = $this->rows($this->query("SELECT * FROM users WHERE id='".$_SESSION['user']['id']."'"));
			if($u==1){
				if($file=="register.php" || $file=="login.php"){
					header("location:index.php");
				}
			} else {
				header("location:logout.php");
			}
		} else {
			if(empty($_SESSION['user'])){
				if($file=="register.php" || $file=="login.php" || $file=="forget.php" || $file=="forget-password.php" || $file=="reset.php" || $file=="reset-password.php"){
				} else {
						header("location:login.php");
				}
			}
		}
	}
	public function message(){
		 if(isset($_SESSION['message']) && !empty($_SESSION['message'])){
			$msg = $_SESSION['message'];
			if($msg['error']=="e"){
				$error = ERROR;
				$class = "danger";
				$fa = "info";
				$size = "sm";
			} elseif($msg['error']=='i'){
				$error = "Info";
				$class = "info";
				$fa = "info";
				$size = "sm";
			} else {
				$error = SUCCESS;
				$class = "success";
				$fa = "check";
				$size = "sm";
			}
			$html =	'<div class="alert alert-'.$size.' alert-border-left alert-'.$class.' light alert-dismissable">
						<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
						<i class="fa fa-'.$fa.' pr10"></i>
						<strong>'.$error.'! </strong>'.$msg['msg'].'
					</div>';
			unset($_SESSION['message']);
			return $html;
		}
	}
}
?>
