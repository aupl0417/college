/*
 *  Document   : readyTasks.js
 *  Author     : pixelcave
 */
var ReadyTasks=function(){return{init:function(){var a=$(".task-list"),s=$("#add-task"),t="";$(".task-done input:checkbox").prop("checked",!0),a.on("click","input:checkbox",function(){$(this).parents("li").toggleClass("task-done")}),a.on("click",".task-close",function(){$(this).parents("li").slideUp()}),$("#add-task-form").on("submit",function(){return t=s.prop("value"),t&&(a.prepend('<li class="animation-slideUp"><a href="javascript:void(0)" class="task-close"><i class="fa fa-times"></i></a><label class="checkbox-inline"><input type="checkbox">'+$("<span />").text(t).html()+"</label></li>"),s.prop("value","").focus()),!1})}}}();