# php-json-graph

PHP class drawing inspiration from Netflix's Falcor JSON Graph

[Netflix Falcor JSON Graph documentation](https://netflix.github.io/falcor/documentation/jsongraph.html)

I find it most useful when working with configurations. YAML seemed to be missing and/or depending on the parsing library missing features that were usefule like concatenation of a string with variables. But regular json can require lots of duplication and find and replace when one configuration variable changes.

This could be for environments or similar applications that could re-use configuration logic. This library can be embedded in your own code at runtime or could be used seperately as a configuration generator by passing in the JSON Graph typed array and then writing the full output back to your store.

Simple example, where the same contact person is used for all defined routes in an API

```json
{
  "contact": "me@example.com",
  "route1": {
    "contact": {
      "$type": "ref",
      "value": ["contact"]
    }
  },
  "route2": {
    "contact": {
      "$type": "ref",
      "value": ["contact"]
    }
  }
}
```

Usage:

```php
<?php
use JSONGraph\Model;

$contact_person = (new Model($json))->get()["route1"]["contact"];
echo $contact_person;

```

Nested example of setting the url in the config based on an environment variable.

```json
{
  "environment": {
    "production": "https://production.example.com",
    "staging": "https://staging.example.com",
    "development": "http://localhost:8080"
  },
  "url": {
    "$type": "ref",
    "value": [
      "environment",
      {
        "$type": "env",
        "value": "ENVIRONMENT"
      }
    ]
  }
}
```

```php
<?php
use JSONGraph\Model;

//using constant variable
$url = (new Model($json, [
    "ENVIRONMENT"=>"production"
]))->get()["url"];


//using environment variables
putenv('ENVIRONMENT=production'); //This may have been set in the code or through deployment configurations
$url = (new Model($json))->get()["url"];

echo $url;

```

More Examples to come...
