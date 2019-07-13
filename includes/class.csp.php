<?php
/**
 *
 * A simple class for dynamic construction of Content-Security-Policy HTTP header string.
 *
 * This is just quick write to get the job for Flyspray done. May change completely!
 *
 * It does not check if the added rules are valid or make sense in the context and http request/response!
 * So it is currently up to the code sections who use that class that the resulting csp string is correct.
 *
 * @license http://opensource.org/licenses/lgpl-license.php Lesser GNU Public License
 * @author peterdd
 *
 * @see https://www.w3.org/TR/CSP2/
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP
 * @see https://caniuse.com/#search=csp
 *
 * Example: $csp=new ContentSecurityPolicy(); $csp->add('default-src', "'none'"); $csp->add('style-src', "'self'"); $csp->emit();
 */
class ContentSecurityPolicy {

	#private $csp=array();
	public $csp=array();

	# for debugging just to track which extension/plugins wants to add csp-entries
	#public $history=array();

	function __construct(){
		$this->csp=array();
	}

	/**
	* get the constructed concatenated value string part for the http Content-Security-Header
	*
	* TODO: maybe some syntax checks and logical verification when building the string.
	*
	* MAYBE: add optional parameter to get only a part like $csp->get('img-src')
	* Alternatively the user can just access the currently public $csp->csp['img-src'] to get that values as array.
	*
	*/
	public function get(){
		$out = '';
		foreach ( $this->csp as $key => $values ) {
			$out .= $key.' '.implode(' ', $values).'; ';
		}
		$out = trim($out, '; ');
		return $out;
	}
	
	/**
	 * adds a value to a csp type
	 *
	 * @param type
	 * @param value single values for a type
	 *
	 * examples:
	 * $csp->add('default-src', "'self'"); # surrounding double quotes "" used to pass the single quotes
	 * $csp->add('img-src', 'mycdn.example.com'); # single quoted string ok
	*/
	public function add($type, $value){
		if( isset($this->csp[$type]) ) {
			if( !in_array( $value, $this->csp[$type] ) ) {
				$this->csp[$type][] = $value;
			}
		} else {
			$this->csp[$type] = array($value);
		}
		#$this->history[]=debug_backtrace()[1];
	}

	/**
	* sends the Content-Security-Policy http headers
	*/
	public function emit() {
		$string=$this->get();
		header('Content-Security-Policy: '.$string );
		# some older web browsers used vendor prefixes before csp got w3c recommendation.
		# maybe use useragent string to detect who should receive this outdated vendor csp strings.
		# for IE 10-11
		header('X-Content-Security-Policy: '.$string );
		# for Chrome 15-24, Safari 5.1-6, ..
		header('X-WebKit-CSP: '.$string );
	}

	/**
	* Put the csp as meta-tags in the HTML-head section.
	*
	* Q: What is the benefit of adding csp as meta tags too?
	*
	* I don't know, maybe this way the csp persist if someone saves a page to his harddrive for instance or if bad web proxies rip off csp http headers?
	* Do webbrowsers store the CSP-HTTP header to the HTML-head as metatags automatically if there is no related metatag in the original page? Mhh..
	*/
	public function getMeta() {
		$string=$this->get();
		$out= '<meta http-equiv="Content-Security-Policy" content="'.$string.'">';
		# enable if you think it is necessary for your customers.
		# older web browsers used vendor prefixes before csp2 got a w3c recommendation standard..
		# maybe use useragent string to detect who should receive this outdated vendor csp strings.
		# for IE 10-11
		$out.= '<meta http-equiv="X-Content-Security-Policy" content="'.$string.'">';
		# for Chrome 15-24, Safari 5.1-6, ..
		$out.= '<meta http-equiv="X-WebKit-CSP" content="'.$string.'">';
		return $out;
	}

}
