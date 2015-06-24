<?php

$underscoredPluginName = Inflector::underscore($plugin);
$header = <<<EOF
<?php

echo \$this->Html->css(array(
	'../AjaxTemplate/bootstrap/css/bootstrap',
	'../AjaxTemplate/css/toastr.min',
	'../AjaxTemplate/fuelux/css/fuelux',
	'../AjaxTemplate/css/admin',
), array('inline' => false));
echo \$this->Html->script(array(
	'../AjaxTemplate/js/jquery.min',
	'../AjaxTemplate/js/jquery.tipsy',
	'../AjaxTemplate/js/underscore-min',
	'../AjaxTemplate/js/admin',
	'../AjaxTemplate/js/toastr.min',
	'../AjaxTemplate/bootstrap/js/bootstrap.min',
	'../AjaxTemplate/fuelux/js/combobox',
	'../AjaxTemplate/fuelux/js/selectlist',
	'../AjaxTemplate/fuelux/js/search',
	'../AjaxTemplate/fuelux/js/loader',
	'../AjaxTemplate/fuelux/js/repeater',
	'../AjaxTemplate/fuelux/js/ajax_data_source',
	'../AjaxTemplate/fuelux/js/repeater_list',
), array('inline' => false));

\$this->viewVars['title_for_layout'] = __('$pluralHumanName');


\$actions[] = \$this->Html->link('<i class = "glyphicon glyphicon-pencil"></i>', '#{$modelClass}PlaceholderId',
	array('class' => 'edit', 'tooltip' => __('Edit this item'), 'escape' => false)
);

\$actions[] = \$this->Html->link('<i class = "glyphicon glyphicon-trash"></i>',
	'#{$modelClass}PlaceholderId',
	array('class' => 'delete', 'tooltip' => __('Remove this item'), 'row-action' => 'delete', 'escape' => false, 'confirm-message' => __('Are you sure?'))
);
?>\n
EOF;
echo $header;

?>

<script>

<?php echo "<?php  \$this->Html->scriptStart(array('inline' => false)); ?>"; ?>

$(function(){

	var dataSource = new AjaxDataSource({
		data_url: '<?php echo "<?php echo \$this->Html->url(array('action' => 'get_datagrid_data', 'ext' => 'json')); ?>"; ?>', 
		columns: [<?php $i = 0; foreach ($fields as $field): $i++;
			if (in_array($field, array('created', 'modified', 'updated'))) continue;
			$isKey = false; echo "\n\t\t\t{";
			if (!empty($associations['belongsTo'])) {
				foreach ($associations['belongsTo'] as $alias => $details) {
					if ($field === $details['foreignKey']) {
						$isKey = true;
			echo "\n\t\t\t\tlabel:  '<?php echo __('".Inflector::humanize($alias)."'); ?>',\n\t\t\t\tproperty: '{$alias}.{$details['displayField']}',";
						break;
					}
				}
			}
			if ($isKey !== true) { 
			echo "\n\t\t\t\tlabel: '<?php echo __('".Inflector::humanize($field)."'); ?>',\n\t\t\t\tproperty: '{$modelClass}.{$field}',";
			} 
			echo "\n\t\t\t\tsortable: true";
			if($field == 'id') echo ",";
			echo "\n\t\t\t}";
			if(count($fields) != $i) echo ",";
			endforeach; 
			echo "\n\t\t],\n";
			?>
		checkbox : true,
		data: {},
		idField : '<?php echo $modelClass.".id"; ?>',
		sortField : '<?php echo $modelClass.".id"; ?>',
		action_boutons : <?php echo "<?php echo json_encode(\$actions); ?>"; ?>
	});

	$('#<?php echo $singularVar; ?>_datagrid').repeater({ 
		dataSource: dataSource
	});

	//repeater ajax form 
	$('#list_<?php echo $singularVar; ?>_form').submit(function(e)
	{
		var postData = $(this).serializeArray();
		var formURL = $(this).attr("action");
		$('#<?php echo $singularVar; ?>_datagrid').repeater('loader', 'show');
		$.ajax(
		{
			url : formURL,
			type: "POST",
			data : postData,
			success:function(response, textStatus, jqXHR) 
			{
				if(response.result == 'success' && response.action == 'delete')
				{
					$('#<?php echo $singularVar; ?>_datagrid').repeater('delete', response.ids);
					toastr.success(response.message);
				}
				else
				{
					toastr.error(response.message); 
				}
				$('#<?php echo $singularVar; ?>_datagrid').repeater('loader', 'hide');
			},
			error: function(jqXHR, textStatus, errorThrown) 
			{
				$('#<?php echo $singularVar; ?>_datagrid').repeater('loader', 'hide');
				toastr.error("<?php echo "<?php echo __('An error occured please try again!'); ?>"; ?>");
			}
		});
		e.preventDefault();

		return false;
	});

	//repeater ajax add form 
	$('#add_<?php echo $singularVar; ?>_form').submit(function(e)
	{
		var postData = $(this).serializeArray();
		var formURL = $(this).attr("action");
		$('#<?php echo $singularVar; ?>_datagrid').repeater('dialogLoader', 'show');
		$.ajax(
		{
			url : formURL,
			type: "POST",
			data : postData,
			success:function(response, textStatus, jqXHR) 
			{
				if(response.result == 'success')
				{
					$('#<?php echo $singularVar; ?>_datagrid').repeater('appendRow', response.record);
					toastr.success(response.message);
					$('#add_<?php echo $singularVar; ?>_form').find('input, select').val('');
				}
				else
				{
					toastr.error(response.message); 
				}
				$('#<?php echo $singularVar; ?>_datagrid').repeater('dialogLoader', 'hide');
				$('#<?php echo $modelClass; ?>AddDialog').modal('hide'); 
			},
			error: function(jqXHR, textStatus, errorThrown) 
			{
				$('#<?php echo $singularVar; ?>_datagrid').repeater('dialogLoader', 'hide');
				toastr.error("<?php echo "<?php echo __('An error occured please try again!'); ?>"; ?>");
			}
		});
		e.preventDefault();

		return false;
	});

	//repeater ajax edit form 
	$('#edit_<?php echo $singularVar; ?>_form').submit(function(e)
	{
		var postData = $(this).serializeArray();
		var formURL = $(this).attr("action");
		$('#<?php echo $singularVar; ?>_datagrid').repeater('dialogLoader', 'show')
		$.ajax(
		{
			url : formURL,
			type: "POST",
			data : postData,
			success:function(response, textStatus, jqXHR) 
			{
				if(response.result == 'success')
				{
					$('#<?php echo $singularVar; ?>_datagrid').repeater('updateRow', response.record);
					toastr.success(response.message);
				}
				else
				{
					toastr.error(response.message); 
				}
				 $('#<?php echo $singularVar; ?>_datagrid').repeater('dialogLoader', 'hide'); 
				$('#<?php echo $modelClass; ?>EditDialog').modal('hide'); 
			},
			error: function(jqXHR, textStatus, errorThrown) 
			{
  				$('#<?php echo $singularVar; ?>_datagrid').repeater('dialogLoader', 'hide');
  				toastr.error("<?php echo "<?php echo __('An error occured please try again!'); ?>"; ?>");
			}
		});
		e.preventDefault();

		return false;
	});

	$(document).on('click', 'a.delete', function(event){
		var $el = $(this);
		var checkbox = $el.attr('href');
		var form = $(checkbox).closest('form');
		var action = $el.attr('row-action');
		var confirmMessage = $el.attr('confirm-message');
		if (confirmMessage && !confirm(confirmMessage)) {
			return false;
		}
		$('input[type=checkbox]', form).prop('checked', false);
		$(checkbox).prop("checked", true);
		$('#<?php echo $modelClass; ?>-action select', form).val(action);
		$('#<?php echo $modelClass; ?>ActionBtn').trigger('click');
		event.preventDefault();
		
		return false;
	});

	$(document).on('click', 'a.edit', function(event){
		$('#edit_<?php echo $singularVar; ?>_form').find('input, select').val('');
		var data = $(this).closest('tr').data('item_data');
		
		$('#edit_<?php echo $singularVar; ?>_form input, #edit_<?php echo $singularVar; ?>_form select').each(function(){
			
			if($(this).attr('id'))
			{	
				regex = /\[([^\]]*)]/g;
				keys = [];
				
				while (m = regex.exec($(this).attr('name'))) {
				  keys.push(m[1]);
				}

				if(data.hasOwnProperty(keys[0]) && data[keys[0]].hasOwnProperty(keys[1])){
					
					if($(this).parent().hasClass('checkbox'))
					{
						if($(this).attr('type') == 'checkbox')
						{
							$(this).prop('checked', data[keys[0]][keys[1]]);
							$(this).val(1);
						}
						else
						{
							$(this).val(0);
						}
					}
					else
					{
						$(this).val(data[keys[0]][keys[1]]);
					}
				}
			}
		});

		$('#<?php echo $modelClass; ?>EditDialog').modal('show');
		
		event.preventDefault();
		return false;
	});

	$('#<?php echo $modelClass; ?>EditDialog').on('hidden.bs.modal', function (e) {
	  	$('#edit_<?php echo $singularVar; ?>_form').clearForm();
	});

	$('#<?php echo $modelClass; ?>AddDialog').on('hidden.bs.modal', function (e) {
	  	$('#add_<?php echo $singularVar; ?>_form').clearForm();
	});

	$.fn.clearForm = function() {
		
		return this.each(function() {
			var type = this.type, tag = this.tagName.toLowerCase();
			
			if (tag == 'form')
				return $(':input',this).clearForm();
			if (type == 'text' || type == 'password' || tag == 'textarea')
				this.value = '';
			else if (type == 'checkbox' || type == 'radio')
				this.checked = false;
			else if (tag == 'select')
				this.selectedIndex = -1;
		});
	};
});


<?php echo "<?php \$this->Html->scriptEnd(); ?>"; ?>
</script>

<div class="<?php echo $pluralVar; ?> index fuelux">
	
	<?php echo "<?php  echo \$this->Form->create(\$this->name,
			array('url' => array('action' => 'delete', 'ext' => 'json'), \n
				'id' => 'list_{$singularVar}_form')\n
			);?>"; ?>
	<div class="repeater" id="<?php echo $singularVar; ?>_datagrid">
	  <div class="repeater-header">
		<div class="repeater-header-left">
			<!-- Button trigger modal -->
			<?php echo "<?php  echo \$this->Html->link(\n
					__('New {$modelClass}'), '#',\n
					array('class' => 'btn btn-primary', 'data-toggle' => 'modal', 'data-target' =>'#{$modelClass}AddDialog')\n
				);?>"; ?>
		</div>
		<div class="repeater-header-right">
			<div id="<?php echo $modelClass; ?>-action" class="control-group bulk-action">
				<div class="input inline">
					<select name="data[<?php echo $modelClass; ?>][action]" class="input-level <?php echo $modelClass; ?>ActionList" id="<?php echo $modelClass; ?>Action">
						<option value="delete" selected = "selected">
							<?php echo "<?php echo __('Delete');  ?>"; ?>
						</option>
					</select>
					<button type="submit" id = "<?php echo $modelClass; ?>ActionBtn" value="submit" class="btn btn-default">
						<?php echo "<?php echo __('Submit');  ?>"; ?>
					</button>
				</div>				
			</div>
		</div>
		<div class="repeater-header-right">
		  <span class="repeater-title"></span>
		  <div class="repeater-search">
			<div class="input-group">
				<div class="input-group-btn selectlist" data-resize="auto" data-initialize="selectlist">
				  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					<span class="selected-label"></span>
					<span class="caret"></span>
					<span class="sr-only">
						Toggle Dropdown				
					</span>
				  </button>
				  <ul class="dropdown-menu" role="menu">
<?php
					foreach ($fields as $field) {
						if(!in_array($field, array('created', 'modified', 'updated'))) {
								$value = $modelClass.'.'.$field;
								$fieldLabel = Inflector::humanize($field);
							if (!empty($associations['belongsTo'])) {
								foreach ($associations['belongsTo'] as $alias => $details) {
									if ($field === $details['foreignKey']) {
										$value = $alias.'.'.$details['displayField'];
										$fieldLabel = Inflector::humanize($alias);
									}
								}
							}
							
							echo <<<EOF
							<li data-value="{$value}"><a href="#">$fieldLabel</a></li>
EOF;
						}
				}
?>
				  </ul>
				  <input class="hidden hidden-field" name="column" readonly="readonly" aria-hidden="true" type="text">
				</div>
				<div class="search input-group">
				  <input type="search" class="form-control" placeholder="<?php echo "<?php echo __('Search');  ?>"; ?>"/>
				  <span class="input-group-btn">
					<button class="btn btn-default" type="button">
					  <span class="glyphicon glyphicon-search"></span>
					  <span class="sr-only">
					  	<?php echo "<?php echo __('Search');  ?>"; ?>
					  </span>
					</button>
				  </span>
				</div>
			</div>
		  </div>
		</div>
		<div class="repeater-header-right">
		  <div class="repeater-views">
			 <a href="javascript:void(0)" class = "btn btn-default repeater-reload">
			 	<span class="glyphicon glyphicon-refresh"></span>
			 </a>
		  </div>
		</div>
	  </div>
	  <div class="repeater-viewport">
		<div class="repeater-canvas"></div>
		<div class="loader repeater-loader"></div>
	  </div>
	  <div class="repeater-footer">
		<div class="repeater-footer-left">
		  <div class="repeater-itemization">
			<span><span class="repeater-start"></span> - 
			<span class="repeater-end"></span> 
			<?php echo "<?php echo __('of');  ?>"; ?>
			<span class="repeater-count"></span> 
				<?php echo "<?php echo __('items');  ?>"; ?>
			</span>
			<div class="btn-group selectlist" data-resize="auto">
			  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
				<span class="selected-label">&nbsp;</span>
				<span class="caret"></span>
				<span class="sr-only">
					<?php echo "<?php echo __('Toggle Dropdown');  ?>"; ?>
				</span>
			  </button>
			  <ul class="dropdown-menu" role="menu">
				<li data-value="5"><a href="#">5</a></li>
				<li data-value="10" data-selected="true"><a href="#">10</a></li>
				<li data-value="20"><a href="#">20</a></li>
				<li data-value="50" data-foo="bar" data-fizz="buzz"><a href="#">50</a></li>
				<li data-value="100"><a href="#">100</a></li>
			  </ul>
			  <input class="hidden hidden-field" name="itemsPerPage" readonly="readonly" aria-hidden="true" type="text"/>
			</div>
			<span>
			<?php 
				echo "<?php echo __('Per Page'); ?>";
			?>
			</span>
		  </div>
		</div>
		<div class="repeater-footer-right">
		  <div class="repeater-pagination" style = "float:left;">
			<button type="button" class="btn btn-default btn-sm repeater-prev">
			  <span class="glyphicon glyphicon-chevron-left"></span>
			  <span class="sr-only">
			  	<?php echo "<?php echo __('Previous Page');  ?>"; ?>
			  </span>
			</button>
			<label class="page-label" id="myPageLabel">
				<?php echo "<?php echo __('Page');  ?>"; ?>
			</label>
			<div class="repeater-primaryPaging active">
			  <div class="input-group input-append dropdown combobox">
				<input type="text" class="form-control" aria-labelledby="<?php echo $modelClass; ?>Management">
				<div class="input-group-btn">
				  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
					<span class="caret"></span>
					<span class="sr-only">
						<?php echo "<?php echo __('Toggle Dropdown');  ?>"; ?>
					</span>
				  </button>
				  <ul class="dropdown-menu dropdown-menu-right"></ul>
				</div>
			  </div>
			</div>
			<input type="text" class="form-control repeater-secondaryPaging" aria-labelledby="<?php echo $modelClass; ?>Management">
			<span>
				<?php echo "<?php echo __('of');  ?>"; ?>
				<span class="repeater-pages"></span>
			</span>
			<button type="button" class="btn btn-default btn-sm repeater-next">
			  <span class="glyphicon glyphicon-chevron-right"></span>
			  <span class="sr-only">
			  	<?php echo "<?php echo __('Next Page');  ?>"; ?>
			  </span>
			</button>
		  </div>
		</div>
	  </div>
	</div>
	<?php echo "<?php echo \$this->Form->end();?>"; ?>
</div>

<div class="modal" id="<?php echo $modelClass; ?>AddDialog"  role="dialog" aria-hidden="true"  data-backdrop = "static">
 
	<?php echo "<?php  echo \$this->Form->create('{$modelClass}',
			array('url' => array('action' => 'add', 'ext' => 'json'), \n
				'id' => 'add_{$singularVar}_form')\n
			);?>"; ?>
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">
						<?php echo "<?php echo __('Close');  ?>"; ?>

					</span>
				</button>
				<h4 class="modal-title">
					<?php echo "<?php echo __('Add {$modelClass}');  ?>"; ?>
				</h4>
			</div>

			<div class="modal-body">
<?php
				echo "\t\t\t<?php\n";

				echo "\t\t\t\t\$this->Form->inputDefaults(array('label' => false, 'class' => 'span10'));\n";
				foreach ($fields as $field) {
					$options = array();
					if ($field == $primaryKey) {
						continue;
					} elseif (!in_array($field, array('created', 'modified', 'updated'))) {
						$fieldLabel = Inflector::humanize(str_replace('_id', '', $field));
						$id = 'Add'.$modelClass.Inflector::camelize($field);
						echo <<<EOF
						echo \$this->Form->input('{$field}', array(
							'label' => __('{$fieldLabel}'),
							'id' => '{$id}'
						));\n
EOF;
						}
					}

			echo "\t\t\t?>\n";
?>
			</div>
		  	<div class="loader" data-initialize="loader"></div>
			<div class="modal-footer">
				<?php echo "<?php \n
				echo \$this->Html->link(__('Cancel'), '#', array('class' => 'btn btn-danger', 'data-dismiss' => 'modal')); \n 
				?>"; ?>
				<?php echo "<?php \n
				echo \$this->Form->button(__('Save'), array('class' => 'btn btn-primary'));\n
				?>"; ?>
			</div>
		</div><!-- /.modal-content -->
  	</div><!-- /.modal-dialog -->
<?php echo "<?php echo \$this->Form->end(); ?>"; ?>
</div><!-- /.modal -->

<div class="modal" id="<?php echo $modelClass; ?>EditDialog"  role="dialog" aria-hidden="true" data-backdrop = "static">
	
	<?php echo "<?php  echo \$this->Form->create('{$modelClass}',
			array('url' => array('action' => 'edit', 'ext' => 'json'), \n
				'id' => 'edit_{$singularVar}_form')\n
			);?>"; ?> 

	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">
						<?php echo "<?php echo __('Close');  ?>"; ?>
					</span>
				</button>
				<h4 class="modal-title">
					<?php echo "<?php echo __('Edit {$modelClass}');  ?>"; ?>
				</h4>
	  		</div>
			<div class="modal-body">
<?php
				echo "\t\t\t<?php\n";
				echo "\t\t\t\techo \$this->Form->input('{$primaryKey}');\n";
				echo "\t\t\t\t\$this->Form->inputDefaults(array('label' => false, 'class' => 'span10'));\n";
				foreach ($fields as $field) {
					if ($field == $primaryKey) {
						continue;
					} elseif (!in_array($field, array('created', 'modified', 'updated'))) {
						$fieldLabel = Inflector::humanize(str_replace('_id', '', $field));
						$id = 'Edit'.$modelClass.Inflector::camelize($field);
						echo <<<EOF
				echo \$this->Form->input('{$field}', array(
					'label' => __('{$fieldLabel}'),
					'id' => '{$id}'
				));\n
EOF;
					}
				}

			echo "\t\t\t?>\n";
?>
			</div>
	  		<div class="loader"  data-initialize="loader"></div>
			<div class="modal-footer">
				<?php echo "<?php \n
				echo \$this->Html->link(__('Cancel'), '#', array('class' => 'btn btn-danger', 'data-dismiss' => 'modal')); \n 
				?>"; ?>
				<?php echo "<?php \n
				echo \$this->Form->button(__('Save'), array('class' => 'btn btn-primary'));\n
				?>"; ?>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
<?php echo "<?php echo \$this->Form->end(); ?>"; ?>
</div><!-- /.modal -->