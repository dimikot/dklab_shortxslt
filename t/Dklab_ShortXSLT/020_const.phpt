--TEST--
Dklab_ShortXSLT: const and value-of shortcut
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{./ab/cd}
-----
{#name}
-----
{ #name }
-----
{#name(1, '2(', '3}')}
-----
{#@attr}
-----
{#@attr(@other, '}')}
-----
{h:func(./ab/cd, 123)}
-----
<tag title="{./ab/cd}" />
-----
<tag title="{#name}" />
-----
<tag title="{#name(1, '2(', '3}')}" />
-----
<tag title="{h:func(./ab/cd, 123)}" />
-----
<tag title="{h:func(./ab/cd, #name, 123)}" />
-----
{#name(./ab/cd, #name(1, 2), 123)}
-----
{#name('one&lt;two')}
EOT;
massCallPreprocess($v);

?>




--EXPECT--
******************************************************************
{./ab/cd}
------------------------------------------------------------------
<xsl_:value-of xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="./ab/cd" />
******************************************************************

******************************************************************
{#name}
------------------------------------------------------------------
<xsl_:value-of xmlns:php_="http://php.net/xsl" xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="php_:function(&apos;constant&apos;, &apos;name&apos;)" />
******************************************************************

******************************************************************
{ #name }
------------------------------------------------------------------
{ #name }
******************************************************************

******************************************************************
{#name(1, '2(', '3}')}
------------------------------------------------------------------
<xsl_:value-of xmlns:php_="http://php.net/xsl" xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="php_:function(&apos;Dklab_ShortXSLT::_callConstGetter&apos;, &apos;constant&apos;, &apos;name&apos;, 1, &apos;2(&apos;, &apos;3}&apos;)" />
******************************************************************

******************************************************************
{#@attr}
------------------------------------------------------------------
<xsl_:value-of xmlns:php_="http://php.net/xsl" xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="php_:function(&apos;constant&apos;, @attr)" />
******************************************************************

******************************************************************
{#@attr(@other, '}')}
------------------------------------------------------------------
<xsl_:value-of xmlns:php_="http://php.net/xsl" xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="php_:function(&apos;Dklab_ShortXSLT::_callConstGetter&apos;, &apos;constant&apos;, @attr, @other, &apos;}&apos;)" />
******************************************************************

******************************************************************
{h:func(./ab/cd, 123)}
------------------------------------------------------------------
<xsl_:value-of xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="h:func(./ab/cd, 123)" />
******************************************************************

******************************************************************
<tag title="{./ab/cd}" />
------------------------------------------------------------------
<tag title="{./ab/cd}" />
******************************************************************

******************************************************************
<tag title="{#name}" />
------------------------------------------------------------------
<tag xmlns:php_="http://php.net/xsl" title="{php_:function(&apos;constant&apos;, &apos;name&apos;)}" />
******************************************************************

******************************************************************
<tag title="{#name(1, '2(', '3}')}" />
------------------------------------------------------------------
<tag xmlns:php_="http://php.net/xsl" title="{php_:function(&apos;Dklab_ShortXSLT::_callConstGetter&apos;, &apos;constant&apos;, &apos;name&apos;, 1, &apos;2(&apos;, &apos;3}&apos;)}" />
******************************************************************

******************************************************************
<tag title="{h:func(./ab/cd, 123)}" />
------------------------------------------------------------------
<tag title="{h:func(./ab/cd, 123)}" />
******************************************************************

******************************************************************
<tag title="{h:func(./ab/cd, #name, 123)}" />
------------------------------------------------------------------
<tag xmlns:php_="http://php.net/xsl" title="{h:func(./ab/cd, php_:function(&apos;constant&apos;, &apos;name&apos;), 123)}" />
******************************************************************

******************************************************************
{#name(./ab/cd, #name(1, 2), 123)}
------------------------------------------------------------------
<xsl_:value-of xmlns:php_="http://php.net/xsl" xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="php_:function(&apos;Dklab_ShortXSLT::_callConstGetter&apos;, &apos;constant&apos;, &apos;name&apos;, ./ab/cd, php_:function(&apos;Dklab_ShortXSLT::_callConstGetter&apos;, &apos;constant&apos;, &apos;name&apos;, 1, 2), 123)" />
******************************************************************

******************************************************************
{#name('one&lt;two')}
------------------------------------------------------------------
<xsl_:value-of xmlns:php_="http://php.net/xsl" xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="php_:function(&apos;Dklab_ShortXSLT::_callConstGetter&apos;, &apos;constant&apos;, &apos;name&apos;, &apos;one&lt;two&apos;)" />
******************************************************************
