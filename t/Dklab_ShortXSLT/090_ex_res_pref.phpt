--TEST--
Dklab_ShortXSLT: exclude-result-prefixes (text only)
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
<xsl:transform version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
>
--------
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:aaa="bbb"
>
--------
<xsl:stylesheet version="1.1"
	exclude-result-prefixes="aaa"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:aaa="a"
	xmlns:bbb="b"
>
--------
<xsl:stylesheet version="1.1"
	exclude-result-prefixes="aaa"xmlns="http://www.w3.org/1999/xhtml"xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:aaa="a"
	xmlns:bbb="b"
>
--------
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"xmlns:aaa="a"xmlns:bbb="b"exclude-result-prefixes="aaa">
--------
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"xmlns:aaa="a"xmlns:bbb="b">
--------
<xsl:stylesheet exclude-result-prefixes="aaa"version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"xmlns:aaa="a"xmlns:bbb="b">
EOT;
massCallPreprocess($v);
?>




--EXPECT--
******************************************************************
<xsl:transform version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
>
------------------------------------------------------------------
<xsl:transform version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" exclude-result-prefixes="xsl"
>
******************************************************************

******************************************************************
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:aaa="bbb"
>
------------------------------------------------------------------
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:aaa="bbb" exclude-result-prefixes="xsl aaa"
>
******************************************************************

******************************************************************
<xsl:stylesheet version="1.1"
	exclude-result-prefixes="aaa"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:aaa="a"
	xmlns:bbb="b"
>
------------------------------------------------------------------
<xsl:stylesheet version="1.1"
	exclude-result-prefixes="aaa xsl aaa bbb"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:aaa="a"
	xmlns:bbb="b"
>
******************************************************************

******************************************************************
<xsl:stylesheet version="1.1"
	exclude-result-prefixes="aaa"xmlns="http://www.w3.org/1999/xhtml"xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:aaa="a"
	xmlns:bbb="b"
>
------------------------------------------------------------------
<xsl:stylesheet version="1.1"
	exclude-result-prefixes="aaa xsl aaa bbb"xmlns="http://www.w3.org/1999/xhtml"xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:aaa="a"
	xmlns:bbb="b"
>
******************************************************************

******************************************************************
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"xmlns:aaa="a"xmlns:bbb="b"exclude-result-prefixes="aaa">
------------------------------------------------------------------
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"xmlns:aaa="a"xmlns:bbb="b"exclude-result-prefixes="aaa xsl aaa bbb">
******************************************************************

******************************************************************
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"xmlns:aaa="a"xmlns:bbb="b">
------------------------------------------------------------------
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"xmlns:aaa="a"xmlns:bbb="b" exclude-result-prefixes="xsl aaa bbb">
******************************************************************

******************************************************************
<xsl:stylesheet exclude-result-prefixes="aaa"version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"xmlns:aaa="a"xmlns:bbb="b">
------------------------------------------------------------------
<xsl:stylesheet exclude-result-prefixes="aaa xsl aaa bbb"version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"xmlns:aaa="a"xmlns:bbb="b">
******************************************************************

