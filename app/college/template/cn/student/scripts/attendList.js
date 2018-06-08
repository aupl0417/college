var trainRecord = function() {
  var dataGrid = new Datatable();
  return {
    initList: function(id) {
      var table = $("#attendDetail");
      var columns = [];
      $('thead > tr > th', table).each(function() { !! $(this).data('dt') && columns.push({
          "data": $(this).data('dt'),
          "sortable": !! $(this).data('sort'),
          "visible": ! $(this).data('hide')
        });
      });
      dataGrid.init({
        src: table,
        dataTable: {
          "ajax": {
            "type": "POST",
            "url": `/student/attendList.json?id=${id}`
          },
          "columns": columns,
          "order": [[0, "desc"]]
        }
      });
      return dataGrid;
    }
  };
} ();
