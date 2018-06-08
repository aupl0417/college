/***
Wrapper/Helper Class for datagrid based on jQuery Datatable Plugin
***/
var Datatable = function() {

    var tableOptions; // main options
    var dataTable; // datatable object
    var table; // actual table jquery object
	var tableFilter; //用于检索该表格的相关表格
    var tableContainer; // actual table container object
    var tableWrapper; // actual table wrapper jquery object
    var tableInitialized = false;
    var ajaxParams = {}; // set filter mode
    var the;

    var countSelectedRecords = function() {
        var selected = $('tbody > tr > td:nth-child(1) input[type="checkbox"]:checked', table).size();
        var text = tableOptions.dataTable.language.GlobalGroupActions;
        if (selected > 0) {
            $('.table-group-actions > span', tableWrapper).text(text.replace("_TOTAL_", selected));
        } else {
            $('.table-group-actions > span', tableWrapper).text("");
        }
    };

    return {

        //main function to initiate the module
        init: function(options) {

            if (!$().dataTable) {
                return;
            }

            the = this;
			
            // default settings
            options = $.extend(true, {
                src: "", // actual table  
                filterApplyAction: "filter",
                filterCancelAction: "filter_cancel",
                resetGroupActionInputOnSuccess: true,
                loadingMessage: 'Loading...',
                dataTable: {
                    "dom": "<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'<'table-group-actions pull-right'>>r><'table-scrollable't><'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'>>", // datatable layout
                    "pageLength": 10, // default records per page
                    "language": { // language settings
                        // Global spesific
                        "GlobalGroupActions": "_TOTAL_ 条记录被选中:  ",
                        "GlobalAjaxRequestGeneralError": "请求无法完成,请检查链接有效性!",

                        // data tables spesific
                        "lengthMenu": "<span class='seperator'>|</span>每页 _MENU_ 条记录",
                        "info": "<span class='seperator'>|</span>共 _TOTAL_ 条记录",
                        "infoEmpty": "没有符合条件的记录",
                        "emptyTable": "No data available in table",
                        "zeroRecords": "没有符合条件的记录",
                        "paginate": {
                            "previous": "上一页",
                            "next": "下一页",
                            "last": "末页",
                            "first": "首页",
                            "page": "当前",
                            "pageOf": "共"
                        }
                    },

                    "orderCellsTop": true,
                    "columnDefs": [{ // define columns sorting options(by default all columns are sortable extept the first checkbox column)
                        'orderable': false,
                        'targets': [0]
                    }],

                    "pagingType": "bootstrap_extended", // pagination type(bootstrap, bootstrap_full_number or bootstrap_extended)
                    "autoWidth": false, // disable fixed width and enable fluid table
                    "processing": false, // enable/disable display message box on record load
                    "serverSide": true, // enable/disable server side ajax loading

                    "ajax": { // define ajax settings
                        "url": "", // ajax URL
                        "type": "POST", // request type
                        "timeout": 20000,
                        "data": function(data) { // add request parameters before submit
							
							if(ajaxParams){
								data.search = {};
								$.each(ajaxParams, function(key, value) {									
									if(value.value !== '') data.search[key] = value;
								});
							}
							
							//console.log(data);
                            Global.blockUI({
                                message: tableOptions.loadingMessage,
                                target: tableContainer,
                                overlayColor: 'none',
                                cenrerY: true,
                                boxed: true
                            });
                        },
                        "dataSrc": function(res) { // Manipulate the data returned from the server
                            if (res.customActionMessage) {
                                Global.alert({
                                    type: (res.customActionStatus == 'OK' ? 'success' : 'danger'),
                                    icon: (res.customActionStatus == 'OK' ? 'check' : 'warning'),
                                    message: res.customActionMessage,
                                    container: tableWrapper,
                                    place: 'prepend'
                                });
                            }

                            if (res.customActionStatus) {
                                if (tableOptions.resetGroupActionInputOnSuccess) {
                                    $('.table-group-action-input', tableWrapper).val("");
                                }
                            }

                            if ($('.group-checkable', table).size() === 1) {
                                $('.group-checkable', table).attr("checked", false);
                                $.uniform.update($('.group-checkable', table));
                            }

                            if (tableOptions.onSuccess) {
                                tableOptions.onSuccess.call(undefined, the);
                            }

                            Global.unblockUI(tableContainer);

                            return res.data;
                        },
                        "error": function() { // handle general connection errors
                            if (tableOptions.onError) {
                                tableOptions.onError.call(undefined, the);
                            }

                            Global.alert({
                                type: 'danger',
                                icon: 'warning',
                                message: tableOptions.dataTable.language.GlobalAjaxRequestGeneralError,
                                container: tableWrapper,
                                place: 'prepend'
                            });

                            Global.unblockUI(tableContainer);
                        }
                    },

                    "drawCallback": function(oSettings) { // run some code on table redraw
                        if (tableInitialized === false) { // check if table has been initialized
                            tableInitialized = true; // set table initialized
                            table.show(); // display table
                        }
                        Global.initUniform($('input[type="checkbox"]', table)); // reinitialize uniform checkboxes on each table reload
                        countSelectedRecords(); // reset selected records indicator

                        // callback for ajax data load
                        if (tableOptions.onDataLoad) {
                            tableOptions.onDataLoad.call(undefined, the);
                        }
                    },
					
					"tableTools": {						
						/*"sSwfPath": "../../assets/global/plugins/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf",
						"aButtons": [{
							"sExtends": "copy",
							"sButtonText": "复制",
							"sInfo": 'Please press "CTR+P" to print or "ESC" to quit',
							"sMessage": "Generated by DataTables"	
						},{
							"sExtends": "pdf",
							"sButtonText": "导出PDF"
						}, {
							"sExtends": "csv",
							"sButtonText": "导出CSV"
						}, {
							"sExtends": "xls",
							"sButtonText": "Excel"
						} , {
							"sExtends": "print",
							"sButtonText": "Print",
							"sInfo": 'Please press "CTR+P" to print or "ESC" to quit',
							"sMessage": "Generated by DataTables"							
						} ]*/
					}
                }
            }, options);

            tableOptions = options;
			//console.log(tableOptions);
/* 			$.extend(true, $.fn.DataTable.TableTools.classes, {
				"container": "btn-group",
				"buttons": {
					"normal": "btn btn-sm default",
					"disabled": "btn btn-sm default disabled"
				},
				"collection": {
					"container": "DTTT_dropdown dropdown-menu tabletools-dropdown-menu"
				}
			}); */
			
            // create table's jquery object
            table = $(options.src);			
			tableFilter = ($('#filter-'+table.attr('id')).size() === 1) ? $('#filter-'+table.attr('id')) : table;
            tableContainer = table.parents(".table-container");

            // apply the special class that used to restyle the default datatable
            var tmp = $.fn.dataTableExt.oStdClasses;

            $.fn.dataTableExt.oStdClasses.sWrapper = $.fn.dataTableExt.oStdClasses.sWrapper + " dataTables_extended_wrapper";
            $.fn.dataTableExt.oStdClasses.sFilterInput = "form-control input-small input-sm input-inline";
            $.fn.dataTableExt.oStdClasses.sLengthSelect = "form-control input-xsmall input-sm input-inline";
			
            // initialize a datatable
            dataTable = table.DataTable(options.dataTable);
			
            // revert back to default
            $.fn.dataTableExt.oStdClasses.sWrapper = tmp.sWrapper;
            $.fn.dataTableExt.oStdClasses.sFilterInput = tmp.sFilterInput;
            $.fn.dataTableExt.oStdClasses.sLengthSelect = tmp.sLengthSelect;

            // get table wrapper
            tableWrapper = table.parents('.dataTables_wrapper');

            // build table group actions panel
			
            if ($('.table-actions-wrapper', tableContainer).size() === 1) {
                $('.table-group-actions', tableWrapper).html($('.table-actions-wrapper', tableContainer).html()); // place the panel inside the wrapper
                $('.table-actions-wrapper', tableContainer).remove(); // remove the template container
            }
			
			
/*             if ($('.table-footer-wrapper', tableContainer).size() === 1 && $('.table-footer', tableContainer).size() === 1) {
                $('.table-footer', tableWrapper).html($('.table-footer-wrapper', tableContainer).html()); // place the panel inside the wrapper
                $('.table-footer-wrapper', tableContainer).remove(); // remove the template container
            } */
			
            // handle group checkboxes check/uncheck
            $('.group-checkable').change(function() {
				
                var set = $('tbody > tr > td:nth-child(1) input[type="checkbox"]', table);
                var checked = $(this).is(":checked");
                $(set).each(function() {
                    $(this).attr("checked", checked);
                });
                $.uniform.update(set);
                countSelectedRecords();
            });

            // handle row's checkbox click
            table.on('change', 'tbody > tr > td:nth-child(1) input[type="checkbox"]', function() {
                countSelectedRecords();
            });
			//console.log(tableFilter);
            // handle filter submit button click
            tableFilter.on('click', '.filter-submit', function(e) {
                e.preventDefault();	
                the.submitFilter();
            });

            // handle filter cancel button click
            tableFilter.on('click', '.filter-cancel', function(e) {
                e.preventDefault();
                the.resetFilter();
            });
        },

        submitFilter: function() {
			the.clearAjaxParams();	
            //the.setAjaxParam("action", tableOptions.filterApplyAction);
/* 			console.log(tableOptions);
			return false; */
            // get all typeable inputs
            $('textarea.form-filter, select.form-filter, input.form-filter:not([type="radio"],[type="checkbox"])', tableFilter).each(function() {
                the.setAjaxParam($(this).attr("name"), $(this).val(), $(this).data('filter'));
            });

            // get all checkboxes
            $('input.form-filter[type="checkbox"]:checked', tableFilter).each(function() {
                the.addAjaxParam($(this).attr("name"), $(this).val(), $(this).data('filter'));
            });

            // get all radio buttons
            $('input.form-filter[type="radio"]:checked', tableFilter).each(function() {
                the.setAjaxParam($(this).attr("name"), $(this).val(), $(this).data('filter'));
            });

            dataTable.ajax.reload();
        },

        resetFilter: function() {
			//console.log(table);
			for(e in dataTable)
			
			$('textarea.form-filter, select.form-filter, input.form-filter', tableFilter).each(function() {
                $(this).val("");
            });
            $('input.form-filter[type="checkbox"]', tableFilter).each(function() {
                $(this).attr("checked", false);
            });
            the.clearAjaxParams();
            //the.addAjaxParam("action", tableOptions.filterCancelAction);
            dataTable.ajax.reload();
        },

        getSelectedRowsCount: function() {
            return $('tbody > tr > td:nth-child(1) input[type="checkbox"]:checked', table).size();
        },

        getSelectedRows: function() {
            var rows = [];
            $('tbody > tr > td:nth-child(1) input[type="checkbox"]:checked', table).each(function() {
                rows.push($(this).val());
            });

            return rows;
        },

        setAjaxParam: function(name, value, filter) {
			if(name === undefined || value === ''){
				return false;
			};
			filter = filter || "eq";			
			if (ajaxParams[name]) {
                if(ajaxParams[name].length === undefined){
					ajaxParams[name] = [ajaxParams[name]];
				}
				ajaxParams[name].push({value: value, filter: filter});
				
            }else{
				ajaxParams[name] = {value: value, filter: filter};
			}
        },

        addAjaxParam: function(name, value, filter) {
			filter = filter || "eq";
            if (!name){
				return false;
			}
			if (!ajaxParams[name]) {
                ajaxParams[name] = [];
            }

            skip = false;
            for (var i = 0; i < (ajaxParams[name]).length; i++) { // check for duplicates
                if (ajaxParams[name][i] === value) {
                    skip = true;
                }
            }

            if (skip === false) {
                ajaxParams[name].push({value: value, filter: filter});
            }
        },

        clearAjaxParams: function(name, value) {
            ajaxParams = {};
        },

        getDataTable: function() {
            return dataTable;
        },

        getTableWrapper: function() {
            return tableWrapper;
        },

        gettableContainer: function() {
            return tableContainer;
        },

        getTable: function() {
            return table;
        }

    };

};