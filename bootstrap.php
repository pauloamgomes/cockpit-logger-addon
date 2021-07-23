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
      if ($this->context['hostname'] && isset($_SERVER['REQUEST_URI'])) {
        $context['hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
      }
      if ($this->context['request_uri'] && isset($_SERVER['REQUEST_URI'])) {
        $context['request_uri'] = $_SERVER['REQUEST_URI'];
      }
      if ($this->context['referrer'] && isset($_SERVER['HTTP_REFERER'])) {
        $context['referrer'] = $_SERVER['HTTP_REFERER'];
      }
      if ($this->context['http_method'] && isset($_SERVER['REQUEST_METHOD'])) {
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

  'eventDisabled' => function ($event) {
    return isset($this->disabledEvents[$event]);
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

  'parseTextLogEntry' => function ($line) {
    $entry = [];

    // Check if line is in valid format.
    if (preg_match('/^\[.*\] cockpit.*: .* {.*} /', $line)) {
      // Extract date.
      if (preg_match('/^\[(.*)\] cockpit.*/', $line, $matches)) {
        $entry['date'] = $matches[1];
      }
      // Extract level.
      if (preg_match('/^\[.*\] cockpit\.(.*): .*/', $line, $matches)) {
        $entry['level'] = $matches[1];
      }
      // Extract user.
      if (preg_match('/"user":"([a-zA-Z-0-9-_]+)"/', $line, $matches)) {
        $entry['user'] = $matches[1];
      }
      // Extract message.
      if (preg_match('/\[.*\] cockpit\.[A-Z]+: (.*) {/', $line, $matches)) {
        $entry['message'] = $matches[1];
      }
      // Extra context/extra.
      if (preg_match('/\[.*\] cockpit\.[A-Z]+: .* (.*) /', $line, $matches)) {
        $extra = json_decode($matches[1]);
        $entry['extra'] = $matches[1];
      }
    }

    return $entry;
  },

  'parseJsonLogEntry' => function ($json, $dateFormat) {
    $entry = [];
    if (isset($json['message'])) {
      $entry['message'] = $json['message'];
    }
    if (isset($json['level_name'])) {
      $entry['level'] = $json['level_name'];
    }
    if (isset($json['datetime'])) {
      $entry['date'] = date($dateFormat, strtotime($json['datetime']['date']));
    }
    if (isset($json['context'])) {
      $entry['user'] = $json['context']['user'] ?? '';
    }
    return $entry;
  },

  'parseEntryType' => function($level) {
    switch ($level) {
      case 'ERROR':
      case 'CRITICAL':
      case 'ALERT':
      case 'EMERGENCY':
        return 'danger';
        break;

      case 'WARNING':
        return 'warning';
        break;
    }
    return 'info';
  },

  'parseEntryUser' => function($userEntry) {
    $username = $userEntry['user'] ?? $userEntry;

    $user = [
      'name' => $username,
      'id' => '',
    ];
    $account = $this->app->storage->findOne('cockpit/accounts', [
      'user' => $username,
    ]);
    if ($account && isset($account['_id'])) {
      $user['id'] = $account['_id'];
    }
    return $user;
  },

  'getLogContents' => function($filename, $maxRows = 100) {
    if (!file_exists($filename)) {
      return ['entries' => []];
    }
    $settings = $this->getSettings();
    $lines = $this->tail($filename, $maxRows);
    $entries = [];
    foreach ($lines as $idx => $line) {
      $json = json_decode($line, TRUE);
      if (is_array($json)) {
        $entry = $this->parseJsonLogEntry($json, $settings['dateFormat']);
      }
      else {
        $entry = $this->parseTextLogEntry($line);
      }
      if (empty($entry) || !isset($entry['user']) || !isset($entry['level'])) {
        continue;
      }
      $entry['type'] = $this->parseEntryType($entry['level']);
      $entry['user'] = $this->parseEntryUser($entry['user']);
      $entry['raw'] = $line;

      $entries[] = $entry;
    }

    return ['entries' => $entries];
  },

  'tail' => function($filepath, $lines = 100) {
    // Tails n lines of a text file.
    // Based on https://gist.github.com/lorenzos/1711e81a9162320fde20

    // Open file
    $f = @fopen($filepath, "rb");
    if ($f === FALSE) {
      return false;
    }
    // Jump to last character
    fseek($f, -1, SEEK_END);
    // Read it and adjust line number if necessary
    // (Otherwise the result would be wrong if file doesn't end with a blank line)
    if (fread($f, 1) != "\n") {
      $lines -= 1;
    }
    // Start reading
    $output = '';
    $chunk = '';
    // While we would like more
    while (ftell($f) > 0 && $lines >= 0) {
      // Figure out how far back we should jump
      $seek = min(ftell($f), 4096);
      // Do the jump (backwards, relative to where we are)
      fseek($f, -$seek, SEEK_CUR);
      // Read a chunk and prepend it to our output
      $output = ($chunk = fread($f, $seek)) . $output;
      // Jump back to where we started reading
      fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
      // Decrease our line counter
      $lines -= substr_count($chunk, "\n");
    }
    // While we have too many lines
    // (Because of buffer size we might have read too many)
    while ($lines++ < 0) {
      // Find first newline and remove all text before that
      $output = substr($output, strpos($output, "\n") + 1);
    }
    fclose($f);
    $lines = explode("\n", trim($output));
    return array_reverse($lines);
  },

  'getSettings' => function() {
    $settings = array_replace_recursive(
      $this->app->storage->getKey('cockpit/options', 'logger.settings', []),
      $this->app['config']['logger'] ?? []
    );

    return $settings;
  },

]);

// Initialize logging during cockpit bootstrap.
$app->on('cockpit.bootstrap', function () use ($app) {

  // Load translations (if any available).
  if ($translationspath = $app->path(__DIR__ . '/config/i18n/' . $app('i18n')->locale . '.php')) {
    $app('i18n')->load($translationspath, $app('i18n')->locale);
  }

  // Load settings (config.yaml can override db settings).
  $settings = array_replace_recursive(
    $app->storage->getKey('cockpit/options', 'logger.settings', []),
    $app['config']['logger'] ?? []
  );

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
  else if ($settings['handler'] == 'SyslogUdpHandler') {
    $handler = new Monolog\Handler\SyslogUdpHandler($settings['syslog']['host'], $settings['syslog']['port'], $settings['syslog']['facility'], $level, TRUE, $settings['syslog']['ident']);
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

  // Set disabled events.
  $events = [];
  if (!empty($settings['disabledEvents'])) {
    foreach ($settings['disabledEvents'] as $event) {
      $events[$event] = $event;
    }
  }
  $this->module('logger')->disabledEvents = $events;
  include_once __DIR__ . '/actions.php';
});

// If admin.
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
  include_once __DIR__ . '/admin.php';
}
