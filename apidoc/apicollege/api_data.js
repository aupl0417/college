define({ "api": [
  {
    "type": "post",
    "url": "/courseManage/addCourse.json",
    "title": "添加",
    "description": "<p>添加课程</p>",
    "name": "addCourse_json",
    "group": "courseManage",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "string",
            "optional": false,
            "field": "courseName",
            "description": "<p>课程名     必须</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\"id\": \"1001\",\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n\"id\": \"1002\",\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "/home/web/app/apicollege/model/Enroll/test.php",
    "groupTitle": "courseManage"
  }
] });
