# Changelog

Формат основан на Keep a Changelog и Семантическом версионировании.

## [Unreleased]

### Added
- Единый слой валидации: `App/Helpers/Validator` + ошибки `ValidationException` → 422 (problem+json).
- Валидация в `AuthController::login` и `AuthController::refresh`.
- Юнит‑тесты на валидатор и обработчик ошибок для валидации.
- Базовый функционал промокодов:
  - API эндпоинты: загрузка CSV батча, список кодов с фильтрами, выдача кода пользователю, отчёт по выдачам, список батчей.
  - Dashboard: страницы загрузки CSV, список и выдача, отчёт с экспортом в CSV, список батчей с агрегатами.

### Changed
- `ApiErrorHandler` мапит `ValidationException` в 422 Unprocessable Entity.

## [0.2.0] — 2025-09-05

### Added
- Полная переработка и расширение документации на русском языке: `README.md`, `ARCHITECTURE.md`, `ENVIRONMENT.md`, `DEPLOYMENT.md`, `CONTRIBUTING.md`, `CODESTYLE.md`.
- Обновлён раздел API: `docs/api.md` и пользовательское руководство `docs/user_manual.md`.
- Описаны воркеры и примеры конфигурации Supervisor.

### Changed
- Исправлены проблемы с кодировкой и несоответствия в старых документах.
- README переписан: уточнены стек, структура, безопасность, запуск и консоль.
- Уточнены пути и форматы заголовков для передачи `initData`.

## [0.1.1] — 2025-08-22

### Added
- Дополнительные PHPDoc‑комментарии.
- Расширение конфигурации.

### Changed
- Уточнена иерархия middleware.
- Перенос логирования в общий хелпер `Logger`.

## [0.1.0] — 2025-08-22

### Added
- Базовая структура проекта: API + Dashboard.
- Стартовая документация (ARCHITECTURE, README, CONTRIBUTING, CHANGELOG).
