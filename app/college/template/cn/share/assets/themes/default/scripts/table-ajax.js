var TableAjax = function () {



    var handleRecords = function () {

        var grid = new Datatable();
        grid.init({
            src: $("#orderlist"),
            onSuccess: function (grid) {
                // execute some code after table records loaded
            },
            onError: function (grid) {
                // execute some code on network or other general error  
            },
            onDataLoad: function(grid) {
                // execute some code on ajax data load
            },
            loadingMessage: 'Loading...',
            dataTable: { // here you can define a typical datatable settings from http://datatables.net/usage/options 

                // Uncomment below line("dom" parameter) to fix the dropdown overflow issue in the datatable cells. The default datatable layout
                // setup uses scrollable div(table-scrollable) with overflow:auto to enable vertical scroll(see: assets/global/scripts/datatable.js). 
                // So when dropdowns used the scrollable div should be removed. 
                //"dom": "<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'<'table-group-actions pull-right'>>r>t<'row'<'col-md-8 col-sm-12'pli><'col-md-4 col-sm-12'>>",
                
                "bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.

                "lengthMenu": [
                    [10, 20, 50, 100, 150, -1],
                    [10, 20, 50, 100, 150, "全部"] // change per page values here
                ],
                "pageLength": 10, // default record count per page
                "ajax": {
                    "url": "table_ajax.php", // ajax source
                },
                "order": [
                    [1, "asc"]
                ]// set first column as a default sort by asc			
            }
        });

        // Submit操作按钮
        grid.getTableWrapper().on('click', '.table-group-action-submit', function (e) {
            e.preventDefault();
            var action = $(".table-group-action-input", grid.getTableWrapper());
            if (action.val() != "" && grid.getSelectedRowsCount() > 0) {
                grid.setAjaxParam("customActionType", "group_action");
                grid.setAjaxParam("customActionName", action.val());
                grid.setAjaxParam("id", grid.getSelectedRows());
                grid.getDataTable().ajax.reload();
                grid.clearAjaxParams();
            } else if (action.val() == "") {
                Global.alert({
                    type: 'danger',
                    icon: 'warning',
                    message: '请选择一个有效的操作',
                    container: grid.getTableWrapper(),
                    place: 'prepend',
					closeInSeconds: 5
                });
            } else if (grid.getSelectedRowsCount() === 0) {
                Global.alert({
                    type: 'danger',
                    icon: 'warning',
                    message: '没有选中记录',
                    container: grid.getTableWrapper(),
                    place: 'prepend',
					closeInSeconds: 5
                });
            }
        });
    }

    return {

        //main function to initiate the module
        init: function () {
            handleRecords();
        }

    };

}();