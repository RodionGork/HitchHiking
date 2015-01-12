<?php

function loadData() {
    $file = file_get_contents('data.json');
    if ($file === false) {
        $file = '[]';
    }
    $data = json_decode($file);
    return empty($data) ? array() : $data;
}

function saveData($data) {
    file_put_contents('data.json', json_encode($data));
}

function compareRecords($a, $b) {
    return ($a->t < $b->t) ? -1 : (($a->t > $b->t) ? 1 : 0);
}

function addRecord(&$data, $time, $name) {
    global $error;
    $time = trim($time);
    $name = trim($name);
    if (!preg_match('/^\d\d\:\d\d$/', $time)) {
        $error = 'Time format should be ##:##';
        return;
    }
    if (strlen($name) < 4) {
        $error = 'Name is too short';
        return;
    }
    $rec = new stdClass();
    $rec->name = $name;
    $rec->time = date('Y-m-d') . " $time";
    $data[] = $rec;
    saveData($data);
}

$curTime = time();

$data = loadData();

if (isset($_POST['time']) && isset($_POST['name'])) {
    $time = $_POST['time'];
    $name = $_POST['name'];
    addRecord($data, $time, $name);
}

$dataNew = array();

foreach ($data as $rec) {
    $rec->t = strtotime("{$rec->time}:00 GMT+3");
    if ($rec->t < $curTime) {
        continue;
    }
    $s = explode(' ', $rec->time);
    $rec->showTime = $s[1];
    $dataNew[] = $rec;
}

$data = $dataNew;

usort($data, "compareRecords");

$curTime = date('H:i', $curTime + 3 * 3600);

require 'template.html';
