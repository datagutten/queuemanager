<?Php
require 'pdo_helper.php';
$db=new pdo_helper;
$db->connect_db_config();

if(!isset($argv[1]))
	trigger_error("argv1 is not set",E_USER_ERROR);

$st=$db->query_(sprintf('SELECT * FROM jobs_%s WHERE id=%s',$argv[2],$argv[1]),false);

$job=$st->fetch(PDO::FETCH_ASSOC);
if(!file_exists($job['folder']) && !is_dir($job['folder']))
{
	$db->query_(sprintf('UPDATE jobs_%1$s SET started=%2$s,finished=%2$s WHERE id=%3$s',$argv[2],time(),$job['id']));
	throw new Exception($job['folder'].' does not exist');
}
chdir($job['folder']);

echo shell_exec($job['command']);

$db->query_(sprintf('UPDATE jobs_%s SET finished=%s WHERE id=%s',$argv[2],time(),$job['id']));