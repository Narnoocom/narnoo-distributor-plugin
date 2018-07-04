<?php
include "vendor/autoload.php";
//https://github.com/ahmedkhan847/twittersearchphpsdk/tree/master/Twitter
use Narnoo\Authenticate\Authenticate;

$list 	= new Authenticate();
//$list->setToken("eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczpcL1wvYXBpcy5uYXJub28uY29tXC8iLCJpYXQiOjE1Mjg4NTA2MDksImV4cCI6MTU2MDM4NjYwOSwiaWQiOiIzOSIsInR5cGUiOjF9.5pyCHk9UjEu2n0ncbjFbMSJvpAbIto-7wlny3wLcw0o");
$key 	= '14617323103239';
$secret = 'd3611a13076dad2e0327923f4ef26280af48cb96';
$lists 	= $list->getToken($key,$secret);
echo "<pre>";
print_r($lists);
echo "</pre>";
?>