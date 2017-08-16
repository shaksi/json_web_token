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
