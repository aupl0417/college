var trainRecord = function() {
  let dataGrid = new Datatable();
  return {
    initList: function(id) {
      let table = $("#scheduleDetail");
      let columns = [];
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
            "url": `/teacher/trainRecord.json?id=${id}`
          },
          "columns": columns,
          "order": [[4, "desc"]]
        }
      });
      return dataGrid;
    }
  };
} ();
