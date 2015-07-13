<?php

$taskLocation = realpath(dirname(__FILE__));
chdir($taskLocation);
include("../config.php");


$file = file_get_contents("templates/metrogram-template.xml");
date_default_timezone_set(TZ);
if (date('I')) {
    $file = str_replace("[--UTCOFFSET--]", 60, $file);
} else {
    $file = str_replace("[--UTCOFFSET--]", 0, $file);
}


$file = str_replace("[--sunrise--]", Date("Y-m-d") . "T" . date_sunrise(time(), SUNFUNCS_RET_STRING, LAT, LONG, 90 + (60 / 60), 0) . ":00", $file);
$file = str_replace("[--sunset--]", Date("Y-m-d") . "T" . date_sunset(time(), SUNFUNCS_RET_STRING, LAT, LONG, 90, 0) . ":00", $file);
$file = str_replace("[--LAT--]", LAT, $file);
$file = str_replace("[--LONG--]", LONG, $file);
$file = str_replace("[--ALTITUDE--]", ALTITUDE, $file);

$link = mysql_connect(SERVER, USER, PASSWORD);
if (!$link) {
    die('Could not connect: ' . mysql_error());
}

$db_found = mysql_select_db(DATABASE);

$sql = "SELECT * FROM Weather.meteogram
WHERE `from` >= now() - INTERVAL 48 HOUR GROUP BY hour(`from`) ORDER BY `from` DESC;";

$query = mysql_query($sql);
$tabularContent = "";
$rowtemplate = file_get_contents("templates/metrogram-rowdata-template.xml");
while ($r = mysql_fetch_array($query)) {
    $rowdata = $rowtemplate;
    $rowdata = str_replace("[--precipitation--]", $r['precipitation'], $rowdata);
    $rowdata = str_replace("[--deg--]", $r['deg'], $rowdata);
    $rowdata = str_replace("[--code--]", $r['code'], $rowdata);
    $rowdata = str_replace("[--name--]", $r['name'], $rowdata);
    $rowdata = str_replace("[--windSpeed--]", $r['windSpeed'], $rowdata);
    $rowdata = str_replace("[--temperature--]", $r['temperature'], $rowdata);
    $rowdata = str_replace("[--pressure--]", $r['pressure'], $rowdata);
    $tabularContent .= $rowdata;
    
}

$file = str_replace("[--tabularContent--]", $tabularContent, $file);
file_put_contents("data/cache-metrogram.xml", $file);
