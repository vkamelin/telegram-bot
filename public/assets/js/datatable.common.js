(function (window, $) {
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  window.csrfToken = csrfToken;

  window.createDatatable = function (selector, url, columns, dataCallback, options) {
    const defaultOptions = {
      processing: true,
      serverSide: true,
      stateSave: true,
      ajax: {
        url: url,
        type: 'POST',
        data: function (d) {
          d._csrf_token = csrfToken;
          if (typeof dataCallback === 'function') {
            dataCallback(d);
          }
        }
      },
      columns: columns,
      lengthMenu: [[10,25,50,100,1000,-1],[10,25,50,100,1000,"Все"]],
      dom: 'Bfrtip',
      buttons: ['excel'],
      language: {
        url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Russian.json"
      }
    };

    const extraOptions = $.extend(true, {}, options);
    return $(selector).DataTable($.extend(true, {}, defaultOptions, extraOptions));
  };
})(window, jQuery);
