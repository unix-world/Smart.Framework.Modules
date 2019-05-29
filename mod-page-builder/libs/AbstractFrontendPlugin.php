<?php
// Class: \SmartModExtLib\PageBuilder\AbstractFrontendPlugin
// (c) 2006-2019 unix-world.org - all rights reserved
// v.3.7.8 r.2019.01.03 / smart.framework.v.3.7

namespace SmartModExtLib\PageBuilder;

//----------------------------------------------------- PREVENT DIRECT EXECUTION
if(!defined('SMART_FRAMEWORK_RUNTIME_READY')) { // this must be defined in the first line of the application
	@http_response_code(500);
	die('Invalid Runtime Status in PHP Script: '.@basename(__FILE__).' ...');
} //end if
//-----------------------------------------------------


//=====================================================================================
//===================================================================================== CLASS START
//=====================================================================================

/**
 * Class: AbstractFrontendPlugin - Abstract Frontend Plugin, provides the Abstract Definitions to create PageBuilder (Frontend) Plugins.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints		needs to be extended as: UniqueClassPluginName
 *
 * @access 		PUBLIC
 *
 * @version 	v.20190529
 * @package 	PageBuilder
 *
 */
abstract class AbstractFrontendPlugin extends \SmartAbstractAppController {


	private $plugin_initialized = false;
	private $plugin_name = 'ERROR-NO-PLUGIN-NAME';
	private $plugin_config = array();
	private $plugin_caller_module_path = 'modules/app/';


	//=====
	/**
	 * Initialize Plugin (internal use only)
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	final public function initPlugin($plugin_name, $plugin_config, $plugin_caller_module_path) {
		//--
		if($this->plugin_initialized === true) {
			return;
		} //end if
		//--
		if(\SmartFileSysUtils::check_if_safe_file_or_dir_name((string)$plugin_name)) {
			$this->plugin_name = (string) $plugin_name;
		} //end if
		//--
		if(is_array($plugin_config)) {
			$this->plugin_config = (array) array_change_key_case((array)$plugin_config, CASE_LOWER); // plugin config ; force all keys lower case
		} //end if
		//--
		if(\SmartFileSysUtils::check_if_safe_path((string)$plugin_caller_module_path)) {
			$this->plugin_caller_module_path = (string) $plugin_caller_module_path;
		} //end if
		//--
		$this->plugin_initialized = true;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get Plugin Name
	 */
	final public function getPluginName() {
		//--
		return (string) $this->plugin_name;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get Plugin Config as Array
	 */
	final public function getPluginConfig() {
		//--
		return (array) $this->plugin_config;
		//--
	} //END FUNCTION
	//=====


	//=====
	/**
	 * Get Plugin Caller Module Path
	 */
	final public function getPluginCallerModulePath() {
		//--
		return (string) $this->plugin_caller_module_path;
		//--
	} //END FUNCTION
	//=====


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>