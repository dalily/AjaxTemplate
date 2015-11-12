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
<?php $underscoredPluginName = str_replace(".", '', Inflector::underscore($plugin)); ?>



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

		$limit = "10";
		
		if ( isset( $this->params['data']['start'] ) && $this->params['data']['length'] != '-1' )
		{
			$limit = $this->params['data']['length'];
		}

		$page = "1";
		
		if ( isset( $this->params['data']['start'] ))
		{
			$page = ($this->params['data']['start'] / $limit) + 1;
		}

		$order = "";
		
		if ( isset( $this->params['data']['order'] ) )
		{
			$order = "";

			foreach ($this->params['data']['order'] as $i => $datum)
			{
				if ( $this->params['data']['columns'][$datum['column']]['orderable'] == "true" )
				{
					if(!empty($order)) $order .= ", ";
					$order .= "".$this->params['data']['columns'][$datum['column']]['data']." ".$datum['dir'];
				}
			}
		}

		if($order == "") $order = "<?php echo $currentModelName; ?>.id DESC";
		
		$conditions = array();
		
		if ( isset($this->params['data']['filter']))
		{
			$conditions = array($this->params['data']['filter']);
		}

		$this->Paginator->settings = array(
			'conditions' => $conditions,
			'limit' => $limit,
			'page' => $page,
			'order' => $order
		);

		$datum = $this->Paginator->paginate('<?php echo $currentModelName; ?>');

		$data = array(
			"draw" => (isset($this->params['data']['draw']))? $this->params['data']['draw'] : 1, 
			"recordsTotal" => $this->params['paging']['<?php echo $currentModelName; ?>']['count'], 
			"recordsFiltered" => $this->params['paging']['<?php echo $currentModelName; ?>']['count'],
    		"data" => $datum
		);
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

		$id = (isset($this->request->data['id']))? $this->request->data['id'] : -1;

		if ($this-><?php echo $currentModelName; ?>->delete($id)) {
			$message = __('Request deleted');
			$result = 'success';
		} else {
			$message = __('An error occured');
			$result = 'error';
		}

		$data =  array('message' =>  $message, 'result' => $result, 'id' => $id);
		
		$this->set('data', $data);
        $this->set('_serialize', 'data');
	}
