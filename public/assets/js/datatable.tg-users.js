$(document).ready(function() {
  const params = new URLSearchParams(window.location.search);

  createDatatable('#tgUsersTable', '/dashboard/tg-users/data', [
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
  ], function(d) {
    ['is_premium','is_user_banned','is_bot_banned','is_subscribed','language_code'].forEach(function(key) {
      if (params.has(key)) {
        d[key] = params.get(key);
      }
    });
  });
});
