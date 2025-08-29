$(document).ready(function() {
  const csrfToken = window.csrfToken;

  createDatatable('#scheduledTable', '/dashboard/scheduled/data', [
    { data: 'id' },
    { data: 'user_id' },
    { data: 'method' },
    { data: 'type' },
    { data: 'priority' },
    { data: 'send_after' },
    { data: 'status', render: function(data){
      if (data === 'pending') return '<span class="badge bg-warning text-dark">ожидает</span>';
      if (data === 'processing') return '<span class="badge bg-info text-dark">отправляется</span>';
      if (data === 'canceled') return '<span class="badge bg-secondary">отменено</span>';
      return data;
    }},
    { data: 'created_at' },
    {
      data: null,
      className: 'text-end',
      render: function(data, type, row) {
        const isPending = row.status === 'pending';
        const sendForm = isPending ? ('<form method="post" action="/dashboard/scheduled/' + row.id + '/send-now" class="d-inline">'
          + '<input type="hidden" name="_csrf_token" value="' + csrfToken + '">'
          + '<button type="submit" class="btn btn-sm btn-outline-secondary" title="Отправить сейчас">'
            + '<i class="bi bi-send"></i>'
          + '</button>'
          + '</form>') : '';
        const editLink = isPending ? ('<a href="/dashboard/scheduled/' + row.id + '/edit" class="btn btn-sm btn-outline-primary ms-1" title="Редактировать">'
          + '<i class="bi bi-pencil"></i>'
          + '</a>') : '';
        const cancelForm = isPending ? ('<form method="post" action="/dashboard/scheduled/' + row.id + '/cancel" class="d-inline ms-1">'
          + '<input type="hidden" name="_csrf_token" value="' + csrfToken + '">'
          + '<button type="submit" class="btn btn-sm btn-outline-warning" title="Отменить">'
            + '<i class="bi bi-x-circle"></i>'
          + '</button>'
          + '</form>') : '';
        const delForm = isPending ? ('<form method="post" action="/dashboard/scheduled/' + row.id + '/delete" class="d-inline ms-1">'
          + '<input type="hidden" name="_csrf_token" value="' + csrfToken + '">'
          + '<button type="submit" class="btn btn-sm btn-outline-danger" title="Удалить">'
            + '<i class="bi bi-trash"></i>'
          + '</button>'
          + '</form>') : '';
        return sendForm + editLink + cancelForm + delForm;
      },
      orderable: false,
      searchable: false
    }
  ]);
});
