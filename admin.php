<?php

/**
 * @file
 * Cockpit Logger admin functions.
 */

// Module ACL definitions.
$this("acl")->addResource('logger', [
  'manage.admin',
  'manage.view',
]);

// Bind admin routes.
$app->on('admin.init', function () use ($app) {
  $this->bindClass('Logger\\Controller\\Admin', 'settings/logger');

  // Only display recent logs if Logger is enabled and handler is StreamHandler.
  $enabled = $app->module('logger')->enabled;
  $settings = $app->module('logger')->getSettings();
  $permission = $app->module('cockpit')->hasaccess('logger', 'manage.view');

  if ($app->module('cockpit')->hasaccess('logger', 'manage.admin')) {
    // Add settings entry.
    $this->on('cockpit.view.settings.item', function () {
      $this->renderView("logger:views/partials/settings.php");
    });
  }

  if ($enabled && $permission && $settings['handler'] === 'StreamHandler') {
    $this->bindClass('Logger\\Controller\\RecentLogs', 'recent-logs');
    // Add to modules menu.
    $this('admin')->addMenuItem('modules', [
      'label' => 'Recent Logs',
      'icon'  => 'logger:icon.svg',
      'route' => '/recent-logs',
      'active' => strpos($this['route'], '/recent-logs') === 0,
    ]);
  }

});
