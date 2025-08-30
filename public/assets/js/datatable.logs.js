$(document).ready(function(){
  const $file = $('#logFile');
  const $level = $('#logLevel');
  const $search = $('#logSearch');

  function loadFiles() {
    return $.post('/dashboard/logs/files', {_csrf_token: window.csrfToken})
      .then(function(resp){
        const files = resp.files || [];
        $file.empty();
        files.forEach(function(f){
          const opt = $('<option/>').val(f.name).text(f.name + ' (' + f.mtime + ', ' + humanSize(f.size) + ')');
          $file.append(opt);
        });
        if (files.length > 0) {
          $file.val(files[0].name);
        }
      });
  }

  function humanSize(bytes) {
    const units = ['B','KB','MB','GB'];
    let i = 0; let n = bytes;
    while (n >= 1024 && i < units.length - 1) { n /= 1024; i++; }
    return (Math.round(n * 10)/10) + ' ' + units[i];
  }

  let table;
  loadFiles().then(function(){
    table = createDatatable('#logsTable', '/dashboard/logs/data', [
      { data: 'datetime' },
      { data: 'level_name' },
      { data: 'channel' },
      { data: 'message', render: function(v){ return $('<div/>').text(v || '').html(); } },
      { data: null, render: function(row){
          const cls = row.context_exception_class || '';
          const msg = row.context_exception_message || '';
          if (!cls && !msg) return '';
          return '<span title="' + $('<div/>').text(msg).html() + '">' + $('<div/>').text(cls).html() + '</span>';
        }
      },
      { data: 'request_id' }
    ], function(d){
      d.file = $file.val();
      d.level = $level.val();
      // DataTables already passes search.value, but we want instant filter button
      d.search = d.search || {};
      d.search.value = $search.val();
    }, {
      order: [[0, 'desc']]
    });
  });

  $('#logReload, #logLevel').on('click change', function(){ table && table.ajax.reload(); });
  $('#logFile').on('change', function(){ table && table.ajax.reload(); });
  $('#logSearch').on('keypress', function(e){ if (e.which === 13) { table && table.ajax.reload(); } });
});

