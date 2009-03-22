<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
>
<xsl:import href="1_layout.xsl" />
<xsl:template name="body">
	{#HELLO(/root/name)}
	<h2>
	{if $debug != 0}
		<a href="page.php">Replace keys by TEXTS</a>
	{else}
		<a href="page.php?debug=1">Debug mode: show KEYS</a>
	{/if}
	</h2>
</xsl:template>
</xsl:stylesheet>
