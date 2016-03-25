<?Php
class queuemanager
{
	public $dir; //Working dir
	public $db;
	private $st_add_job;
	private $st_update_job_started;
	private $st_update_job_finished;
	private $st_running_job_count;
	private $st_get_jobs;
	private $st_get_job;
	private $st_check_table;
	public $table_suffix;
	function __construct($dir=false)
	{
		$this->dir=$dir;
		require 'pdo_helper.php';
		$this->db=new pdo_helper;
	}
	public function init($suffix)
	{
		$this->db->connect_db_config(); //Load config using relative path
		$this->st_add_job=$this->db->prepare('INSERT INTO jobs_'.$suffix.' (command,folder,description,added) VALUES (?,?,?,?)');
		$this->st_get_not_started=$this->db->prepare(sprintf('SELECT * FROM jobs_%s WHERE started IS NULL',$suffix));
		$this->st_update_job_started=$this->db->prepare(sprintf('UPDATE jobs_%s SET started=? WHERE id=?',$suffix));
		$this->st_update_job_finished=$this->db->prepare(sprintf('UPDATE jobs_%s SET finished=? WHERE id=?',$suffix));
		$this->st_running_job_count=$this->db->prepare(sprintf('SELECT count(id) FROM jobs_%s WHERE started IS NOT NULL AND finished IS NULL',$suffix));
		$this->st_get_jobs=$this->db->prepare(sprintf('SELECT * FROM jobs_%s WHERE started IS NULL',$suffix)); //Get jobs to be started
		$this->st_get_job=$this->db->prepare(sprintf('SELECT * FROM jobs_%s WHERE id=?',$suffix));
		$this->st_check_table=$this->db->prepare('SHOW TABLES LIKE ?');
		return $this->set_suffix($suffix);
	}
	public function set_suffix($suffix)
	{
		if(empty($suffix))
			throw new Exception('Empty suffix');
		if(($check=$this->check_table($suffix))===false && ($create=$this->create_table($suffix))===false)
			return false;
		else
			$this->table_suffix=$suffix;
	}
	private function check_table($suffix)
	{
		$this->db->execute_($this->st_check_table,array('jobs_'.$suffix));
		if($this->st_check_table->rowCount()>0)
			return true;
		else
			return false;
	}
	private function create_table($suffix)
	{
		$q=sprintf('CREATE TABLE `jobs_%s` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `command` varchar(100) NOT NULL,
				  `folder` varchar(45) NOT NULL,
				  `added` int(11) DEFAULT NULL,
				  `started` int(11) DEFAULT NULL,
				  `finished` int(11) DEFAULT NULL,
				  `description` varchar(100) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;',$suffix);
		return $this->db->query_($q);
	}
	public function running_jobs()
	{
		return $this->db->execute_($this->st_running_job_count,NULL,'column');
	}
	function add_job($command,$description='')
	{
		$this->db->execute_($this->st_add_job,array($command,$this->dir,$description,time()));
	}
	function get_jobs()
	{
		return $this->db->execute_($this->st_get_jobs,NULL,'all');
	}
	function start_job($job)
	{
		$this->db->execute_($this->st_update_job_started,array(time(),$job['id'])); //Set job started time
		shell_exec("php worker.php {$job['id']} {$this->table_suffix}>/dev/null &");
	}
}