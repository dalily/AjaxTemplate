/*
 * Fuel UX Repeater - List View Plugin
 * https://github.com/ExactTarget/fuelux
 *
 * Copyright (c) 2014 ExactTarget
 * Licensed under the BSD New license.
 */

// -- BEGIN UMD WRAPPER PREFACE --

// For more information on UMD visit: 
// https://github.com/umdjs/umd/blob/master/jqueryPlugin.js

(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // if AMD loader is available, register as an anonymous module.
         define(['jquery', 'fuelux/repeater'], factory);
    } else {
        // OR use browser globals if AMD is not present
        factory(jQuery);
    }
}(function ($) {
// -- END UMD WRAPPER PREFACE --

// -- BEGIN MODULE CODE HERE --
		//repeater ajax form 

	if($.fn.repeater){

		$.fn.repeater.Constructor.prototype.render = function( options ) {
			var self = this;
			var viewChanged = false;
			var viewObj = $.fn.repeater.views[ self.currentView ] || {};
			var prevView;

			var start = function() {
				options.preserve = ( options.preserve !== undefined ) ? options.preserve : !viewChanged;
				self.clear( options );
				if ( !self.infiniteScrollingEnabled || ( self.infiniteScrollingEnabled && viewChanged ) ) {
					self.$loader.show().loader( 'play' );
				}
				self.getDataOptions( options, function( opts ) {
					self.options.dataSource.getDataSource( opts, function( data ) {
						self.options.data = data;
						var renderer = viewObj.renderer;
						if ( self.infiniteScrollingEnabled ) {
							self.infiniteScrollingCallback( {} );
						} else {
							self.itemization( data );
							self.pagination( data );
						}
						if ( renderer ) {
							self.runRenderer( self.$canvas, renderer, data, function() {
								if ( self.infiniteScrollingEnabled ) {
									if ( viewChanged || options.clearInfinite ) {
										self.initInfiniteScrolling();
									}
									self.infiniteScrollPaging( data, options );
								}
								self.$loader.hide().loader( 'pause' );
								self.$element.trigger( 'loaded.fu.repeater' );
							} );
						}
					} );
				} );
			};

			options = options || {};

			if ( options.changeView && this.currentView !== options.changeView ) {
				prevView = this.currentView;
				this.currentView = options.changeView;
				this.$element.attr( 'data-currentview', this.currentView );
				viewChanged = true;
				if ( this.infiniteScrollingEnabled ) {
					self.infiniteScrolling( false );
				}
				viewObj = $.fn.repeater.views[ self.currentView ] || {};
				if ( viewObj.selected ) {
					viewObj.selected.call( this, {
						prevView: prevView
					}, function() {
						start();
					} );
				} else {
					start();
				}
			} else {
				start();
			}
		}

		$.fn.repeater.Constructor.prototype.dialogLoader = function( action ) {
			$loader = $( '.modal.in' ).find('.loader');
			if(action == 'show')
				$loader.show().loader( 'play' );
			else
				$loader.hide().loader( 'pause' );
		}
		$.fn.repeater.Constructor.prototype.loader = function( action ) {
			if(action == 'show')
				this.$loader.show().loader( 'play' );
			else
				this.$loader.hide().loader( 'pause' );
		}

		$.fn.repeater.Constructor.prototype.delete = function (ids) {
			var self = this;
			var data, $item;
			var model = self.options.dataSource._model;
			var primary_key = self.options.dataSource._primary_key;

			$.each(ids, function(key, id){

				self.$canvas.find('.repeater-list-items tr').each(function(){
					$item = $(this);
					data = $item.data('item_data') || {};


					if(data[model][primary_key]==id){
						$(this).remove();
					}
				});

				var _data = _.reject(self.options.data.items, function(row){ return row[model][primary_key] == id; });
				var total = self.options.data.count-1;
				start = self.options.data.start;
		        end = self.options.data.end - 1;

		        if(_data.length == 0) end = 0;			
		       	start = (_data.length == 0) ? 0 : start;
				self.$count.text(total);
				self.$start.text(start);
				self.$end.text(end);
				self.options.data.items = _data;
				self.options.data.start = start;
				self.options.data.end = end;
				self.options.data.count = total;
			});
			this.resize();
		};

		$.fn.repeater.Constructor.prototype.appendRow = function (row) {

	       var self = this;
	        var _data = this.options.data.items;

			var model = self.options.dataSource._model;
			var primary_key = self.options.dataSource._primary_key;
	        
	        if(this.options.dataSource._action_boutons)
	        {
			        
	            row.checkbox_column = '<input type="hidden" name="data['+model+']['+row[model][primary_key]+']['+primary_key+']" id="'+model+row[model][primary_key]+'Id_" value="0"><input type="checkbox" name="data['+model+']['+row[model][primary_key]+']['+primary_key+']" class="row-select" value="1" id="'+model+row[model][primary_key]+'Id">';
                
                row.action_column = '<div class = "item-actions">';
                
                $.each(this.options.dataSource._action_boutons, function (key, bouton) {
                    row.action_column += bouton.replace('Placeholder', row[model][primary_key]);
                });
                row.action_column += '</div>';
	        }
			
			var renderRow = function(row){
				var $item = $('<tr data-container="true"></tr>');
				$item.addClass('selectable selected');
				$item.attr('tabindex', 0);
				$item.attr('row-id', row[model][primary_key]);	// allow items to be tabbed to / focused on
				$item.data('item_data', row);
				
				$item.on('click.fu.repeater-list', function() {
					var $row = $(this);
					if($row.hasClass('selected')){
						$row.removeClass('selected');
						self.$element.trigger('itemDeselected.fu.repeater', $row);
					}else{
						if(self.options.list_selectable!=='multi'){
							self.$canvas.find('.repeater-list-items tr.selected').each(function(){
								$(this).removeClass('selected');
								self.$element.trigger('itemDeselected.fu.repeater', $(this));
							});
						}
						$row.addClass('selected');
						self.$element.trigger('itemSelected.fu.repeater', $row);
						//$row.find('td:first').find('input:checkbox').prop('checked', true);
					}
				});
				// allow selection via enter key
				$item.keyup(function (e) {
					if (e.keyCode === 13) {
						$item.trigger('click.fu.repeater-list');
					}
				});

				var renderCell =  function(content, column){
					var cssClass = column.cssClass;
					var $td = $('<td></td>');
					var width = column._auto_width;

					if(column.visible === false){
						$td.addClass('no-visible');
					}
					
					$td.addClass(((cssClass!==undefined) ? cssClass : '')).append(content);
					if(width!==undefined){
						$td.width(width);
					}

					return $td;
				};

				$.each(self.options.data.columns, function (index, column) {
					field = column.property;
					tmp = field.split('.');
					if(tmp.length > 1)
						$item.append(renderCell(row[tmp[0]][tmp[1]], column));
					else
						$item.append(renderCell(row[field], column));
				});

				return $item;
			};
			this.$canvas.find('.repeater-list-items tr.selected').removeClass('selected');
			this.$canvas.find( '.repeater-list-items tbody' ).append(renderRow(row));
			this.resize();
			
			_data.push(row);
			var total = this.options.data.count+1;
			var start = this.options.data.start;
	        var end = this.options.data.end + 1;		

			this.$count.text(total);
			this.$start.text(start);
			this.$end.text(end);
			this.options.data.items = _data;
			this.options.data.start = start;
			this.options.data.end = end;
			this.options.data.count = total;
		};

		$.fn.repeater.Constructor.prototype.updateRow = function (updated_row) {
			var self = this;
			var idField = self.options.dataSource._idField;
			var model = self.options.dataSource._model;
			var primary_key = self.options.dataSource._primary_key;

			$item = $('tr[row-id='+updated_row[model][primary_key]+']');
			var _data = this.options.data.items;
			var target_row = _.find(_data, function(row){ return row[model][primary_key] == updated_row[model][primary_key]; });
			_.map(_data, function(obj, key){(obj[model][primary_key] == updated_row[model][primary_key])? updated_row : obj; });

			this.options.data.items = _data;		
			var index = 0;

			$item.find('td').each(function () {
				
				if(self.options.data.columns[index].property != 'action_column' && 
					self.options.data.columns[index].property != 'checkbox_column' &&
					self.options.data.columns[index].property != idField
				)
				{
					field = self.options.data.columns[index].property;
					tmp = field.split('.');

					$(this).html(updated_row[tmp[0]][tmp[1]]);
				}
				index++;
			});
	
			this.$canvas.find('.repeater-list-items tr.selected').removeClass('selected');
			$item.addClass('selected');
			$item.data('item_data', updated_row);
			this.resize();	
		}
		
		$.fn.repeater.Constructor.prototype.clearSelectedItems = function(){
			this.$canvas.find('.repeater-list-check').remove();
			this.$canvas.find('.repeater-list-items tr.selected').removeClass('selected');
		};

		$.fn.repeater.Constructor.prototype.getSelectedItems = function(){
			var selected = [];
			this.$canvas.find('.repeater-list-items tr.selected').each(function(){
				var $item = $(this);
				selected.push({ data: $item.data('item_data'), element: $item });
			});
			return selected;
		};

		$.fn.repeater.Constructor.prototype.setSelectedItems = function(items, force){
			var selectable = this.options.list_selectable;
			var self = this;
			var data, i, $item, l;

			var eachFunc = function(){
				$item = $(this);
				data = $item.data('item_data') || {};
				if(data[items[i].property]===items[i].value){
					selectItem($item, items[i].selected);
				}
			};

			var selectItem = function($itm, select){
				select = (select!==undefined) ? select : true;
				if(select){
					if(!force && selectable!=='multi'){
						self.clearSelectedItems();
					}
					if(!$itm.hasClass('selected')){
						$itm.addClass('selected');
						//$itm.find('td:first').prepend('<div class="repeater-list-check"><span class="glyphicon glyphicon-ok"></span></div>');
						//$itm.find('td:first').find('input:checkbox').trigger('click');
					}
				}else{
					//$itm.find('.repeater-list-check').remove();
					$itm.removeClass('selected');
					//$itm.find('td:first').find('input:checkbox').trigger('click');
				}
			};

			if(!$.isArray(items)){
				items = [items];
			}
			if(force===true || selectable==='multi'){
				l = items.length;
			}else if(selectable){
				l = (items.length>0) ? 1 : 0;
			}else{
				l = 0;
			}
			for(i=0; i<l; i++){
				if(items[i].index!==undefined){
					$item = this.$canvas.find('.repeater-list-items tr:nth-child(' + (items[i].index + 1) + ')');
					if($item.length>0){
						selectItem($item, items[i].selected);
					}
				}else if(items[i].property!==undefined && items[i].value!==undefined){
					//lint demanded this function not be within this loop
					this.$canvas.find('.repeater-list-items tr').each(eachFunc);
				}
			}
		};

		$.fn.repeater.defaults = $.extend({}, $.fn.repeater.defaults, {
			list_columnRendered: null,
			list_columnSizing: false,
			list_columnSyncing: true,
			list_infiniteScroll: false,
			list_noItemsHTML: '',
			list_selectable: true,
			list_sortClearing: false,
			list_rowRendered: null,
			data: [],
			total : 0
		});

		$.fn.repeater.views.list = {
			dataOptions: function(opts, callback){
				if(this.list_sortDirection){
					opts.sortDirection = this.list_sortDirection;
				}
				if(this.list_sortProperty){
					opts.sortProperty = this.list_sortProperty;
				}
				callback(opts);
			},
			initialize: function(helpers, callback){
				this.list_sortDirection = null;
				this.list_sortProperty = null;
				callback();
			},
			selected: function(helpers, callback){
				var infScroll = this.options.list_infiniteScroll;
				var opts;

				this.list_firstRender = true;
				this.$loader.addClass('noHeader');

				if(infScroll){
					opts = (typeof infScroll === 'object') ? infScroll : {};
					this.infiniteScrolling(true, opts);
				}

				callback({});
			},
			renderer: {
				complete: function(helpers, callback){
					columnSyncing.call(this, helpers, callback);
					$(this.$element.attr('id')).trigger('onLoadDatagrid');
					$(document).trigger('onLoadDatagrid');
				},
				nested: [
					{
						complete: function(helpers, callback){
							var auto = [];
							var self = this;
							var i, l, newWidth, taken;

							if(!this.options.list_columnSizing || this.list_columnsSame){
								callback();
							}else{
								i = 0;
								taken = 0;
								helpers.item.find('td').each(function(){
									var $col = $(this);
									
									var isLast = ($col.next('td').next('td').length===0 && self.list_columns[i].property != 'action_column') ? true : false;
									var width;
									
									if(self.list_columns[i].width!==undefined){
										width = self.list_columns[i].width;
										$col.width(width);
										taken +=  $col.outerWidth();
										if(!isLast){
											self.list_columns[i]._auto_width = width;

										}else{
											$col.width('');
										}
									}else{
										auto.push({ col: $col, index: i, last: isLast });
									}
									i++;
								});

								l=auto.length;
								if(l>0){
									newWidth = Math.floor((this.$canvas.width() - taken) / l);
									for(i=0; i<l; i++){
										if(!auto[i].last){
											auto[i].col.width(newWidth);
											this.list_columns[auto[i].index]._auto_width = newWidth;
										}
									}
								}
								callback();
							}
						},
						render: function(helpers, callback){
							var differentColumns = function(oldCols, newCols){
								var i, j, l;
								if(!oldCols){ return true; }
								if(!newCols){ return false; }
								for(i=0, l=newCols.length; i<l; i++){
									if(!oldCols[i]){
										return true;
									}else{
										for(j in newCols[i]){
											if(oldCols[i][j]!==newCols[i][j]){
												return true;
											}
										}
									}
								}
								return false;
							};

							if(this.list_firstRender || differentColumns(this.list_columns, helpers.data.columns)){
								this.$element.find('.repeater-list-header').remove();
								this.list_columns = helpers.data.columns;
								this.list_columnsSame = false;
								this.list_firstRender = false;
								this.$loader.removeClass('noHeader');
								callback({ action: 'prepend', item: '<table class="table"  role="grid" ><thead class = "repeater-list-header" data-preserve="deep" aria-readonly="true"><tr data-container="true"></tr></thead><tbody class="repeater-list-items" data-container="true" aria-readonly="true"></tbody></table>' });
							}else{
								this.list_columnsSame = true;
								callback({ skipNested: true });
							}
						},
						nested: [
							{
								render: function(helpers, callback){
									var chev = 'glyphicon-chevron';
									var chevDown = chev + '-down';
									var chevUp = chev + '-up';
									var index = helpers.index;
									var self = this;
									var subset = helpers.subset;
									var cssClass, $item, sortable, $span;

									cssClass = subset[index].cssClass;
									$item = $('<td><span class="glyphicon"></span></td>');
									$item.addClass(((cssClass!==undefined) ? cssClass : '')).prepend(subset[index].label);
									$span = $item.find('span.glyphicon:first');

									sortable = subset[index].sortable;
									visible = subset[index].visible;

									if(visible === false){
										$item.addClass('no-visible');
									}

									if(sortable){
										$item.addClass('sortable');
										$item.on('click.fu.repeater-list', function(){
											self.list_sortProperty = (typeof sortable === 'string') ? sortable : subset[index].property;
											if($item.hasClass('sorted')){
												if($span.hasClass(chevUp)){
													$span.removeClass(chevUp).addClass(chevDown);
													self.list_sortDirection = 'desc';
												}else{
													if(!self.options.list_sortClearing){
														$span.removeClass(chevDown).addClass(chevUp);
														self.list_sortDirection = 'asc';
													}else{
														$item.removeClass('sorted');
														$span.removeClass(chevDown);
														self.list_sortDirection = null;
														self.list_sortProperty = null;
													}
												}
											}else{
												helpers.container.find('td').removeClass('sorted');
												$span.removeClass(chevDown).addClass(chevUp);
												self.list_sortDirection = 'asc';
												$item.addClass('sorted');
											}
											self.render({ clearInfinite: true, pageIncrement: null });
										});
									}
									if(subset[index].sortDirection==='asc' || subset[index].sortDirection==='desc'){
										helpers.container.find('td').removeClass('sorted');
										$item.addClass('sortable sorted');
										if(subset[index].sortDirection==='asc'){
											$span.addClass(chevUp);
											this.list_sortDirection = 'asc';
										}else{
											$span.addClass(chevDown);
											this.list_sortDirection = 'desc';
										}
										this.list_sortProperty = (typeof sortable === 'string') ? sortable : subset[index].property;
									}

									callback({ item: $item });
								},
								repeat: 'data.columns'
							}
						]
					},
					{
						after: function(helpers, callback){
							var canvas = this.$canvas;
							var header = canvas.find('.repeater-list-header');
							if(this.staticHeight){
								helpers.item.height(canvas.height()-header.outerHeight());
							}
							callback();
						},
						render: function(helpers, callback){
							var $item = this.$canvas.find('.repeater-list-items');
							var obj = {};
							var $empty;
							if($item.length>0){
								obj.action = 'none';
							}else{
								$item = $('<div class="" data-infinite="true"><table ></table></div>');
							}
							obj.item = $item;
							if(helpers.data.items.length<1){
								obj.skipNested = true;
								$empty = $('<tr class="empty"><td></td></tr>');
								$empty.find('td').append(this.options.list_noItemsHTML);
								$item.find('.repeater-list-items').append($empty);
							}else{
								$item.find('.repeater-list-items tr.empty:first').remove();
							}
							callback(obj);
						},
						nested: [
							{
								complete: function(helpers, callback){
									$(document).trigger('onLoadDatagrid');
									var obj = { container: helpers.container };
									if(helpers.item!==undefined){
										obj.item = helpers.item;
									}
									if(this.options.list_rowRendered){
										this.options.list_rowRendered(obj, function(){
											callback();
										});
									}else{
										callback();
									}
								},
								render: function(helpers, callback){
									var $item = $('<tr data-container="true"></tr>');
									var self = this;
									var model = self.options.dataSource._model;
									var primary_key = self.options.dataSource._primary_key;
									
									if(this.options.list_selectable){
										$item.addClass('selectable');
										$item.attr('tabindex', 0);	// allow items to be tabbed to / focused on
										$item.data('item_data', helpers.subset[helpers.index]);
										$item.attr('row-id', helpers.subset[helpers.index][model][primary_key]);


										$item.on('click.fu.repeater-list', function() {
											var $row = $(this);
											if($row.hasClass('selected')){
												$row.removeClass('selected');
												self.$element.trigger('itemDeselected.fu.repeater', $row);
											}else{
												if(self.options.list_selectable!=='multi'){
													self.$canvas.find('.repeater-list-items tr.selected').each(function(){
														$(this).removeClass('selected');
														self.$element.trigger('itemDeselected.fu.repeater', $(this));
													});
												}
												$row.addClass('selected');
												self.$element.trigger('itemSelected.fu.repeater', $row);
											}
										});
										// allow selection via enter key
										$item.keyup(function (e) {
											if (e.keyCode === 13) {
												$item.trigger('click.fu.repeater-list');
											}
										});
									}

									

									this.list_curRowIndex = helpers.index;
									callback({ item: $item });
								},
								repeat: 'data.items',
								nested: [
									{
										after: function(helpers, callback){
											var obj = { container: helpers.container };
											if(helpers.item!==undefined){
												obj.item = helpers.item;
											}
											if(this.options.list_columnRendered){
												this.options.list_columnRendered(obj, function(){
													callback();
												});
											}else{
												callback();
											}
										},
										render: function(helpers, callback){
											var cssClass = helpers.subset[helpers.index].cssClass;
					
											tmp = helpers.subset[helpers.index].property.split('.');
											
											if(tmp.length == 2)
												var content = helpers.data.items[this.list_curRowIndex][tmp[0]][tmp[1]];
											else
											if(tmp.length == 3)
												var content = helpers.data.items[this.list_curRowIndex][tmp[0]][tmp[1]][tmp[2]];
											else
												var content = helpers.data.items[this.list_curRowIndex][helpers.subset[helpers.index].property];
											var $item = $('<td></td>');
											var width = helpers.subset[helpers.index].width;
											
											if(helpers.subset[helpers.index].visible === false){
												$item.addClass('no-visible');
											}
											
											$item.addClass(((cssClass!==undefined) ? cssClass : '')).append(content);
											if(width!==undefined){
												$item.width(width);
											}
											callback({ item: $item });
										},
										repeat: 'this.list_columns'
									}
								]
							}
						]
					}
				]
			},
			resize: function(helpers, callback){
				columnSyncing.call(this, { data: { items: [''] } }, callback);
			}
		};

		var columnSyncing = function(helpers, callback){
			var i = 0;
			var widths = [];
			var $header, $items;

			if(!this.options.list_columnSyncing || (helpers.data.items.length<1)){
				callback();
			}else{
				$header = this.$element.find('.repeater-list-header:first');
				$items = this.$element.find('.repeater-list-items:first');
				$items.find('tr:first td').each(function(){
					widths.push($(this).outerWidth());
				});
				widths.pop();
				$header.find('td').each(function(){
					if(widths[i]!==undefined){
						$(this).width(widths[i]);
					}
					i++;
				});
				callback();
			}
		};
	}

// -- BEGIN UMD WRAPPER AFTERWORD --
}));
// -- END UMD WRAPPER AFTERWORD --