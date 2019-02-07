
var demo_tasks = {
  "docTitle": "",
  "docDate": "2019-01-28T17:05:24.634Z",
  "docType": "smartWorkFlow.TodoList",
  "docVersion": "1.0",
  "dataFormat": "data/structure",
  "data": {
    "view": "day",
    "date": "2017-05-03",
    "now": "2017-05-07",
    "todos": {
      "data": [
        {
          "id": 1,
          "text": "Project #1",
          "start_date": "2017-05-02 00:00",
          "duration": 77,
          "progress": 0.4,
          "open": true,
          "type": "project",
          "meta1": "one",
          "end_date": "2017-07-17 03:00",
          "parent": 0
        },
        {
          "id": 2,
          "text": "Task <a> #1",
          "details": "Some <b> details...",
          "start_date": "2017-05-02 00:00",
          "parent": "1",
          "progress": 0.5,
          "open": true,
          "type": "flextask",
          "duration": 77,
          "end_date": "2017-07-17 03:00"
        },
        {
          "id": 5,
          "text": "Task #1.1",
          "start_date": "2017-05-02 00:00",
          "duration": 7,
          "parent": "2",
          "progress": 0.6,
          "open": false,
          "end_date": "2017-05-09 00:00"
        },
        {
          "id": 6,
          "text": "Task #1.2",
          "start_date": "2017-05-03 00:00",
          "duration": 7,
          "parent": "2",
          "progress": 0.6,
          "open": true,
          "end_date": "2017-05-10 00:00"
        },
        {
          "id": 3,
          "text": "Task #2",
          "start_date": "2017-05-11 00:00",
          "duration": 8,
          "parent": "1",
          "progress": 0.6,
          "open": true,
          "end_date": "2017-05-19 00:00"
        },
        {
          "id": 7,
          "text": "Task #2.1",
          "start_date": "2017-05-12 00:00",
          "duration": 8,
          "parent": "3",
          "progress": 0.6,
          "open": true,
          "color": "#778899",
          "end_date": "2017-05-20 00:00"
        },
        {
          "id": 4,
          "text": "Task #3",
          "start_date": "2017-05-13 00:00",
          "duration": 6,
          "parent": "1",
          "progress": 0.5,
          "open": true,
          "color": "#003399",
          "end_date": "2017-05-19 00:00"
        },
        {
          "id": 8,
          "text": "Task #3.1",
          "start_date": "2017-05-14 00:00",
          "duration": 5,
          "parent": "4",
          "progress": 0.5,
          "open": true,
          "end_date": "2017-05-19 00:00"
        },
        {
          "id": 9,
          "text": "Task #3.2",
          "start_date": "2017-05-14 00:00",
          "duration": 4,
          "parent": "4",
          "progress": 0.5,
          "open": true,
          "end_date": "2017-05-18 00:00"
        },
        {
          "id": 10,
          "text": "Milestone #1 <i>",
          "start_date": "2017-05-14 00:00",
          "parent": "4",
          "open": true,
          "type": "milestone",
          "end_date": "2017-05-14 00:00",
          "duration": 0
        },
        {
          "id": 11,
          "text": "Project #2",
          "start_date": "2017-05-02 00:00",
          "duration": 12,
          "progress": 0.6,
          "open": true,
          "type": "project",
          "color": "#5C996B",
          "end_date": "2017-05-14 00:00",
          "parent": 0
        },
        {
          "id": 12,
          "text": "Task #1",
          "start_date": "2017-05-03 00:00",
          "duration": 5,
          "parent": "11",
          "progress": 1,
          "open": true,
          "end_date": "2017-05-08 00:00"
        },
        {
          "id": 13,
          "text": "Task #2",
          "start_date": "2017-05-02 00:00",
          "duration": 7,
          "parent": "11",
          "progress": 0.5,
          "open": true,
          "color": "#FF3300",
          "end_date": "2017-05-09 00:00"
        },
        {
          "id": 17,
          "text": "Task #2.1",
          "start_date": "2017-05-03 00:00",
          "duration": 2,
          "parent": "13",
          "progress": 1,
          "open": true,
          "end_date": "2017-05-05 00:00"
        },
        {
          "id": 18,
          "text": "Task #2.2",
          "start_date": "2017-05-06 00:00",
          "duration": 3,
          "parent": "13",
          "progress": 0.8,
          "open": true,
          "end_date": "2017-05-09 00:00"
        },
        {
          "id": 19,
          "text": "Task #2.3",
          "start_date": "2017-05-10 00:00",
          "duration": 4,
          "parent": "13",
          "progress": 0.2,
          "open": true,
          "end_date": "2017-05-14 00:00"
        },
        {
          "id": 20,
          "text": "Task #2.4",
          "start_date": "2017-05-10 00:00",
          "duration": 4,
          "parent": "13",
          "progress": 0,
          "open": true,
          "end_date": "2017-05-14 00:00"
        },
        {
          "id": 14,
          "text": "Task #3",
          "start_date": "2017-05-02 00:00",
          "duration": 6,
          "parent": "11",
          "progress": 0.8,
          "open": true,
          "end_date": "2017-05-08 00:00"
        },
        {
          "id": 15,
          "text": "Task #4",
          "start_date": "2017-05-02 00:00",
          "duration": 5,
          "parent": "11",
          "progress": 0.2,
          "open": true,
          "end_date": "2017-05-07 00:00"
        },
        {
          "id": 21,
          "text": "Task #4.1",
          "start_date": "2017-05-03 00:00",
          "duration": 4,
          "parent": "15",
          "progress": 0.5,
          "open": true,
          "color": "#29487D",
          "end_date": "2017-05-07 00:00"
        },
        {
          "id": 22,
          "text": "Task #4.2",
          "start_date": "2017-05-03 00:00",
          "duration": 4,
          "parent": "15",
          "progress": 0.1,
          "open": true,
          "color": "#666699",
          "end_date": "2017-05-07 00:00"
        },
        {
          "id": "X23",
          "text": "Task #4.3",
          "start_date": "2017-05-03 00:00",
          "duration": 5,
          "parent": "15",
          "progress": 0,
          "open": true,
          "color": "#FFCC00",
          "textColor": "#111111",
          "end_date": "2017-05-08 00:00"
        },
        {
          "id": 16,
          "text": "Task #5",
          "start_date": "2017-05-02 00:00",
          "duration": 7,
          "parent": "11",
          "progress": 0,
          "open": true,
          "end_date": "2017-05-09 00:00"
        }
      ],
      "links": [
        {
          "id": "1",
          "source": "1",
          "target": "2",
          "type": "1"
        },
        {
          "id": "2",
          "source": "2",
          "target": "3",
          "type": "0"
        },
        {
          "id": "3",
          "source": "3",
          "target": "4",
          "type": "0"
        },
        {
          "id": "4",
          "source": "2",
          "target": "5",
          "type": "2"
        },
        {
          "id": "5",
          "source": "2",
          "target": "6",
          "type": "2"
        },
        {
          "id": "6",
          "source": "3",
          "target": "7",
          "type": "2"
        },
        {
          "id": "7",
          "source": "4",
          "target": "8",
          "type": "2"
        },
        {
          "id": "8",
          "source": "4",
          "target": "9",
          "type": "2"
        },
        {
          "id": "9",
          "source": "4",
          "target": "10",
          "type": "2"
        },
        {
          "id": "10",
          "source": "11",
          "target": "12",
          "type": "1"
        },
        {
          "id": "11",
          "source": "11",
          "target": "13",
          "type": "1"
        },
        {
          "id": "12",
          "source": "11",
          "target": "14",
          "type": "1"
        },
        {
          "id": "13",
          "source": "11",
          "target": "15",
          "type": "1"
        },
        {
          "id": "14",
          "source": "11",
          "target": "16",
          "type": "1"
        },
        {
          "id": "15",
          "source": "13",
          "target": "17",
          "type": "1"
        },
        {
          "id": "16",
          "source": "17",
          "target": "18",
          "type": "0"
        },
        {
          "id": "17",
          "source": "18",
          "target": "19",
          "type": "0"
        },
        {
          "id": "18",
          "source": "19",
          "target": "20",
          "type": "0"
        },
        {
          "id": "19",
          "source": "15",
          "target": "21",
          "type": "2"
        },
        {
          "id": "20",
          "source": "15",
          "target": "22",
          "type": "2"
        },
        {
          "id": "21",
          "source": "15",
          "target": "X23",
          "type": "2"
        }
      ]
    }
  }
};

