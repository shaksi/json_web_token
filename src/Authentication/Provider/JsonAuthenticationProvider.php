<?php

namespace Drupal\json_web_token\Authentication\Provider;

use Drupal\jwt\Transcoder\JwtTranscoderInterface;
use Drupal\jwt\Transcoder\JwtDecodeException;
use Drupal\jwt\Authentication\Event\JwtAuthGenerateEvent;
use Drupal\jwt\Authentication\Event\JwtAuthValidateEvent;
use Drupal\jwt\Authentication\Event\JwtAuthValidEvent;
use Drupal\jwt\Authentication\Event\JwtAuthEvents;
use Drupal\jwt\JsonWebToken\JsonWebToken;
use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * JWT Authentication Provider.
 */
class JsonAuthenticationProvider implements AuthenticationProviderInterface {
  public function applies(Request $request) {
    $content = json_decode($request->getContent());
    return isset($content->username, $content->password) && !empty($content->username) && !empty($content->password);
  }

  public function authenticate(Request $request) {
    $flood_config = $this->configFactory->get('user.flood');
    $content = json_decode($request->getContent());

    $username = $content->username;
    $password = $content->password;
    // Flood protection: this is very similar to the user login form code.
    // @see \Drupal\user\Form\UserLoginForm::validateAuthentication()
    // Do not allow any login from the current user's IP if the limit has been
    // reached. Default is 50 failed attempts allowed in one hour. This is
    // independent of the per-user limit to catch attempts from one IP to log
    // in to many different user accounts.  We have a reasonably high limit
    // since there may be only one apparent IP for all users at an institution.
    if ($this->flood->isAllowed('json_authentication_provider.failed_login_ip', $flood_config->get('ip_limit'), $flood_config->get('ip_window'))) {
     $accounts = $this->entityManager->getStorage('user')
       ->loadByProperties(array('name' => $username, 'status' => 1));
     $account = reset($accounts);
     if ($account) {
       if ($flood_config->get('uid_only')) {
         // Register flood events based on the uid only, so they apply for any
         // IP address. This is the most secure option.
         $identifier = $account->id();
       }
       else {
         // The default identifier is a combination of uid and IP address. This
         // is less secure but more resistant to denial-of-service attacks that
         // could lock out all users with public user names.
         $identifier = $account->id() . '-' . $request->getClientIP();
       }
       // Don't allow login if the limit for this user has been reached.
       // Default is to allow 5 failed attempts every 6 hours.
       if ($this->flood->isAllowed('json_authentication_provider.failed_login_user', $flood_config->get('user_limit'), $flood_config->get('user_window'), $identifier)) {
         $uid = $this->userAuth->authenticate($username, $password);
         if ($uid) {
           $this->flood->clear('json_authentication_provider.failed_login_user', $identifier);
           return $this->entityManager->getStorage('user')->load($uid);
         }
         else {
           // Register a per-user failed login event.
           $this->flood->register('json_authentication_provider.failed_login_user', $flood_config->get('user_window'), $identifier);
         }
       }
     }
    }

    // Always register an IP-based failed login event.
    $this->flood->register('json_authentication_provider.failed_login_ip', $flood_config->get('ip_window'));
    return [];
  }
}
