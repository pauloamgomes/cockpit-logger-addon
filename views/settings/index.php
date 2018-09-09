<div>
    <ul class="uk-breadcrumb">
        <li><a href="@route('/settings')">@lang('Settings')</a></li>
        <li class="uk-active"><span>@lang('Logger Configuration')</span></li>
    </ul>
</div>

<div class="uk-margin-top" riot-view>
    <form id="account-form" class="uk-form uk-grid uk-grid-gutter" onsubmit="{ submit }">

      <div class="uk-grid-margin uk-width-medium-2-3">

        <div class="uk-form-row">
          <label class="uk-text-small">@lang('Enable/Disable log functionality')</label>
          <div class="uk-margin-top">
            <field-boolean bind="settings.enabled" title="@lang('Enabled')" label="@lang('Enabled')"></field-boolean>
          </div>
        </div>

        <div class="uk-form-row">
            <label class="uk-text-small">@lang('Context attributes to include on each log entry')</label>
            <div class="uk-margin-top">
              <field-boolean bind="settings.context.user" title="@lang('Enabled')" label="@lang('Log the Username')"></field-boolean>
            </div>
            <div class="uk-margin-top">
              <field-boolean bind="settings.context.hostname" title="@lang('Enabled')" label="@lang('Log the Hostname')"></field-boolean>
            </div>
            <div class="uk-margin-top">
              <field-boolean bind="settings.context.request_uri" title="@lang('Enabled')" label="@lang('Log the Request URI')"></field-boolean>
            </div>
            <div class="uk-margin-top">
              <field-boolean bind="settings.context.referrer" title="@lang('Enabled')" label="@lang('Log the Referrer')"></field-boolean>
            </div>
            <div class="uk-margin-top">
              <field-boolean bind="settings.context.http_method" title="@lang('Enabled')" label="@lang('Log the HTTP Method')"></field-boolean>
            </div>
        </div>

        <div class="uk-form-row">
          <div class="uk-form-select uk-margin-top">
            <label class="uk-text-small">@lang('Log Level')</label>
            <div class="uk-text-primary uk-margin-top">{ settings.level }</div>
            <select ref="selectLevel" class="uk-width-1-1 uk-form-large" onchange="{ toggleLevel }">
              <option each="{ option,idx in levels }" value="{ option }" selected="{ settings.level === option }">{ option }</option>
            </select>
            <p class="uk-text-small uk-text-muted"> @lang('Debug will set extra logging info and will include all other levels. Core events are logged with notice level.') </p>
          </div>
        </div>

        <div class="uk-form-row">
          <div class="uk-form-select">
            <label class="uk-text-small">@lang('Log Formatter')</label>
            <div class="uk-text-primary uk-margin-top">{ settings.formatter }</div>
            <select ref="selectFormatter" class="uk-width-1-1 uk-form-large" onchange="{ toggleFormatter }">
              <option each="{ option,idx in formatters }" value="{ option }" selected="{ settings.formatter === option }">{ option }</option>
            </select>
            <p class="uk-text-small uk-text-muted" if="{settings.formatter == 'LineFormatter'}"> @lang('Formats the log entries using plain text lines.')</p>
            <p class="uk-text-small uk-text-muted" if="{settings.formatter == 'JsonFormatter'}"> @lang('Formats the log entries using a JSON string that can be easily handled by a 3rd pary.')</p>
            <p class="uk-text-small uk-text-muted" if="{settings.formatter == 'HtmlFormatter'}"> @lang('Formats the log entries using HTML')</p>
          </div>
        </div>

        <div class="uk-form-row uk-panel-box">
            <label class="uk-text-small">@lang('Log Date Format')</label>
            <input class="uk-width-1-1 uk-form-large" type="text" ref="name" bind="settings.dateFormat" required="required">
            <p class="uk-text-small uk-text-muted"> @lang('Use a valid PHP date format (e.g. Y-m-d H:i:s)') </p>
        </div>

        <div class="uk-form-row">
          <div class="uk-form-select">
            <label class="uk-text-small">@lang('Log Handler')</label>
            <div class="uk-text-primary uk-margin-top">{ settings.handler }</div>
            <select ref="selectHandler" class="uk-width-1-1 uk-form-large" onchange="{ toggleHandler }">
              <option each="{ option,idx in handlers }" value="{ option }" selected="{ settings.handler === option }">{ option }</option>
            </select>
            <p class="uk-text-small uk-text-muted" if="{settings.handler == 'StreamHandler'}"> @lang('Saves log entries in the filesystem using the configured location and filename')</p>
            <p class="uk-text-small uk-text-muted" if="{settings.handler == 'SyslogHandler'}"> @lang('Writes the log entries using the operating system syslog functionality. Requires an ident and syslog facility.')</p>
          </div>
        </div>

        <div class="uk-form-row" if="{settings.handler === 'StreamHandler'}">
          <div class="uk-margin uk-panel-box">
            <label class="uk-text-small">@lang('Log location')</label>
            <input class="uk-width-1-1 uk-form-large" type="text" ref="name" bind="settings.log.path" required="required" pattern="(^#storage:[a-z_\-\s0-9]+)|(^\/[A-Z_a-z\-\s0-9\/\.]+)+">
            <p class="uk-text-small uk-text-muted"> @lang('Use either a relative location to the storage like "#storage:logs" or an absolute path like "/logs". In both cases ensure that web server can write on the target location.') </p>
          </div>
          <div class="uk-margin uk-panel-box">
            <label class="uk-text-small">@lang('Log filename')</label>
            <input class="uk-width-1-1 uk-form-large" type="text" ref="name" bind="settings.log.filename" required="required">
          </div>
        </div>

        <div class="uk-form-row" if="{settings.handler === 'SyslogHandler'}">
          <div class="uk-margin uk-panel-box">
            <label class="uk-text-small">@lang('Syslog Ident')</label>
            <input class="uk-width-1-1 uk-form-large" type="text" ref="name" bind="settings.syslog.ident" required="required">
            <p class="uk-text-small uk-text-muted">The syslog "ident" string to identify the program name (e.g. cockpit)</p>
          </div>
          <div class="uk-panel-box">
            <div class="uk-form-select">
              <label class="uk-text-small">@lang('Syslog Facility')</label>
              <div class="uk-text-primary uk-margin-top">{ settings.syslog.facility }</div>
              <select ref="selectFacility" class="uk-form-large uk-width-1-1" onchange="{ toggleFacility }">
                <option each="{ option,idx in facilities }" value="{ option }" selected="{ settings.syslog.facility === option }">{ option }</option>
              </select>
              <p class="uk-text-small uk-text-muted">Select the syslog facility to use.</p>
            </div>
          </div>
        </div>

        <div class="uk-form-row uk-width-1-3">
            <button class="uk-button uk-button-large uk-width-1-3 uk-button-primary uk-margin-right">@lang('Save')</button>
            <a href="@route('/settings')">@lang('Cancel')</a>
        </div>

      </div>

      <div class="uk-grid-margin uk-width-medium-1-3">
        <div class="uk-form-row">
            <label class="uk-text-small">@lang('Log Events')</label>

            <div class="uk-margin-top" each="{event, idx in settings.events}" onclick="{ setEvent }" style="cursor:pointer;">
                <div class="uk-form-switch">
                    <input ref="check" type="checkbox" id="{ event['name'] }" checked="{ event.enabled }"/>
                    <label for="{ event['name'] }"></label>
                </div>
                <span>{ event['name'] }</span>
            </div>

            <p class="uk-text-small uk-text-muted"> @lang('Set the Cockpit events to be automatically logged.') </p>
        </div>
      </div>

    </form>

    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.settings = {{ json_encode($settings) }};

        this.handlers = [
          'StreamHandler',
          'SyslogHandler'
        ];

        this.formatters = [
          'JsonFormatter',
          'LineFormatter',
          'HtmlFormatter'
        ];

        this.levels = [
          'DEBUG',
          'INFO',
          'NOTICE',
          'WARNING',
          'ERROR',
          'CRITICAL',
          'ALERT',
          'EMERGENCY'
        ];

        this.facilities = [
          'local0',
          'local1',
          'local2',
          'local3',
          'local4',
          'local5',
          'local6',
          'local7',
        ];

        this.on('mount', function() {
            this.trigger('update');

            App.$(this.refs.event).on('keydown', function(e) {

                if (e.keyCode == 13) {
                    e.preventDefault();

                    if ($this.settings.events.indexOf($this.refs.event.value.trim()) != -1) {
                        App.ui.notify("Event already exists");
                    } else {
                        $this.settings.events.push($this.refs.event.value.trim());
                    }

                    $this.refs.event.value = '';
                    $this.update();

                    return false;
                }

            });


            // bind clobal command + save
            Mousetrap.bindGlobal(['command+s', 'ctrl+s'], function(e) {
                e.preventDefault();
                $this.submit();
                return false;
            });
        });

        submit(e) {
            if(e) e.preventDefault();
            var settings = this.settings;
            App.callmodule('logger:saveSettings', [settings]).then(function(data) {
                if (data.result && !data.result.error) {
                    App.ui.notify(App.i18n.get("Settings saved successful"), "success");
                } else if(data.result.error) {
                    App.ui.notify(App.i18n.get("Cannot create log path directory"), "danger");
                } else {
                    App.ui.notify(App.i18n.get("Saving of settings failed."), "danger");
                }
            });
        }

        toggleLevel() {
          this.settings.level = this.refs.selectLevel.value;
        }

        toggleFacility() {
          this.settings.syslog.facility = this.refs.selectFacility.value;
        }

        toggleHandler() {
          this.settings.handler = this.refs.selectHandler.value;
        }

        toggleFormatter() {
          this.settings.formatter = this.refs.selectFormatter.value;
        }

        setEvent(e) {
          e.preventDefault();
          $this.settings.events[e.item.idx].enabled = !$this.settings.events[e.item.idx].enabled;
        }

    </script>

</div>
