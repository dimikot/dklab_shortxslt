<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.1"
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
>
<xsl:template match="/">
	<html>
	<head><title>{#SITE_NAME}</title></head>
	<body>
		<div style="padding:6px; border: 2px solid black">
			{#MENU}:
			{for-each document('1_menu.xml')/menu/item}
				<a href="{@url}">{.}</a>
				<xsl:text> </xsl:text>
			{/for-each}
		</div>
		<br/>
		<xsl:call-template name="body" />
	</body>
	</html>
</xsl:template>
</xsl:stylesheet>
