--TEST--
Dklab_ShortXSLT: concat values n XSLT attributes
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$xslText = <<<EOT
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl"
	xmlns:aaa="aaaaaa"
	exclude-result-prefixes="php"
>
<xsl:template match="root">
	<works title="some {#M_PI}" />
</xsl:template>
</xsl:stylesheet>
EOT;

$sxsl = new Dklab_ShortXSLT(null, true);
$xslText = $sxsl->process($xslText);
echo $xslText;
echo "\n----------------------------------------\n\n";

$xslDoc = new DOMDocument();
$xslDoc->loadXML($xslText);

$xsl = new XSLTProcessor();
$xsl->registerPHPFunctions();
$xsl->importStyleSheet($xslDoc);

$doc = new DOMDocument();
$doc->loadXML('<root>abc</root>');
echo $xsl->transformToXML($doc);
?>




--EXPECT--
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl"
	xmlns:aaa="aaaaaa"
	exclude-result-prefixes="php xsl php aaa"
>
<xsl:template match="root">
	<works xmlns:php_="http://php.net/xsl" title="some {php_:function(&apos;constant&apos;, &apos;M_PI&apos;)}" />
</xsl:template>
</xsl:stylesheet>
----------------------------------------

<?xml version="1.0"?>
<works xmlns="http://www.w3.org/1999/xhtml" title="some 3.14159265359"/>
