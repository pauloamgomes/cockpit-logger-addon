<?php

/**
 * @file
 * Cockpit Logger admin functions.
 */

// Module ACL definitions.
$this("acl")->addResource('logger', [
  'manage.admin',
]);

// Add setting entry.
$this->on('cockpit.view.settings.item', function () {
  $this->renderView("logger:views/partials/settings.php");
});

// Bind admin routes.
$app->on('admin.init', function () use ($app) {
  $this->bindClass('Logger\\Controller\\Admin', 'settings/logger');
});
