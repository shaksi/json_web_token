# json_web_token
drupal json web token based on https://dropsolid.com/en/blog/drupal-8-and-react-native

creates an api endpoint on drupal 8, which requires username/password to authenticate against drupal.
A nice starting point to work with react native etc.

endpoint - /api/v1/token

once enabled, endpoint needs to be enabled in /admin/config/services/rest (look for 'Token rest resource')

Granularity - resource
method -  post
request formats -  hal_jason, json
authentication - json_authentication_provider


example Curl Request

```curl
curl -X POST \
  'http://local.dev/api/v1/token?_format=hal_json' \
  -H 'authorization: json_auth' \
  -H 'cache-control: no-cache' \
  -H 'content-type: application/hal+json' \
  -H 'x-csrf-token: 6U3lTKQyc8azmDDro4FlcT7oFbfLpEFO9h3OUU8JB20' \
  -d '{"username": "admin", "password": "admin"}'
```