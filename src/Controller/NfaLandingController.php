<?php

namespace Drupal\nfa_landing\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for NFA Landing routes.
 */
class NfaLandingController extends ControllerBase {

  /**
   * Returns a page title.
   */
  public function title() {
    $site = $this->config('nfa_landing.settings')->get('site');
    if (isset($this->config('nfa_landing.settings')->get('sites')[$site])) {
      $site = $this->config('nfa_landing.settings')->get('sites')[$site]['name'];
    }

    return $site;
  }

  /**
   * Builds the page content.
   */
  public function build() {

    // If the user is logged in redirect to the front page.
    if ($this->currentUser()->isAuthenticated()) {
      $url = Url::fromRoute('<front>');
      return new RedirectResponse($url->toString());
    }

    // Create links to other NFA sites using the current active environment. For
    // example if we are in the staging environment of Farmers, the links should
    // be to the staging environments of Forests and BRMS.
    $active_environment = $this->config('environment_indicator.indicator')->get();
    $sites = $this->config('nfa_landing.settings')->get('sites');
    $active_site = $this->config('nfa_landing.settings')->get('site');

    if (empty($active_environment) || empty($active_site) || empty($sites)) {
      $build['content'] = [
        '#type' => 'item',
        '#markup' => $this->t('The NFA Landing page has not been configured. Contact the system administrator and <a href="/user/login">click here to login</a>.'),
      ];
      return $build;
    }
    $sites_links = [];
    foreach ($sites as $site) {
      if (strtolower($site['name']) != $active_site) {
        $url = Url::fromUri($site[strtolower($active_environment['name'])]);
        $sites_links[] = Link::fromTextAndUrl(t('Go to @site', ['@site' => $site['name']]), $url);
      }
    }
    // Create a link to another environments of the active site. For example if
    // we are in the staging environment of Farmers, the link should be to the
    // production environment of Farmers.
    if ($active_environment['name'] == 'Staging') {
      $url = Url::fromUri($sites[$active_site]['production']);
      $env_link = Link::fromTextAndUrl(t('Click here to go to production'), $url);
    }
    else {
      // Development and production will show a link to Staging.
      $url = Url::fromUri($sites[$active_site]['staging']);
      $env_link = Link::fromTextAndUrl(t('Click here to go to staging'), $url);
    }

    $build['content'] = [
      '#theme' => 'nfa_landing',
      '#message' => $this->config('nfa_landing.settings')->get('message'),
      '#site_links' => $sites_links,
      '#env_link' => $env_link,
      '#active_env' => $active_environment,
      '#login_link' => Link::fromTextAndUrl(t('Login to @site', ['@site' => $sites[$active_site]['name']]), Url::fromRoute('user.login')),
    ];

    return $build;
  }

}
