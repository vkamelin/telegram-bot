$(document).ready(function() {
  const csrfToken = $('meta[name="csrf-token"]').attr('content');

  $('#usersTable').DataTable({
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
      {data: 'user_id'},
      {data: 'username'},
      {data: 'first_name'},
      {data: 'last_name'},
      {data: 'alias'},
      {
        data: null,
        render: function(data, type, row) {
          return `<a href="/dashboard/users/${row.id}">View</a>`;
        },
        sortable: false,
      },
    ],
  });
});
