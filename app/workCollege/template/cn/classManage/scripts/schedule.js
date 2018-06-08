/*=============================================================================
#     FileName: schedule.js
#         Desc:
#       Author: Wuyuanhang
#        Email: QQ:554119220
#   LastChange: 2016-10-14 17:24:18
#      History:
#      Paramer:
=============================================================================*/
var scheduleList = function() {
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
            "url": `/classSchedule/index.json?clID=${id}`
          },
          "columns": columns,
          "order": [[4, "desc"]]
        }
      });
      return dataGrid;
    }
  };
} ();
