# Runbook (операционный гайд)

## Проверка статуса

### Приложение
```bash
kubectl get pods
```

### Воркеры
```bash
supervisorctl status
supervisorctl tail workers
```

### Redis
```bash
redis-cli ping
```

### MySQL
```bash
mysqladmin ping
```

## Перезапуск сервиса
```bash
kubectl rollout restart deployment app
```

## Перезапуск воркеров
```bash
supervisorctl restart workers
```

### Ручная постановка отложенных сообщений в очередь
Если воркер отложенных сообщений не работает или требуется запустить обработку вручную:
```bash
php run scheduled:dispatch --limit=200
```
Где `--limit` — максимальное количество записей за один проход.

## Очистка очередей и логов
```bash
redis-cli FLUSHALL
rm -f storage/logs/*.log
```

## Типовые инциденты
- Ошибка 500 → проверить `storage/logs/*.log`, при необходимости перезапустить сервис
- Переполнение очереди → очистить очередь в Redis и перезапустить воркеры
- Сбой БД → проверить доступность MySQL (`mysqladmin ping`) и восстановить её
