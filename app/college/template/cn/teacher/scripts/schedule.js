var calander = function(defaultDate) {
	let param = {
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay,listWeek'
		},
		//defaultView: 'listWeek',
		defaultDate: defaultDate,
		locale: 'zh-cn',
		navLinks: true,
		weekNumbers: true,
		weekNumbersWithinDays: true,
		weekNumberCalculation: 'ISO',

		slotDuration: '00:05:00',
		scrollTime: '16:00:00',
		minTime: '08:00:00',
		maxTime: '22:00:00',
		editable: false,
		eventDragStart: function(event, jsEvent, ui, view) {},
		eventDragStart: function(event, jsEvent, ui, view) {},
		eventDrop: function(event, delta, revertFunc, jsEvent, ui, view) {},
		eventResize: function(event, delta, revertFunc) {},
		dayClick: function(event, jsEvent, view) {},
		events: function(startTime, endTime, timezone, callback) {
			$.ajax({
				url: '/teacher/schedule.json',
				dataType: 'json',
				data: {
					'startTime': Math.round(new Date(startTime).getTime() / 1000),
					'endTime': Math.round(new Date(endTime).getTime() / 1000),
				},
				success: function(res) {
					console.log(res);
					let events = [];
					if (1001 == res.id) {
						for (let i in res.info) {
							events.push({
								title: res.info[i].title,
								start: res.info[i].startTime,
								end: res.info[i].endTime
							});
						}
						console.log(events);
						callback(events);
					}
				}
			});
		}
	};
	$('#calendar').fullCalendar(param);
};

