<style>
.uk-dropdown-scrollable {
  width: 600px;
  height: 170px;
  word-wrap: break-word;
}
.entry-date {
  white-space: nowrap;
}

.uk-badge.uk-alert-info {
  background-color: #AC92EC;
}

.uk-badge.uk-alert-danger {
  background-color: #d85030;
  color: #ffffff;
}
</style>

<div>
    <ul class="uk-breadcrumb">
        <li class="uk-active"><span>@lang('Recent Log Messages')</span></li>
    </ul>
</div>

<div class="uk-margin-top" riot-view>

    <div class="uk-form uk-clearfix" show="{!loading && entries.length}">
        <div class="uk-form-select">
          <a onclick="{pauseFetching}" class="uk-button uk-button-outline uk-text-uppercase uk-alert-success" show="{fetching}">
            <i class="uk-icon-spinner uk-icon-spin"></i> Fetching...
          </a>
          <a onclick="{resumeFetching}" class="uk-button uk-button-outline uk-text-uppercase uk-text-muted" show="{!fetching}">
            <i class="uk-icon-circle-o"></i> Paused
          </a>
        </div>
        <div class="uk-form-select">
          <a class="uk-link-muted uk-text-small"><i class="uk-margin-left uk-icon-long-arrow-down"></i> { maxRows } rows</a>
          <select class="uk-width-1-1" onchange="{ setMaxRows }">
              <option value="100">100 rows</option>
              <option value="200">200 rows</option>
              <option value="500">500 rows</option>
              <option value="1000">1000 rows</option>
          </select>
        </div>
        <span class="uk-form-icon">
            <i class="uk-icon-filter"></i>
            <input type="text" class="uk-form-large uk-form-blank" ref="txtfilter" placeholder="@lang('Filter by text...')" onchange="{ updatefilter }">
        </span>
        @if($app->module('cockpit')->hasaccess('logger', 'manage.admin'))
        <div class="uk-float-right">
            <a class="uk-button uk-button-primary uk-button-large" href="@route('/recent-logs/download')">
                <i class="uk-icon-download uk-icon-justify"></i> @lang('Download')
            </a>
        </div>
        @endif
    </div>

    <div class="uk-text-xlarge uk-text-center uk-text-primary uk-margin-large-top" show="{ loading }">
        <i class="uk-icon-spinner uk-icon-spin"></i>
    </div>

    <div class="uk-text-large uk-text-center uk-margin-large-top uk-text-muted" show="{ !loading && handler != 'StreamHandler' }">
        <img class="uk-svg-adjust" src="@url('assets:app/media/icons/database.svg')" width="100" height="100" alt="@lang('Invalid handler')" data-uk-svg />
        <p>@lang('Configured handler doesnt support fetching of logs! Switch to StreamHandler if you want to see logs here.')</p>
    </div>

    <div class="uk-text-large uk-text-center uk-margin-large-top uk-text-muted" show="{ !loading && !entries.length && handler == 'StreamHandler' }">
        <img class="uk-svg-adjust" src="@url('assets:app/media/icons/database.svg')" width="100" height="100" alt="@lang('Recent log messages')" data-uk-svg />
        <p>@lang('No recent log messages found!')</p>
    </div>

    <div class="uk-text-large uk-text-center uk-margin-large-top uk-text-muted" show="{ !filepath && handler == 'StreamHandler' }">
        <img class="uk-svg-adjust" src="@url('assets:app/media/icons/emoticon-sad.svg')" width="100" height="100" alt="@lang('Cannot open the log files')" data-uk-svg />
        <p>@lang('Cannot open the log file.')</p>
    </div>

    <table class="uk-table uk-table-tabbed uk-table-striped uk-margin-top" if="{ !loading && entries.length }">
        <thead>
            <tr>
                <th class="uk-text-small" width="10"></th>
                <th class="uk-text-small" width="140">@lang('Date')</th>
                <th class="uk-text-small" width="100">@lang('Level')</th>
                <th class="uk-text-small" width="150">@lang('User')</th>
                <th class="uk-text-small" width="70%">@lang('Message')</th>
            </tr>
        </thead>
        <tbody>
            <tr each="{entry, idx in entries}" show="{ infilter(entry) }">
                <td class="uk-text-center">
                  <div data-uk-dropdown="mode:'click', delay:300" onclick="{pauseFetching}">
                    <a class="extrafields-indicator uk-link-muted" data-uk-tooltip="pos:'right'"><i class="uk-icon-eye uk-icon-justify"></i></a>
                    <div class="uk-dropdown uk-dropdown-scrollable uk-text-left">
                        <span class="uk-text-small uk-text-uppercase uk-text-muted">@lang('Raw log entry')</span>
                        <div class="uk-margin-top uk-text-small"><raw content="{entry.raw}" /></div>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="entry-date uk-text-small">{entry.date}</td>
                <td><span class="uk-badge uk-text-small uk-alert-{entry.type}">{entry.level}</span></td>
                <td>
                  <a href="/accounts/account/{entry.user.id}" target="_blank" class="uk-text-small">
                    {entry.user.name}
                  </a>
                </td>
                <td>{entry.message}</td>
            </tr>
        </tbody>
    </table>


    <script type="view/script">

        var $this = this;

        this.mixin(RiotBindMixin);

        this.loading = true;
        this.fetching = true;
        this.filepath = {{ json_encode($filepath) }};
        this.handler = {{ json_encode($handler) }};
        this.entries = [];
        this.entry = false;
        this.maxRows = 100;

        this.on('mount', function() {
          $this.fetchData();
        });

        fetchData() {
          if (!this.fetching) {
            return;
          }
          App.callmodule('logger:getLogContents', [$this.filepath, $this.maxRows]).then(function(data) {
            if (data && data.result && data.result.entries) {
              $this.entries = data.result.entries;
              setTimeout(function() {
                $this.fetchData();
              }, 3500);
            } else {
              App.ui.notify(App.i18n.get("Cannot fetch log entries from " + $this.filepath), "danger");
            }
            $this.loading = false;
            $this.update();
          });
        }

        pauseFetching(e) {
          e.preventDefault();
          $this.fetching = false;
        }

        resumeFetching(e) {
          e.preventDefault();
          $this.fetching = true;
          $this.fetchData();
        }

        viewEntry(entry) {
          $this.entry = entry;
          $this.fetching = false;
        }

        hideLogView() {
          $this.entry = false;
          $this.update();
        }

        updatefilter(e) {
        }

        setMaxRows(e) {
          $this.maxRows = e.target.value;
        }

        infilter(entry, value, name, label) {
            if (!this.refs.txtfilter.value) {
                return true;
            }

            value = this.refs.txtfilter.value.toLowerCase();
            name  = [
              entry.date.toLowerCase(),
              entry.level.toLowerCase(),
              entry.user.name.toLowerCase(),
              entry.message.toLowerCase()
            ].join(' ');

            return name.indexOf(value) !== -1;
        }


    </script>

</div>

