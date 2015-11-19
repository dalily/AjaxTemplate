<?php
/**
 * Command-line code generation utility to automate programmer chores.
 *
 * Bake is CakePHP's code generation script, which can help you kickstart
 * application development by writing fully functional skeleton controllers,
 * models, and views. Going further, Bake can also write Unit Tests for you.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 1.2.0.5012
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('BakeShell', 'Console/Command');

/**
 * Bake is a command-line code generation utility for automating programmer chores.
 *
 * @package       Cake.Console.Command
 * @link          http://book.cakephp.org/view/1522/Code-Generation-with-Bake
 */
class ExtBakeShell extends BakeShell {

/**
 * Contains tasks to load and instantiate
 *
 * @var array
 */

public $tasks = array('Project', 'DbConfig', 'Model', 'Controller', 'AjaxTemplate.ExtView', 'Plugin', 'Fixture', 'Test');

/**
 * The connection being used.
 *
 * @var string
 */
	public $connection = 'default';

/**
 * Assign $this->connection to the active task if a connection param is set.
 *
 */
	public function startup() {
		parent::startup();
		$task = Inflector::classify($this->command);
		if (isset($this->{$task}) && !in_array($task, array('Project', 'DbConfig'))) {
			if (isset($this->params['connection'])) {
				$this->{$task}->connection = $this->params['connection'];
			}
		}
	}

/**
 * Override main() to handle action
 *
 */
	public function main() {

		if (!is_dir($this->DbConfig->path)) {
			$path = $this->Project->execute();
			if (!empty($path)) {
				$this->DbConfig->path = $path . 'Config' . DS;
			} else {
				return false;
			}
		}

		if (!config('database')) {
			$this->out(__d('cake_console', 'Your database configuration was not found. Take a moment to create one.'));
			$this->args = null;
			return $this->DbConfig->execute();
		}
		$this->out(__d('cake_console', 'Interactive Bake Shell'));
		$this->hr();
		$this->out(__d('cake_console', '[D]atabase Configuration'));
		$this->out(__d('cake_console', '[M]odel'));
		$this->out(__d('cake_console', '[V]iew'));
		$this->out(__d('cake_console', '[C]ontroller'));
		$this->out(__d('cake_console', '[P]roject'));
		$this->out(__d('cake_console', '[F]ixture'));
		$this->out(__d('cake_console', '[T]est case'));
		$this->out(__d('cake_console', '[Q]uit'));

		$classToBake = strtoupper($this->in(__d('cake_console', 'What would you like to Bake?'), array('D', 'M', 'V', 'C', 'P', 'F', 'T', 'Q')));
		switch ($classToBake) {
			case 'D':
				$this->DbConfig->execute();
				break;
			case 'M':
				$this->ExtModel->execute();
				break;
			case 'V':
				$this->ExtView->execute();
				break;
			case 'C':
				$this->Controller->execute();
				break;
			case 'P':
				$this->Project->execute();
				break;
			case 'F':
				$this->Fixture->execute();
				break;
			case 'T':
				$this->Test->execute();
				break;
			case 'Q':
				exit(0);
				break;
			default:
				$this->out(__d('cake_console', 'You have made an invalid selection. Please choose a type of class to Bake by entering D, M, V, F, T, or C.'));
		}
		$this->hr();
		$this->main();
	}

	public function runCommand($command, $argv) {
		if (strtolower($command) == 'model') {
			$command = 'model';
		}
		if (strtolower($command) == 'controller') {
			$command = 'controller';
		}
		if (strtolower($command) == 'view') {
			$command = 'ext_view';
		}
		return parent::runCommand($command, $argv);
	}

/**
 * Gets the option parser instance and configures it.
 *
 * @return ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->description(
			__d('cake_console',	'The Bake script generates controllers, views and models for your application.' .
			' If run with no command line arguments, Bake guides the user through the class creation process.' .
			' You can customize the generation process by telling Bake where different parts of your application are using command line arguments.')
		)->addSubcommand('all', array(
			'help' => __d('cake_console', 'Bake a complete MVC. optional <name> of a Model')
		))->addSubcommand('project', array(
			'help' => __d('cake_console', 'Bake a new app folder in the path supplied or in current directory if no path is specified'),
			'parser' => $this->Project->getOptionParser()
		))->addSubcommand('plugin', array(
			'help' => __d('cake_console', 'Bake a new plugin folder in the path supplied or in current directory if no path is specified.'),
			'parser' => $this->Plugin->getOptionParser()
		))->addSubcommand('db_config', array(
			'help' => __d('cake_console', 'Bake a database.php file in config directory.'),
			'parser' => $this->DbConfig->getOptionParser()
		))->addSubcommand('model', array(
			'help' => __d('cake_console', 'Bake a model.'),
			'parser' => $this->Model->getOptionParser()
		))->addSubcommand('view', array(
			'help' => __d('cake_console', 'Bake views for controllers.'),
			'parser' => $this->View->getOptionParser()
		))->addSubcommand('ext_view', array(
			'help' => __d('cake_console', 'Bake views for controllers.'),
			'parser' => $this->View->getOptionParser()
		))->addSubcommand('controller', array(
			'help' => __d('cake_console', 'Bake a controller.'),
			'parser' => $this->Controller->getOptionParser()
		))->addSubcommand('fixture', array(
			'help' => __d('cake_console', 'Bake a fixture.'),
			'parser' => $this->Fixture->getOptionParser()
		))->addSubcommand('test', array(
			'help' => __d('cake_console', 'Bake a unit test.'),
			'parser' => $this->Test->getOptionParser()
		))->addOption('connection', array(
			'help' => __d('cake_console', 'Database connection to use in conjunction with `bake all`.'),
			'short' => 'c',
			'default' => 'default'
		))->addOption('theme', array(
			'short' => 't',
			'help' => __d('cake_console', 'Theme to use when baking code.')
		));

		return $parser;
	}

}