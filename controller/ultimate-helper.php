<?php
/**
* Tools Engine plugin file. 
*
* Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com 
*/

namespace Smackcoders\TOOLSENGINE;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class LocationRule
 * @package Smackcoders\TOOLSENGINE
 */
class UltimateHelper
{
	protected static $instance = null,$plugin;

	/**
	 * UltimateHelper constructor.
	 */
	public function __construct()
	{

	}

	/**
	 * UltimateHelper Instances
	 */
	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
			self::$plugin = Plugin::getInstance();
			self::$instance->doHooks();
		}
		return self::$instance;
	}

	public static function doHooks(){

	}

	public static function formattedVar( &$array, $key, $default = null ) {

		if( is_array($array) && array_key_exists($key, $array) ) {
			$formattedVar = $array[ $key ];
			unset( $array[ $key ] );
			return $formattedVar;    
		}

		return $default;
	}

	public static function getSubArray( $array, $keys ) {

		$subArray = array();

		foreach( $keys as $key ) {

			$subArray[ $key ] = $array[ $key ];

		}

		return $subArray;

	}
}
