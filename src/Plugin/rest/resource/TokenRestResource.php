<?php

namespace Drupal\json_web_token\Plugin\rest\resource;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\jwt\Transcoder\JwtTranscoderInterface;
use Drupal\jwt\Authentication\Event\JwtAuthGenerateEvent;
use Drupal\jwt\Authentication\Event\JwtAuthEvents;
use Drupal\jwt\JsonWebToken\JsonWebToken;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    /**
     * The JWT Transcoder service.
     *
     * @var \Drupal\jwt\Transcoder\JwtTranscoderInterface
     */
    protected $transcoder;

    /**
     * The event dispatcher.
     *
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * Constructs a HTTP basic authentication provider object.
     *
     * @param \Drupal\jwt\Transcoder\JwtTranscoderInterface $transcoder
     *   The jwt transcoder service.
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
     *   The event dispatcher service.
     */
    public function __construct() {
      $this->transcoder = JwtTranscoderInterface;
      $this->eventDispatcher = EventDispatcherInterface;
    }

  public function post() {
    if(\Drupal::currentUser()->isAnonymous()){
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
  public function generateToken() {
    $event = new JwtAuthGenerateEvent(new JsonWebToken());
    $this->eventDispatcher->dispatch(JwtAuthEvents::GENERATE, $event);
    $jwt = $event->getToken();
    return $this->transcoder->encode($jwt);
  }

}
