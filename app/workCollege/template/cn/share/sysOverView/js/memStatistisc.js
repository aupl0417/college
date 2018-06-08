/**
 * Created by Administrator on 2016/1/31.
 */
    $(document).ready(function() {
        $.ajax({
            'url': '/sysOverView/memStatistisc.json',
            'dataType': 'json',
            'success': function(res) {
                if (res.id == '3010') {
                    var data = res.info;
                    //console.log(data);
                    var litres = [];
                    for(var e in data){
                        litres.push(data[e]);
                    }

                    var chart = AmCharts.makeChart("chartdivMem", {
                        "type": "pie",
                        "startDuration": 0,
                        "theme": "light",
                        "addClassNames": true,
                        "legend":{
                            "position":"right",
                            "marginRight":10,
                            "autoMargins":false
                        },
                        "innerRadius": "30%",
                        "defs": {
                            "filter": [{
                                "id": "shadow",
                                "width": "200%",
                                "height": "200%",
                                "feOffset": {
                                    "result": "offOut",
                                    "in": "SourceAlpha",
                                    "dx": 0,
                                    "dy": 0
                                },
                                "feGaussianBlur": {
                                    "result": "blurOut",
                                    "in": "offOut",
                                    "stdDeviation": 5
                                },
                                "feBlend": {
                                    "in": "SourceGraphic",
                                    "in2": "blurOut",
                                    "mode": "normal"
                                }
                            }]
                        },
                        "dataProvider": [{
                            "memL": "消费商会员数量",
                            "litres": litres[0]
                        }, {
                            "memL": "创客会员数量",
                            "litres": litres[2]
                        }, {
                            "memL": "创投会员数量",
                            "litres": litres[3]
                        }],
                        "valueField": "litres",
                        "titleField": "memL",
                        "export": {
                            "enabled": true
                        }
                    });

                    chart.addListener("init", handleInit);

                    chart.addListener("rollOverSlice", function(e) {
                        handleRollOver(e);
                    });

                    function handleInit(){
                        chart.legend.addListener("rollOverItem", handleRollOver);
                    }

                    function handleRollOver(e){
                        var wedge = e.dataItem.wedge.node;
                        wedge.parentNode.appendChild(wedge);
                    }
                }
            }
        });
    })

$(document).ready(function() {
    $.ajax({
        'url': '/sysOverView/memStatistisc.json',
        'dataType': 'json',
        'success': function(res) {
            if (res.id == '3010') {
                var data = res.info;
                //console.log(data);
                var litres = [];
                for(var e in data){
                    litres.push(data[e]);
                }

                var chart = AmCharts.makeChart("chartdivMem1", {
                    "type": "pie",
                    "startDuration": 0,
                    "theme": "none",
                    "addClassNames": true,
                    "legend":{
                        "position":"right",
                        "marginRight":10,
                        "autoMargins":false
                    },
                    "innerRadius": "30%",
                    "defs": {
                        "filter": [{
                            "id": "shadow",
                            "width": "200%",
                            "height": "200%",
                            "feOffset": {
                                "result": "offOut",
                                "in": "SourceAlpha",
                                "dx": 0,
                                "dy": 0
                            },
                            "feGaussianBlur": {
                                "result": "blurOut",
                                "in": "offOut",
                                "stdDeviation": 5
                            },
                            "feBlend": {
                                "in": "SourceGraphic",
                                "in2": "blurOut",
                                "mode": "normal"
                            }
                        }]
                    },
                    "dataProvider": [{
                        "agentL": "个人会员",
                        "litres": litres[4]
                    }, {
                        "agentL": "企业会员",
                        "litres": litres[5]
                    }],
                    "valueField": "litres",
                    "titleField": "agentL",
                    "export": {
                        "enabled": true
                    }
                });


                chart.addListener("init", handleInit);

                chart.addListener("rollOverSlice", function(e) {
                    handleRollOver(e);
                });

                function handleInit(){
                    chart.legend.addListener("rollOverItem", handleRollOver);
                }

                function handleRollOver(e){
                    var wedge = e.dataItem.wedge.node;
                    wedge.parentNode.appendChild(wedge);
                }
            }
        }
    });
})
