# Runbook (операционный гайд)

## Проверка статуса
```bash
kubectl get pods
```

## Перезапуск сервиса
```bash
kubectl rollout restart deployment app
```

## Типовые инциденты
- Ошибка 500 → проверить логи
