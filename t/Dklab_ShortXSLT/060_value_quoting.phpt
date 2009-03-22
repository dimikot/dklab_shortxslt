--TEST--
Dklab_ShortXSLT: value-of shortcut quoting
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{concat(aaa, "bbb", ccc)}
--------
{concat(aaa, 'bbb', ccc)}
--------
{concat(aaa, "bbb", #ccc)}
--------
{concat(aaa, 'bbb', #ccc)}
--------
<tag title="{concat(aaa, 'bbb', ccc)}" />
--------
<tag title='{concat(aaa, "bbb", ccc)}' />
--------
<tag title="{concat(aaa, 'bbb', #ccc)}" />
--------
<tag title='{concat(aaa, "bbb", #ccc)}' />
--------
<tag title='{concat(#ccc)}' />
EOT;
massCallPreprocess($v);
?>




--EXPECT--
******************************************************************
{concat(aaa, "bbb", ccc)}
------------------------------------------------------------------
<xsl_:value-of xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="concat(aaa, &quot;bbb&quot;, ccc)" />
******************************************************************

******************************************************************
{concat(aaa, 'bbb', ccc)}
------------------------------------------------------------------
<xsl_:value-of xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="concat(aaa, &apos;bbb&apos;, ccc)" />
******************************************************************

******************************************************************
{concat(aaa, "bbb", #ccc)}
------------------------------------------------------------------
<xsl_:value-of xmlns:php_="http://php.net/xsl" xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="concat(aaa, &quot;bbb&quot;, php_:function(&apos;constant&apos;, &apos;ccc&apos;))" />
******************************************************************

******************************************************************
{concat(aaa, 'bbb', #ccc)}
------------------------------------------------------------------
<xsl_:value-of xmlns:php_="http://php.net/xsl" xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="concat(aaa, &apos;bbb&apos;, php_:function(&apos;constant&apos;, &apos;ccc&apos;))" />
******************************************************************

******************************************************************
<tag title="{concat(aaa, 'bbb', ccc)}" />
------------------------------------------------------------------
<tag title="{concat(aaa, &apos;bbb&apos;, ccc)}" />
******************************************************************

******************************************************************
<tag title='{concat(aaa, "bbb", ccc)}' />
------------------------------------------------------------------
<tag title='{concat(aaa, &quot;bbb&quot;, ccc)}' />
******************************************************************

******************************************************************
<tag title="{concat(aaa, 'bbb', #ccc)}" />
------------------------------------------------------------------
<tag xmlns:php_="http://php.net/xsl" title="{concat(aaa, &apos;bbb&apos;, php_:function(&apos;constant&apos;, &apos;ccc&apos;))}" />
******************************************************************

******************************************************************
<tag title='{concat(aaa, "bbb", #ccc)}' />
------------------------------------------------------------------
<tag xmlns:php_="http://php.net/xsl" title='{concat(aaa, &quot;bbb&quot;, php_:function(&apos;constant&apos;, &apos;ccc&apos;))}' />
******************************************************************

******************************************************************
<tag title='{concat(#ccc)}' />
------------------------------------------------------------------
<tag xmlns:php_="http://php.net/xsl" title='{concat(php_:function(&apos;constant&apos;, &apos;ccc&apos;))}' />
******************************************************************

