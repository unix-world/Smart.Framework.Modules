
var flowchartDataObj = {
  "docTitle": "Sample FlowChart Editor",
  "docType": "smartWorkFlow.FlowChart",
  "docVersion": "1.0",
  "dataFormat": "data/structure",
  "data": {
      "numberOfElements": 9,
      "nodes": [
        {
          "elementId": "flowchartWindow1",
          "positionX": 933,
          "positionY": 178,
          "clsName": "window jtk-node jtk-draggable jtk-connected jtk-endpoint-anchor",
          "isInverted": false,
          "label": "Process #1",
          "icon": "fa fa-xl fa-cogs"
        },
        {
          "elementId": "flowchartWindow2",
          "positionX": 495,
          "positionY": 245,
          "clsName": "window jtk-node diamond jtk-draggable jtk-connected jtk-endpoint-anchor",
          "isInverted": true,
          "label": "Decision #2",
          "icon": "fa fa-xl fa-cloud"
        },
        {
          "elementId": "flowchartWindow3",
          "positionX": 106,
          "positionY": 243,
          "clsName": "window jtk-node jtk-endpoint-anchor jtk-draggable jtk-connected",
          "isInverted": false,
          "label": "Process #3 Do Somenthing",
          "icon": ""
        },
        {
          "elementId": "flowchartWindow5",
          "positionX": 102,
          "positionY": 477,
          "clsName": "window jtk-node jtk-draggable jtk-connected jtk-endpoint-anchor",
          "isInverted": false,
          "label": "Process #5",
          "icon": ""
        },
        {
          "elementId": "flowchartWindow6",
          "positionX": 862,
          "positionY": 452,
          "clsName": "window jtk-node jtk-draggable jtk-connected jtk-endpoint-anchor",
          "isInverted": false,
          "label": "Process #6",
          "icon": ""
        },
        {
          "elementId": "flowchartElemTerminal_4d6a51cff85c52be0848570a557967a83649f22b",
          "positionX": 514,
          "positionY": 551,
          "clsName": "window jtk-node circle jtk-endpoint-anchor jtk-draggable jtk-connected",
          "isInverted": false,
          "label": "Stop",
          "icon": ""
        },
        {
          "elementId": "flowchartElemTerminal_26cb302894481b03df591da39406518d2ee1dd56",
          "positionX": 420,
          "positionY": 20,
          "clsName": "window jtk-node circle jtk-endpoint-anchor jtk-draggable jtk-connected",
          "isInverted": false,
          "label": "Start",
          "icon": "fa fa-xl fa-play"
        },
        {
          "elementId": "flowchartElemDisplay_106ca31df6e8c7df10820550868c9ca448459b65",
          "positionX": 751,
          "positionY": 73,
          "clsName": "window jtk-node oval jtk-endpoint-anchor jtk-draggable jtk-connected",
          "isInverted": false,
          "label": "Display",
          "icon": "fa fa-xl fa-firefox"
        },
        {
          "elementId": "flowchartElemData_8c5ffe231f97045dccbfd0b5445992175c83851a",
          "positionX": 300,
          "positionY": 395,
          "clsName": "window jtk-node parallelogram jtk-endpoint-anchor jtk-draggable jtk-connected",
          "isInverted": false,
          "label": "Database",
          "icon": "fa fa-xl fa-database"
        }
      ],
      "connections": [
        {
          "pageSourceId": "flowchartWindow2",
          "pageTargetId": "flowchartWindow3",
          "sourceAnchor": "LeftMiddle",
          "targetAnchor": "RightMiddle",
          "textLabel": "Yes"
        },
        {
          "pageSourceId": "flowchartWindow3",
          "pageTargetId": "flowchartWindow5",
          "sourceAnchor": "BottomCenter",
          "targetAnchor": "LeftMiddle",
          "textLabel": ""
        },
        {
          "pageSourceId": "flowchartWindow6",
          "pageTargetId": "flowchartWindow5",
          "sourceAnchor": "TopCenter",
          "targetAnchor": "RightMiddle",
          "textLabel": ""
        },
        {
          "pageSourceId": "flowchartWindow3",
          "pageTargetId": "flowchartWindow3",
          "sourceAnchor": "TopCenter",
          "targetAnchor": "LeftMiddle",
          "textLabel": "Loop"
        },
        {
          "pageSourceId": "flowchartWindow2",
          "pageTargetId": "flowchartWindow1",
          "sourceAnchor": "RightMiddle",
          "targetAnchor": "LeftMiddle",
          "textLabel": ""
        },
        {
          "pageSourceId": "flowchartWindow1",
          "pageTargetId": "flowchartWindow6",
          "sourceAnchor": "BottomCenter",
          "targetAnchor": "RightMiddle",
          "textLabel": "Some Step"
        },
        {
          "pageSourceId": "flowchartWindow5",
          "pageTargetId": "flowchartElemTerminal_4d6a51cff85c52be0848570a557967a83649f22b",
          "sourceAnchor": "BottomCenter",
          "targetAnchor": "LeftMiddle",
          "textLabel": ""
        },
        {
          "pageSourceId": "flowchartWindow6",
          "pageTargetId": "flowchartElemTerminal_4d6a51cff85c52be0848570a557967a83649f22b",
          "sourceAnchor": "BottomCenter",
          "targetAnchor": "RightMiddle",
          "textLabel": ""
        },
        {
          "pageSourceId": "flowchartElemTerminal_26cb302894481b03df591da39406518d2ee1dd56",
          "pageTargetId": "flowchartWindow2",
          "sourceAnchor": "BottomCenter",
          "targetAnchor": "TopCenter",
          "textLabel": ""
        },
        {
          "pageSourceId": "flowchartElemDisplay_106ca31df6e8c7df10820550868c9ca448459b65",
          "pageTargetId": "flowchartWindow2",
          "sourceAnchor": "BottomCenter",
          "targetAnchor": "TopCenter",
          "textLabel": ""
        },
        {
          "pageSourceId": "flowchartElemData_8c5ffe231f97045dccbfd0b5445992175c83851a",
          "pageTargetId": "flowchartWindow2",
          "sourceAnchor": "TopCenter",
          "targetAnchor": "BottomCenter",
          "textLabel": "Check in DB"
        }
      ]
    }
};
