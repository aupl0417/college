var Echarts = function () {
    // 路径配置
    require.config({
        paths: {
            echarts: '/frame/public/assets/global/plugins/echarts'
        }
    });

    //基本线图/柱图
    var echartExample1 = function () {
        require(
                [
                    'echarts',
                    'echarts/chart/line',
                    //'echarts/chart/bar' // 使用柱状图就加载bar模块，按需加载

                ],
                function (ec) {
                    // 基于准备好的dom，初始化echarts图表
                    if ($('#example1').size() != 1)
                        return false;
                    var chartExample1 = ec.init($('#example1')[0]);

                    var option = {
                        title: {
                            text: '本周返还红积分变化',
                            //subtext: '纯属虚构'
                            x: 'center'
                        },
                        tooltip: {
                            trigger: 'axis'
                        },
                        legend: {
                            data: ['最高返还积分', '最低返还积分'],
                            orient: 'vertical',
                            x: 'left',
                        },
                        toolbox: {
                            show: false,
                            feature: {
                                mark: {show: true},
                                dataView: {show: true, readOnly: true},
                                magicType: {show: true, type: ['line', 'bar']},
                                restore: {show: true},
                                saveAsImage: {show: true}
                            }
                        },
                        calculable: true,
                        xAxis: [
                            {
                                type: 'category',
                                boundaryGap: false,
                                data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
                            }
                        ],
                        yAxis: [
                            {
                                type: 'value',
                                axisLabel: {
                                    formatter: '{value} ￥'
                                }
                            }
                        ],
                        series: [
                            {
                                name: '最高返还积分',
                                type: 'line',
                                data: [11, 11, 12, 13, 12, 13, 10],
                                markPoint: {
                                    data: [
                                        {type: 'max', name: '最大值'},
                                        {type: 'min', name: '最小值'}
                                    ]
                                },
                                markLine: {
                                    data: [
                                        {type: 'average', name: '平均值'}
                                    ]
                                }
                            },
                            {
                                name: '最低返还积分',
                                type: 'line',
                                data: [1, 3, 2, 5, 3, 2, 3],
                                markPoint: {
                                    data: [
                                        {name: '周最低', value: 1, xAxis: 1, yAxis: 1.5}
                                    ]
                                },
                                markLine: {
                                    data: [
                                        {type: 'average', name: '平均值'}
                                    ]
                                }
                            }
                        ]
                    };


                    // 为echarts对象加载数据 
                    chartExample1.setOption(option);


                }
        );
    }

    //K线图
    var echartExample2 = function () {
        require(
                [
                    'echarts',
                    'echarts/chart/k', // 使用K图就加载k块，按需加载

                ],
                function (ec) {
                    // 基于准备好的dom，初始化echarts图表
                    if ($('#example2').size() != 1)
                        return false;
                    var chartExample2 = ec.init($('#example2')[0]);

                    var option = {
                        title: {
                            text: '2015年上半年上证指数'
                        },
                        tooltip: {
                            trigger: 'axis',
                            formatter: function (params) {
                                var res = params[0].seriesName + ' ' + params[0].name;
                                res += '<br/>  开盘 : ' + params[0].value[0] + '  最高 : ' + params[0].value[3];
                                res += '<br/>  收盘 : ' + params[0].value[1] + '  最低 : ' + params[0].value[2];
                                return res;
                            }
                        },
                        legend: {
                            data: ['上证指数']
                        },
                        toolbox: {
                            show: false,
                            feature: {
                                mark: {show: true},
                                dataZoom: {show: true},
                                dataView: {show: true, readOnly: false},
                                //magicType: {show: true, type: ['line', 'bar']},
                                restore: {show: true},
                                saveAsImage: {show: true}
                            }
                        },
                        dataZoom: {
                            show: true,
                            realtime: true,
                            start: 50,
                            end: 100
                        },
                        xAxis: [
                            {
                                type: 'category',
                                boundaryGap: true,
                                axisTick: {onGap: false},
                                splitLine: {show: false},
                                data: [
                                    "2015/1/24", "2015/1/25", "2015/1/28", "2015/1/29", "2015/1/30",
                                    "2015/1/31", "2015/2/1", "2015/2/4", "2015/2/5", "2015/2/6",
                                    "2015/2/7", "2015/2/8", "2015/2/18", "2015/2/19", "2015/2/20",
                                    "2015/2/21", "2015/2/22", "2015/2/25", "2015/2/26", "2015/2/27",
                                    "2015/2/28", "2015/3/1", "2015/3/4", "2015/3/5", "2015/3/6",
                                    "2015/3/7", "2015/3/8", "2015/3/11", "2015/3/12", "2015/3/13",
                                    "2015/3/14", "2015/3/15", "2015/3/18", "2015/3/19", "2015/3/20",
                                    "2015/3/21", "2015/3/22", "2015/3/25", "2015/3/26", "2015/3/27",
                                    "2015/3/28", "2015/3/29", "2015/4/1", "2015/4/2", "2015/4/3",
                                    "2015/4/8", "2015/4/9", "2015/4/10", "2015/4/11", "2015/4/12",
                                    "2015/4/15", "2015/4/16", "2015/4/17", "2015/4/18", "2015/4/19",
                                    "2015/4/22", "2015/4/23", "2015/4/24", "2015/4/25", "2015/4/26",
                                    "2015/5/2", "2015/5/3", "2015/5/6", "2015/5/7", "2015/5/8",
                                    "2015/5/9", "2015/5/10", "2015/5/13", "2015/5/14", "2015/5/15",
                                    "2015/5/16", "2015/5/17", "2015/5/20", "2015/5/21", "2015/5/22",
                                    "2015/5/23", "2015/5/24", "2015/5/27", "2015/5/28", "2015/5/29",
                                    "2015/5/30", "2015/5/31", "2015/6/3", "2015/6/4", "2015/6/5",
                                    "2015/6/6", "2015/6/7", "2015/6/13"
                                ]
                            }
                        ],
                        yAxis: [
                            {
                                type: 'value',
                                scale: true,
                                boundaryGap: [0.01, 0.01]
                            }
                        ],
                        series: [
                            {
                                name: '上证指数',
                                type: 'k',
                                data: [// 开盘，收盘，最低，最高
                                    [2320.26, 2302.6, 2287.3, 2362.94],
                                    [2300, 2291.3, 2288.26, 2308.38],
                                    [2295.35, 2346.5, 2295.35, 2346.92],
                                    [2347.22, 2358.98, 2337.35, 2363.8],
                                    [2360.75, 2382.48, 2347.89, 2383.76],
                                    [2383.43, 2385.42, 2371.23, 2391.82],
                                    [2377.41, 2419.02, 2369.57, 2421.15],
                                    [2425.92, 2428.15, 2417.58, 2440.38],
                                    [2411, 2433.13, 2403.3, 2437.42],
                                    [2432.68, 2434.48, 2427.7, 2441.73],
                                    [2430.69, 2418.53, 2394.22, 2433.89],
                                    [2416.62, 2432.4, 2414.4, 2443.03],
                                    [2441.91, 2421.56, 2415.43, 2444.8],
                                    [2420.26, 2382.91, 2373.53, 2427.07],
                                    [2383.49, 2397.18, 2370.61, 2397.94],
                                    [2378.82, 2325.95, 2309.17, 2378.82],
                                    [2322.94, 2314.16, 2308.76, 2330.88],
                                    [2320.62, 2325.82, 2315.01, 2338.78],
                                    [2313.74, 2293.34, 2289.89, 2340.71],
                                    [2297.77, 2313.22, 2292.03, 2324.63],
                                    [2322.32, 2365.59, 2308.92, 2366.16],
                                    [2364.54, 2359.51, 2330.86, 2369.65],
                                    [2332.08, 2273.4, 2259.25, 2333.54],
                                    [2274.81, 2326.31, 2270.1, 2328.14],
                                    [2333.61, 2347.18, 2321.6, 2351.44],
                                    [2340.44, 2324.29, 2304.27, 2352.02],
                                    [2326.42, 2318.61, 2314.59, 2333.67],
                                    [2314.68, 2310.59, 2296.58, 2320.96],
                                    [2309.16, 2286.6, 2264.83, 2333.29],
                                    [2282.17, 2263.97, 2253.25, 2286.33],
                                    [2255.77, 2270.28, 2253.31, 2276.22],
                                    [2269.31, 2278.4, 2250, 2312.08],
                                    [2267.29, 2240.02, 2239.21, 2276.05],
                                    [2244.26, 2257.43, 2232.02, 2261.31],
                                    [2257.74, 2317.37, 2257.42, 2317.86],
                                    [2318.21, 2324.24, 2311.6, 2330.81],
                                    [2321.4, 2328.28, 2314.97, 2332],
                                    [2334.74, 2326.72, 2319.91, 2344.89],
                                    [2318.58, 2297.67, 2281.12, 2319.99],
                                    [2299.38, 2301.26, 2289, 2323.48],
                                    [2273.55, 2236.3, 2232.91, 2273.55],
                                    [2238.49, 2236.62, 2228.81, 2246.87],
                                    [2229.46, 2234.4, 2227.31, 2243.95],
                                    [2234.9, 2227.74, 2220.44, 2253.42],
                                    [2232.69, 2225.29, 2217.25, 2241.34],
                                    [2196.24, 2211.59, 2180.67, 2212.59],
                                    [2215.47, 2225.77, 2215.47, 2234.73],
                                    [2224.93, 2226.13, 2212.56, 2233.04],
                                    [2236.98, 2219.55, 2217.26, 2242.48],
                                    [2218.09, 2206.78, 2204.44, 2226.26],
                                    [2199.91, 2181.94, 2177.39, 2204.99],
                                    [2169.63, 2194.85, 2165.78, 2196.43],
                                    [2195.03, 2193.8, 2178.47, 2197.51],
                                    [2181.82, 2197.6, 2175.44, 2206.03],
                                    [2201.12, 2244.64, 2200.58, 2250.11],
                                    [2236.4, 2242.17, 2232.26, 2245.12],
                                    [2242.62, 2184.54, 2182.81, 2242.62],
                                    [2187.35, 2218.32, 2184.11, 2226.12],
                                    [2213.19, 2199.31, 2191.85, 2224.63],
                                    [2203.89, 2177.91, 2173.86, 2210.58],
                                    [2170.78, 2174.12, 2161.14, 2179.65],
                                    [2179.05, 2205.5, 2179.05, 2222.81],
                                    [2212.5, 2231.17, 2212.5, 2236.07],
                                    [2227.86, 2235.57, 2219.44, 2240.26],
                                    [2242.39, 2246.3, 2235.42, 2255.21],
                                    [2246.96, 2232.97, 2221.38, 2247.86],
                                    [2228.82, 2246.83, 2225.81, 2247.67],
                                    [2247.68, 2241.92, 2231.36, 2250.85],
                                    [2238.9, 2217.01, 2205.87, 2239.93],
                                    [2217.09, 2224.8, 2213.58, 2225.19],
                                    [2221.34, 2251.81, 2210.77, 2252.87],
                                    [2249.81, 2282.87, 2248.41, 2288.09],
                                    [2286.33, 2299.99, 2281.9, 2309.39],
                                    [2297.11, 2305.11, 2290.12, 2305.3],
                                    [2303.75, 2302.4, 2292.43, 2314.18],
                                    [2293.81, 2275.67, 2274.1, 2304.95],
                                    [2281.45, 2288.53, 2270.25, 2292.59],
                                    [2286.66, 2293.08, 2283.94, 2301.7],
                                    [2293.4, 2321.32, 2281.47, 2322.1],
                                    [2323.54, 2324.02, 2321.17, 2334.33],
                                    [2316.25, 2317.75, 2310.49, 2325.72],
                                    [2320.74, 2300.59, 2299.37, 2325.53],
                                    [2300.21, 2299.25, 2294.11, 2313.43],
                                    [2297.1, 2272.42, 2264.76, 2297.1],
                                    [2270.71, 2270.93, 2260.87, 2276.86],
                                    [2264.43, 2242.11, 2240.07, 2266.69],
                                    [2242.26, 2210.9, 2205.07, 2250.63],
                                    [2190.1, 2148.35, 2126.22, 2190.1]
                                ]
                            }
                        ]
                    };

                    // 为echarts对象加载数据 
                    chartExample2.setOption(option);


                }
        );
    }


    //饼图
    var echartExample3 = function () {
        require(
                [
                    'echarts',
                    //'echarts/chart/funnel',
                    //'echarts/chart/bar',
                    'echarts/chart/pie', // 使用饼图就加载pie模块，按需加载

                ],
                function (ec) {
                    // 基于准备好的dom，初始化echarts图表
                    if ($('#example3').size() != 1)
                        return false;
                    var chartExample3 = ec.init($('#example3')[0]);

                    var option = {
                        title: {
                            text: '公司账户积分明细',
                            //subtext: '纯属虚构',
                            x: 'center'
                        },
                        tooltip: {
                            trigger: 'item',
                            formatter: "{a} <br/>{b} : {c} ({d}%)"
                        },
                        legend: {
                            orient: 'vertical',
                            x: 'left',
                            data: ['红积分', '白积分', '库存积分', '冻结积分', '现金账户总额']
                        },
                        toolbox: {
                            show: false,
                            feature: {
                                mark: {show: true},
                                dataView: {show: true, readOnly: false},
                                magicType: {
                                    show: true,
                                    type: ['pie', 'funnel'],
                                    option: {
                                        funnel: {
                                            x: '25%',
                                            width: '50%',
                                            funnelAlign: 'left',
                                            max: 1548
                                        }
                                    }
                                },
                                restore: {show: true},
                                saveAsImage: {show: true}
                            }
                        },
                        calculable: true,
                        series: [
                            {
                                name: '访问来源',
                                type: 'pie',
                                radius: '55%',
                                center: ['50%', '60%'],
                                data: [
                                    {value: 335, name: '红积分'},
                                    {value: 310, name: '白积分'},
                                    {value: 234, name: '库存积分'},
                                    {value: 135, name: '冻结积分'},
                                    {value: 1548, name: '现金账户总额'}
                                ]
                            }
                        ]
                    };

                    // 为echarts对象加载数据 
                    chartExample3.setOption(option);


                }
        );
    }

    //混搭
    var echartExample4 = function () {
        require(
                [
                    'echarts',
                    'echarts/chart/line',
                    'echarts/chart/bar',
                    'echarts/chart/pie'

                ],
                function (ec) {
                    // 基于准备好的dom，初始化echarts图表
                    if ($('#example4').size() != 1)
                        return false;
                    var chartExample4 = ec.init($('#example4')[0]);

                    var option = {
                        tooltip: {
                            trigger: 'axis'
                        },
                        toolbox: {
                            show: true,
                            y: 'bottom',
                            feature: {
                                mark: {show: true},
                                dataView: {show: true, readOnly: false},
                                magicType: {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                                restore: {show: true},
                                saveAsImage: {show: true}
                            }
                        },
                        calculable: true,
                        legend: {
                            data: ['直接访问', '邮件营销', '联盟广告', '视频广告', '搜索引擎', '百度', '谷歌', '必应', '其他']
                        },
                        xAxis: [
                            {
                                type: 'category',
                                splitLine: {show: false},
                                data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
                            }
                        ],
                        yAxis: [
                            {
                                type: 'value',
                                position: 'right'
                            }
                        ],
                        series: [
                            {
                                name: '直接访问',
                                type: 'bar',
                                data: [320, 332, 301, 334, 390, 330, 320]
                            },
                            {
                                name: '邮件营销',
                                type: 'bar',
                                tooltip: {trigger: 'item'},
                                stack: '广告',
                                data: [120, 132, 101, 134, 90, 230, 210]
                            },
                            {
                                name: '联盟广告',
                                type: 'bar',
                                tooltip: {trigger: 'item'},
                                stack: '广告',
                                data: [220, 182, 191, 234, 290, 330, 310]
                            },
                            {
                                name: '视频广告',
                                type: 'bar',
                                tooltip: {trigger: 'item'},
                                stack: '广告',
                                data: [150, 232, 201, 154, 190, 330, 410]
                            },
                            {
                                name: '搜索引擎',
                                type: 'line',
                                data: [862, 1018, 964, 1026, 1679, 1600, 1570]
                            },
                            {
                                name: '搜索引擎细分',
                                type: 'pie',
                                tooltip: {
                                    trigger: 'item',
                                    formatter: '{a} <br/>{b} : {c} ({d}%)'
                                },
                                center: [160, 130],
                                radius: [0, 50],
                                itemStyle:{
                                    normal: {
                                        labelLine: {
                                            length: 20
                                        }
                                    }
                                },
                                data: [
                                    {value: 1048, name: '百度'},
                                    {value: 251, name: '谷歌'},
                                    {value: 147, name: '必应'},
                                    {value: 102, name: '其他'}
                                ]
                            }
                        ]
                    };
                    // 为echarts对象加载数据 
                    chartExample4.setOption(option);


                }
        );
    }



    return {
        init: function () {
            echartExample1();
            //echartExample2();
            echartExample3();
            //echartExample4();
        }
    };

}();