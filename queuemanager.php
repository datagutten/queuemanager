<?Php
require 'class_queuemanager.php';
$queue=new queuemanager;
if(($init=$queue->init($argv[1]))===false)
	die("Init error\n");
$db=$queue->db;
//Read jobs from db
//Start workers
//Workers write to DB when they are done
//Check how many workers are active and start new
$running_jobs=$queue->running_jobs();

$joblimit=10;

if($running_jobs<$joblimit)
{
	$jobs=$queue->get_jobs();
	$jobs_left=count($jobs);
	echo "$jobs_left jobs to be started\n";
	foreach($jobs as $job)
	{
		while($running_jobs>=$joblimit)
		{
			$running_jobs=$queue->running_jobs();
			if($running_jobs>=$joblimit)
			{
				echo "Limit reached, $running_jobs jobs running. Waiting 2 seconds\n";
				sleep(2);
			}
		}
		if($running_jobs<$joblimit)
		{
			$queue->start_job($job);
			echo "Started job {$job['id']} ($running_jobs jobs running) ($jobs_left left)\n";
			$running_jobs++;
			$jobs_left--;
		}
	}
}
sleep(30); //Wait 30 seconds for more jobs
if(isset($argv[2]))
	echo "All jobs started\n";
else
	shell_exec("php queuemanager.php {$argv[1]} second");