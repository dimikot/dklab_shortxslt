--TEST--
Dklab_ShortXSLT: if-else shortcut
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{if ./ab/cd}
    yyyy
{/if}
--------
{  if ./ab/cd  }
    yyyy
{  /if  }
--------
{if ./ab/cd}
    {#yyyy}
{else}
    zzzz
{/if}
--------
{if ./ab/cd}
    yyyy
{elseif ss/dd/mm}
    xxxx
{else}
    {#zzzz}
{/if}
--------
{if ./ab/cd}
    yyyy
{elseif ss/dd/mm}
	xxxx
{else}
    zzzz
    {if zzzz}
    	bbbb
    {/if}
    aaaa
{/if}
--------
{if ./ab/cd = #name(1, 2)}
    yyyy
{elseif ss/dd/mm = #key}
    xxxx
{else}
    zzzz
{/if}
--------
<tag title="{if ./ab/cd}aaaa{else}bbbb{/if}" />
--------
{if ./ab/cd = '&lt;'}aaaa{else}bbbb{/if}

EOT;
massCallPreprocess($v);
?>




--EXPECT--
******************************************************************
{if ./ab/cd}
    yyyy
{/if}
------------------------------------------------------------------
<xsl_:choose xmlns:xsl_="http://www.w3.org/1999/XSL/Transform"><xsl_:when test="./ab/cd">
    yyyy
</xsl_:when></xsl_:choose>
******************************************************************

******************************************************************
{  if ./ab/cd  }
    yyyy
{  /if  }
------------------------------------------------------------------
{  if ./ab/cd  }
    yyyy
{  /if  }
******************************************************************

******************************************************************
{if ./ab/cd}
    {#yyyy}
{else}
    zzzz
{/if}
------------------------------------------------------------------
<xsl_:choose xmlns:xsl_="http://www.w3.org/1999/XSL/Transform"><xsl_:when test="./ab/cd">
    <xsl_:value-of xmlns:php_="http://php.net/xsl" select="php_:function(&apos;constant&apos;, &apos;yyyy&apos;)" />
</xsl_:when><xsl_:otherwise>
    zzzz
</xsl_:otherwise></xsl_:choose>
******************************************************************

******************************************************************
{if ./ab/cd}
    yyyy
{elseif ss/dd/mm}
    xxxx
{else}
    {#zzzz}
{/if}
------------------------------------------------------------------
<xsl_:choose xmlns:xsl_="http://www.w3.org/1999/XSL/Transform"><xsl_:when test="./ab/cd">
    yyyy
</xsl_:when><xsl_:when test="ss/dd/mm">
    xxxx
</xsl_:when><xsl_:otherwise>
    <xsl_:value-of xmlns:php_="http://php.net/xsl" select="php_:function(&apos;constant&apos;, &apos;zzzz&apos;)" />
</xsl_:otherwise></xsl_:choose>
******************************************************************

******************************************************************
{if ./ab/cd}
    yyyy
{elseif ss/dd/mm}
	xxxx
{else}
    zzzz
    {if zzzz}
    	bbbb
    {/if}
    aaaa
{/if}
------------------------------------------------------------------
<xsl_:choose xmlns:xsl_="http://www.w3.org/1999/XSL/Transform"><xsl_:when test="./ab/cd">
    yyyy
</xsl_:when><xsl_:when test="ss/dd/mm">
	xxxx
</xsl_:when><xsl_:otherwise>
    zzzz
    <xsl_:choose><xsl_:when test="zzzz">
    	bbbb
    </xsl_:when></xsl_:choose>
    aaaa
</xsl_:otherwise></xsl_:choose>
******************************************************************

******************************************************************
{if ./ab/cd = #name(1, 2)}
    yyyy
{elseif ss/dd/mm = #key}
    xxxx
{else}
    zzzz
{/if}
------------------------------------------------------------------
<xsl_:choose xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" xmlns:php_="http://php.net/xsl"><xsl_:when test="./ab/cd = php_:function(&apos;Dklab_ShortXSLT::_callConstGetter&apos;, &apos;constant&apos;, &apos;name&apos;, 1, 2)">
    yyyy
</xsl_:when><xsl_:when xmlns:php_="http://php.net/xsl" test="ss/dd/mm = php_:function(&apos;constant&apos;, &apos;key&apos;)">
    xxxx
</xsl_:when><xsl_:otherwise>
    zzzz
</xsl_:otherwise></xsl_:choose>
******************************************************************

******************************************************************
<tag title="{if ./ab/cd}aaaa{else}bbbb{/if}" />
------------------------------------------------------------------
<tag title="{if ./ab/cd}aaaa{else}bbbb{/if}" />
******************************************************************

******************************************************************
{if ./ab/cd = '&lt;'}aaaa{else}bbbb{/if}
------------------------------------------------------------------
<xsl_:choose xmlns:xsl_="http://www.w3.org/1999/XSL/Transform"><xsl_:when test="./ab/cd = &apos;&lt;&apos;">aaaa</xsl_:when><xsl_:otherwise>bbbb</xsl_:otherwise></xsl_:choose>
******************************************************************

