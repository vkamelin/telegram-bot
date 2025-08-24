<?php
/**
 * @var array $push
 * @var string $csrfToken Токен CSRF
 * @var array  $errors
 */

$imageUrl = ''; // URL загруженного изображения
$videoUrl = ''; // URL загруженного видео

// Если есть загруженные файлы, то получаем их URL
if (!empty($push['media_filename']) && $push['media_type'] !== 'text') {
    $imageUrl = ($push['media_type'] === 'photo') ? url("/uploads/pushes/{$push['media_filename']}") : '';
    $videoUrl = ($push['media_type'] === 'video') ? url("/uploads/pushes/{$push['media_filename']}") : '';
}

$isNew = empty($push['id']); // Новая рассылка или редактирование
?>

<?php if ($isNew) { ?>
<h1 class="mb-5">Создать рассылку</h1>
<?php } else { ?>
<h1 class="mb-5">Редактировать рассылку</h1>
<?php } ?>

<?php if (!empty($errors)) { ?>
    <div class="alert alert-danger" role="alert">
        <?= implode('<br>', $errors) ?>
    </div>
<?php } ?>

<form method="post" enctype="multipart/form-data" class="mb-5" id="push-form">
    <input type="hidden" name="<?= $_ENV['CSRF_TOKEN_NAME'] ?? '_csrf_token' ?>" value="<?= $csrfToken ?>">
    <input type="hidden" name="send" id="send" value="0">

    <?php if (!$isNew) { ?>
        <div class="d-flex justify-content-start align-items-center mb-5">
            <h4>Рассылка ID <?= $push['id'] ?></h4>
            <p class="lead mx-5">создана <?= $push['created_at'] ?></p>
            <p class="lead">обновлена <?= $push['updated_at'] ?></p>
        </div>
    <?php } ?>

    <div class="row mb-5">
        <div class="col-md-6">
            <label for="image" class="form-label fw-bold">Изображение</label>
            <input type="file" class="file" id="image" name="image" data-browse-on-zone-click="true">
        </div>
        <div class="col-md-6">
            <label for="video" class="form-label fw-bold">Видео</label>
            <input type="file" class="file" id="video" name="video" data-browse-on-zone-click="true">
        </div>
    </div>
    
    <div class="mb-5">
        <div>
            <label for="text" class="form-label fw-bold">Контент</label>
            <textarea class="form-control" id="text" name="text" rows="10" placeholder="Введите текст рассылки" style="display: none;"></textarea>
        </div>

        <div class="btn-group btn-group-sm">
            <a type="button" class="btn btn-outline-secondary" onclick="execCmd('bold')" title="Жирный">
                <i class="bi bi-type-bold"></i>
            </a>
            <a type="button" class="btn btn-outline-secondary" onclick="execCmd('italic')" title="Курсив">
                <i class="bi bi-type-italic"></i>
            </a>
            <a type="button" class="btn btn-outline-secondary" onclick="execCmd('underline')" title="Подчёркнутый">
                <i class="bi bi-type-underline"></i>
            </a>
            <a type="button" class="btn btn-outline-secondary" onclick="execCmd('strikeThrough')" title="Зачёркнутый">
                <i class="bi bi-type-strikethrough"></i>
            </a>
            <a type="button" class="btn btn-outline-secondary" onclick="insertSpoiler()" title="Спойлер">
                <i class="bi bi-bricks"></i>
            </a>
            <a type="button" class="btn btn-outline-secondary" onclick="insertLink()" title="Ссылка">
                <i class="bi bi-link-45deg"></i>
            </a>
            <a type="button" class="btn btn-outline-secondary" onclick="wrapSelection('code')" title="Моноширинный">
                <i class="bi bi-code"></i>
            </a>
            <a type="button" class="btn btn-outline-secondary" onclick="insertBlockquote()" title="Цитата">
                <i class="bi bi-quote"></i>
            </a>
            <a type="button" class="btn btn-outline-secondary" onclick="clearFormatting()" title="Очистить форматирование">
                <i class="bi bi-eraser"></i>
            </a>
        </div>
        
        <div id="editor" contenteditable="true" class="mt-1"><?= $push['text'] ?></div>
    </div>

    <div class="row mb-5">
        <div class="col-6">
            <label for="button_text" class="form-label fw-bold">Текст кнопки</label>
            <input type="text" class="form-control" id="button_text" name="button_text" value="<?= $push['button_text'] ?>"
                   placeholder="Введите текст кнопки">
        </div>
        <div class="col-6">
            <label for="button_url" class="form-label fw-bold">Ссылка кнопки</label>
            <input type="text" class="form-control" id="button_url" name="button_url" value="<?= $push['button_url'] ?>"
                   placeholder="Введите ссылку кнопки">
        </div>
    </div>
    
    <input type="hidden" id="scheduled_at" name="scheduled_at" value="">
    
    <div class="d-flex justify-content-between align-items-center mb-5">
        <button type="submit" name="save" class="btn btn-outline-success mb-5">
            <i class="bi bi-floppy"></i>
            Сохранить
        </button>
        <button type="button" name="send" class="btn btn-outline-primary mb-5" data-bs-toggle="modal" data-bs-target="#confirm-send-modal">
            <i class="bi bi-send"></i>
            Отправить
        </button>
    </div>
    
</form>

<div class="modal" id="confirm-send-modal" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <p class="fw-bold">Рассылка отправится по всей базе пользователей. Отправить сейчас?</p>
            </div>
            <div class="modal-footer">
                <a type="button" class="btn btn-outline-success" id="send-push" data-bs-dismiss="modal">
                    <i class="bi bi-send"></i>
                    Да
                </a>
                <a type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">
                    <i class="bi bi-slash-circle"></i>
                    Нет
                </a>
            </div>
        </div>
    </div>
</div>


<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.5.2/css/fileinput.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs5.min.css" rel="stylesheet">
<style>
    .file-drop-zone-title {
        font-size: 1rem !important;
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-fileinput/5.5.2/js/fileinput.min.js"></script>
<script src="https://cdn.jsdelivr.net/gh/kartik-v/bootstrap-fileinput@5.5.0/js/locales/ru.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.4.0/purify.min.js"></script>

<script>
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  
  $(document).ready(function() {
    $('#send-push').on('click', () => {
      $('#send').val(1);
      $('#push-form').trigger('submit');
    });
  });

  $("#image").fileinput({
      <?php if ($push['media_type'] === 'photo'): ?>
    initialPreview: ["<?= $imageUrl ?>"],
    initialPreviewConfig: [{
      type: "image",
      url: "<?= url("/dashboard/pushes/{$push['id']}/delete-image")?>",
      key: <?= $push['id']?>,
      caption: "<?= $push['image'] ?>"
    }],
      <?php endif; ?>
    initialPreviewAsData: true,
    overwriteInitial: false,
    showUpload: false,
    showRemove: true,
    language: 'ru',
    deleteExtraData: {_csrf_token: csrfToken}
  });

  $("#video").fileinput({
      <?php if ($push['media_type'] === 'video'): ?>
    initialPreview: ["<?= $videoUrl ?>"],
    initialPreviewConfig: [{
      type: "video",
      filetype: "video/mp4",
      url: "<?= url("/dashboard/pushes/{$push['id']}/delete-video")?>",
      key: <?= $push['id']?>,
      caption: "Видео",
      downloadUrl: "<?= $videoUrl ?>",
      filename: "<?= $push['video'] ?>"
    }],
      <?php endif; ?>
    initialPreviewAsData: true,
    overwriteInitial: false,
    showUpload: false,
    showRemove: true,
    deleteExtraData: {_csrf_token: csrfToken}
  });

  // Стандартные команды форматирования
  function execCmd(command, value = null) {
    document.execCommand(command, false, value);
  }

  // Обёртка выделенного текста в указанный тег
  function wrapSelection(tag) {
    const sel = window.getSelection();
    if (sel.rangeCount) {
      const range = sel.getRangeAt(0);
      const wrapper = document.createElement(tag);
      wrapper.appendChild(range.extractContents());
      range.insertNode(wrapper);
    }
  }

  // Вставка спойлера (тег <tg-spoiler>)
  function insertSpoiler() {
    const sel = window.getSelection();
    if (!sel.rangeCount) return;

    const range = sel.getRangeAt(0);
    const spoiler = document.createElement("tg-spoiler");
    spoiler.appendChild(range.extractContents());
    range.insertNode(spoiler);
  }

  // Вставка ссылки (тег <a>)
  function insertLink() {
    const url = prompt("Введите URL:", "http://");
    if (url) {
      document.execCommand('createLink', false, url);
    }
  }

  // Вставка цитаты (тег <blockquote>)
  function insertBlockquote() {
    const sel = window.getSelection();
    if (sel.rangeCount) {
      const range = sel.getRangeAt(0);
      const blockquote = document.createElement("blockquote");
      blockquote.appendChild(range.extractContents());
      range.insertNode(blockquote);
    }
  }

  // Очистка форматирования: убираем все HTML теги, оставляя только текст с переносами строк
  function clearFormatting() {
    const editor = document.getElementById("editor");
    const text = editor.innerText; // сохранит переносы строк
    editor.innerText = text;
  }

  // Обработка клавиши Enter: вставляем символ перевода строки вместо абзаца
  document.getElementById("editor").addEventListener("keydown", function(e) {
    if (e.key === "Enter") {
      e.preventDefault();
      document.execCommand('insertText', false, '\n');
    }
  });

  // Функция для очистки ненужных <br> тегов из HTML
  function sanitizeContent(html) {
    // Заменяем все <br> (с возможным пробелом и слэшем) на символ перевода строки
    return html.replace(/<br\s*\/?>/gi, '\n');
  }

  // Перед отправкой формы копируем содержимое редактора в textarea
  $('#push-form').on('submit', function(e) {
    // Забираем innerHTML (там уже есть \n при вашем insertText)
    let html = $('#editor').html();

    // Конвертим все «физические» переводы строк в <br>
    html = html.replace(/\r\n|\r|\n/g, '<br>');

    // На всякий случай превращаем <div>…</div> в <br>
    html = html
    .replace(/<div>/gi, '<br>')
    .replace(/<\/div>/gi, '');

    // Фильтрация, включая <br> в список разрешённых
    const clean = DOMPurify.sanitize(html, {
      ALLOWED_TAGS: [
        'b','strong','i','em','u','ins',
        's','strike','del','a',
        'code','pre','tg-spoiler',
        'br'               // ← не забываем br
      ],
      ALLOWED_ATTR: ['href']
    });

    // Превращаем <br> обратно в \n
    const text = clean.replace(/<br\s*\/?>/gi, '\n');

    // (опционально) глянуть в консоли, что у вас в text:
    console.log(JSON.stringify(text));

    // Запихиваем в скрытое поле
    $('#text').val(text);
  });
</script>