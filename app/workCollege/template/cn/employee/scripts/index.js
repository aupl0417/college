/**
 * 
 */
var Employee = function() {
    var eGrid = new Datatable();
    return {
        initList: function() {
            var table   = $("#detail");
            var columns = [];
            $("thead > tr > th", table).each(
                function() { !! $(this).data('dt') && columns.push({
                    "data": $(this).data('dt'),
                    "sortable": !! $(this).data('sort'),
                    "visible": ! $(this).data('hide')
                });
                });
            eGrid.init({
                src: table,
                dataTable: {
                    "ajax": {
                        "url": "/employee/index.json"
                    },
                    "columns": columns,
                    "order": [[0, "desc"]]
                }
            });
            return eGrid;
        },
    };
} ();
