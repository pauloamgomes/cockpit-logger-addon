# Cockpit CMS Logger Addon

This addon extends Cockpit CMS (Next) core by providing logging functionality based on the awesome [Monolog Library](https://github.com/Seldaek/monolog).

## Installation

Installation can be performed with ot without php composer.

### Without php composer
1. Download zip and extract to 'your-cockpit-docroot/addons/Logger' (e.g. cockpitcms/addons/Logger)
2. Install Monolog dependency using composer
```bash
$ cd your-cockpit-docroot/addons/Logger
$ composer install
```
3. Access module settings `https://your-cockpit-site/settings/logger` and confirm that page loads.

### Using php composer
1. Install addon using composer
```bash
$ cd your-cockpit-docroot/addons
$ composer create-project pauloamgomes/logger Logger
```

## Configuration

The Logger operations are available to the super admin user, and they can be used by other users if they belong to a group with proper permissions. The following permissions are defined:

  - *manage.admin* → Can access Logger settings

Above ACLs can be added to the global configuration file as below:

```yaml
groups:
  # manage logger settings
  managers:
    Logger:
        manage.admin: true
  # view log entries
  editors:
    Logger:
        manage.view: true
```

### Main Settings

The settings page provide all the main configurations:

![Cockpit Logger settings page](https://monosnap.com/image/5F2FjRF4Wzypte4dikVB1uP8vWPSaA.png)

1. **Enable** - Check that option to enable the logging

2. **Context attributes** - included in the logging entry as an extra array

   * Username - Includes the username of the active user
   * Hostname - Includes the hostname of the request
   * Request URI - Includes the request URI
   * Referrer - Includes the Referrer URI
   * HTTP Method - Includes the HTTP method of the request

3. **Log Level** - Set the log level

   Cockpit Logger uses the same log levels defined in Monolog:

   * DEBUG
   * INFO
   * NOTICE
   * WARNING
   * ERROR
   * CRITICAL
   * ALERT
   * EMERGENCY

   When its set to DEBUG, additional details about the request will be included in the log entry:

   * Duration time
   * Memory used
   * Number of loaded php files

### Monolog settings

The Monolog sections are resumed to the Formatter and Handler.

#### Formatters

A formatter defines the structure of the log entries,
Cockpit Logger supports three Monolog formatters

| Formatter       | Purpose                                                                                  |
| :---------------| :----------------------------------------------------------------------------------------|
| LineFormatter   | Formats a log record into a one-line string. |
| JsonFormatter   | Formats a log record into json                                                           |
| HtmlFormatter   | Used to format log records into a human readable html table, mainly suitable for emails. |

Its also possible to define the format of the date in the log entries, the value must be a valid PHP date format like *Y-m-d H:i:s*

#### Handlers

A handler defines how the logs will be saved. Cockpit Logger provides two Handlers:

| Handler          | Purpose                                                                         |
| :--------------- | :------------------------------------------------------------------------------ |
| StreamHandler    | Saves log entries in the filesystem using the configured location and filename  |
| SyslogHandler    | Writes the log entries using the operating system syslog functionality. Requires an ident and syslog facility. |
| SyslogHandler    | Writes the log entries using the operating system syslog functionality. Requires an ident and syslog facility. |
| SyslogUdpHandler | Pushes the log entries to a remove rsyslog server. |

The StreamHandler requires two additional settings:

   - **Log Location** - Use either a relative location to the storage like "#storage:logs" or an absolute path like "/logs". In both cases ensure that web server can write on the target location
   - **Log Filename** - The filename (e.g. cockpit.log)

The SyslogHandler requires also to additional settings:

   - **Ident** - A string to identify the program name (e.g. cockpit)
   - **Facility** - The Syslog Facility to use

#### Settings via config.yaml

Besides the configuration page the settings can also be defined in the cockpit config/config.yaml file, e.g.:

```yaml
# Logger addon
logger:
  enabled: 1,
  level: INFO
  handler: StreamHandler
  formatter: LineFormatter
  context:
    user: 1
    hostname: 0
    http_method: 1
    referrer: 1
    request_uri: 1
```

### Event Settings

Most relevant Cockpit events (e.g. User login, Collection removal) can be set to be logged automatically:

   * collections.save.after
   * collections.remove.after
   * collections.removecollection
   * collections.createcollection
   * collections.updatecollection
   * regions.save.after
   * regions.remove
   * singleton.save.after
   * singleton.saveData.after
   * singleton.remove
   * forms.save.after
   * cockpit.assets.save
   * cockpit.media.upload
   * cockpit.media.removefiles
   * cockpit.media.rename
   * cockpit.assets.remove
   * cockpit.account.login
   * cockpit.account.logout
   * cockpit.clearcache
   * cockpit.api.erroronrequest
   * cockpit.request.error
   * imagestyles.save.after
   * imagestyles.createstyle
   * imagestyles.remove

### Examples

* Saving a collection entry using **LineFormatter** and **NOTICE** level:
```bash
[2018-09-08 23:06:20] cockpit.NOTICE: Collection entry saved {"_id":"5aa5024609677doc2021128895","collection":"simpleblock","isUpdate":true,"user":"admin","hostname":"traefik_global.docker_default","request_uri":"/collections/save_entry/simpleblock","http_method":"POST"}
```
* Saving a collection entry using **LineFormatter** and **DEBUG** level:
```bash
[2018-09-08 23:06:46] cockpit.NOTICE: Collection entry saved {"_id":"5aa5024609677doc2021128895","collection":"simpleblock","isUpdate":true,"user":"admin","hostname":"traefik_global.docker_default","request_uri":"/collections/save_entry/simpleblock","http_method":"POST","debug":{"duration_time":"0.007 Sec","memory_usage":"2 MB","loaded_files":67}}
```
* Saving a collection entry using the **JsonFormatter** and **DEBUG** Level:
```json
{
    "channel": "cockpit",
    "context": {
        "_id": "5aa5024609677doc2021128895",
        "collection": "simpleblock",
        "debug": {
            "duration_time": "0.008 Sec",
            "loaded_files": 67,
            "memory_usage": "2 MB"
        },
        "hostname": "traefik_global.docker_default",
        "http_method": "POST",
        "isUpdate": true,
        "request_uri": "/collections/save_entry/simpleblock",
        "user": "admin"
    },
    "datetime": {
        "date": "2018-09-08 23:09:10.434059",
        "timezone": "UTC",
        "timezone_type": 3
    },
    "extra": [],
    "level": 250,
    "level_name": "NOTICE",
    "message": "Collection entry saved"
}
```
* Saving a collection entry using the **HtmlFormatter** and **NOTICE** Level:
![HtmlFormatter Example](https://monosnap.com/image/S2UWRNV3ktlOIWH1o8D6rBStETeot9.png)

## Logging on other addons using the Logger functions:

The Cockpit Logger can be used on any other code by just invoking the module, e.g.:
```php
$this->app->module('logger')->info('writing a new log entry', ['hello' => 'world']);
$this->app->module('logger')->warning('something weird happened');
$this->app->module('logger')->error('something very wrong happened', ['error' => $e]);

```

## Viewing the most recent logs in Cockpit

Logs can be accessed on the server and processed using any tool, additionaly its also possible to access from the UI
to the most recent log entries - `https://your-cockpit-site/recent-logs`.

![Recent log messages](https://monosnap.com/image/HaxDOWGzODFqHRKsVl1GVcJbAJeW2R.png)

The UI supports automatic fetch and dynamic filters:

![Recent log messages using filters](https://monosnap.com/image/V7C6x1cXgGgj66BR73NS3Cv2aOklvI.png)

## Security considerations

When using the StreamLogger and the log location is set to the `#storage:logs` ensure that file is not public accessible.

## Copyright and license

Copyright 2018 pauloamgomes under the MIT license.


