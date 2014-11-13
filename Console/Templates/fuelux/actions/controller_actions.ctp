<?php
/**
 * Bake Template for Controller action generation.
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Console.Templates.default.actions
 * @since         CakePHP(tm) v 1.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>

/**
 * beforeFilter method
 *
 * @return void
 */
function beforeFilter() { 
	parent::beforeFilter();
}

/**
 * get_datagrid_data method
 *
 * @return array
 */
	public function <?php echo $admin ?>get_datagrid_data() {
		$sort_field = $this->request->data['sort'];
		$sort_order = $this->request->data['order'];
		$limit = $this->request->data['limit'];
		$page = $this->request->data['page'];
		$conditions = isset($this->request->data['conditions'])? $this->request->data['conditions'] : array();
		
		$this->Paginator->settings = array(
			'conditions' => $conditions,
			'limit'	=> $limit, 
			'order' => array(
				$sort_field => $sort_order
			),
			'page' => $page
		);

		$unformated_data = $this->Paginator->paginate();
		$formated_data = $this->__format_datagrid_data($unformated_data);
		$data = array('rows' => $formated_data, 'total' => $this->params['paging']['<?php echo $currentModelName; ?>']['count']);
 		$this->set('data', $data);
        $this->set('_serialize', 'data');
	}
/**
 * __format_datagrid_data method
 *
 * @param array $unformated_data unformated data
 * @return array $formated_data formated data
 */
	protected function __format_datagrid_data($unformated_data) {
		$formated_data = array();

		foreach ($unformated_data as $datum) {

			$formated_data[] = $datum;
		}

		return $formated_data;
	}

<?php $compact = array(); ?>
/**
 * <?php echo $admin ?>index method
 *
 * @return void
 */
	public function <?php echo $admin ?>index() {
<?php
		foreach (array('belongsTo', 'hasAndBelongsToMany') as $assoc):
			foreach ($modelObj->{$assoc} as $associationName => $relation):
				if (!empty($associationName)):

					$otherModelName = $this->_modelName($associationName);
					$otherPluralName = $this->_pluralName($associationName);
					echo "\t\t\${$otherPluralName} = \$this->{$currentModelName}->{$otherModelName}->find('list');\n";
					$compact[] = "'{$otherPluralName}'";
				endif;
			endforeach;
		endforeach;
		if (!empty($compact)):
			echo "\t\t\$this->set(compact(" . join(', ', $compact) . "));\n";
		endif;
	?>
	}

/**
 * <?php echo $admin ?>add method
 *
 * @return void
 */
 	public function <?php echo $admin ?>add() {

		$inserted_record = array();
		$errors = array();
		$this-><?php echo $currentModelName; ?>->create();
		
		if ($this-><?php echo $currentModelName; ?>->save($this->request->data)) {
			$message = __('The <?php echo strtolower($singularHumanName); ?> has been saved');
			$result = 'success';
			$inserted_record = $this-><?php echo $currentModelName; ?>->find('first', array(
				'conditions' => array(
					'<?php echo $currentModelName; ?>.id' => $this-><?php echo $currentModelName; ?>->id
				)
			));
		} else {
			$errors = $this-><?php echo $currentModelName; ?>->validationErrors;
			$message =__('The <?php echo strtolower($singularHumanName); ?> could not be saved. Please, try again.');
			$result = 'error';
		}

		$formated_record = $this->__format_datagrid_data(array($inserted_record));
		$data = array('message' =>  $message, 'result' => $result, 'record' => $formated_record[0], 'errors' => $errors);
		$this->set('data', $data);
        $this->set('_serialize', 'data');
	}

/**
 * <?php echo $admin ?>edit method
 *
 * @return void
 */
	public function <?php echo $admin ?>edit() {
		
		$updated_record = array();
		$errors = array();

		if ($this-><?php echo $currentModelName; ?>->save($this->request->data)) {

			$message = __('The <?php echo strtolower($singularHumanName); ?> has been saved');
			$result = 'success';
			$updated_record = $this-><?php echo $currentModelName; ?>->find('first', array(
				'conditions' => array(
					'<?php echo $currentModelName; ?>.id' => $this-><?php echo $currentModelName; ?>->id
				)
			));

		} else {
			$errors = $this-><?php echo $currentModelName; ?>->validationErrors;
			$message =__('The <?php echo strtolower($singularHumanName); ?> could not be saved. Please, try again.');
			$result = 'error';
		}

		$formated_record = $this->__format_datagrid_data(array($updated_record));
		$data = array('message' =>  $message, 'result' => $result, 'record' => $formated_record[0] , 'errors' => $errors);
		$this->set('data', $data);
        $this->set('_serialize', 'data');
	}

/**
 * <?php echo $admin ?>delete method
 *
 * @access public
 * @return void
 */
	public function <?php echo $admin ?>delete() {

		$data = $this->request->data('<?php echo $currentModelName; ?>');
		$action = !empty($data['action']) ? $data['action'] : null;
		$ids = array();
		
		foreach ($data as $id => $value) {
			if (is_array($value) && !empty($value['id'])) {
				$ids[] = $id;
			}
		}

		list($action, $ids) = array($action, $ids);

		if(count($ids) === 0)
		{
			$message = __('No item selected');
			$result = 'error';
		}
		elseif($action == null)
		{
			$message = __('No action selected');
			$result = 'error';
		}
		else
		{
			$processed = $this-><?php echo $currentModelName; ?>->delete($ids);

			if ($processed) {
				$message = __('<?php echo $currentModelName; ?> deleted');
				$result = 'success';
			} else {
				$message = __('An error occured');
				$result = 'error';
			}
		}

		$data =  array('message' =>  $message, 'result' => $result, 'action' => $action, 'ids' => $ids);
		
		$this->set('data', $data);
        $this->set('_serialize', 'data');		
	}
