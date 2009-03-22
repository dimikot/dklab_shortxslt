<?php

/**
 * Extended DOMDocument with automatic XML/XSLT preprocessing support
 * on document load or XSLT execution.
 * 
 * Allows you to assign a number of callback functions which are
 * be called before XML document is loaded (including xinclude
 * and external entities) or XSLT stylesheed is executed (including 
 * xsl:import and xsl:include). 
 * 
 * You may even use document() function in your XSLT stylesheets
 * and be sure that a content loaded by document() will be preprocessed
 * too. This is a true magic, yes.
 * 
 * The solution is PHP-native and does not use libxslt's 
 * xsltDocLoaderFunc hook. The magic is doubled.
 * 
 * @version 0.51
 */
class Dklab_DOMDocument extends DOMDocument
{
    /***
     *** Public API.
     ***/
     
    /**
     * Set this document URI. 
     * 
     * Unfortunately you cannot assign to $this->documentURI directly,
     * because this call cannot be intetcepted. Instead, use this method.
     * 
     * @param string $uri
     * @return void
     */
    public function setDocumentURI($uri)
    {
        $this->documentURI = $this->_assemble($uri);
    }
    
    /**
     * Return real document URI previously assigned.
     * 
     * @return string.
     */
    public function getDocumentURI()
    {
        list (, $uri) = Dklab_DOMDocument_Proto::match($this->documentURI);
        return $uri;
    }
    
    /**
     * Add a preprocessot to the processing queue.
     * 
     * @param callback $preprocessor  function(string $content, string $path)
     * @return void
     */
    public function addPreprocessor($preprocessor)
    {
        $this->_proto->addPreprocessor($preprocessor);
    }
    
    /**
     * Set a cache directory to store preprocessed documents to.
     * 
     * @param string $dir
     * @return void
     */
    public function setCacheDir($dir)
    {
        $this->_proto->setCacheDir($dir);
    }

    /***
     *** Inherited or private members.
     ***/

    /**
     * This document protocol prefix object.
     * 
     * @var string
     */
    private $_proto = null;
    
    /**
     * Assemble the wrapped URI.
     * 
     * @param string $uri
     * @return string
     */
    private function _assemble($uri)
    {
        $wrapped = $this->_proto->assemble($uri);
        //echo "$uri -> $wrapped\n";
        return $wrapped;
    }
    
    /**
     * Return PHP's current URI if no URI is known while loading
     * (e.g. by loadXML() call).
     * 
     * @return string
     */
    private function _getUnknownCurrentUri()
    {
        $uri = getcwd() . '/';  // PHP's behaviour
    }
    
    /**
     * Wrap document loading.
     * 
     * @see DOMDocument::load
     */
    public function load($filename, $options = 0)
    {
        $path = str_replace('\\', '/', @realpath($filename));
        if (!strlen($path)) {
            $path = $filename;
        }
        return parent::load($this->_assemble($path), $options);
    }

    
    /**
     * Wrap HTML document loading.
     * 
     * @see DOMDocument::loadHTMLFile
     */
    public function loadHTMLFile($filename)
    {
        $path = str_replace('\\', '/', @realpath($filename));
        if (!strlen($path)) {
            $path = $filename;
        }
        return parent::loadHTMLFile($this->_assemble($path));
    }
        
    /**
     * Wrap XML string loading.
     * 
     * @see DOMDocument::loadXML
     */
    public function loadXML($content, $options = 0)
    {
        $content = $this->_proto->runPreprocessors($content, $this->_getUnknownCurrentUri());
        $result = parent::loadXML($content, $options);
        $this->setDocumentURI($this->documentURI); // wrap after loading
        return $result;
    }
    
    /**
     * Wrap HTML string loading.
     * 
     * @see DOMDocument::loadHTML
     */
    public function loadHTML($content)
    {
        $content = $this->_proto->runPreprocessors($content, $this->_getUnknownCurrentUri());
        $result = parent::loadHTML($content);
        $this->setDocumentURI($this->documentURI); // wrap after loading
        return $result;
    }    

    /**
     * Create a new DOMDocument.
     * 
     * @see DOMDocument::__construct
     */
    public function __construct($version = null, $encoding = null)
    {
        parent::__construct($version, $encoding);
        $this->_proto = new Dklab_DOMDocument_Proto();
    }
    
    /**
     * Destroy the DOMDocument.
     * 
     * @return void
     */
    public function __destruct()
    {
        // It is not enough to delete the object and call the destructor,
        // because object is stored in static Dklab_DOMDocument_StreamWrapper 
        // storage too. 
        $this->_proto->unregister();
    }
}

/**
 * Stream protocol with preprocessing and caching support.
 * Each DOMDocument has its own protocol object.
 */
class Dklab_DOMDocument_Proto
{
    private $_name;
    private $_callbacks = array();
    private $_cacheDir = null;
    
    public function __construct()
    {
        static $wrapperNum = 0;
        $this->_name = "dkdd" . ($wrapperNum++);
        stream_wrapper_register($this->_name, 'Dklab_DOMDocument_StreamWrapper');
        // To avoid cyclic refs, we perform register/unregister stage in Document.
        Dklab_DOMDocument_StreamWrapper::protoRegister($this);
    }
    
    public function unregister()
    {
        // To avoid cyclic refs, we perform register/unregister stage in Document.
        Dklab_DOMDocument_StreamWrapper::protoUnregister($this);
    } 

    public function __destruct()
    {
        stream_wrapper_unregister($this->_name);
    }
    
    public function getName()
    {
        return $this->_name;
    }
    
    public function addPreprocessor($callback)
    {
        $this->_callbacks[] = $callback;
    }
    
    public function setCacheDir($dir)
    {
        $this->_cacheDir = $dir;
    }
    
    public function loadCache($path, $stamp)
    {
        if ($this->_cacheDir === null || !$stamp) {
            return null;
        }
        $cacheFilename = $this->_getCacheFilename($path);
        // Read from cache with 1 second gap (for safety).
        if (abs(@filemtime($cacheFilename) - $stamp) <= 1) {
            // We cannot use file_get_contents(), because it does not
            // support LOCK_SH in PHP 5.x.
            $f = @fopen($cacheFilename, "rb");
            if (!$f) {
                return null;
            }
            flock($f, LOCK_SH);
            return fread($f, filesize($cacheFilename));
        }
        return null;
    }
    
    public function saveCache($path, $stamp, $content)
    {
        if ($this->_cacheDir === null || !$stamp) {
            return;
        }
        $cacheFilename = $this->_getCacheFilename($path);
        $f = @fopen($cacheFilename, "a+b");
        if (!$f) {
            return;
        }
        flock($f, LOCK_EX);
        ftruncate($f, 0);
        fwrite($f, $content);
        fclose($f);
        $old = umask(0);
        @chmod($cacheFilename, 0666);
        umask($old);
        touch($cacheFilename, $stamp);
    }
    
    private function _getCacheFilename($path)
    {
        if ($this->_cacheDir === null || !strlen($path)) {
            return;
        }
        if (!@is_dir($this->_cacheDir)) {
            $old = umask(0);
            @mkdir($this->_cacheDir, 0777, true);
            umask($old);
        }
        return $this->_cacheDir . "/dkdd." . md5($path) . "." . preg_replace('/[^-\w.]|\.\./', '_', $path);
    }
    
    public function runPreprocessors($content, $path)
    {
        foreach (array_reverse($this->_callbacks) as $callback) {
            $content = call_user_func($callback, $content, $path);
        }
        return $content;
    }
    
    public function assemble($uri)
    {
        // Mangle protocol's or Windows drive's ":", else URL becomes 
        // incorrect. Unfortunately we cannot mangle it to %XX, because it is
        // not well-supported by DOM module. So we quote it as "//".
        $uri = preg_replace("{^(\w+):(/)}", "$1//$2", $uri);
        return $this->_name . "://" . $uri;        
    }
    
    public static function match($wrapped)
    {
        // Parse input URL and extract protocol name and real filename.
        if (!preg_match('{(\w+)://(.*)}s', $wrapped, $m)) {
            throw new Exception("Stream path must be started by a protocol, '$wrapped' given");
        }
        $uri = $m[2];
        // Unmangle ":" from "//" if needed.
        $uri = preg_replace("{^(\w+)//(/)}", "$1:$2", $uri);
        // We also need to unmangle "%3A", because DOMDocument produces
        // such URLs in Windows ("file:///C%3A/a.txt"), but fopen() for
        // this path does not work.
        $uri = preg_replace("{^(\w+:///\w+)%3A(/)}si", "$1:$2", $uri);
        return array($m[1], $uri);
    }
}

/**
 * An internal tool to intercept nested DOMDocument loading in:
 * - external entities
 * - xinclude
 * - XSLT
 * Do not use it separately.
 */
class Dklab_DOMDocument_StreamWrapper
{
    private static $_protos = array();
    
    /***
     *** Public API.
     ***/
     
    public static function protoRegister(Dklab_DOMDocument_Proto $proto)
    {
        self::$_protos[$proto->getName()] = $proto;
    }

    public static function protoUnregister(Dklab_DOMDocument_Proto $proto)
    {
        // This calls the destructor too.
        unset(self::$_protos[$proto->getName()]);
    }
    
    /***
     *** Standard stream callback methods.
     ***/

    private $_proto = null;
    private $_fname = null;
    private $_content = null;
    private $_pos = null;

    function stream_open($path, $mode, &$options, &$opened_path)
    {
        // Parse input URL and extract protocol name and real filename.
        list ($this->_proto, $this->_fname) = Dklab_DOMDocument_Proto::match($path);
        
        // Find corresponding Proto object.
        $proto = $stamp = null;
        if (isset(self::$_protos[$this->_proto])) {
            $proto = self::$_protos[$this->_proto];
            // Calculate file timestamp.
            if (preg_match('{^file://}s', $this->_fname) || !preg_match('{^\w+://}s', $this->_fname)) {
                $stamp = @filemtime($this->_fname);
            }
        }
        
        // Try to load content from the cache.
        $this->_content = $proto? $proto->loadCache($this->_fname, $stamp) : null;
        if ($this->_content === null) {
            // Load the resource from file or URL.
            $f = @fopen($this->_fname, $mode, $options & STREAM_USE_PATH);
            if (!$f) {
                return false;
            }
            $this->_content = '';
            while (!feof($f)) {
                $this->_content .= fread($f, 1024 * 64);
            }
            fclose($f);
            // Call all the preprocessors and store results into cache.
            if ($proto) {
                $this->_content = $proto->runPreprocessors($this->_content, $this->_fname);
                $proto->saveCache($this->_fname, $stamp, $this->_content); 
            }
        }
        $this->_pos = 0;
        return true;
    }

    function stream_read($count)
    {
        $ret = self::substr($this->_content, $this->_pos, $count);
        $this->_pos += self::strlen($ret);
        return $ret;
    }

    function stream_write($data)
    {
        $left = self::substr($this->_content, 0, $this->_pos);
        $right = self::substr($this->_content, $this->_pos + self::strlen($data));
        $this->_content = $left . $data . $right;
        $this->_pos += self::strlen($data);
        return self::strlen($data);
    }

    function stream_tell()
    {
        return $this->_pos;
    }

    function stream_eof()
    {
        return $this->_pos >= self::strlen($this->_content);
    }

    function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < self::strlen($this->_content) && $offset >= 0) {
                     $this->_pos = $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                     $this->_pos += $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            case SEEK_END:
                if (self::strlen($this->_content) + $offset >= 0) {
                     $this->_pos = self::strlen($this->_content) + $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            default:
                return false;
        }
    }
    
    function url_stat($path)
    {
        return array();
    }
    
    
    /**
     * mbstring work-around functions. 
     **/
   	private static function strlen($s)
   	{
    	if (function_exists($orig = 'mb_orig_' . ($func = __FUNCTION__))) {
    		return $orig($s);
    	} else {
            return $func($s);
    	}
   	}
   	
   	private static function substr($str, $from, $len)
   	{
    	if (function_exists($orig = 'mb_orig_' . ($func = __FUNCTION__))) {
    		return $orig($str, $from, $len);
    	} else {
            return $func($str, $from, $len);
    	}
   	}  
}
