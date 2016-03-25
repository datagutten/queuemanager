<?Php
require 'pdo_helper.php';
$db=new pdo_helper;
$db->connect_db_config();

if(!isset($argv[1]))
	trigger_error("argv1 is not set",E_USER_ERROR);

$st=$db->query_(sprintf('SELECT * FROM jobs_%s WHERE id=%s',$argv[2],$argv[1]),false);

$job=$st->fetch(PDO::FETCH_ASSOC);

chdir($job['folder']);
//$db->query_(sprintf('UPDATE jobs_%s SET started=%s WHERE id=%s',$argv[2],time(),$job['id']));
echo shell_exec($job['command']);

$db->query_(sprintf('UPDATE jobs_%s SET finished=%s WHERE id=%s',$argv[2],time(),$job['id']));