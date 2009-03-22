--TEST--
Dklab_ShortXSLT: const callback must be scalar only
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

try {
	$sxsl = new Dklab_ShortXSLT(array('Class', 'Wrong'));
} catch (Exception $e) {
	echo "Exception: {$e->getMessage()}\n";
}

try {
	$sxsl = new Dklab_ShortXSLT('non-existed');
} catch (Exception $e) {
	echo "Exception: {$e->getMessage()}\n";
}

?>




--EXPECT--
Exception: First argument must be in form of "funcName" or "ClassName::methodName", array given
Exception: First argument must be a callback in form of "funcName" or "ClassName::methodName", "non-existed" given
