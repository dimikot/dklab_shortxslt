--TEST--
Dklab_ShortXSLT: call-template
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{call-template my:abcd}
--------
{call-template my:abcd x:param="'value'" param="something"}
--------
{call-template my:some-template x='concat(a, "b")'}
--------
{call-template my:some-template x="concat(a, 'b')"}
--------
{call-template my:some-template x="concat(a, &apos;b&apos;)"}
--------
{call-template my:some-template y="/a + #b + #@c"}
--------
{if /a/b}
  {call-template my:some-template x='#a + 1'}
{/if}
--------
{call-template my:some y="/a + '#b' + #c"}
--------
{call-template my:some y='/a + "#b" + #c'}
EOT;
massCallPreprocess($v);
?>




--EXPECT--
******************************************************************
{call-template my:abcd}
------------------------------------------------------------------
<xsl_:call-template xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" name="my:abcd"/>
******************************************************************

******************************************************************
{call-template my:abcd x:param="'value'" param="something"}
------------------------------------------------------------------
<xsl_:call-template xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" name="my:abcd">
<xsl_:with-param name="x:param" select="&apos;value&apos;" />
<xsl_:with-param name="param" select="something" />
</xsl_:call-template>
******************************************************************

******************************************************************
{call-template my:some-template x='concat(a, "b")'}
------------------------------------------------------------------
<xsl_:call-template xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" name="my:some-template">
<xsl_:with-param name="x" select="concat(a, &quot;b&quot;)" />
</xsl_:call-template>
******************************************************************

******************************************************************
{call-template my:some-template x="concat(a, 'b')"}
------------------------------------------------------------------
<xsl_:call-template xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" name="my:some-template">
<xsl_:with-param name="x" select="concat(a, &apos;b&apos;)" />
</xsl_:call-template>
******************************************************************

******************************************************************
{call-template my:some-template x="concat(a, &apos;b&apos;)"}
------------------------------------------------------------------
<xsl_:call-template xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" name="my:some-template">
<xsl_:with-param name="x" select="concat(a, &apos;b&apos;)" />
</xsl_:call-template>
******************************************************************

******************************************************************
{call-template my:some-template y="/a + #b + #@c"}
------------------------------------------------------------------
<xsl_:call-template xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" xmlns:php_="http://php.net/xsl" name="my:some-template">
<xsl_:with-param name="y" select="/a + php_:function(&apos;constant&apos;, &apos;b&apos;) + php_:function(&apos;constant&apos;, @c)" />
</xsl_:call-template>
******************************************************************

******************************************************************
{if /a/b}
  {call-template my:some-template x='#a + 1'}
{/if}
------------------------------------------------------------------
<xsl_:choose xmlns:xsl_="http://www.w3.org/1999/XSL/Transform"><xsl_:when test="/a/b">
  <xsl_:call-template xmlns:php_="http://php.net/xsl" name="my:some-template">
<xsl_:with-param name="x" select="php_:function(&apos;constant&apos;, &apos;a&apos;) + 1" />
</xsl_:call-template>
</xsl_:when></xsl_:choose>
******************************************************************

******************************************************************
{call-template my:some y="/a + '#b' + #c"}
------------------------------------------------------------------
<xsl_:call-template xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" xmlns:php_="http://php.net/xsl" name="my:some">
<xsl_:with-param name="y" select="/a + &apos;#b&apos; + php_:function(&apos;constant&apos;, &apos;c&apos;)" />
</xsl_:call-template>
******************************************************************

******************************************************************
{call-template my:some y='/a + "#b" + #c'}
------------------------------------------------------------------
<xsl_:call-template xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" xmlns:php_="http://php.net/xsl" name="my:some">
<xsl_:with-param name="y" select="/a + &quot;#b&quot; + php_:function(&apos;constant&apos;, &apos;c&apos;)" />
</xsl_:call-template>
******************************************************************

