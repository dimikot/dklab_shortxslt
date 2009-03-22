--TEST--
Dklab_ShortXSLT: const and trailing space after it
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{#aaa + bbb}
EOT;
massCallPreprocess($v);

?>




--EXPECT--
******************************************************************
{#aaa + bbb}
------------------------------------------------------------------
<xsl_:value-of xmlns:php_="http://php.net/xsl" xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="php_:function(&apos;constant&apos;, &apos;aaa&apos;) + bbb" />
******************************************************************
