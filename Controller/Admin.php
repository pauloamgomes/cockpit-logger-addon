<?php

namespace Logger\Controller;

use \Cockpit\AuthController;

/**
 * Admin controller class.
 */
class Admin extends AuthController {

  /**
   * Default index controller.
   */
  public function index() {
    if (!$this->app->module('cockpit')->hasaccess('logger', 'manage.admin')) {
      return FALSE;
    }

    // Initialize settings with defaults.
    $settings = array_replace_recursive([
      'enabled' => FALSE,
      'level' => 'INFO',
      'log' => [
        'path' => '/logs',
        'filename' => 'cockpit.log',
      ],
      'syslog' => [
        'ident' => 'cockpit',
        'facility' => 'local0',
        'port' => '514',
      ],
      'handler' => 'StreamHandler',
      'formatter' => 'JsonFormatter',
      'dateFormat' => 'Y-m-d H:i:s',
      'context' => [
        'user' => TRUE,
        'hostname' => FALSE,
        'http_method' => TRUE,
        'referrer' => TRUE,
        'request_uri' => TRUE,
      ],
      'events' => [
        ['name' => 'collections.save.after', 'enabled' => TRUE],
        ['name' => 'collections.remove.after', 'enabled' => TRUE],
        ['name' => 'collections.removecollection', 'enabled' => TRUE],
        ['name' => 'collections.createcollection', 'enabled' => TRUE],
        ['name' => 'collections.updatecollection', 'enabled' => TRUE],
        ['name' => 'regions.save.after', 'enabled' => TRUE],
        ['name' => 'regions.remove', 'enabled' => TRUE],
        ['name' => 'singleton.save.after', 'enabled' => TRUE],
        ['name' => 'singleton.saveData.after', 'enabled' => TRUE],
        ['name' => 'singleton.remove', 'enabled' => TRUE],
        ['name' => 'forms.save.after', 'enabled' => TRUE],
        ['name' => 'cockpit.assets.save', 'enabled' => TRUE],
        ['name' => 'cockpit.media.upload', 'enabled' => TRUE],
        ['name' => 'cockpit.media.removefiles', 'enabled' => TRUE],
        ['name' => 'cockpit.media.rename', 'enabled' => TRUE],
        ['name' => 'cockpit.assets.remove', 'enabled' => TRUE],
        ['name' => 'cockpit.account.login', 'enabled' => TRUE],
        ['name' => 'cockpit.account.logout', 'enabled' => TRUE],
        ['name' => 'cockpit.clearcache', 'enabled' => TRUE],
        ['name' => 'cockpit.api.erroronrequest', 'enabled' => TRUE],
        ['name' => 'cockpit.request.error', 'enabled' => TRUE],
        ['name' => 'imagestyles.save.after', 'enabled' => TRUE],
        ['name' => 'imagestyles.createstyle', 'enabled' => TRUE],
        ['name' => 'imagestyles.remove', 'enabled' => TRUE],
      ],
    ], $this->app->module('logger')->getSettings());

    return $this->render('logger:views/settings/index.php', ['settings' => $settings]);
  }

}
