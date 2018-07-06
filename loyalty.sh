curl -XPOST 192.168.1.110:9200/loyalty -d '{
  "settings":{
     "index":{
        "analysis":{
           "analyzer":{
              "lowercase_keyword":{
                 "tokenizer":"keyword",
                 "filter":"lowercase"
              }
           }
        }
     }
  },
  "mappings": {
    "members": {
      "properties": {
        "uuid": {
          "type": "string",
          "index": "not_analyzed"
        },
        "pin": {
          "type": "string",
          "index": "not_analyzed"
        },
        "qr": {
          "type": "string",
          "index": "not_analyzed"
        },
        "password": {
          "type": "string",
          "index": "not_analyzed"
        },
        "full_name": {
          "type": "string",
          "fields": {
              "raw": {
                  "type":  "string",
                  "index": "not_analyzed"
              }
          }
        },
        "first_name": {
          "type": "string",
          "copy_to": "full_name",
          "fields": {
              "raw": {
                  "type":  "string",
                  "index": "not_analyzed"
              }
          }
        },
        "last_name": {
          "type": "string",
          "copy_to": "full_name",
          "fields": {
              "raw": {
                  "type":  "string",
                  "index": "not_analyzed"
              }
          }
        },
        "phone": {
          "type": "string",
          "index": "not_analyzed"
        },
        "email": {
          "type": "string",
          "fields": {
              "raw": {
                  "type":  "string",
                  "index": "not_analyzed"
              }
          }
        },
        "created_at": {
          "format": "strict_date_optional_time||epoch_millis",
          "type": "date"
        },
        "projects": {
          "type": "nested",
          "properties": {
            "id": {
              "type": "string",
              "index": "not_analyzed"
            },
            "subscribed_at": {
              "format": "strict_date_optional_time||epoch_millis",
              "type": "date"
            }
          }
        }
      }
    },
    "points": {
      "properties": {
        "created_at": {
          "format": "strict_date_optional_time||epoch_millis",
          "type": "date"
        },
        "uuid": {
          "type": "string",
          "index": "not_analyzed"
        },
        "points": {
          "type": "double"
        },
        "type": {
          "type": "string",
          "fields": {
              "raw": {
                  "type":  "string",
                  "index": "not_analyzed"
              }
          }
        },
        "tags": {
          "type": "string",
          "analyzer": "lowercase_keyword",
          "fields": {
              "raw": {
                  "type":  "string",
                  "index": "not_analyzed"
              }
          }
        }
      }
    }
  }
}'