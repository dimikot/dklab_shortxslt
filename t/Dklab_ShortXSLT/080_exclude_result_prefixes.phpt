--TEST--
Dklab_ShortXSLT: exclude-result-prefixes (with XML loading)
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
	<works>
		{/root}
		{if boolean(#M_PI)}
			<ok>ok!</ok>
		{/if}
	</works>
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
	<works>
		<xsl_:value-of xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="/root" />
		<xsl_:choose xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" xmlns:php_="http://php.net/xsl"><xsl_:when test="boolean(php_:function(&apos;constant&apos;, &apos;M_PI&apos;))">
			<ok>ok!</ok>
		</xsl_:when></xsl_:choose>
	</works>
</xsl:template>
</xsl:stylesheet>
----------------------------------------

<?xml version="1.0"?>
<works xmlns="http://www.w3.org/1999/xhtml">abc<ok>ok!</ok></works>

