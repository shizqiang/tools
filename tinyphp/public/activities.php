<?php
use models\Activity;
use libs\DB;

require '../autoload.php';


$activity = new Activity();
$activity->name = 'test123';
$activity->store();
$row = Activity::find(['id =' => 100101]);
$activity = new Activity($row);
$activity->adcodes = json_encode(['100002']);
$r = $activity->update();
var_dump($r);
// var_dump($activity);
print DB::Redis()->get('name');

// $row = Activity::find(['id = ' => 100100]);

// $activity = new Activity($row);
// $activity->delete();