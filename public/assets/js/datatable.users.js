$(document).ready(function() {
  createDatatable('#usersTable', '/dashboard/users/data', [
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
  ]);
});
