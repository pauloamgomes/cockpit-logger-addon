<?php

/**
 * @file
 * Cockpit module bootstrap implementation.
 */

// Autoload Monolog library.
require __DIR__ . '/vendor/autoload.php';

$this->module('logger')->extend([

  'enabled' => FALSE,

  'debug' => function ($message, array $context = []) {
    $this->write($message, 'info', $context);
  },

  'info' => function ($message, array $context = []) {
    $this->write($message, 'info', $context);
  },

  'notice' => function($message, array $context = []) {
    $this->write($message, 'notice', $context);
  },

  'warning' => function($message, array $context = []) {
    $this->write($message, 'warning', $context);
  },

  'error' => function($message, array $context = []) {
    $this->write($message, 'error', $context);
  },

  'critical' => function($message, array $context = []) {
    $this->write($message, 'critical', $context);
  },

  'alert' => function($message, array $context = []) {
    $this->write($message, 'alert', $context);
  },

  'emergency' => function($message, array $context = []) {
    $this->write($message, 'emergency', $context);
  },

  'write' => function($message, $type, $context = []) {
    if ($this->enabled) {
      if ($this->context['user']) {
        $user = $this->app->module('cockpit')->getUser();
        if ($user) {
          $context['user'] = $user['user'];
        }
      }
      if ($this->context['hostname']) {
        $context['hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
      }
      if ($this->context['request_uri']) {
        $context['request_uri'] = $_SERVER['REQUEST_URI'];
      }
      if ($this->context['referrer']) {
        $context['referrer'] = $_SERVER['HTTP_REFERER'];
      }
      if ($this->context['http_method']) {
        $context['http_method'] = $_SERVER['REQUEST_METHOD'];
      }
      // Set some debug information.
      if ($this->level == 'DEBUG') {
        $duration_time = round(microtime(TRUE) - COCKPIT_START_TIME, 3);
        $bytes  = memory_get_peak_usage(TRUE);
        if ($bytes > 1024 * 1024) {
          $memory_usage = round($bytes / 1024 / 1024, 2).' MB';
        }
        elseif ($bytes > 1024) {
          $memory_usage = round($bytes / 1024, 2).' KB';
        }
        else {
          $memory_usage = $bytes . ' B';
        }

        $context['debug'] = [
          'duration_time' => $duration_time . ' Sec',
          'memory_usage' => $memory_usage,
          'loaded_files' => count(get_included_files())
        ];
      }

      // Write to log with message and context.
      $this->log->$type($message, $context);
    }
  },

  'getEvents' => function() {
    return $this->events;
  },

  'eventEnabled' => function($event) {
    return isset($this->events[$event]);
  },

  'saveSettings' => function ($settings) {
    // If handler is StreamHandler confirm that is possible to write.
    if ($settings['handler'] == 'StreamHandler') {
      if (!$path = $this->app->path($settings['log']['path'])) {
        $path = $settings['log']['path'];
      }
      if (!$this->app->helper('fs')->mkdir($path)) {
        return ['error' => 'mkdir'];
      }
    }
    // Save the settings in cockpit options.
    $this->app->storage->setKey('cockpit/options', 'logger.settings', $settings);
    return $settings;
  },

]);

// Initialize logging during cockpit bootstrap.
$app->on('cockpit.bootstrap', function () use ($app) {

  // Load translations (if any available).
  if ($translationspath = $app->path(__DIR__ . '/config/i18n/' . $app('i18n')->locale . '.php')) {
    $app('i18n')->load($translationspath, $app('i18n')->locale);
  }

  // Load settings.
  $settings = $app->storage->getKey('cockpit/options', 'logger.settings', []);

  // Stop here if no settings (e.g. logger not configured yet) or not enabled.
  if (!isset($settings['enabled']) || !$settings['enabled']) {
    return;
  }

  // Set level.
  $levels = Monolog\Logger::getLevels();
  $level = isset($levels[$settings['level']]) ? $levels[$settings['level']] : Monolog\Logger::INFO;

  // Set formatter.
  $formatterClass = 'Monolog\Formatter\\' . $settings['formatter'];
  $formatter = new $formatterClass(NULL, $settings['dateFormat']);

  // Set handler.
  if ($settings['handler'] == 'SyslogHandler') {
    $handler = new Monolog\Handler\SyslogHandler($settings['syslog']['ident'], $settings['syslog']['facility'], $level);
  }
  else {
    $path = $app->path($settings['log']['path']);
    $file = $path . DIRECTORY_SEPARATOR . $settings['log']['filename'];
    $handler = new Monolog\Handler\StreamHandler($file, $level);
  }
  $handler->setFormatter($formatter);
  $this->module('logger')->log = new Monolog\Logger('cockpit');
  $this->module('logger')->log->pushHandler($handler);
  $this->module('logger')->enabled = TRUE;
  $this->module('logger')->level = $settings['level'];
  $this->module('logger')->context = $settings['context'];

  // Act on cockpit core events.
  $events = [];
  foreach ($settings['events'] as $event) {
    if ($event['enabled']) {
      $events[$event['name']] = $event['name'];
    }
  }
  if (!empty($events)) {
    $this->module('logger')->events = $events;
    include_once __DIR__ . '/actions.php';
  }
});

// If admin.
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
  include_once __DIR__ . '/admin.php';
}
