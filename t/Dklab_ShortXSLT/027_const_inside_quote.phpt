--TEST--
Dklab_ShortXSLT: const inside quotes
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{aaa + "#bbb"}
EOT;
massCallPreprocess($v);

?>




--EXPECT--
******************************************************************
{aaa + "#bbb"}
------------------------------------------------------------------
<xsl_:value-of xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="aaa + &quot;#bbb&quot;" />
******************************************************************