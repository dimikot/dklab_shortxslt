--TEST--
Dklab_ShortXSLT: if-else broken nesting
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
yyyy
{/if}
--------
{if ./ab/cd}
    {#yyyy}
{else}
    zzzz
{else}
    zzzz
{/if}
EOT;
massCallPreprocess($v);
?>




--EXPECT--
******************************************************************
yyyy
{/if}
------------------------------------------------------------------
yyyy
</xsl_:when></xsl_:choose>
******************************************************************

******************************************************************
{if ./ab/cd}
    {#yyyy}
{else}
    zzzz
{else}
    zzzz
{/if}
------------------------------------------------------------------
<xsl_:choose xmlns:xsl_="http://www.w3.org/1999/XSL/Transform"><xsl_:when test="./ab/cd">
    <xsl_:value-of xmlns:php_="http://php.net/xsl" select="php_:function(&apos;constant&apos;, &apos;yyyy&apos;)" />
</xsl_:when><xsl_:otherwise>
    zzzz
</xsl_:when><xsl_:otherwise>
    zzzz
</xsl_:otherwise></xsl_:choose>
******************************************************************

