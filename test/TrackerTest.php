<?php
include 'class.Track.php';
Class TrackClass extends PHPUnit_Framework_TestCase{

	public function inputLogData () {

		return array(
			array('just a test message',true,false),
			array('just a test message',null,true),
			array(1,array(),false),
			array('1',array(),true),
			array(true,array(),false),
			array('test',array(true,false),true),
			array('test',array(true,false),true),
			array('test',array(null,123),true),
			array('test',array('null',123),true),
			array('test1',array('displayMessage' => true,123),true),
			array('test2',array('displayMessage' => false,123),true),
			array('test3',array('displayMessage' => null,123),true),
			array('test4',array('displayMessage' => 'null',123),true),
			array('test5',array('displayMessage' => true,'route' => 123),true),
			array('test6',array('displayMessage' => true,'route' => true),true),
			array('test7',array('displayMessage' => true,'route' => false),false),
			array('test8',array('displayMessage' => true,'route' => 'test\\13'),true),
			array('test9',array('displayMessage' => true,'route' => 'test\\13', 'error' => ''),true),
			array('test10',array('displayMessage' => true,'route' => 'test\\13', 'error' => false),true),
			array('test11',array('displayMessage' => true,'route' => 'test\\13', 'error' => null),true),
			array('test12',array('displayMessage' => true,'route' => 'test\\13', 'error' => true),true),
			array('test13',array('displayMessage' => true,'route' => 'test\\13', 'error' => 123),true),
			array('test13',array('displayMessage' => true,'route' => 'test\\13', 'error' => 'fucking error message'),true)
			);

	}

	public function inputSettingsData () {

		return array(
			array(array(),true,null,false),
			array(false,array(),true,false),
			array('','',false,false),
			array('/','logs.log','',false),
			array('/test/',array('erros' => 'errors.log'),array(),true),
			array('/test/',array('erros' => null),array('displayMessage'),true),
			array('/test/',array('erros' => false),array('displayMessage' => true),true),
			array('test',array('erros' => 'errors.log'),array('displayMessage' => true),true),
			array('test',array('erros' => 'errors.log',array()),array('displayMessage' => true),true),
			array('test',array('erros' => 'errors.log',1,true,array()),array('displayMessage' => true),true),
			array('test/log',array('erros' => 'errors.log',1,true,array()),array('displayMessage' => true),true),
			array('test/log/123456',array('erros'=> 'errors.log',1,true,array()),array('displayMessage' => false),true),
			array('test',array('errors' => 'errors.log', 'exceptions' => 'exceptions.log','trace' => 'log.logs'),array('displayMessage' => true),true),
			array('test',array('errors' => null, 'exceptions' => 'exceptions.log','trace' => 'log.logs'),array('displayMessage' => true),true)
			);

	}


	/**
	* @dataProvider inputSettingsData
	*/
	public function testSettings ( $directory, $files, $settings, $expected ) {

		$this->assertEquals($expected,Track::settings($directory,$files,$settings));

	}

	/**
	* @dataProvider inputSettingsData
	*/
	public function testExceptionHandler ( $directory, $files, $settings, $expected ) {

		try {
			throw new Exception("Error Processing Request", 1);			
		} catch (\Exception $e) {
			if ($this->assertEquals($expected,Track::settings($directory,$files,$settings))) {
				Track::ExceptionHandler($e);
			}
		}
	}

	/**
	* @dataProvider inputLogData
	* To run test, make param settings non-optional
	*/
	public function testLog ( $message, $settings, $expected ) {

		Track::settings('test',array('errors' => 'errors.log', 'exceptions' => 'exceptions.log','trace' => 'log.logs'),null);
		$this->assertEquals($expected,Track::log($message, $settings));		

	}

}
?>