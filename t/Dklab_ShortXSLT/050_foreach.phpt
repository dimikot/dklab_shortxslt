--TEST--
Dklab_ShortXSLT: foreach shortcut
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{foreach ./ab/cd #xxx}
    yyyy
{/foreach}
--------
{for-each ./ab/cd &lt; xxx}
    yyyy
{/for-each}
--------
<tag title="{foreach ./ab/cd}bbbb{/foreach}" />
EOT;
massCallPreprocess($v);
?>


--EXPECT--
******************************************************************
{foreach ./ab/cd #xxx}
    yyyy
{/foreach}
------------------------------------------------------------------
<xsl_:for-each xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" xmlns:php_="http://php.net/xsl" select="./ab/cd php_:function(&apos;constant&apos;, &apos;xxx&apos;)">
    yyyy
</xsl_:for-each>
******************************************************************

******************************************************************
{for-each ./ab/cd &lt; xxx}
    yyyy
{/for-each}
------------------------------------------------------------------
<xsl_:for-each xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="./ab/cd &lt; xxx">
    yyyy
</xsl_:for-each>
******************************************************************

******************************************************************
<tag title="{foreach ./ab/cd}bbbb{/foreach}" />
------------------------------------------------------------------
<tag title="{foreach ./ab/cd}bbbb{/foreach}" />
******************************************************************

