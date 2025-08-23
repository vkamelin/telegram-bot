$(document).ready(function() {
  const csrfToken = window.csrfToken;

  createDatatable('#scheduledTable', '/dashboard/scheduled/data', [
    { data: 'id' },
    { data: 'user_id' },
    { data: 'method' },
    { data: 'type' },
    { data: 'priority' },
    { data: 'send_after' },
    { data: 'created_at' },
    {
      data: null,
      render: function(data, type, row) {
        const sendForm = '<form method="post" action="/dashboard/scheduled/' + row.id + '/send-now" class="d-inline">'
          + '<input type="hidden" name="_csrf_token" value="' + csrfToken + '">'
          + '<button type="submit" class="btn btn-sm btn-outline-secondary">Send now</button>'
          + '</form>';
        const delForm = '<form method="post" action="/dashboard/scheduled/' + row.id + '/delete" class="d-inline ms-1">'
          + '<input type="hidden" name="_csrf_token" value="' + csrfToken + '">'
          + '<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>'
          + '</form>';
        return sendForm + ' ' + delForm;
      },
      orderable: false,
      searchable: false
    }
  ]);
});
