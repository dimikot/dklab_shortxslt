<?php
/**
 * Dklab_ShortXSLT: simplified XSLT syntax compiler.
 * 
 * Supported instructions and XPath macros:
 *   - Operator {} outside tag attributes:
 *       <span>{some/xpath/expr and other/expr}</span>
 *   - Constant reference:
 *       <tag title="{#abc}" />
 *       <span>{#abc}</span>
 *   - Constant with arguments:
 *       <tag title="{#abc(1, 'a', #xx(0))}" />
 *       <tag title="{#@const_in_attr}" />
 *   - Constants outside attributes:
 *       <span><tag title="{#abc(1, 'a', #xx(0))}" /></span>
 *   - If-elseif-else instruction (outside a tag only):
 *       {if some &lt; "other"}
 *           first
 *       {elseif other &gt; "some"}
 *           second
 *       {else}
 *           third
 *       {/if}
 *   - Foreach instruction (outside a tag only):
 *       {for-each /some/node}
 *           {#node}: {./value}
 *       {/for-each}
 *   - Call-template instriction with parameters (outside a tag only):
 *       {call-template some-template param1="/some" param2="'value'"}
 * 
 * You may add your own instructions and XPath macros by 
 * overridding _processInstruction() + _getInstructionTagRegexps() 
 * and _processXpath() methods.
 * 
 * Also by default this library adds exclude-result-prefixes to
 * xsl:stylesheet tag enumerating all namespaces defined within
 * this tag via xmlns:XXX.
 *
 * @version 0.92
 */
class Dklab_ShortXSLT
{
    /**
     * Namespaces used by this processor.
     * 
     * @var array
     */
    private $_namespaces = array(
        'PHP' => array('php_', "http://php.net/xsl"),
        'XSL' => array('xsl_', "http://www.w3.org/1999/XSL/Transform"),
    );
    
    /**
     * Constant getter callback.
     * Format: "func" or "class::staticFunc".
     * 
     * @var string
     */
    private $_constGetter;
    
    /**
     * List of namespace identifiers (in array keys!) added to the chunk.
     * E.g. array('PHP' => true, 'XSL' => true).
     * 
     * @var array
     */
    private $_addXmlns;
    
    /**
     * Is this chunk within a tag?
     * 
     * @param bool
     */
    private $_inTag;
    
    /**
     * Nested instructions stack.
     * 
     * @var array
     */
    private $_nestStack = array();
    
    /**
     * Should we auto-generate exclude-result-prefixes in xsl:stylesheet?
     * 
     * @var bool
     */
    private $_exclPref;
    
    
    /***
     *** Public API.
     ***/
    
    /**
     * Create a new processor.
     * 
     * @param string $constGetter         Callback used to fetch constants.
     *                                    Format: "func" or "class::staticFunc".
     *                                    Only functions or static methods are supported
     *                                    to correctly implement the result caching.
     * @param bool $removeResultPrefixes  If true, all namespaces defined in xsl:stylesheet
     *                                    will be enumerated in exclude-result-prefixes.
     */
    public function __construct($constGetter = null, $removeResultPrefixes = true)
    {
        $this->_constGetter = $constGetter? $constGetter : 'constant';
        if (!is_string($this->_constGetter)) {
            throw new Exception('First argument must be in form of "funcName" or "ClassName::methodName", ' . gettype($this->_constGetter) . ' given');
        }
        if (!is_callable(false !== strpos($this->_constGetter, "::")? explode("::", $this->_constGetter, 2) : $this->_constGetter)) {
            throw new Exception('First argument must be a callback in form of "funcName" or "ClassName::methodName", "' . $this->_constGetter . '" given');
        }
        $this->_exclPref = !!$removeResultPrefixes;
    }
    
    /**
     * Process an input XSLT document.
     * 
     * @param string $content
     * @param string $path
     * @return string
     */
    public function process($content, $path = null)
    {
        // Parse the content into chunks: tags, non-tags and CDATA.
        $chunks = preg_split('/( 
                  <!\[CDATA\[ .*? \]\]>
                | <!-- .*? --> 
                | <\? .*? >
                | (?: (?i) <script\b .*? <\/script> )
                | (?: (?i) <style\b .*? <\/style> )
                | < (?: [^>"\']+ | "[^"<]*" | \'[^\'<]*\' )* > 
            )/sx', 
            $content, 
            0, 
            PREG_SPLIT_DELIM_CAPTURE
        );
        $stylesheetFound = false;
        foreach ($chunks as $i => $chunk) {
            if ($chunk && $chunk[0] == '<') {
            	if (preg_match('/^ (<[!?] | <script\b | <style\b) /six', $chunk)) {
            	    continue;
	            }
                // Note that we may modify only the tag in which xmlns:XXX
                // attribute is defined. If xmlns:XXX is defined WITHIN the 
                // body of this tag, it is NOT allowed to be enumerated in
                // exclude-result-prefixes: XSLT says "no such namespace".
                if (!$stylesheetFound && $this->_exclPref && preg_match('/^ < ([^\s:<>"\']+:)? (stylesheet|transform)\b/sx', $chunk)) {
                    $prefixes = $this->_collectPrefixesFromJoinedTags($chunk);
                    $chunk = $this->_addAttrib($chunk, 'exclude-result-prefixes', $prefixes);
                    $stylesheetFound = true;
                }
                $chunks[$i] = $this->_processChunk($chunk, true);
            } else {
                $chunks[$i] = $this->_processChunk($chunk, false);
            } 
        }
        // Join the result.
        return join("", $chunks);
    }
   
   
    /***
     *** Protected API (may be overloaded).
     ***/
   
    /**
     * Process XPath expression (within "{}" or xsl:value-of).
     * May be overridden in derived classes to implement more logic.
     * 
     * @param string $content
     * @return string
     */
    protected function _processXpath($content)
    {
        // Process const references in xpath expression.
        $content = preg_replace_callback('/#([-\w@]+)\b(\s*\()?/s', array($this, '_processXpathConstCallback'), $content);
        // All done.
        return $content;
    }
   
    /**
     * Process an outside-tag instruction: tag like "{tag options}".
     * May be overridden in derived classes to implement more logic.
     * 
     * @param string $instr Instruction nam.
     * @param string $tail  Instruction parameters.
     * @return string       Resulting XSLT or null in case of error.
     */
    protected function _processInstruction($instr, $tail)
    {         
        $this->_addXmlns['XSL'] = true;
        $xsl = $this->_namespaces['XSL'][0];
        switch ($instr) {
            case "if":
                $tail = $this->_processXpathContent($tail);
                $content = "<{$xsl}:choose{$this->_getCurrentXmlnsSpacePrefixed(!!$this->_nestStack)}><{$xsl}:when test=\"{$tail}\">";
                array_push($this->_nestStack, "if");
                break;
            case "elseif": case "elsif":
                $tail = $this->_processXpathContent($tail);
                $content = "</{$xsl}:when><{$xsl}:when{$this->_getCurrentXmlnsSpacePrefixed(true)} test=\"{$tail}\">";
                array_pop($this->_nestStack);
                array_push($this->_nestStack, "if");
                break;
            case "else":
                $content = "</{$xsl}:when><{$xsl}:otherwise>";
                array_pop($this->_nestStack);
                array_push($this->_nestStack, "else");
                break;
            case "/if":
                if (array_pop($this->_nestStack) == "else") {
                    $content = "</{$xsl}:otherwise>";
                } else {                    
                    $content = "</{$xsl}:when>";
                }
                $content .= "</{$xsl}:choose>";
                break;
            case "foreach": case "for-each":
                $tail = $this->_processXpathContent($tail);
                $content = "<{$xsl}:for-each{$this->_getCurrentXmlnsSpacePrefixed(!!$this->_nestStack)} select=\"{$tail}\">";
                array_push($this->_nestStack, "foreach");
                break;
            case "/foreach": case "/for-each":
                array_pop($this->_nestStack);
                $content = "</{$xsl}:for-each>";
                break;
            case "call-template":
                $content = $this->_processInstructionCallTemplate($tail);
                break;
            default:
                return null;
        }
        return $content;
    }   
    
    /**
     * Return the list of regexps which determines what "{xxx}"
     * need to be treated as instructions, not as xsl:value-of
     * outside a tag.
     * May be overridden in derived classes to implement more logic.
     * 
     * @return array
     */
    protected function _getInstructionTagRegexps()
    {
        return array("if", "/if", "else", "else?if", "for-?each", "/for-?each", "call-template");
    }
   
    /**
     * Escape "quote" and "apostrophe" characters.
     * 
     * @param string $content
     * @return string
     */
    protected function _attribQuoting($content)
    {
        $content = str_replace('"', '&quot;', $content);
        $content = str_replace("'", '&apos;', $content);
        return $content;
    }

   
    /***
     *** Private methods.
     ***/
   
    /**
     * INTERNAL wrapper to call constant getter with arguments.
     * The problem is that XSLT sometimes passes not plain
     * strings, but DOM nodes as PHP callback arguments, so
     * we need to convert them to strings.
     * 
     * This method MUST be public to be called from XSLT!
     * 
     * @param string $constGetter   Which callback to call.
     * @param ...
     * @return string 
     */ 
    public static function _callConstGetter()
    {
        $args = func_get_args();
        $getter = array_shift($args);
        if (false !== strpos($getter, '::')) {
            $getter = explode("::", $getter);
        }
        foreach ($args as $k => $v) {
            if (is_array($v) && $v && $v[0] instanceof DOMNode) {
                $v = $v[0]->nodeValue;
            }
            if (is_array($v) && empty($v)) {
                $v = null;
            }
            $args[$k] = (string)$v;
        }
        return call_user_func_array($getter, $args);
    }

    /**
     * Process a chunk within or outside a tag.
     * 
     * @param string $content
     * @param bool $inTag
     * @return string
     */
    private function _processChunk($content, $inTag)
    {
        $this->_inTag = $inTag;
        $this->_addXmlns = null;
        // Process {} blocks.
        // ATTENTION! We only process blocks with NON-SPACE after the first '{'.
        // This is needed for better compatibility with CSS and JS inside XSLT.
        // Note that unquoted ">" is ALLOWED by standards in attribute content!
        $content = preg_replace_callback(
            '/ \{ (?=\S)  ( (?' . '> [^<{}"\']+ | " [^"<]* " | \' [^\'<]* \' )* )  \}/sx',
            array($this, '_processChunkCallback'),
            $content
        );
        // Add xmlns if we are inside a tag.
        if ($this->_inTag && ($xmlns = $this->_getCurrentXmlnsSpacePrefixed())) {
            $content = preg_replace('{(<[^\s/]+)}s', "$1" . $xmlns, $content); 
        }
        return $content;
    }
    
    /**
     * Build xmlns clause by namespace names in $this->_addXmlns.
     * If xmlns is not empty, result is prefixed by space.
     * 
     * @param bool $noXsl  If true, no "xsl" namespace is added.
     * @return string
     */
    private function _getCurrentXmlnsSpacePrefixed($noXsl = false)
    {
        if (!$this->_addXmlns) {
            return '';
        }
        $xmlns = array();
        foreach ($this->_addXmlns as $k => $dummy) {
            if ($noXsl && $k == "XSL") continue;
            $xmlns[] = 'xmlns:' . $this->_namespaces[$k][0] . '="' . $this->_namespaces[$k][1] . '"';
        }
        return $xmlns? ' ' . join(' ', $xmlns) : ''; 
    }
    
    /**
     * Collect xmlns:xxx namespace prefixes from $content.
     * Assume that contains only tags, no data between tags.
     * 
     * @param string $content
     * @return string           Space-delimited list of prefixes.
     */
    private function _collectPrefixesFromJoinedTags($content)
    {
        // Remove quoted values, tag markers and "=".
        // Only attribute names remain.
        $content = preg_replace(
            '{(?' . '>  <\S+  |  [/>=]+  |  " [^"<]* "  |  \' [^\'<]* \'  )+}sx', 
            ' ',
            $content
        );
        // Collect xmlns:XXXX values.
        if (!preg_match_all('/\bxmlns:([^\s:=]+)/s', $content, $matches, PREG_PATTERN_ORDER)) {
            return;
        }
        return join(" ", array_unique($matches[1]));
    }
    
    /**
     * Add an attribute to a tag $tag.
     * If this attribute already exists, append its value space-delimited.
     * 
     * @param string $tag
     * @param string $name
     * @param string $value
     * @return string
     */
    private function _addAttrib($tag, $name, $value)
    {
        $parts = preg_split("/\b $name \s*=\s* (?: \"([^\"]*)\" | '([^']*)' ) /sx", $tag, 2, PREG_SPLIT_DELIM_CAPTURE);
        if (count($parts) > 1) {
            // We already have this attribute. Append its values.
            $value = trim(strlen($parts[1])? $parts[1] : $parts[2]) . " " . trim($value);
            $tag = $parts[0] . $name . '=' . '"' . $this->_attribQuoting(trim($value)) . '"' . $parts[count($parts) - 1];
        } else {
            $tag = preg_replace('{(?=\s*/?' . '>)}s', ' ' . $name . '=' . '"' . $this->_attribQuoting(trim($value)) . '"', $tag, 1);
        }
        return $tag;
    }
    
    /**
     * Process a parsed piece of template.
     * 
     * @param string $tTagOrFlag
     * @param string $tMiddle
     * @param string $tContent
     * @param string $tNonSpace
     * @param string $tRemain
     * @return string
     */
    private function _processChunkCallback($m)
    {
        $content = $m[1];
        $isInstruction = false;

        // If we are outside a tag, build xmlns from scratch, non-incremental.
        if (!$this->_inTag) {
            $this->_addXmlns = null;
        }

        // Save original content to check if something is modified later.
        $orig = $content;
          
        // Process if-else instruction and others if outside a tag.
        if (!$this->_inTag) {
            $result = $this->_processInstructionContent($content);
            if ($result !== null) {
                $content = $result;
                $isInstruction = true;
            }
        }

        // Wrap using xsl:value-of or "{}".
        if (!$isInstruction) {
            $content = $this->_processXpathContent($content);
            if ($content !== $orig) {
                // Trim spaces if something changed.
                $content = trim($content);
            }
            if (!$this->_inTag) {
                // No htmlspecialchars() here, because {} instruction implies quoting,
                // same as a tag content.
                if (!$this->_nestStack) {
                    // Add xsl namespace only if we are not within an instruction.
                    $this->_addXmlns['XSL'] = true;
                }
                $content = 
                    "<{$this->_namespaces['XSL'][0]}:value-of" 
                    . $this->_getCurrentXmlnsSpacePrefixed() 
                    . ' select="' . $content . '"' 
                    . ' />';
            } else {
                $content = '{' . $content . '}';
            }
        }
        
        return $content;
    }
    
    /**
     * Callback for _processConst().
     * 
     * @param array $m
     * @return string
     */
    private function _processXpathConstCallback($m)
    {
        $this->_addXmlns['PHP'] = true;
        $text = "{$this->_namespaces['PHP'][0]}:function("
            . (!empty($m[2])? "'" . __CLASS__ . "::_callConstGetter', " : "")
            . "'{$this->_constGetter}', "
            . (substr($m[1], 0, 1) !== '@'? "'{$m[1]}'" : $m[1]);
        if (empty($m[2])) {
            $text .= ")";
        } else {
            $text .= ", ";
        }
        return $text;
    }
    
    /**
     * Process XPath content within {}.
     * It calls _processXpath() for all parts outside quotes and 
     * apostrophes to correctly process {a + "#abc"}.
     * 
     * @param string $content
     * @return string
     */
    private function _processXpathContent($content)
    {
        $parts = preg_split(
            '/((?' . '> " [^"<]* " | \' [^\'<]* \' ))/sx',
            $content,
            0, 
            PREG_SPLIT_DELIM_CAPTURE
        );
        // Process XPath only outside quotes.
        for ($i = 0; $i < count($parts); $i += 2) {
            $parts[$i] = $this->_processXpath($parts[$i]);
        }
        $content = join("", $parts);
        return $this->_attribQuoting($content);
    }

    /**
     * Try to process the specified piece of code as instruction.
     * Return null if it is not a valid instruction.
     * 
     * @param string $content
     * @return string
     */
    private function _processInstructionContent($content)
    {   
        $m = null;
        if (!preg_match('{^\s*(' . join("|", $this->_getInstructionTagRegexps()) . ')\b\s*(.*)}s', $content, $m)) {
            return null;
        }
        list ($instr, $tail) = array($m[1], rtrim($m[2]));
        return $this->_processInstruction($instr, $tail);
    }
    
    /**
     * Process call-templat instruction.
     * 
     * @param string $tail
     * @return string
     */
    private function _processInstructionCallTemplate($tail)
    {
        // Split by template name and parameters.
        if (!preg_match('/^(\S+)\s*(.*)$/s', $tail, $m)) {
            return null;
        }
        $tplName = $m[1];
        // Extract parameters.
        preg_match_all(
            '/ ([^<>{}"\'\s]+) \s* = \s* (?' . '> " ([^"<]*) " | \' ([^\'<]*) \' )/sx',
            $m[2],
            $matches,
            PREG_SET_ORDER
        );
        // Compile parameters. This causes modification of _addXmlns if
        // parameter xpath value contains implicit namespaces.
        $params = array();
        foreach ($matches as $match) {
            $params[$match[1]] = $this->_processXpathContent(strlen($match[2])? $match[2] : $match[3]);
        }
        // Build xsl:call-template element.
        $this->_addXmlns['XSL'] = true;
        $xsl = $this->_namespaces['XSL'][0];
        $content = "<{$xsl}:call-template"
            . $this->_getCurrentXmlnsSpacePrefixed(!!$this->_nestStack)
            . " name=\"{$this->_attribQuoting($tplName)}\""
            . ($params? ">\n" : "/>");
        if ($params) {
            foreach ($params as $k => $v) {
                $content .= "<{$xsl}:with-param name=\"{$this->_attribQuoting($k)}\" select=\"{$v}\" />\n";
            }
            $content .= "</{$xsl}:call-template>";
        }
        // All done.
        return $content;
    }
}
