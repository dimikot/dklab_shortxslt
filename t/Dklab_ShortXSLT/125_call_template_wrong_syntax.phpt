--TEST--
Dklab_ShortXSLT: call-template wrong syntax
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{call-template}
--------
{call-template tpl "abc}
--------
{call-template tpl "abc"=aaa}
--------
{call-template tpl abc="aaa" ddd xxx="yyy"}
--------
{call-template tpl abc="aaa" ddd=' xxx="yyy"}
EOT;
massCallPreprocess($v);
?>




--EXPECT--
******************************************************************
{call-template}
------------------------------------------------------------------
<xsl_:value-of xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="call-template" />
******************************************************************

******************************************************************
{call-template tpl "abc}
------------------------------------------------------------------
{call-template tpl "abc}
******************************************************************

******************************************************************
{call-template tpl "abc"=aaa}
------------------------------------------------------------------
<xsl_:call-template xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" name="tpl"/>
******************************************************************

******************************************************************
{call-template tpl abc="aaa" ddd xxx="yyy"}
------------------------------------------------------------------
<xsl_:call-template xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" name="tpl">
<xsl_:with-param name="abc" select="aaa" />
<xsl_:with-param name="xxx" select="yyy" />
</xsl_:call-template>
******************************************************************

******************************************************************
{call-template tpl abc="aaa" ddd=' xxx="yyy"}
------------------------------------------------------------------
{call-template tpl abc="aaa" ddd=' xxx="yyy"}
******************************************************************
