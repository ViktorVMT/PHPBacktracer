<?php 

/**
 * Handle with \Exceptions,E^ALL, log them and allow to retrieve filtered data from 
 * log files
 * Allow write custom messages into files and retrieve filtered data from files
 * @author Ivaylo Ivanov Ivchoyyyy@gmail.com
 * 
 */

Abstract Class Track {

	/**
	* @var string
	* Directory for log error file(s)
	*/
	public static $dir;

	/**
	* @var array
	* By default, show errors
	*/
	public static $showErrors;

	/**
	* @var array
	* Array with file names to log errors and exception ( if its only one name, both E and 
	* will be logged in the same file)
	*/
	public static $files;

	/**
	* Check for php version, assing properties and determine whether to show errors or not 
	* @param string / directory for log error file(s)
	* @param array / file(s) for E and Exceptions
	* @param array / key-value displayMessage => false 
	* @return boolean
	*/
	public static function settings ( $dir, $files, $settings = null ) {

		defined('DS') ? true : define('DS',DIRECTORY_SEPARATOR);
		$phpVersion = substr(PHP_VERSION,0,1);
		if ($phpVersion < 5) {
			print('Error! Track library needs php5 and above');		
			return false;	
		}
		if (!is_array($files)) {
			print('Files param should be an array with at least one file where to write'."\n");
			return false;
		}
		if (isset($settings['displayMessage'])) {
			self::$showErrors = $settings['displayMessage'];
		} else {
			self::$showErrors = true;
		}		
		if (self::resolveLogDir($dir)) {
			self::$files = $files;
			self::setHandlers();
			return true;			
		}
		return false;

	}

	/**
	* Check log directory for write access and if it's not created, try to create it
	* @param string / directory for log error file(s)
	* @return boolean 
	*/
	public static function resolveLogDir($dir){

		if (!is_string($dir)) {
			print('Route directory should be string (if you don\'t want to specify any directory, just set it to: \'/\' or leave it empty \'\').'."\n");
			return false;
		}
		if (strlen($dir) < 1 || $dir == ' ') {
			self::$dir = '';
			$dir = '/';
		} elseif ($dir == '/') {
			self::$dir = '';
		} else {
			self::$dir = $dir.DS;
		}
		$path = dirname($_SERVER['SCRIPT_FILENAME']).DS.self::$dir;
		if (!is_dir($path)) {	
			if (!mkdir($path, 0766, true)) {	
				print('Cannot create log directory'."\n");	
				return false;				
			}	
		} elseif (!is_writable($path)) {
			print('Directory is not writable');
			return false;
		}	
		return true;

	}

	/**
	* Set exception and error handlers
	* @link http://php.net/manual/en/function.set-error-handler.php
	* @return boolean
	*/
	public static function setHandlers () {	

		/*
		The following error types cannot be handled with a 
		user defined function: E_ERROR, E_PARSE, E_CORE_ERROR, 
		E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, 
		and most of E_STRICT raised in the file where set_error_handler() is called.
		*/
		error_reporting(E_ALL ^ E_DEPRECATED | E_ERROR | E_PARSE | E_CORE_ERROR 
			| E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_STRICT);
	  	set_Exception_handler(array(get_class(), 'ExceptionHandler'));
	  	set_error_handler(array(get_class(), "errorHandler"));	
	  	return true;

	}

	/**
	* Catch Exceptions
	* @param object
	*/
	public static function ExceptionHandler ( $e ) {  

		$ExceptionMessage = "Exception Message: " . $e->getMessage();
		$message = date("Y-m-d - H:i:s").' | Exception |: ';
		if (isset($e->getTrace()[0])) {
			$message .= 'Exception in Class: '.$e->getTrace()[0]['class'].',';		
		}else {
			$message .= 'Exception in File: '.$e->getFile().',';
		}  
		$ExceptionNumber = $e->getCode();
		$message .=  'Error Number: '.$ExceptionNumber.', Message: '.$ExceptionMessage;
		self::dispatchError($message,true);
		
	}

	/**
	* Catch E errors
	* @param int / error number
	* @param string / error
	* @param string / file
	* @param int / line
	*/
	public static function errorHandler ( $errno, $error, $errstr, $errLine ) {

		$message = date("Y-m-d - H:i:s") . ' | ERROR |: ';
		switch ($errno) {
			case 2:
				$message .= 'WARNING : ' . $error . ' in ' . $errstr . ' in line : ' . $errLine;	  
				break;
			case  4:
				$message .= 'PARSE ERROR : ' . $error . ' in ' . $errstr . ' in line : ' . $errLine;
				break;
			case  8:
				$message .= 'NOTICE : ' . $error . ' in ' . $errstr . ' in line : ' . $errLine;
				break;
			case  1024:
				$message .= 'TRIGGER ERROR : ' . $error . ' in ' . $errstr . ' in line : ' . $errLine;
				break;		 		   
			case  2048:
				$message .= 'STRICT : ' . $error . ' in ' . $errstr . ' in line : ' . $errLine;
				break; 
		}
		self::dispatchError($message,false);
   
	} 

	/**
	* Dispatch error to log function
	* @param string / message
	* @param boolean / true for exception
	*/
	public static function dispatchError ( $message, $exceptions ) {

		if (isset(self::$files['exceptions'])) {
			if ($exceptions) {	
				self::log($message,
					array('route' => self::$dir.self::$files['exceptions'],'displayMessage' => self::$showErrors, 'error' => true));				
			} else {	
				if (!isset(self::$files['errors'])) {
					self::$files['errors'] = array_values(self::$files)[0];
				}
				self::log($message,
					array('route' => self::$dir . self::$files['errors'],'displayMessage' => self::$showErrors, 'error' => true));
			}
		} else {			
			$files = array_values(self::$files);
			self::log($message,
			   array('route' =>  self::$dir . $files[0], 'displayMessage' => self::$showErrors, 'error' => true));
		}

	}

	/**
	* Write logs into file
	* @param string / message 
	* @param array / keys : error, route, displayMessage ; values : boolean, string, boolean 
	* @return boolean
	*/
	public static function log ( $msg, $settings = array()) {

		if (!is_string($msg)) {
			trigger_error('Message should be string');
			return false;
		}
		if (!empty($settings) && !is_array($settings)) {
			trigger_error('Settings must be key-value array');
			return false;			
		}
		if (isset($settings['error']) && !self::$files) {
			trigger_error('There is no specified error log files');
			return false;
		}elseif (!isset($settings['error'])) {
			$message = date("Y-m-d - H:i:s") . ' | LOG |: ' . $msg;
			if (!isset($settings['route'])) {				
				if (isset(self::$files['trace'])) {	
					$settings['route'] = self::$dir . self::$files['trace'];	
				} else {	
					print('You didn\'t spesify any log file, a defaultLog.logs file will be created'."\n");
					self::$files['trace'] = 'defaultLogs.logs';
					$settings['route'] = self::$dir . self::$files['trace'];	
				}			
			}								
		} else {
			$message = $msg;
		}
		if (file_exists($settings['route']) && !is_writable($settings['route'])) {
			print('The file specified is not writable');
			return false;
		}
		if (isset($settings['displayMessage']) && $settings['displayMessage']) {
			print(nl2br($message)."\n");
		}  
		if ($settings['route'] && strlen($settings['route']) > 0) {
			$file = explode(DS, $settings['route']);
			if (strlen($file[count($file) - 1]) > 0 ) {
				$handle = fopen($settings['route'],'a');
				$message .= " <<<ENDLINE>>>\n";
				fwrite($handle, $message);
				fclose($handle);
				return true;							
			} else {
				trigger_error('File name is not supplied');
				return false;
			}
		}
		return false;		

	}

	/**
	* Function to retrieve data from log file(s) using some specifications
	* @param array / selected files ( they must in the directory, supplied earlier from settings )
	* @param array / filter could be keyword or case ( log , error , exception )
	* @param bool / true for descading order
	* @param limit / int or numeric
	* @return mixed ( boolean , array / data) 
	*/
	public static function retrieveLogs ( $files, $filter = null, $desc = null, $limit = null ) {

		if (!is_array($files)) {
			trigger_error('Files param should be an array with at least one value');
			return false;
		}
		$logs = array();
		foreach ($files as $key => $fileName) {
			$file = self::$dir . $fileName;
			$continue = false;
			file_exists($file) ? $continue = true : trigger_error($file . " is not existing");
			is_readable($file) ? $continue = true : trigger_error($file . " is not readable");	
			if (!$continue) {
				trigger_error("Unable to retrieve log data from ".$file);	
			} else {
				$handle = fopen($file, 'r');
				$size = filesize($file);
				if ($size > 0) {
					$logs[$fileName] = explode("<<<ENDLINE>>>",fread($handle, $size));
					// remove empty "new line \n";	
					unset($logs[$fileName][count($logs[$fileName]) - 1]);		
					$desc ? $logs[$fileName] = array_reverse($logs[$fileName]) : false;
					$logs[$fileName] = array_map('trim',$logs[$fileName]);
					if (isset($filter['date'])) {
						$fDate = date("Y-m-d",strtotime("-".$filter['date']));
						foreach ($logs[$fileName] as $row => $line) {
							$lDate = substr($line, 0,10);
							if ($fDate != $lDate) {
								unset($logs[$fileName][$row]);
							}
						}
					}
					if (isset($filter['case'])) {
						if ($filter['case'] != 'error' && $filter['case'] != 'log' 
						  && $filter['case'] != 'exception') {
							trigger_error("Param case should error,exception or log");	
							return;
									}			
						foreach ($logs[$fileName] as $row => $line) {
							if (strlen($line) > 1) {
								$line = trim(explode("|", $line)[1]);						
							}
							if (strtolower($line) != strtolower($filter['case'])) {
								unset($logs[$fileName][$row]);
							}
						}
					}
					if (isset($filter['keyword'])) {
						foreach ($logs[$fileName] as $row => $line) {
							if (!stripos($line, $filter['keyword'])) {
								unset($logs[$fileName][$row]);
							}
						}
					}
					if ($limit) {
						if (!is_numeric($limit)) {
							trigger_error("Param limit should be numeric value");
							return;
						}
						$length = count($logs[$fileName]);
						if ($length > $limit) {
							$logs[$fileName] = array_slice($logs[$fileName], 0, - ($length - $limit));					
						}
					}
				}
				fclose($handle);
			}	

		}		
		return $logs;

	}

}
?>