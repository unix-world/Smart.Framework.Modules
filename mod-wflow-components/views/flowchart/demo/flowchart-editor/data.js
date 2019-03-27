
var flowchartDataObj = {
  "docTitle": "",
  "docDate": "2019-02-05T21:11:52.788Z",
  "docType": "smartWorkFlow.FlowChart",
  "docVersion": "1.0",
  "dataFormat": "data/structure",
  "data": {
    "numberOfElements": 11,
    "nodes": [
      {
        "elementId": "flowchartWindow1",
        "positionX": 933,
        "positionY": 178,
        "clsName": "window jtk-node jtk-draggable jtk-connected jtk-endpoint-anchor jtk-managed",
        "usePerimeterAnchors": false,
        "isInverted": false,
        "label": "Process #1",
        "icon": "sfi sfi-cogs"
      },
      {
        "elementId": "flowchartWindow2",
        "positionX": 495,
        "positionY": 245,
        "clsName": "window jtk-node diamond jtk-draggable jtk-connected jtk-endpoint-anchor jtk-managed",
        "usePerimeterAnchors": false,
        "isInverted": true,
        "label": "Decision #2",
        "icon": "sfi sfi-cloud"
      },
      {
        "elementId": "flowchartWindow3",
        "positionX": 106,
        "positionY": 243,
        "clsName": "window jtk-node jtk-endpoint-anchor jtk-draggable jtk-connected jtk-managed",
        "usePerimeterAnchors": false,
        "isInverted": false,
        "label": "Process #3 Do Somenthing",
        "icon": ""
      },
      {
        "elementId": "flowchartWindow5",
        "positionX": 102,
        "positionY": 477,
        "clsName": "window jtk-node jtk-draggable jtk-connected jtk-endpoint-anchor jtk-managed",
        "usePerimeterAnchors": false,
        "isInverted": false,
        "label": "Process #5",
        "icon": ""
      },
      {
        "elementId": "flowchartWindow6",
        "positionX": 862,
        "positionY": 452,
        "clsName": "window jtk-node jtk-draggable jtk-connected jtk-endpoint-anchor jtk-managed",
        "usePerimeterAnchors": false,
        "isInverted": false,
        "label": "Process #6",
        "icon": ""
      },
      {
        "elementId": "flowchartElemTerminal_4d6a51cff85c52be0848570a557967a83649f22b",
        "positionX": 514,
        "positionY": 551,
        "clsName": "window jtk-node circle jtk-endpoint-anchor jtk-draggable jtk-connected jtk-managed",
        "usePerimeterAnchors": false,
        "isInverted": false,
        "label": "Stop",
        "icon": "sfi sfi-stop2"
      },
      {
        "elementId": "flowchartElemTerminal_26cb302894481b03df591da39406518d2ee1dd56",
        "positionX": 420,
        "positionY": 20,
        "clsName": "window jtk-node circle jtk-draggable jtk-connected jtk-endpoint-anchor jtk-managed",
        "usePerimeterAnchors": false,
        "isInverted": false,
        "label": "Start",
        "icon": "sfi sfi-play2"
      },
      {
        "elementId": "flowchartElemData_addd9d66360b025362aa565590cd2bfe7c2c029d",
        "positionX": 904,
        "positionY": 19,
        "clsName": "window jtk-node note jtk-draggable jtk-managed",
        "usePerimeterAnchors": null,
        "isInverted": null,
        "label": "IMPORTANT: This is only a sample Note from FlowChart Demo",
        "icon": "sfi sfi-checkmark2"
      },
      {
        "elementId": "flowchartElemData_53c2b85ce3b851db170d1cb06cf392f006d8a831",
        "positionX": 110,
        "positionY": 75,
        "clsName": "window jtk-node parallelogram jtk-endpoint-anchor jtk-draggable jtk-connected jtk-managed",
        "usePerimeterAnchors": true,
        "isInverted": false,
        "label": "Display",
        "icon": "sfi sfi-chrome"
      },
      {
        "elementId": "flowchartElemData_a269df5eb1ac38bfb940401c49a66f2ac7b16e04",
        "positionX": 751,
        "positionY": 61,
        "clsName": "window jtk-node parallelogram jtk-endpoint-anchor jtk-draggable jtk-connected jtk-managed",
        "usePerimeterAnchors": false,
        "isInverted": false,
        "label": "Display",
        "icon": "sfi sfi-firefox"
      },
      {
        "elementId": "flowchartElemDisplay_726c38be52c41e890297e519a0b3598236d788cb",
        "positionX": 269,
        "positionY": 386,
        "clsName": "window jtk-node oval jtk-endpoint-anchor jtk-draggable jtk-connected jtk-managed",
        "usePerimeterAnchors": false,
        "isInverted": false,
        "label": "Database",
        "icon": "sfi sfi-database"
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
        "textLabel": "No"
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
        "pageSourceId": "flowchartElemData_53c2b85ce3b851db170d1cb06cf392f006d8a831",
        "pageTargetId": "flowchartElemTerminal_26cb302894481b03df591da39406518d2ee1dd56",
        "sourceAnchor": "Perimeter",
        "targetAnchor": "Left",
        "textLabel": "Chrome"
      },
      {
        "pageSourceId": "flowchartElemData_a269df5eb1ac38bfb940401c49a66f2ac7b16e04",
        "pageTargetId": "flowchartElemTerminal_26cb302894481b03df591da39406518d2ee1dd56",
        "sourceAnchor": "Top",
        "targetAnchor": "Right",
        "textLabel": "Firefox"
      },
      {
        "pageSourceId": "flowchartElemDisplay_726c38be52c41e890297e519a0b3598236d788cb",
        "pageTargetId": "flowchartWindow2",
        "sourceAnchor": "Top",
        "targetAnchor": "Bottom",
        "textLabel": "Check in DB"
      }
    ]
  }
};
