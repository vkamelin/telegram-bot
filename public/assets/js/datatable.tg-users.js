$(document).ready(function() {
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const params = new URLSearchParams(window.location.search);

  $('#tgUsersTable').DataTable({
    processing: true,
    serverSide: true,
    stateSave: true,
    ajax: {
      url: '/dashboard/tg-users/data',
      type: 'POST',
      data: function(d) {
        d._csrf_token = csrfToken;
        ['is_premium','is_user_banned','is_bot_banned','is_subscribed','language_code'].forEach(function(key) {
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
      { data: 'username' },
      { data: 'language_code' },
      { data: 'is_premium' },
      { data: 'is_subscribed' },
      { data: 'is_user_banned' },
      { data: 'is_bot_banned' },
      {
        data: null,
        render: function(data, type, row) {
          return '<a href="/dashboard/tg-users/' + row.id + '" class="btn btn-sm btn-outline-secondary">View</a>';
        },
        orderable: false,
        searchable: false
      }
    ]
  });
});
