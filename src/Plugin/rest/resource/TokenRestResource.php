<?php

namespace Drupal\json_web_token\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
* Provides a resource to get a JWT token.
*
* @RestResource(
*   id = "token_rest_resource",
*   label = @Translation("Token rest resource"),
*   uri_paths = {
*     "canonical" = "/api/v1/token",
*     "https://www.drupal.org/link-relations/create" = "/api/v1/token"
*   }
* )
*/

class TokenRestResource extends ResourceBase {
  public function post() {

    if($this->currentUser->isAnonymous()){
     $data['message'] = $this->t("Login failed. If you don't have an account register. If you forgot your credentials please reset your password.");
    }else{
     $data['message'] = $this->t('Login succeeded');
     $data['token'] = $this->generateToken();
    }

    return new ResourceResponse($data);
  }

  /**
  * Generates a new JWT.
  */
  protected function generateToken() {
    $token = new JsonWebToken();
    $event = new JwtAuthIssuerEvent($token);
    $this->eventDispatcher->dispatch(JwtAuthIssuerEvents::GENERATE, $event);
    $jwt = $event->getToken();

    return $this->transcoder->encode($jwt, array());
  }

}
