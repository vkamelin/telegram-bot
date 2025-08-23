$(document).ready(function() {
  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  $('#messagesTable').DataTable({
    processing: true,
    serverSide: true,
    stateSave: true,
    ajax: {
      url: '/dashboard/messages/data',
      type: 'POST',
      data: function(d) {
        d._csrf_token = csrfToken;
      }
    },
    dom: 'Bfrtip',
    buttons: ['excel'],
    columns: [
      { data: 'id' },
      { data: 'user_id' },
      { data: 'method' },
      { data: 'type' },
      { data: 'status' },
      { data: 'priority' },
      { data: 'error' },
      { data: 'code' },
      { data: 'processed_at' },
      {
        data: null,
        render: function(data, type, row) {
          return '<a href="/dashboard/messages/' + row.id + '/resend" class="btn btn-sm btn-outline-secondary">Resend</a> '
            + '<a href="/dashboard/messages/' + row.id + '/response" class="btn btn-sm btn-outline-secondary">Response</a>';
        },
        orderable: false,
        searchable: false
      }
    ]
  });
});
