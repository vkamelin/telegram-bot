# Update Filter

The update filter lets you allow or block incoming Telegram updates by type, chat, or command. It can read rules from the `.env` file or from Redis sets.

## Environment variables

| Variable | Description |
| --- | --- |
| `TG_FILTERS_FROM_REDIS` | When `true`, lists are loaded from Redis; otherwise values are read from `.env`. |
| `TG_ALLOW_UPDATES` | Comma‑separated update types that are allowed. If set, other types are skipped. |
| `TG_DENY_UPDATES` | Comma‑separated update types that are blocked unless explicitly allowed. |
| `TG_ALLOW_CHATS` | Chat IDs that are allowed. All others are skipped when the list is non‑empty. |
| `TG_DENY_CHATS` | Chat IDs that are denied unless explicitly allowed. |
| `TG_ALLOW_COMMANDS` | Bot commands that are allowed. Other commands are skipped when the list is non‑empty. |
| `TG_DENY_COMMANDS` | Bot commands that are denied unless explicitly allowed. |
| `TG_FILTERS_REDIS_PREFIX` | Prefix for Redis keys used by the console command (default `tg:filters`). |

## Redis keys

With `TG_FILTERS_FROM_REDIS=true`, lists are stored in Redis sets under `<prefix>:<list>` where `<prefix>` defaults to `tg:filters`. The available lists are:

- `allow_updates`
- `deny_updates`
- `allow_chats`
- `deny_chats`
- `allow_commands`
- `deny_commands`

### Sample Redis commands

```bash
# Add an allowed chat
redis-cli SADD tg:filters:allow_chats 123456789

# Remove the chat from the set
redis-cli SREM tg:filters:allow_chats 123456789

# View current members
redis-cli SMEMBERS tg:filters:allow_chats

# Drop the whole list
redis-cli DEL tg:filters:allow_chats
```

## Console command

Run `php run.php filter:update` to manage sets interactively. The workflow:

1. Enter one of the list names (`allow_updates/deny_updates/allow_chats/deny_chats/allow_commands/deny_commands`).
2. Choose an operation: `add` or `remove`.
3. Provide the value (update type, chat ID, or command).
4. Confirm the action.

Example:

```
$ php run.php filter:update
List name (allow_updates/deny_updates/allow_chats/deny_chats/allow_commands/deny_commands): allow_chats
Operation (add/remove): add
Value: 123456789
Confirm add of '123456789' in 'allow_chats'? [y/N]: y
Added '123456789' to 'allow_chats'.
```

## Logging

`UpdateFilter::shouldProcess()` accepts an optional variable to capture the skip reason. Each reason is logged at most once per 60 seconds to avoid flooding the logs.
