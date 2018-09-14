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

// Add setting entry.
$this->on('cockpit.view.settings.item', function () {
  $this->renderView("logger:views/partials/settings.php");
});

// Bind admin routes.
$app->on('admin.init', function () use ($app) {
  $this->bindClass('Logger\\Controller\\Admin', 'settings/logger');
  $this->bindClass('Logger\\Controller\\RecentLogs', 'recent-logs');

  if ($app->module('cockpit')->hasaccess('logger', 'manage.view')) {
    // Add to modules menu.
    $this('admin')->addMenuItem('modules', [
      'label' => 'Recent Logs',
      'icon'  => 'logger:icon.svg',
      'route' => '/recent-logs',
      'active' => strpos($this['route'], '/recent-logs') === 0,
    ]);
  }

});
