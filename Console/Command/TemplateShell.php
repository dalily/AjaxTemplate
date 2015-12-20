<?php
/**
 * Copyright 2005-2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2005-2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Template Shell
 *
 * @package templates
 * @subpackage templates.subtemplates
 */
class TemplateShell extends Shell {


/**
 * Contains tasks to load and instantiate
 *
 * @var array
 */
	public $tasks = array();

/**
 * main
 *
 * @return void
 */
	public function main0() {
		$model = $this->in('Model name:');
		$controller = Inflector::pluralize($model);

		$controllerActions = $this->in('Do you want to bake the controller with admin prefix: yes/no', 'y/n', 'n');

		$usePlugin = $this->in("Do you want to bake in plugin: yes/no", 'y/n', 'y');
		if ($usePlugin == 'y') {
			$pluginName = $this->in('Name of your plugin:', null);
			
			if ($pluginName == '') {
				$usePlugin = 'n';
			}
		}

		
		if ($controllerActions == 'y') {
			$controllerActions = '--admin';
		} 
		else
		{
			$controllerActions = '--public';
		}
		
		$theme = '--theme fuelux';
		$plugin = '';

		if ($usePlugin == 'y') {
			$plugin .= "-p $pluginName";
		}	

		$modelCommand = "ajax_template.ext_bake model $plugin $model $theme";
		$controllerCommand = "ajax_template.ext_bake controller $plugin $controller $controllerActions $theme";
		$viewCommand = "ajax_template.ext_bake view $plugin $controller $theme";
		$this->out($modelCommand);
		$this->out($controllerCommand);
		$this->out($viewCommand);
		$this->dispatchShell($modelCommand);
		$this->dispatchShell($controllerCommand);
		$this->dispatchShell($viewCommand);
	}
	public function main() {
		$model = $this->in('Model name:');
		$controller = Inflector::pluralize($model);
		$controllerActions = $this->in('Do you want to bake the controller with admin prefix: yes/no', 'y/n', 'n');
		$usePlugin = $this->in("Do you want to bake in plugin: yes/no", 'y/n', 'n');
		if ($usePlugin == 'y') {
			$pluginName = $this->in('Name of your plugin:', null, '');
			if ($pluginName == '') {
				$usePlugin = 'n';
			}
		}
		
		if ($controllerActions == 'y') {
			$controllerActions = '--admin';
		} 
		else
		{
			$controllerActions = '--public';
		}
		
		$theme = 'fuelux';
		
		$modelCommand = "ajax_template.ext_bake model $model";
		$controllerCommand = "ajax_template.ext_bake controller $controller $controllerActions";
		$viewCommand = "ajax_template.ext_bake view $controller";
		$postfix = " --theme $theme";
		if ($usePlugin == 'y') {
			$postfix .= " --plugin $pluginName";
		}
		$this->out($modelCommand . $postfix);
		$this->out($controllerCommand . $postfix);
		$this->out($viewCommand . $postfix);
		$this->dispatchShell($modelCommand . $postfix);
		$this->dispatchShell($controllerCommand . $postfix);
		$this->dispatchShell($viewCommand . $postfix);
	}
/**
 * Possible Subthemes
 *
 * @param array $list 
 * @return string
 */
	protected function _possibleSubthemes($list) {
		$i = 0;
		$this->out('Possible subthemes to include:');
		foreach ($list as $subtheme) {
			$this->out(++$i . '. ' . $subtheme);
		}
		$response = $this->in('Do you want to include any subthemes?');
		if (is_numeric($response)) {
			return " --subthemes " . $list[$response - 1];
		}
		return '';
	}

/**
 * Help
 *
 * @return void
 */
	public function help() {
		$this->out('Template assistant');
	}
} 