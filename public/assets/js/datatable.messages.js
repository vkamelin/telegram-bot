$(document).ready(function() {
  createDatatable('#messagesTable', '/dashboard/messages/data', [
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
      className: 'text-end',
      render: function(data, type, row) {
        return '<a href="/dashboard/messages/' + row.id + '/resend" class="btn btn-sm btn-outline-secondary">' +
            '<i class="bi bi-repeat" title="Отправит повторно"></i>' +
            '</a> '
          + '<a href="/dashboard/messages/' + row.id + '/response" class="btn btn-sm btn-outline-secondary">' +
            '<i class="bi bi-send" title="Ответить"></i>' +
            '</a>';
      },
      orderable: false,
      searchable: false
    }
  ]);
});
