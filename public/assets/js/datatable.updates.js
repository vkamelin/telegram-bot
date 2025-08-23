$(document).ready(function() {
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  const params = new URLSearchParams(window.location.search);

  $('#updatesTable').DataTable({
    processing: true,
    serverSide: true,
    stateSave: true,
    ajax: {
      url: '/dashboard/updates/data',
      type: 'POST',
      data: function(d) {
        d._csrf_token = csrfToken;
        ['type', 'user_id', 'message_id', 'created_from', 'created_to'].forEach(function(key) {
          if (params.has(key)) {
            d[key] = params.get(key);
          }
        });
      }
    },
    dom: 'Bfrtip',
    buttons: ['excel'],
    columns: [
      { data: 'id' },
      { data: 'update_id' },
      {
        data: 'user_id',
        render: function(data) {
          return '<a href="?user_id=' + data + '">' + data + '</a>';
        }
      },
      {
        data: 'message_id',
        render: function(data) {
          return data ? '<a href="?message_id=' + data + '">' + data + '</a>' : '';
        }
      },
      { data: 'type' },
      { data: 'sent_at' },
      { data: 'created_at' },
      {
        data: null,
        render: function(data, type, row) {
          return '<a href="/dashboard/updates/' + row.id + '" class="btn btn-sm btn-outline-secondary">JSON</a>';
        },
        orderable: false,
        searchable: false
      }
    ]
  });
});
