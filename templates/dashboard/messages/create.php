<?php
/** @var array $groups */
/** @var array $errors */
/** @var array $data */
/** @var string $csrfToken */
?>
<h1 class="mb-3">Send message</h1>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<form method="post" action="<?= url('/dashboard/messages/send') ?>" enctype="multipart/form-data">
    <input type="hidden" name="<?= $_ENV['CSRF_TOKEN_NAME'] ?? '_csrf_token' ?>" value="<?= $csrfToken ?>">

    <div class="mb-3">
        <label for="messageType" class="form-label">Type</label>
        <select class="form-select" name="type" id="messageType">
            <?php
            $types = [
                'text' => 'Text',
                'photo' => 'Photo',
                'audio' => 'Audio',
                'video' => 'Video',
                'document' => 'Document',
                'sticker' => 'Sticker',
                'animation' => 'Animation',
                'voice' => 'Voice',
                'video_note' => 'Video Note',
                'media_group' => 'Media Group'
            ];
            $curType = $data['type'] ?? 'text';
            foreach ($types as $val => $label): ?>
                <option value="<?= $val ?>" <?= $curType === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3 message-fields <?= $curType === 'text' ? '' : 'd-none' ?>" data-type="text">
        <textarea class="form-control" name="text" rows="3" placeholder="Text message"><?= htmlspecialchars($data['text'] ?? '') ?></textarea>
    </div>

    <div class="mb-3 message-fields <?= $curType === 'photo' ? '' : 'd-none' ?>" data-type="photo">
        <input class="form-control mb-2" type="file" name="photo">
        <input class="form-control mb-2" type="text" name="caption" placeholder="Caption" value="<?= htmlspecialchars($data['caption'] ?? '') ?>">
        <input class="form-control mb-2" type="text" name="parse_mode" placeholder="Parse mode" value="<?= htmlspecialchars($data['parse_mode'] ?? '') ?>">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="has_spoiler" id="photoSpoiler" <?= !empty($data['has_spoiler']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="photoSpoiler">Has spoiler</label>
        </div>
    </div>

    <div class="mb-3 message-fields <?= $curType === 'audio' ? '' : 'd-none' ?>" data-type="audio">
        <input class="form-control mb-2" type="file" name="audio">
        <input class="form-control mb-2" type="text" name="caption" placeholder="Caption" value="<?= htmlspecialchars($data['caption'] ?? '') ?>">
        <input class="form-control mb-2" type="text" name="parse_mode" placeholder="Parse mode" value="<?= htmlspecialchars($data['parse_mode'] ?? '') ?>">
        <input class="form-control mb-2" type="number" name="duration" placeholder="Duration" value="<?= htmlspecialchars($data['duration'] ?? '') ?>">
        <input class="form-control mb-2" type="text" name="performer" placeholder="Performer" value="<?= htmlspecialchars($data['performer'] ?? '') ?>">
        <input class="form-control" type="text" name="title" placeholder="Title" value="<?= htmlspecialchars($data['title'] ?? '') ?>">
    </div>

    <div class="mb-3 message-fields <?= $curType === 'video' ? '' : 'd-none' ?>" data-type="video">
        <input class="form-control mb-2" type="file" name="video">
        <input class="form-control mb-2" type="text" name="caption" placeholder="Caption" value="<?= htmlspecialchars($data['caption'] ?? '') ?>">
        <input class="form-control mb-2" type="text" name="parse_mode" placeholder="Parse mode" value="<?= htmlspecialchars($data['parse_mode'] ?? '') ?>">
        <div class="row g-2 mb-2">
            <div class="col"><input class="form-control" type="number" name="width" placeholder="Width" value="<?= htmlspecialchars($data['width'] ?? '') ?>"></div>
            <div class="col"><input class="form-control" type="number" name="height" placeholder="Height" value="<?= htmlspecialchars($data['height'] ?? '') ?>"></div>
        </div>
        <input class="form-control mb-2" type="number" name="duration" placeholder="Duration" value="<?= htmlspecialchars($data['duration'] ?? '') ?>">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="has_spoiler" id="videoSpoiler" <?= !empty($data['has_spoiler']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="videoSpoiler">Has spoiler</label>
        </div>
    </div>

    <div class="mb-3 message-fields <?= $curType === 'document' ? '' : 'd-none' ?>" data-type="document">
        <input class="form-control mb-2" type="file" name="document">
        <input class="form-control mb-2" type="text" name="caption" placeholder="Caption" value="<?= htmlspecialchars($data['caption'] ?? '') ?>">
        <input class="form-control" type="text" name="parse_mode" placeholder="Parse mode" value="<?= htmlspecialchars($data['parse_mode'] ?? '') ?>">
    </div>

    <div class="mb-3 message-fields <?= $curType === 'sticker' ? '' : 'd-none' ?>" data-type="sticker">
        <input class="form-control" type="file" name="sticker">
    </div>

    <div class="mb-3 message-fields <?= $curType === 'animation' ? '' : 'd-none' ?>" data-type="animation">
        <input class="form-control mb-2" type="file" name="animation">
        <input class="form-control mb-2" type="text" name="caption" placeholder="Caption" value="<?= htmlspecialchars($data['caption'] ?? '') ?>">
        <input class="form-control mb-2" type="text" name="parse_mode" placeholder="Parse mode" value="<?= htmlspecialchars($data['parse_mode'] ?? '') ?>">
        <div class="row g-2 mb-2">
            <div class="col"><input class="form-control" type="number" name="width" placeholder="Width" value="<?= htmlspecialchars($data['width'] ?? '') ?>"></div>
            <div class="col"><input class="form-control" type="number" name="height" placeholder="Height" value="<?= htmlspecialchars($data['height'] ?? '') ?>"></div>
        </div>
        <input class="form-control mb-2" type="number" name="duration" placeholder="Duration" value="<?= htmlspecialchars($data['duration'] ?? '') ?>">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="has_spoiler" id="animationSpoiler" <?= !empty($data['has_spoiler']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="animationSpoiler">Has spoiler</label>
        </div>
    </div>

    <div class="mb-3 message-fields <?= $curType === 'voice' ? '' : 'd-none' ?>" data-type="voice">
        <input class="form-control mb-2" type="file" name="voice">
        <input class="form-control mb-2" type="text" name="caption" placeholder="Caption" value="<?= htmlspecialchars($data['caption'] ?? '') ?>">
        <input class="form-control mb-2" type="text" name="parse_mode" placeholder="Parse mode" value="<?= htmlspecialchars($data['parse_mode'] ?? '') ?>">
        <input class="form-control" type="number" name="duration" placeholder="Duration" value="<?= htmlspecialchars($data['duration'] ?? '') ?>">
    </div>

    <div class="mb-3 message-fields <?= $curType === 'video_note' ? '' : 'd-none' ?>" data-type="video_note">
        <input class="form-control" type="file" name="video_note">
        <div class="row g-2 mt-2">
            <div class="col"><input class="form-control" type="number" name="length" placeholder="Length" value="<?= htmlspecialchars($data['length'] ?? '') ?>"></div>
            <div class="col"><input class="form-control" type="number" name="duration" placeholder="Duration" value="<?= htmlspecialchars($data['duration'] ?? '') ?>"></div>
        </div>
    </div>

    <div class="mb-3 message-fields <?= $curType === 'media_group' ? '' : 'd-none' ?>" data-type="media_group">
        <input class="form-control mb-2" type="file" name="media[]" multiple>
        <input class="form-control mb-2" type="text" name="caption" placeholder="Caption" value="<?= htmlspecialchars($data['caption'] ?? '') ?>">
        <input class="form-control" type="text" name="parse_mode" placeholder="Parse mode" value="<?= htmlspecialchars($data['parse_mode'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="modeAll" value="all" <?= (($data['mode'] ?? '') === 'all') ? 'checked' : '' ?>>
            <label class="form-check-label" for="modeAll">All users</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="modeSingle" value="single" <?= (($data['mode'] ?? '') === 'single') ? 'checked' : '' ?>>
            <label class="form-check-label" for="modeSingle">Single user</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="modeSelected" value="selected" <?= (($data['mode'] ?? '') === 'selected') ? 'checked' : '' ?>>
            <label class="form-check-label" for="modeSelected">Selected users</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="modeGroup" value="group" <?= (($data['mode'] ?? '') === 'group') ? 'checked' : '' ?>>
            <label class="form-check-label" for="modeGroup">Group</label>
        </div>
    </div>
    <div id="singleUserSection" class="mb-3 d-none">
        <input type="text" class="form-control" name="user" id="singleUserInput" placeholder="User ID or username" value="<?= htmlspecialchars($data['user'] ?? '') ?>">
    </div>
    <div id="selectedUsersSection" class="mb-3 d-none">
        <input type="text" class="form-control mb-2" id="userSearchInput" placeholder="Search user">
        <ul class="list-group mb-2" id="userSearchResults"></ul>
        <ul class="list-group" id="selectedUsers">
            <?php if (!empty($data['users']) && is_array($data['users'])): foreach ($data['users'] as $u): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center" data-user-id="<?= htmlspecialchars((string)$u) ?>">
                    <span><?= htmlspecialchars((string)$u) ?></span>
                    <input type="hidden" name="users[]" value="<?= htmlspecialchars((string)$u) ?>">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-user">Remove</button>
                </li>
            <?php endforeach; endif; ?>
        </ul>
    </div>
    <div id="groupSection" class="mb-3 d-none">
        <select class="form-select" name="group_id" id="groupSelect">
            <option value="">Select group</option>
            <?php foreach ($groups as $g): ?>
                <option value="<?= $g['id'] ?>" <?= (($data['group_id'] ?? '') == $g['id']) ? 'selected' : '' ?>><?= htmlspecialchars($g['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Send</button>
</form>
<script>
    window.tgUserSearchUrl = '<?= url('/dashboard/tg-users/search') ?>';
</script>
<script src="<?= url('/assets/js/message-send.js') ?>"></script>
