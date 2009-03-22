--TEST--
Dklab_ShortXSLT: custom xpath in instructions
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{if ./ab/cd + #abc}
    yyyy
{/if}
EOT;
massCallPreprocess($v);
?>




--EXPECT--
******************************************************************
{if ./ab/cd + #abc}
    yyyy
{/if}
------------------------------------------------------------------
<xsl_:choose xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" xmlns:php_="http://php.net/xsl"><xsl_:when test="./ab/cd + php_:function(&apos;constant&apos;, &apos;abc&apos;)">
    yyyy
</xsl_:when></xsl_:choose>
******************************************************************

