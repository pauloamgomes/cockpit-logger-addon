<?php

/**
 * @file
 * Cockpit Logger action functions.
 */

// Handle collections save.
$app->on('collections.save.after', function ($name, &$entry, $isUpdate) use ($app) {
  if ($this->module('logger')->eventDisabled('collections.save.after')) {
    return;
  }
  $this->module('logger')->notice("Collection entry saved", [
    '_id' => $entry['_id'],
    'collection' => $name,
    'isUpdate' => $isUpdate,
  ]);
});

// Handle collections save.
$app->on('collections.remove.after', function ($name, $result) use ($app) {
  if ($this->module('logger')->eventDisabled('collections.remove.after')) {
    return;
  }
  if ($result) {
    $this->module('logger')->notice("Collection entry removed", [
      'name' => $name,
    ]);
  }
  else {
    $this->module('logger')->error("Collection removal error", [
      'name' => $name,
    ]);
  }
});

// Handle collections creation.
$app->on('collections.createcollection', function ($collection) use ($app) {
  if ($this->module('logger')->eventDisabled('collections.createcollection')) {
    return;
  }
  $this->module('logger')->notice("Collection created", [
    '_id' => $collection['_id'],
    'name' => $collection['name'],
  ]);
});

// Handle collections update.
$app->on('collections.updatecollection', function ($collection) use ($app) {
  if ($this->module('logger')->eventDisabled('collections.updatecollection')) {
    return;
  }
  $this->module('logger')->notice("Collection updated", [
    '_id' => $collection['_id'],
    'name' => $collection['name'],
  ]);
});

// Handle collections removal.
$app->on('collections.removecollection', function ($name) use ($app) {
  if ($this->module('logger')->eventDisabled('collections.removecollection')) {
    return;
  }
  $this->module('logger')->notice("Collection removed", [
    'name' => $name,
  ]);
});

// Handle regions save.
$app->on('regions.save.after', function ($region) use ($app) {
  if ($this->module('logger')->eventDisabled('regions.save.after')) {
    return;
  }
  $this->module('logger')->notice("Region saved", [
    '_id' => $region['_id'],
    'name' => $region['name'],
  ]);
});

// Handle regions remove.
$app->on('regions.remove', function ($region) use ($app) {
  if ($this->module('logger')->eventDisabled('regions.remove')) {
    return;
  }
  $this->module('logger')->notice("Region removed", [
    '_id' => $region['_id'],
    'name' => $region['name'],
  ]);
});

// Handle singleton save.
$app->on('singleton.save.after', function ($singleton) use ($app) {
  if ($this->module('logger')->eventDisabled('singleton.save.after')) {
    return;
  }
  $this->module('logger')->notice("Singleton saved", [
    '_id' => $singleton['_id'],
    'name' => $singleton['name'],
  ]);
});

// Handle singleton data save.
$app->on('singleton.saveData.after', function ($singleton, $data) use ($app) {
  if ($this->module('logger')->eventDisabled('singleton.saveData.after')) {
    return;
  }
  $this->module('logger')->notice("Singleton data saved", [
    '_id' => $singleton['_id'],
    'type' => $singleton['name'],
  ]);
});

// Handle singleton removal.
$app->on('singleton.remove', function ($singleton) use ($app) {
  if ($this->module('logger')->eventDisabled('regions.remove')) {
    return;
  }
  $this->module('logger')->notice("Singleton removed", [
    '_id' => $singleton['_id'],
    'name' => $singleton['name'],
  ]);
});

// Handle forms save.
$app->on('forms.save.after', function ($name, $entry) use ($app) {
  if ($this->module('logger')->eventDisabled('forms.save.after')) {
    return;
  }
  $this->module('logger')->notice("Form saved", [
    'name' => $name,
    '_id' => $entry['_id'],
  ]);
});

// Assets save.
$app->on('cockpit.assets.save', function ($assets) use ($app) {
  if ($this->module('logger')->eventDisabled('cockpit.assets.save')) {
    return;
  }
  $asset = reset($assets);
  $this->module('logger')->notice("Asset saved", [
    'title' => $asset['title'],
    'path' => $asset['path'],
  ]);
});

// Assets remove.
$app->on('cockpit.assets.remove', function ($assets) use ($app) {
  if ($this->module('logger')->eventDisabled('cockpit.assets.remove')) {
    return;
  }
  $asset = reset($assets);
  $this->module('logger')->notice("Asset removed", [
    'title' => $asset['title'],
    'path' => $asset['path'],
    '_id' => $asset['_id'],
  ]);
});

// Media upload.
$app->on('cockpit.media.upload', function ($_uploaded, $_failed) use ($app) {
  if ($this->module('logger')->eventDisabled('cockpit.media.upload')) {
    return;
  }
  if (!empty($_uploaded)) {
    $this->module('logger')->notice("Media uploaded", [
      'path' => current($_uploaded),
    ]);
  }
  if (!empty($_failed)) {
    $this->module('logger')->error("Media failed to upload", [
      'path' => current($_failed),
    ]);
  }
});

// Media remove files.
$app->on('cockpit.media.removefiles', function ($deletions) use ($app) {
  if ($this->module('logger')->eventDisabled('cockpit.media.removefiles')) {
    return;
  }
  $this->module('logger')->notice("Media files removed", [
    'files' => $deletions,
  ]);
});

// Media rename files.
$app->on('cockpit.media.rename', function ($source, $target) use ($app) {
  if ($this->module('logger')->eventDisabled('cockpit.media.rename')) {
    return;
  }
  $this->module('logger')->notice("Media renamed", [
    'source' => $source,
    'target' => $target,
  ]);
});

// Account login.
$app->on('cockpit.authentication.success', function ($user) use ($app) {
  if ($this->module('logger')->eventDisabled('cockpit.authentication.success')) {
    return;
  }
  $this->module('logger')->notice("User logged in", [
    'user' => $user['user'],
  ]);
});

// Account login error.
$app->on('cockpit.authentication.failed', function ($user) use ($app) {
  if ($this->module('logger')->eventDisabled('cockpit.authentication.failed')) {
    return;
  }
  $this->module('logger')->notice("Login error", [
    'user' => $user,
  ]);
});

// Account logout.
$app->on('cockpit.account.logout', function ($user) use ($app) {
  if ($this->module('logger')->eventDisabled('cockpit.account.logout')) {
    return;
  }
  $this->module('logger')->notice("User logged out", [
    'user' => $user['user'],
  ]);
});

// Cache clear.
$app->on('cockpit.clearcache', function () use ($app) {
  if ($this->module('logger')->eventDisabled('cockpit.clearcache')) {
    return;
  }
  $this->module('logger')->notice("Cache clear executed");
});

// API Request error clear.
$app->on('cockpit.api.erroronrequest', function ($route, $error) use ($app) {
  if ($this->module('logger')->eventDisabled('cockpit.api.erroronrequest')) {
    return;
  }
  $this->module('logger')->error("API Request error", [
    'route' => $route,
    'error' => $error,
  ]);
});

// Cockpit request error.
$app->on('cockpit.request.error', function ($error) use ($app) {
  if ($this->module('logger')->eventDisabled('cockpit.request.error')) {
    return;
  }
  $this->module('logger')->error("Request error", [
    'error' => $error,
  ]);
});

// Cockpi imagestyles addon create style.
$app->on('imagestyles.createstyle', function ($style) {
  if ($this->module('logger')->eventDisabled('imagestyles.createstyle')) {
    return;
  }
  $this->module('logger')->notice("Image style created", [
    '_id' => $style['_id'],
    'name' => $style['name'],
  ]);
});

// Cockpi imagestyles addon save after.
$app->on('imagestyles.save.after', function ($style) {
  if ($this->module('logger')->eventDisabled('imagestyles.save.after')) {
    return;
  }
  $this->module('logger')->notice("Image style updated", [
    '_id' => $style['_id'],
    'name' => $style['name'],
  ]);
});

// Cockpi imagestyles addon remove style.
$app->on('imagestyles.remove', function ($style) {
  if ($this->module('logger')->eventDisabled('imagestyles.remove')) {
    return;
  }
  $this->module('logger')->notice("Image style removed", [
    '_id' => $style['_id'],
    'name' => $style['name'],
  ]);
});

// Custom logger events from other addons.
$app->on('logger.event', function($level, $message, $extra = []) use($app) {
  if (!in_array($level, ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'])) {
    $level = 'notice';
  }
  $this->module('logger')->$level($message, $extra);
});
