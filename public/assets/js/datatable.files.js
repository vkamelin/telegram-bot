$(document).ready(function() {
  createDatatable('#filesTable', '/dashboard/files/data', [
    { data: 'id' },
    { data: 'type' },
    { data: 'original_name' },
    { data: 'mime_type' },
    { data: 'size' },
    { data: 'created_at' },
    {
      data: null,
      className: 'text-end',
      render: function(data, type, row) {
        return '<a href="/dashboard/files/' + row.id + '" class="btn btn-sm btn-outline-secondary">View</a>';
      },
      orderable: false,
      searchable: false
    }
  ]);
});
