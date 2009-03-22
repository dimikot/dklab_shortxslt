--TEST--
Dklab_ShortXSLT: if-else broken nesting (another bug)
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{if /root/ad/view_header_type = 'hide_header'}
    aaa
{elseif /root/ad/view_header_type = 'all'}
    hhh
{/if}
EOT;
massCallPreprocess($v);
?>




--EXPECT--
******************************************************************
{if /root/ad/view_header_type = 'hide_header'}
    aaa
{elseif /root/ad/view_header_type = 'all'}
    hhh
{/if}
------------------------------------------------------------------
<xsl_:choose xmlns:xsl_="http://www.w3.org/1999/XSL/Transform"><xsl_:when test="/root/ad/view_header_type = &apos;hide_header&apos;">
    aaa
</xsl_:when><xsl_:when test="/root/ad/view_header_type = &apos;all&apos;">
    hhh
</xsl_:when></xsl_:choose>
******************************************************************
