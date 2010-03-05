<?php
header("Content-type: text/plain");
chdir(dirname(__FILE__));
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('track_errors', 1);

if (@is_file("../../Dklab_DOMDocument/lib/config.php")) {
	include_once "../../Dklab_DOMDocument/lib/config.php";
}
include_once "../../lib/config.php";
include_once "Dklab/ShortXSLT.php";

$sxsl = new Dklab_ShortXSLT(null);

function callPreprocess($v)
{	
	echo "******************************************************************\n";
	echo "$v\n";
	echo "------------------------------------------------------------------\n";
	try {
		echo trim($GLOBALS['sxsl']->process($v)) . "\n";
	} catch (Exception $e) {
		echo "Exception: {$e->getMessage()}\n";
	}
	echo "******************************************************************\n\n";
}

function massCallPreprocess($mass)
{
	foreach (preg_split('/---+/s', $mass) as $v) {
		callPreprocess(trim($v));
	}
}
