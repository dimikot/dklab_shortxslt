--TEST--
Dklab_ShortXSLT: constants with args
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$xslText = <<<EOT
<xsl:stylesheet version="1.1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="root">
	<works>{#some(/root/abc, /root/def)}</works>
    <works>{#@attr(@other, /root/def)}</works>
</xsl:template>
</xsl:stylesheet>
EOT;

$sxsl = new Dklab_ShortXSLT('Dic::get');
$xslText = $sxsl->process($xslText);

$xslDoc = new DOMDocument();
$xslDoc->loadXML($xslText);

$xsl = new XSLTProcessor();
$xsl->registerPHPFunctions();
$xsl->importStyleSheet($xslDoc);

$doc = new DOMDocument();
$doc->loadXML('<root attr="xxx" other="yyy"><abc>v1</abc><def>v2</def></root>');
echo $xsl->transformToXML($doc);

class Dic
{
	public function get()
	{
		$args = func_get_args();
		return var_export($args, 1);
	}
}
?>




--EXPECT--
<?xml version="1.0"?>
<works>array (
  0 =&gt; 'some',
  1 =&gt; 'v1',
  2 =&gt; 'v2',
)</works><works>array (
  0 =&gt; 'xxx',
  1 =&gt; 'yyy',
  2 =&gt; 'v2',
)</works>

