<?php
require_once '../HighchartsGraph.php';

$h = new HighchartsGraph('USD rate');

$rows = json_decode(file_get_contents("example1.json"), true);
foreach ($rows as &$row)
{
	$h->addPoint($row['date'], ['PayIn' => floatval($row['payin']), 'PayOut' => floatval($row['payout']),]);
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8" />
	<title>Highcharts test</title>
	<link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon"/>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
</head>
<body>
<!--script src="http://code.highcharts.com/modules/exporting.js"></script-->
<div id="container" style="min-width: 400px; height: 400px; margin: 0 auto"></div>
<?= $h->render('container'); ?>
</body>
</html>