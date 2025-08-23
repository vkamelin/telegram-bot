$(document).ready(function() {
  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  $('#panelUsersTable').DataTable({
    processing: true,
    serverSide: true,
    stateSave: true,
    ajax: {
      url: '/dashboard/users/data',
      type: 'POST',
      data: function(d) {
        d._csrf_token = csrfToken;
      },
    },
    columns: [
      {data: 'id'},
      {data: 'email'},
      {data: 'telegram_user_id'},
      {data: 'created_at'},
      {data: 'updated_at'},
      {
        data: null,
        render: function(data, type, row) {
          return `<a href="/dashboard/users/${row.id}/edit">Edit</a>`;
        },
        sortable: false,
      },
    ],
  });
});
