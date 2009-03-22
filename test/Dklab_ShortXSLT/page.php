<?php
require_once dirname(__FILE__) . '/../../lib/config.php';
require_once dirname(__FILE__) . '/../../Dklab_DOMDocument/lib/config.php';
require_once "Dklab/DOMDocument.php";
require_once "Dklab/ShortXSLT.php";

// Create ShortXSLT preprocessor with constant and 
// exclude-result-prefixes support.
$preproc = new Dklab_ShortXSLT("Dictionary::get", true);

// Load XSLT template and assign the preprocessor.
$xslDoc = new Dklab_DOMDocument();
$xslDoc->addPreprocessor(array($preproc, "process"));
// Set cache directory. ATTENTION! It is not safe to set this
// directory in /tmp on shared (non-dedicated) hosting, because
// all web-server users may see and modify it. Use your own,
// secure path instead of /tmp.
$xslDoc->setCacheDir('/tmp/Dklab_ShortXSLT');
$xslDoc->load('page.xsl');

// Initialize the XSLT processor and assign the template.
$xsl = new XSLTProcessor();
$xsl->setParameter("", "debug", intval(@$_GET['debug']));
$xsl->registerPHPFunctions();
$xsl->importStyleSheet($xslDoc);

// Run the transformation.
$doc = new DOMDocument();
$doc->loadXML('<root><name>Vasily Pupkin</name></root>');
echo $xsl->transformToXML($doc);


/**
 * Dictionary: implement site localization logic.
 * Holds keys and corresponding text messages for substitutions.
 */
class Dictionary
{
	/**
	 * A dictionary is commonly load from outside. But we place
	 * it here for demo purposes only.
	 */
	private static $_dictionary = array(
		'MENU'      => "Menu",
		'MAIN'      => "Main page",
		'PAGE'      => "Test page",
		'HELLO'     => "Good morning, %s!",
		'SITE_NAME' => "Test site",
	);
	
	/**
	 * This function is an XSLT constant request callback.
	 * Each time ShortXSLT sees "#xxx" reference in the template,
	 * it replaces this reference to call to the preprocessor.
	 * It works even within templates included via xsl:include
	 * and xsl:import.
	 */
	static function get()
	{
		$args = func_get_args();
		$key = array_shift($args);
		if (!@$_GET['debug']) {
			$value = isset(self::$_dictionary[$key])? 
				self::$_dictionary[$key] : "#{$key}#";
			if ($args) {
				$value = vsprintf($value, $args);
			}
		} else {
			$value = "[ORIG:$key]";
		}
		return $value;
	}
}

echo "<br><br><hr>";
show_source(__FILE__);
