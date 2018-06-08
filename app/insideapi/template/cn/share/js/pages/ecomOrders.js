/*
 *  Document   : ecomOrders.js
 *  Author     : pixelcave
 */
var EcomOrders=function(){return{init:function(){$.extend($.fn.dataTableExt.oSort,{"date-custom-pre":function(t){var e=t.split("/");return 1*(e[2]+e[1]+e[0])},"date-custom-asc":function(t,e){return e>t?-1:t>e?1:0},"date-custom-desc":function(t,e){return e>t?1:t>e?-1:0}}),App.datatables(),$("#ecom-orders").dataTable({columnDefs:[{type:"date-custom",targets:[6]},{orderable:!1,targets:[7]}],order:[[0,"desc"]],pageLength:20,lengthMenu:[[10,20,30,-1],[10,20,30,"All"]]}),$(".dataTables_filter input").attr("placeholder","Search")}}}();