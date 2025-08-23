$(document).ready(function() {
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const params = new URLSearchParams(window.location.search);

  $('#sessionsTable').DataTable({
    processing: true,
    serverSide: true,
    stateSave: true,
    ajax: {
      url: '/dashboard/sessions/data',
      type: 'POST',
      data: function(d) {
        d._csrf_token = csrfToken;
        ['state', 'period'].forEach(function(key) {
          if (params.has(key)) {
            d[key] = params.get(key);
          }
        });
      }
    },
    dom: 'Bfrtip',
    buttons: ['excel'],
    columns: [
      { data: 'user_id' },
      { data: 'state' },
      { data: 'created_at' },
      { data: 'updated_at' }
    ]
  });
});
