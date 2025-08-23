$(document).ready(function() {
  const params = new URLSearchParams(window.location.search);

  createDatatable('#sessionsTable', '/dashboard/sessions/data', [
    { data: 'user_id' },
    { data: 'state' },
    { data: 'created_at' },
    { data: 'updated_at' }
  ], function(d) {
    ['state', 'period'].forEach(function(key) {
      if (params.has(key)) {
        d[key] = params.get(key);
      }
    });
  });
});
