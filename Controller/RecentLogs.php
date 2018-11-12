<?php

namespace Logger\Controller;

use \Cockpit\AuthController;

/**
 * Admin controller class.
 */
class RecentLogs extends AuthController {

  /**
   * Default index controller.
   */
  public function index() {
    if (!$this->app->module('cockpit')->hasaccess('logger', 'manage.view')) {
      return FALSE;
    }

    $settings = $this->app->module('logger')->getSettings();

    $path = $this->app->path($settings['log']['path']);
    $handler = $settings['handler'];

    $filepath = $path . DIRECTORY_SEPARATOR . $settings['log']['filename'];

    if (!file_exists($filepath)) {
      $filepath = FALSE;
    }

    return $this->render('logger:views/logs/index.php', [
      'filepath' => $filepath,
      'handler' => $handler,
    ]);
  }

  public function download() {
    if (!$this->app->module('cockpit')->hasaccess('logger', 'manage.admin')) {
      return FALSE;
    }

    $settings = $this->app->module('logger')->getSettings();

    $path = $this->app->path($settings['log']['path']);

    $filepath = $path . DIRECTORY_SEPARATOR . $settings['log']['filename'];

    if (!$filepath || !is_readable($filepath)) {
      return FALSE;
    }

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", FALSE);
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=\"" . basename($filepath) . "\";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . filesize($filepath));
    readfile($filepath);

    $this->module('logger')->notice("Log file downloaded", [
      'filepath' => $filepath,
    ]);

    $this->app->stop();

  }

}
