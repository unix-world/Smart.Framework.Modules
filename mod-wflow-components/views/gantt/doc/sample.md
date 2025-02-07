
# Gantt: Samples

## Gantt, Sample Code

``` javascript

function drawGantt(gantID, theScale, theReferenceDate, theNowDate) {

	gantInstance = new SmartGanttInstance(); // supports multiple instances

	/*
	gantInstance.config.date_grid = "%m-%d-%Y";
	gantInstance.config.api_date = "%m-%d-%Y %H:%i";
	gantInstance.config.xml_date = "%m,%d,%Y";
	gantInstance.config.work_time = true;
	gantInstance.config.correct_work_time = true;
	gantInstance.config.details_on_create = false;
	gantInstance.config.scale_unit = "day";
	gantInstance.config.duration_unit = "day";
	gantInstance.config.date_scale = "%d";
	gantInstance.config.subscales = [
		{unit:"month", step:1, date:"%F, %Y"}
	];
	gantInstance.config.scale_height = 50;
	gantInstance.config.row_height = 30;
	gantInstance.config.min_column_width = 30;
	*/

	/*
	gantInstance.attachEvent("onTaskClick", function(id, e) {
		alert("You've just clicked an item with id="+id);
	});
	gantInstance.attachEvent("onTaskDblClick", function(id, e) {
		alert("You've just double clicked an item with id="+id);
	});
	*/

	/*
	gantInstance.config.autosize = "xy";
	gantInstance.config.grid_width = 0;
	gantInstance.config.lightbox.sections = [
			{name: "description", height: 38, map_to: "text", type: "textarea", focus: true},
			{name: "parent", type:"parent", allow_root:"true", root_label:"No parent", filter: function(id, task){
			//	if(task.$level > 1){
			//		return false;
			//	}else{
			//		return true;
			//	}
				return true;
			}},

			{name: "time", height: 72, type: "time", map_to: "auto", time_format:["%d", "%m", "%Y", "%H:%i"]}
		];
	*/

	/*
	var theScale = String(window.location.hash);
	if(theScale) {
		theScale = theScale.substr(1);
	}
	*/

	var objScales = getGanttSafeScales(theScale);
	var theDateScale = objScales.dateScale;
	theScale = objScales.scale;

	var objDate = getGanttDatesByScale(theScale, theReferenceDate, theNowDate);
	var gantDateNow = objDate.nowDate;
	var gantDateStart = objDate.startDate;
	var gantDateEnd = objDate.endDate;
	//console.log(gantDateNow, gantDateStart, gantDateEnd);

	jQuery('#' + gantID).dhx_gantt(gantInstance, {
		data: demo_tasks,
		marker_date: String(gantDateNow), // or set to true to use TODAY
		start_date: new Date(String(gantDateStart)),
		end_date: new Date(String(gantDateEnd)), // IMPORTANT: when no end date, flex tasks will fill all available space !!!
		scale_unit: theScale, // day, week, month
		duration_step: 1,
		date_scale: theDateScale,
	//	readonly: true,
	//	sort: true,
		//scale_unit: "week",
		//step:1,
		//show_task_cells : false,
		//show_grid : false,
		//grid_width: 0, // hide right grid
	//	date_scale: "%d"
	}); // .load("data.json")

	//	alternate init (without jQuery)
	/*		gantInstance.init(String(gantID));
	//gantInstance.load("data.json", "json");
	gantInstance.parse(demo_tasks); */

}

```

## Gantt, Sample Data

```javascript

var users_data = {
	"data":[
		{"id":1, "text":"Project #1", "start_date":"2017-05-01", "duration":"11", "progress": 0.6, "open": true, "users": ["John", "Mike", "Anna"], "priority": "2"},
		{"id":2, "text":"Task #1", "start_date":"2017-05-03", "duration":"5", "parent":"1", "progress": 1, "open": true, "users": ["John", "Mike"], "priority": "1"},
		{"id":3, "text":"Task #2", "start_date":"2017-05-02", "duration":"7", "parent":"1", "progress": 0.5, "open": true, "users": ["Anna"], "priority": "1"},
		{"id":4, "text":"Task #3", "start_date":"2017-05-02", "duration":"6", "parent":"1", "progress": 0.8, "open": true, "users": ["Mike", "Anna"], "priority": "2"},
		{"id":5, "text":"Task #4", "start_date":"2017-05-02", "duration":"5", "parent":"1", "progress": 0.2, "open": true, "users": ["John"], "priority": "3"},
		{"id":6, "text":"Task #5", "start_date":"2017-05-02", "duration":"7", "parent":"1", "progress": 0, "open": true, "users": ["John"], "priority": "2"},
		{"id":7, "text":"Task #2.1", "start_date":"2017-05-03", "duration":"2", "parent":"3", "progress": 1, "open": true, "users": ["Mike", "Anna"], "priority": "2"},
		{"id":8, "text":"Task #2.2", "start_date":"2017-05-06", "duration":"3", "parent":"3", "progress": 0.8, "open": true, "users": ["Anna"], "priority": "3"},
		{"id":9, "text":"Task #2.3", "start_date":"2017-05-10", "duration":"4", "parent":"3", "progress": 0.2, "open": true, "users": ["Mike", "Anna"], "priority": "1"},
		{"id":10, "text":"Task #2.4", "start_date":"2017-05-10", "duration":"4", "parent":"3", "progress": 0, "open": true, "users": ["John", "Mike"], "priority": "1"},
		{"id":11, "text":"Task #4.1", "start_date":"2017-05-03", "duration":"4", "parent":"5", "progress": 0.5, "open": true, "users": ["John", "Anna"], "priority": "3"},
		{"id":12, "text":"Task #4.2", "start_date":"2017-05-03", "duration":"4", "parent":"5", "progress": 0.1, "open": true, "users": ["John"], "priority": "3"},
		{"id":13, "text":"Task #4.3", "start_date":"2017-05-03", "duration":"5", "parent":"5", "progress": 0, "open": true, "users": ["Anna"], "priority": "3"}
	],
	"links":[
		{"id":"10","source":"11","target":"12","type":"1"},
		{"id":"11","source":"11","target":"13","type":"1"},
		{"id":"12","source":"11","target":"14","type":"1"},
		{"id":"13","source":"11","target":"15","type":"1"},
		{"id":"14","source":"11","target":"16","type":"1"},

		{"id":"15","source":"13","target":"17","type":"1"},
		{"id":"16","source":"17","target":"18","type":"0"},
		{"id":"17","source":"18","target":"19","type":"0"},
		{"id":"18","source":"19","target":"20","type":"0"},
		{"id":"19","source":"15","target":"21","type":"2"},
		{"id":"20","source":"15","target":"22","type":"2"},
		{"id":"21","source":"15","target":"23","type":"2"}
	]
};

```

## Gantt, Sample HTML Code

```html
<script>
var gChart = drawGantt('mygantt', 'day');
</script>
```

