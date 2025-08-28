$(function () {
  const csrfToken = window.csrfToken || $('meta[name="csrf-token"]').attr('content');
  window.csrfToken = csrfToken;
  const searchUrl = window.tgUserSearchUrl;

  const $typeSelect = $('#messageType');
  const $messageFields = $('.message-fields');

  function toggleMessageFields() {
    const type = $typeSelect.val();
    $messageFields.each(function () {
      const $section = $(this);
      const active = $section.data('type') === type;
      $section.toggleClass('d-none', !active);
      $section.find('input,textarea,select').prop('disabled', !active);
    });
  }
  $typeSelect.on('change', toggleMessageFields);
  toggleMessageFields();

  const $modeRadios = $('input[name="mode"]');
  const $sendModeRadios = $('input[name="send_mode"]');
  const $sendAfterInput = $('#sendAfterInput');
  const $singleSection = $('#singleUserSection');
  const $selectedSection = $('#selectedUsersSection');
  const $groupSection = $('#groupSection');

  function toggleSections() {
    const mode = $modeRadios.filter(':checked').val();
    $singleSection.toggleClass('d-none', mode !== 'single');
    $selectedSection.toggleClass('d-none', mode !== 'selected');
    $groupSection.toggleClass('d-none', mode !== 'group');
  }
  $modeRadios.on('change', toggleSections);
  toggleSections();

  function toggleSchedule() {
    const v = $sendModeRadios.filter(':checked').val();
    const enabled = v === 'schedule';
    $sendAfterInput.prop('disabled', !enabled);
  }
  $sendModeRadios.on('change', toggleSchedule);
  toggleSchedule();

  const $searchInput = $('#userSearchInput');
  const $searchResults = $('#userSearchResults');
  const $selectedUsers = $('#selectedUsers');
  let searchTimer;

  $searchInput.on('input', function () {
    const q = $(this).val().trim();
    clearTimeout(searchTimer);
    if (q.length < 2) {
      $searchResults.empty();
      return;
    }
    searchTimer = setTimeout(function () {
      $.post(searchUrl, {
        _csrf_token: csrfToken,
        user_id: q,
        username: q,
        first_name: q,
        last_name: q
      }, function (data) {
        $searchResults.empty();
        data.forEach(function (u) {
          const name = u.username ? '@' + u.username : ((u.first_name || '') + ' ' + (u.last_name || '')).trim();
          $searchResults.append('<li class="list-group-item d-flex justify-content-between align-items-center">'
            + '<span>' + name + ' (' + u.user_id + ')</span>'
            + '<button type="button" class="btn btn-sm btn-secondary add-user-btn" data-user-id="' + u.user_id + '" data-name="' + (u.username ? '@' + u.username : u.user_id) + '">Add</button>'
            + '</li>');
        });
      }, 'json');
    }, 300);
  });

  $searchResults.on('click', '.add-user-btn', function () {
    const id = $(this).data('user-id');
    const name = $(this).data('name');
    if ($selectedUsers.find('li[data-user-id="' + id + '"]').length) {
      return;
    }
    $selectedUsers.append('<li class="list-group-item d-flex justify-content-between align-items-center" data-user-id="' + id + '">'
      + '<span>' + name + '</span>'
      + '<input type="hidden" name="users[]" value="' + id + '">'
      + '<button type="button" class="btn btn-sm btn-outline-danger remove-user">Remove</button>'
      + '</li>');
  });

  $selectedUsers.on('click', '.remove-user', function () {
    $(this).closest('li').remove();
  });
});
