<?php
/**
 * The ModelTask handles creating and updating models files.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 1.2
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('ModelTask', 'Console/Command/Task');

/**
 * Task class for creating and updating model files.
 *
 * @package	   Cake.Console.Command.Task
 */
class ExtModelTask extends ModelTask {

	public $name = "Model";

/**
 * Override initialize
 *
 * @return void
 */
	public function initialize() {
		$this->path = current(App::path('Model'));
	}

/**
 * Execution method always used for tasks
 *
 * @return void
 */
	public function execute() {
		
		if (isset($this->params['plugin'])) {
			$plugin = $this->params['plugin'];
			$pluginPath = $plugin . '.';
			App::uses($plugin.'AppModel', $pluginPath.'Model');
			$pluginmodel = $plugin.'AppModel';
			$plugin_model = new $pluginmodel();
			$db = ConnectionManager::getDataSource('default');
			$db->cacheSources = false;
			$config = $db->config;
			$config['prefix'] = $plugin_model->tablePrefix;
			ConnectionManager::create('tmpDataSource', $config);
			$this->connection = 'tmpDataSource';		
		}

		parent::execute();
	}
}
