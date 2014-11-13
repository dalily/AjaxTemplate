AjaxDataSource = function(options) {
    this._checkbox = options.checkbox || false;
    this._formatter = options.formatter;
    this._columns = options.columns;
    this.delays = options.delay || [100, 200, 300, 400];
    self._total = options.total || 0;
    this._data = options.data || [];
    this._action_boutons = options.action_boutons;
    this._sortField = options.sortField;
    this._idField = options.idField;
    this._model = '';
    this._primary_key = 'id';
    // allows us to pass in the URL to get data from via Ajax
    this._data_url = options.data_url;

    // allows us to initially load data via Ajax
    this._initial_load_from_server = options.initial_load_from_server || true;

    // Instruct the DataGrid to always load data from the server
    this._always_load_from_server = options.always_load_from_server || false;

    // An optional parameter that instructs the DataGrid to not reload
    // after an event. Set this to `true` on an instance of your data source
    // to enable
    this.reload_data_from_server = true;

    var tmp = this._idField.split('.');
        
    if(tmp.length > 1)
    {
        this._model = tmp[0];
        this._primary_key = tmp[1]
    }    
    if(!this._checkbox)
    {
        $('#'+this._model+'-action').hide();
    }
}

AjaxDataSource.prototype = {
    columns: function() {

        var columns = $.extend(true, [], this._columns);
        
        if(this._action_boutons)
        {
            columns.push({property: 'action_column', label: 'Actions', sortable: false, cssClass : 'action-col', width:100});
        }
        
        if(this._checkbox)
        {
            columns.unshift({
                label: '<input type="hidden" name="data['+this._model+'][checkAll]" id="'+this._model+'CheckAll_" value="0"><input type="checkbox" name="data['+this._model+'][checkAll]" value="1" id="CheckAll">',
                property: 'checkbox_column',
                sortable: false,
                width:30
            });
        }
        else
        {
            columns.unshift({
                label: '<input type="hidden" name="data['+this._model+'][checkAll]" id="'+this._model+'CheckAll_" value="0"><input type="checkbox" name="data['+this._model+'][checkAll]" value="1" id="CheckAll">',
                property: 'checkbox_column',
                sortable: false,
                visible : false
            });        
        }

        return columns;
    },    
    getDataSource :  function(options, callback){
        var self = this;
        setTimeout(function()
        {    
            var data = $.extend(true, [], self._data);
            var count = data.length;
            var total = data.length;
            var sort_field = options.sortProperty || self._sortField;;
            var sort_order = options.sortDirection || 'ASC';
            var conditions = {};
            
            if(options.search){
                search = options.search.toLowerCase();
                var column = $('.repeater-search').find('.selectlist').selectlist('selectedItem').value;
                conditions[column] = search;
            }

            // reload the DataGrid via ajax if any of these conditions are true
            if (self.reload_data_from_server || self._initial_load_from_server || self._always_load_from_server)
            {

                $.ajax(self._data_url, {
                    dataType: 'json',
                    async: false,
                    type: 'POST',
                    data : {
                        sort : sort_field,
                        order : sort_order,
                        page : options.pageIndex+1, 
                        limit : options.pageSize,
                        conditions : (conditions)? conditions : {}  
                    }
                }).done(function(json) {
                    data = json['rows'];
                    total = json['total'];
                });

                // After data has been loaded via ajax, set these optional parameter back to their original state
                self.reload_data_from_server = false;
            } 

            var items = data;

            var resp = {
                count: total,
                items: items,
                page: options.pageIndex,
                pages : Math.ceil(total / options.pageSize)
            };

            var i, l;

            i = options.pageIndex * (options.pageSize || 50);
            l = i + (options.pageSize || 50);
            l = (l <= resp.count) ? l : resp.count;
            resp.start = i + 1;
            resp.end = l;

            resp.columns = self.columns();

            $.each(data, function (index, item) {
                item.checkbox_column = '<input type="hidden" name="data['+self._model+']['+item[self._model][self._primary_key]+']['+self._primary_key+']" id="'+self._model+item[self._model][self._primary_key]+'Id_" value="0"><input type="checkbox" name="data['+self._model+']['+item[self._model][self._primary_key]+']['+self._primary_key+']" class="row-select" value="1" id="'+self._model+item[self._model][self._primary_key]+'Id">';
            });

            if(self._action_boutons)
            {
                $.each(data, function (index, item) {
                    item.action_column = '<div class = "item-actions">';
                    $.each(self._action_boutons, function (key, bouton) {
                        item.action_column += bouton.replace('Placeholder', item[self._model][self._primary_key]);
                    });
                    item.action_column += '</div>';
                });
            }

            if (self._formatter)
                self._formatter(data);

            callback(resp);
        }, self.delays[Math.floor(Math.random() * 4)]);
    },
    filtering : function(options, data){
        var items = data;
        var search;
        
        if(options.search){
            search = options.search.toLowerCase();
            var found = false;
            items = _.filter(items, function(item){
                var result = _.filter(Object.keys(item), function(field){ 

                    if(item[field])
                        return (item[field].toLowerCase().search(options.search.toLowerCase())>=0)
                });

                return result.length > 0;
            });
        }
        if(options.sortProperty){
            items = _.sortBy(items, function(item){
                if(options.sortProperty==='id' || options.sortProperty==='height' || options.sortProperty==='weight'){
                    return parseFloat(item[options.sortProperty]);
                }else{
                    return item[options.sortProperty];
                }
            });
            if(options.sortDirection==='desc'){
                items.reverse();
            }
        }

        return items;
    }
}

 