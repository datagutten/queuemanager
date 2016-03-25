<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Show queue</title>
</head>

<?Php
require 'class_queuemanager.php';
chdir(dirname(__FILE__));
require '../tools/DOMDocument_createElement_simple.php';
$dom=new DOMDocumentCustom;
$body=$dom->createElement('body');


$queue=new queuemanager;
$queue->init('cachetools');

$dom->createElement_simple('h3',$body,false,sprintf('%s jobs is currently running',$queue->running_jobs()));

$start=strtotime('today 0:00');
$end=strtotime('today 23:59');
$st=$queue->db->query_(sprintf('SELECT * FROM jobs_%s WHERE added>=%s AND added<=%s AND started IS NOT NULL',$queue->table_suffix,$start,$end),false);
$table=$dom->createElement_simple('table',$body,array('border'=>'1'));
$tr=$dom->createElement_simple('tr',$table);
foreach(array('Command','Description','Added','Started','Finished','Elapsed') as $field)
{
	$th=$dom->createElement_simple('th',$tr,false,$field);
}
while($row=$st->fetch(PDO::FETCH_ASSOC))
{
	$tr=$dom->createElement_simple('tr',$table);
	foreach(array('command','description','added','started','finished') as $field)
	{
		$td=$dom->createElement_simple('td',$tr,false,$row[$field]);
	}
	if(!empty($row['finished']))
		$dom->createElement_simple('td',$tr,false,$row['finished']-$row['started']);

}

$jobs=$queue->get_jobs();
$dom->createElement_simple('h3',$body,false,sprintf('%s jobs to be started',count($jobs)));
$table=$dom->createElement_simple('table',$body,array('border'=>'1'));
$tr=$dom->createElement_simple('tr',$table);

foreach(array('Command','Description','Added') as $field)
{
	$th=$dom->createElement_simple('th',$tr,false,$field);
}
foreach($jobs as $row)
{
	$tr=$dom->createElement_simple('tr',$table);
	foreach(array('command','description','added') as $field)
	{
		$td=$dom->createElement_simple('td',$tr,false,$row[$field]);
	}
}


echo $dom->saveXML($body);
?>

</html>