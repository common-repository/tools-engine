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

//A singleton class
class Plugin
{
	protected static $instance = null;
	protected $pluginSlug = 'tools-engine';
	protected $pluginVersion = 1.0;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	//Getters
	public function getPluginSlug() {
		return $this->pluginSlug;
	}

	public function getPluginVersion() {
		return $this->pluginVersion;
	}

	public static function activate() {

	}

	/**
	 * The code that runs during plugin deactivation.
	 */
	public static function deactivate() {
	}
}