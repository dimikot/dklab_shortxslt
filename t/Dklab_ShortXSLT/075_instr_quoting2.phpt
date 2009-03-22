--TEST--
Dklab_ShortXSLT: instruction double quoting
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{foreach "./ab/cd" + "{#ab}"}
  aaa
{/foreach}
EOT;
massCallPreprocess($v);
?>




--EXPECT--
******************************************************************
{foreach "./ab/cd" + "{#ab}"}
  aaa
{/foreach}
------------------------------------------------------------------
<xsl_:for-each xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="&quot;./ab/cd&quot; + &quot;{#ab}&quot;">
  aaa
</xsl_:for-each>
******************************************************************
