/*
 *  Document   : uiDraggable.js
 *  Author     : pixelcave
 */
var UiDraggable=function(){return{init:function(){$(".draggable-blocks").sortable({connectWith:".block",items:".block",opacity:.75,handle:".block-title",placeholder:"draggable-placeholder",tolerance:"pointer",start:function(e,t){t.placeholder.css("height",t.item.outerHeight())}})}}}();