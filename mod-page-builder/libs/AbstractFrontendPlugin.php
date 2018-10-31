<?php
// Class: \SmartModExtLib\PageBuilder\AbstractFrontendPlugin
// (c) 2006-2018 unix-world.org - all rights reserved
// Author: Radu Ovidiu I.

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

// TODO: use extend from SmartAbstractAppController instead of this !!!

/**
 * Class: AbstractFrontendPlugin - Abstract Frontend Plugin, provides the Abstract Definitions to create PageBuilder (Frontend) Plugins.
 *
 * @usage  		dynamic object: (new Class())->method() - This class provides only DYNAMIC methods
 * @hints		needs to be extended as: UniqueClassPluginName
 *
 * @access 		PUBLIC
 *
 * @version 	v.181031
 * @package 	PageBuilder
 *
 */
abstract class AbstractFrontendPlugin extends \SmartAbstractAppController {


	private $plugin_initialized = false;
	private $plugin_config = array();


	//=====
	/**
	 * Initialize Plugin (internal use only)
	 *
	 * @access 		private
	 * @internal
	 *
	 */
	final public function initPlugin($plugin_config) {
		//--
		if($this->plugin_initialized === true) {
			return;
		} //end if
		//--
		if(is_array($plugin_config)) {
			$this->plugin_config = (array) array_change_key_case((array)$plugin_config, CASE_LOWER); // plugin config ; make all keys lower
		} //end if
		//--
		$this->plugin_initialized = true;
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


} //END CLASS


//=====================================================================================
//===================================================================================== CLASS END
//=====================================================================================


// end of php code
?>