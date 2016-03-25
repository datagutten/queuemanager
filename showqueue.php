<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Show queue</title>
</head>

<body>
<?Php
require 'class_queuemanager.php';
chdir(dirname(__FILE__));
require '../tools/DOMDocument_createElement_simple.php';
$dom=new DOMDocumentCustom;



$queue=new queuemanager;
$queue->init('cachetools');
echo sprintf('<h3>%s jobs is currently running</h3>',$queue->running_jobs());
$start=strtotime('today 0:00');
$end=strtotime('today 23:59');
$st=$queue->db->query_(sprintf('SELECT * FROM jobs_%s WHERE added>=%s AND added<=%s',$queue->table_suffix,$start,$end),false);
$table=$dom->createElement_simple('table',false,array('border'=>'1'));
$tr=$dom->createElement_simple('tr',$table);
foreach(array('Command','Description','Added','Started','Finished') as $field)
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
}
echo $dom->saveXML($table);
?>
</body>
</html>