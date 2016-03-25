<?Php
class pdo_helper extends PDO
{
	public $locale_path;
	function __construct()
	{
		$foo=false;
	}
	function connect_db($db_host,$db_name,$db_user,$db_password,$persistent=false)
	{
		if($persistent!==false)
			$options=array(PDO::ATTR_PERSISTENT => true);
		else
			$options=NULL;
		$this->db = parent::__construct("mysql:host=$db_host;dbname=$db_name",$db_user,$db_password,$options);
	}
	function connect_db_config()
	{
		require 'config_db.php';
		if(!isset($persistent))
			$persistent=false;
		return $this->connect_db($db_host,$db_name,$db_user,$db_password,$persistent);
	}
	function query_($q,$fetch='all',&$timing=false)
	{
		$start=time();
		$st=$this->query($q);
		$end=time();

		$timing=$end-$start;
		if($st===false)
		{
			$errorinfo=$this->db->errorInfo();
			//trigger_error("SQL error: {$errorinfo[2]}",E_USER_WARNING);
			throw new Exception("SQL error: {$errorinfo[2]}");
			//return false;
		}
		elseif($fetch===false)
			return $st;
		elseif($fetch=='column')
			return $st->fetch(PDO::FETCH_COLUMN);
		elseif($fetch=='all')
			return $st->fetchAll(PDO::FETCH_ASSOC);
		elseif($fetch=='all_column')
			return $st->fetchAll(PDO::FETCH_COLUMN);		
	}
	function execute_($st,$parameters,$fetch=false)
	{
		if($st->execute($parameters)===false)
		{
			$errorinfo=$st->errorInfo();
			//trigger_error("SQL error: {$errorinfo[2]}",E_USER_WARNING);
			throw new Exception("SQL error: {$errorinfo[2]}");
			return false;
		}
		elseif($fetch=='column')
			return $st->fetch(PDO::FETCH_COLUMN);
		elseif($fetch=='all')
			return $st->fetchAll(PDO::FETCH_ASSOC);
	}
}