--TEST--
Dklab_ShortXSLT: custom xpath in instructions
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{if ab + #a}
  yyy
{elseif ab = #a}
  zzz
{/if}
--------
{foreach ab + #abc}
  yyy
{/foreach}
EOT;
massCallPreprocess($v);
?>




--EXPECT--
******************************************************************
{if ab + #a}
  yyy
{elseif ab = #a}
  zzz
{/if}
------------------------------------------------------------------
<xsl_:choose xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" xmlns:php_="http://php.net/xsl"><xsl_:when test="ab + php_:function(&apos;constant&apos;, &apos;a&apos;)">
  yyy
</xsl_:when><xsl_:when xmlns:php_="http://php.net/xsl" test="ab = php_:function(&apos;constant&apos;, &apos;a&apos;)">
  zzz
</xsl_:when></xsl_:choose>
******************************************************************

******************************************************************
{foreach ab + #abc}
  yyy
{/foreach}
------------------------------------------------------------------
<xsl_:for-each xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" xmlns:php_="http://php.net/xsl" select="ab + php_:function(&apos;constant&apos;, &apos;abc&apos;)">
  yyy
</xsl_:for-each>
******************************************************************

