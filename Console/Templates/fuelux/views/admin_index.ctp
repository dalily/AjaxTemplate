<?php
$underscoredPluginName = Inflector::underscore($plugin);
$header = <<<EOF
echo <?php \$this->Html->css(array(
	'/AjaxTemplate/plugins/bootstrap/css/bootstrap.min.css',
	'/AjaxTemplate/plugins/fontawesome/css/font-awesome.min.css',
	'/AjaxTemplate/plugins/toastr/toastr.min',
	'/AjaxTemplate/plugins/DataTables/css/jquery.dataTables.css',
	'/AjaxTemplate/plugins/bootstrap-datepicker/bootstrap-datepicker3.standalone.min',
	'/AjaxTemplate/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker',
	'/AjaxTemplate/plugins/DataTables/css/DT_bootstrap',
	'AjaxTemplate.admin',
), array('inline' => false));
echo \$this->Html->script(array(
	'/AjaxTemplate/plugins/jquery/jquery.min.js',
	'/AjaxTemplate/plugins/bootstrap/js/bootstrap.min.js',
	'/AjaxTemplate/plugins/bootbox/bootbox.min',
	'/AjaxTemplate/plugins/DataTables/jquery.dataTables.min.js',
	'/AjaxTemplate/plugins/toastr/toastr.min.js',
	'/AjaxTemplate/plugins/moment/moment-with-locales',
	'/AjaxTemplate/plugins/bootstrap-datepicker/bootstrap-datepicker.min.js',
	'/AjaxTemplate/plugins/bootstrap-datetimepicker/bootstrap-datetimepicker',
), array('inline' => false));

\$this->viewVars['title_for_layout'] = __d('$underscoredPluginName', '$pluralHumanName');

\$this->Html
	->addCrumb('', '/admin', array('icon' => 'home'))
	->addCrumb(__d('$underscoredPluginName', '${pluralHumanName}'), array('action' => 'index'));
?>\n
EOF;
echo $header;

?>

<script>

<?php echo "<?php  \$this->Html->scriptStart(array('inline' => false, 'block' => 'scriptBottom')); ?>"; ?>

var <?php echo $singularVar; ?>Crud = {
		datagrid : {},
		init : function(){
		     <?php echo $singularVar; ?>Crud.datagrid = $('#<?php echo $singularVar; ?>_datagrid').DataTable({
		        "processing": true,
		        "serverSide": true,
		        "language": {
					"lengthMenu": "_MENU_ Enregistrements par page",
					"processing": '<?php echo "<?php echo \$this->Html->image(\"loading-spinner-grey.gif\"); ?>"; ?><span>&nbsp;&nbsp;Loading...</span>',
					"sInfo": "",
					"sInfoEmpty": "",
					"zeroRecords" : 'aucun enregistrement trouvé' 
				},
		        "ajax": {
		        	url : '<?php echo "<?php echo \$this->Html->url(array('action' => 'get_datagrid_data', 'ext' => 'json')); ?>"; ?>',
		        	type: "POST",
		 			data : function ( d ) {
					  	var value = $('#<?php echo $modelClass;?>Filter').find('input[type = search]').val();
					  	var column = $('#<?php echo $modelClass;?>Filter').find('.hidden').val();
					  	
					  	if(column && value)
					  	{
					  		d['filter'] = {};
					  		d['filter'][column] = value;
					  	}	
		            }
		        },
				"sort": true,
				"filter": false,
				"columns": [<?php $i = 2; foreach ($fields as $field): $i++;
			if (in_array($field, array('created', 'modified', 'updated'))) continue;
			$isKey = false; echo "\n\t\t\t\t\t{";
			if (!empty($associations['belongsTo'])) {
				foreach ($associations['belongsTo'] as $alias => $details) {
					if ($field === $details['foreignKey']) {
						$isKey = true;
			echo "\n\t\t\t\t\t\ttitle:  '<?php echo __d('{$underscoredPluginName}', '".Inflector::humanize($alias)."'); ?>',\n\t\t\t\t\t\tdata: '{$alias}.{$details['displayField']}',";
						break;
					}
				}
			}
			if ($isKey !== true) { 
			echo "\n\t\t\t\t\t\ttitle: '<?php echo __d('{$underscoredPluginName}', '".Inflector::humanize($field)."'); ?>',\n\t\t\t\t\t\tdata: '{$modelClass}.{$field}',";
			} 
			echo "\n\t\t\t\t\t\tsortable: true";
			if($field == 'id') echo ",";
			//if($field == 'id') echo "\n\t\t\t\twidth : 40"; 
			echo "\n\t\t\t\t\t}";
			if(count($fields) != $i) echo ",";
			endforeach; 
			echo "\n\t\t\t\t{
				title:  '<?php echo __d('request_managment', 'Actions'); ?>',
				data: null,
				sortable: false
			}],\n";

			?>
				"columnDefs": [{
					"targets": [5],
					"width" : "230px",
					render: function (e, type, data, meta)
					{	
						var actions = [{
							'value': 'Détail',
							'attr': {
								'icon': 'folder-open-o',
								'class': "btn btn-xs btn-primary btn-open",
								'action-id': data.<?php echo $modelClass;?>.id
							}
						}];

						actions.push({
							'value': 'Modifier',
							'attr': {
								'icon': 'pencil',
								'class': "btn btn-xs btn-primary btn-edit",
								'action-id': data.<?php echo $modelClass;?>.id
							}
						});	

						actions.push({
							'value': 'Supprimer',
							'attr': {
								'icon': 'remove',
								'class': "btn btn-xs btn-danger btn-delete",
								'action-id': data.<?php echo $modelClass;?>.id
							}
						});	
						return createButtonGroup(actions);
					}
				}],
		    });			
		},
		showDetail : function (elm) {
	        var tr = $(elm).closest('tr');
	        var row = <?php echo $singularVar; ?>Crud.datagrid.row( tr );
	 
	        if ( row.child.isShown() ) {
	            // This row is already open - close it
	            row.child.hide();
	            tr.removeClass('shown');
	        }
	        else {
	            // Open this row
	            row.child( <?php echo $singularVar; ?>Crud.detail(row.data()) ).show();
	            tr.addClass('shown');
	        }
	    },
		detail : function(d){

		    return '<table id = "<?php echo $singularVar; ?>_row_detail" cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">'+
			<?php $i = 2; foreach ($fields as $field): $i++;
			if (in_array($field, array('created', 'modified', 'updated'))) continue;
			$isKey = false; echo "\t\t\t";
			echo "\n\t\t\t\t'<tr>'+";
			if (!empty($associations['belongsTo'])) {
				foreach ($associations['belongsTo'] as $alias => $details) {
					if ($field === $details['foreignKey']) {
						$isKey = true;
						echo "\n\t\t\t\t'<td><?php echo __d('{$underscoredPluginName}', '".Inflector::humanize($alias)."'); ?></td>'+";
						break;
					}
				}
			}

			if ($isKey !== true) { 
				echo "\n\t\t\t\t'<td><?php echo __d('{$underscoredPluginName}', '".Inflector::humanize($field)."'); ?></td>'+";
			} 
			echo "\n\t\t\t\t\t'<td>'+d.{$modelClass}.{$field}+'</td>'+";
			echo "\n\t\t\t\t'</tr>'+";
			endforeach; 
			echo "\n\t\t\t\t'</table>';";
			?>	
		},
		addRow : function(row){
			var formURL = $('#add_<?php echo $singularVar; ?>_form').attr("action");
			$('#<?php echo $singularVar; ?>_datagrid').trigger('dialogLoader', 'show');
			$.ajax(
			{
				url : formURL,
				type: "POST",
				data : postData,
				success:function(response, textStatus, jqXHR) 
				{
					if(response.result == 'success')
					{
						<?php echo $singularVar; ?>Crud.datagrid.row.add(response.record).draw();
						toastr.success(response.message);
						$('#add_<?php echo $singularVar; ?>_form').find('input, select').val('');
					}
					else
					{
						toastr.error(response.message); 
					}
					$('#<?php echo $singularVar; ?>_datagrid').trigger('dialogLoader', 'hide');
					$('#<?php echo $modelClass; ?>AddDialog').modal('hide'); 
				},
				error: function(jqXHR, textStatus, errorThrown) 
				{
					$('#<?php echo $singularVar; ?>_datagrid').trigger('dialogLoader', 'hide');
					toastr.error("<?php echo "<?php echo __d('{$underscoredPluginName}', 'An error occured please try again!'); ?>"; ?>");
				}
			});
			
		},
		deleteRow : function(id, tr){

			$('#<?php echo $singularVar; ?>_datagrid').trigger('loader', 'show');
			$.ajax(
			{
				url : '<?php  echo Router::url(array('action' => 'delete', 'ext' => 'json'));?>',
				type: "POST",
				data : {id : id},
				success:function(response, textStatus, jqXHR) 
				{
					if(response.result == 'success')
					{
						<?php echo $singularVar; ?>Crud.datagrid.row(tr).remove().draw( false );
						toastr.success(response.message);
					}
					else
					{
						toastr.error(response.message); 
					}
					$('#<?php echo $singularVar; ?>_datagrid').trigger('loader', 'hide');
				},
				error: function(jqXHR, textStatus, errorThrown) 
				{
					$('#<?php echo $singularVar; ?>_datagrid').trigger('loader', 'hide');
					toastr.error("<?php echo "<?php echo __d('{$underscoredPluginName}', 'An error occured please try again!'); ?>"; ?>");
				}
			});
		},
		updateRow : function(data){
			var formURL = $('#edit_<?php echo $singularVar; ?>_form').attr("action");
			$('#<?php echo $singularVar; ?>_datagrid').trigger('dialogLoader', 'show')
			$.ajax(
			{
				url : formURL,
				type: "POST",
				data : data,
				success:function(response, textStatus, jqXHR) 
				{
					var tr = $('[action-id = '+response.record.<?php echo $modelClass; ?>.id+']').closest('tr'); 
					if(response.result == 'success')
					{
						<?php echo $singularVar; ?>Crud.datagrid.row(tr).data( response.record ).draw();
						toastr.success(response.message);
					}
					else
					{
						toastr.error(response.message); 
					}
					 $('#<?php echo $singularVar; ?>_datagrid').trigger('dialogLoader', 'hide'); 
					$('#<?php echo $modelClass; ?>EditDialog').modal('hide'); 
				},
				error: function(jqXHR, textStatus, errorThrown) 
				{
	  				$('#<?php echo $singularVar; ?>_datagrid').trigger('dialogLoader', 'hide');
	  				toastr.error("<?php echo "<?php echo __d('{$underscoredPluginName}', 'An error occured please try again!'); ?>"; ?>");
				}
			});
		}
	}

	jQuery(document).ready(function() {
		<?php echo $singularVar; ?>Crud.init();

	 	$('#<?php echo $singularVar; ?>_datagrid tbody').on('click', '.btn-open', function(){
	 		<?php echo $singularVar; ?>Crud.showDetail(this)
	 	});

		//datagrid ajax form 
		$('.<?php echo $pluralVar; ?>').on('click', '.btn-delete', function(e)
		{
			var id = $(this).attr("action-id");
			var tr = $(this).closest("tr");
			
			if(confirm("<?php echo "<?php echo __d('{$underscoredPluginName}', 'Are you sure'); ?>"; ?>")){
				<?php echo $singularVar; ?>Crud.deleteRow(id, tr);
			}
			
			e.preventDefault();

			return false;
		});

		//datagrid ajax add form 
		$('#add_<?php echo $singularVar; ?>_form').submit(function(e)
		{
			var postData = $(this).serializeArray();
			<?php echo $singularVar; ?>Crud.addRow(postData);
			e.preventDefault();

			return false;
		});

		//datagrid ajax edit form 
		$('#edit_<?php echo $singularVar; ?>_form').submit(function(e)
		{
			var postData = $(this).serializeArray();
			<?php echo $singularVar; ?>Crud.updateRow(postData);
			e.preventDefault();

			return false;
		});

		$(document).on('click', '.btn-edit', function(event){
			$('#edit_<?php echo $singularVar; ?>_form').find('input, select').val('');
			var data = <?php echo $singularVar; ?>Crud.datagrid.row($(this).closest('tr')).data();
			console.log(data);
			$('#edit_<?php echo $singularVar; ?>_form input, #edit_<?php echo $singularVar; ?>_form select').each(function(){
				
				if($(this).attr('id'))
				{	
					regex = /\[([^\]]*)]/g;
					keys = [];
					
					while (m = regex.exec($(this).attr('name'))) {
					  keys.push(m[1]);
					}

					if(data.hasOwnProperty(keys[0]) && data[keys[0]][keys[1]]){
						$(this).val(data[keys[0]][keys[1]]);
					}
				}
			});

			$('#<?php echo $modelClass; ?>EditDialog').modal('show');
			
			event.preventDefault();
			return false;
		});

		$('#<?php echo $modelClass; ?>Filter').on('click', 'a', function (e) {
		  	var field_name =  $(this).parent().attr('data-value')
		  	var field_label = $(this).text();
		  	$(this).closest('.datagrid-search').find('.hidden').val(field_name);
		  	$(this).closest('.datagrid-search').find('.selected-label').text(' ' +field_label);
		  	$(this).closest('.datagrid-search').find('input[type = search]').val("");
		  	$(this).closest('.datagrid-search').find('input[type = search]').attr('placeholder', 'Search by '+field_label);
		});

		$('#<?php echo $modelClass; ?>Filter .search').on('click', '.btn', function (e) {
		  	<?php echo $singularVar; ?>Crud.datagrid.ajax.reload();
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

<div class="<?php echo $pluralVar; ?> index">
	<div class="datagrid" id="<?php echo $singularVar; ?>_datagrid_container">
		<div class="datagrid-toolbar">
			<div class="col-xs-12 col-sm-6 col-md-8 no-padding">
				<!-- Button trigger modal -->
				<?php echo "<?php  echo \$this->Croogo->adminAction(\n
						__d('{$underscoredPluginName}', 'New {$modelClass}'), '#',\n
						array('button' => 'primary', 'data-toggle' => 'modal', 'data-target' =>'#{$modelClass}AddDialog')\n
					);?>"; ?>
			</div>
			<div class="col-xs-6 col-md-4 no-padding">
			  	<div class="datagrid-search" id = "<?php echo $modelClass; ?>Filter">
					<div class="input-group">
						<div class="input-group-btn selectlist" data-resize="auto" data-initialize="selectlist">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								<span class="selected-label">Id</span>
								<span class="caret"></span>
								<span class="sr-only">Toggle Dropdown</span>
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
									?>
					<?php echo "\n\t\t\t\t\t\t\t\t<li data-value=\"{$value}\">\t
									<a href=\"#\">{$fieldLabel}</a>
								</li>"; ?>
						<?php
								}
						}
						?>
						<?php echo "\n\t\t\t\t\t\t\t</ul>\n"?>
							<input class="hidden hidden-field" name="column" readonly="readonly" aria-hidden="true" type="text" value = "<?php echo $modelClass; ?>.id">
						</div>
						<div class="search input-group">
							<input type="search" class="form-control" placeholder="<?php echo "<?php  echo __d('{$underscoredPluginName}', 'Search by Id');  ?>"; ?>"/>
						  	<span class="input-group-btn">
								<button class="btn btn-default" type="button">
							  		<span class="glyphicon glyphicon-search"></span>
							  		<span class="sr-only">
							  		<?php echo "<?php  echo __d('{$underscoredPluginName}', 'Search');  ?>"; ?>
							 		</span>
								</button>
						  	</span>
						</div>
					</div>
			  	</div>
			</div>
			<div class = "clear"></div>
	  	</div>
		<table id="<?php echo $singularVar; ?>_datagrid" class="display table-bordered"></table>
	</div>
</div>

<div class="modal fade" id="<?php echo $modelClass; ?>AddDialog" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="<?php echo $modelClass; ?>Edition" data-backdrop = "static">
 
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
						<?php echo "<?php  echo __d('{$underscoredPluginName}', 'Close');  ?>"; ?>

					</span>
				</button>
				<h4 class="modal-title">
					<?php echo "<?php  echo __d('{$underscoredPluginName}', 'Add {$modelClass}');  ?>"; ?>
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
						switch ($schema[$field]['type']) {
							case 'datetime':
								echo "\t\t\t\techo \$this->Form->input('{$field}', array(
					'label' => __d('{$underscoredPluginName}', '{$fieldLabel}'),
					'id' => '{$id}',
					'type' => 'text',
					'class' => 'datetimepicker'
				));\n";		break;
							case 'date':
								echo "\t\t\t\techo \$this->Form->input('{$field}', array(
					'label' => __d('{$underscoredPluginName}', '{$fieldLabel}'),
					'id' => '{$id}',
					'type' => 'text',
					'class' => 'datepicker'
				));\n";
							break;
							default:
								echo "\t\t\t\techo \$this->Form->input('{$field}', array(
					'label' => __d('{$underscoredPluginName}', '{$fieldLabel}'),
					'id' => '{$id}'
				));\n";
						}
					}
				}

			echo "\t\t\t?>\n";
?>
			</div>
		  	<div class="loader" data-initialize="loader"></div>
			<div class="modal-footer">
				<?php echo "<?php \n
				echo \$this->Html->link(__d('{$underscoredPluginName}', 'Cancel'), '#', array('class' => 'btn btn-danger', 'data-dismiss' => 'modal')); \n 
				?>"; ?>
				<?php echo "<?php \n
				echo \$this->Form->button(__d('{$underscoredPluginName}', 'Save'), array('class' => 'btn btn-primary'));\n
				?>"; ?>
			</div>
		</div><!-- /.modal-content -->
  	</div><!-- /.modal-dialog -->
<?php echo "<?php echo \$this->Form->end(); ?>"; ?>
</div><!-- /.modal -->

<div class="modal fade" id="<?php echo $modelClass; ?>EditDialog" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="<?php echo $modelClass; ?>Edition" backdrop = "static">
	
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
						<?php echo "<?php  echo __d('{$underscoredPluginName}', 'Close');  ?>"; ?>
					</span>
				</button>
				<h4 class="modal-title">
					<?php echo "<?php  echo __d('{$underscoredPluginName}', 'Edit {$modelClass}');  ?>"; ?>
				</h4>
	  		</div>
			<div class="modal-body">
<?php
				echo "\t\t\t<?php\n";
				echo "\t\t\t\techo \$this->Form->input('{$primaryKey}');\n";
				echo "\t\t\t\t\$this->Form->inputDefaults(array('label' => false, 'class' => 'span10'));\n";
				
				foreach ($fields as $field) {
					$options = array();
					if ($field == $primaryKey) {
						continue;
					} elseif (!in_array($field, array('created', 'modified', 'updated'))) {
						$fieldLabel = Inflector::humanize(str_replace('_id', '', $field));
						$id = 'Edit'.$modelClass.Inflector::camelize($field);
						switch ($schema[$field]['type']) {
							case 'datetime':
								echo "\t\t\t\techo \$this->Form->input('{$field}', array(
					'label' => __d('{$underscoredPluginName}', '{$fieldLabel}'),
					'id' => '{$id}',
					'type' => 'text',
					'class' => 'datetimepicker'
				));\n";		break;
							case 'date':
								echo "\t\t\t\techo \$this->Form->input('{$field}', array(
					'label' => __d('{$underscoredPluginName}', '{$fieldLabel}'),
					'id' => '{$id}',
					'type' => 'text',
					'class' => 'datepicker'
				));\n";
							break;
							default:
								echo "\t\t\t\techo \$this->Form->input('{$field}', array(
					'label' => __d('{$underscoredPluginName}', '{$fieldLabel}'),
					'id' => '{$id}'
				));\n";
						}
					}
				}

			echo "\t\t\t?>\n";
?>
			</div>
	  		<div class="loader"  data-initialize="loader"></div>
			<div class="modal-footer">
				<?php echo "<?php \n
				echo \$this->Html->link(__d('{$underscoredPluginName}', 'Cancel'), '#', array('class' => 'btn btn-danger', 'data-dismiss' => 'modal')); \n 
				?>"; ?>
				<?php echo "<?php \n
				echo \$this->Form->button(__d('{$underscoredPluginName}', 'Save'), array('class' => 'btn btn-primary'));\n
				?>"; ?>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
<?php echo "<?php echo \$this->Form->end(); ?>"; ?>
</div><!-- /.modal -->