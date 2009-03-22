--TEST--
Dklab_ShortXSLT: value-of shortcut
--FILE--
<?php
require dirname(__FILE__) . '/init.php';

$v = <<<EOT
{./ab/cd + "{xy}" = zz}
EOT;
massCallPreprocess($v);

?>




--EXPECT--
******************************************************************
{./ab/cd + "{xy}" = zz}
------------------------------------------------------------------
<xsl_:value-of xmlns:xsl_="http://www.w3.org/1999/XSL/Transform" select="./ab/cd + &quot;{xy}&quot; = zz" />
******************************************************************
