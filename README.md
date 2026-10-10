# Neuro Graph

**An interactive tool that extracts key concepts from YouTube videos, articles and more — and maps them into a weighted semantic graph.** Connections are weighted by relevance and drawn with line thickness and numeric scores, so you can *see* how strongly ideas relate to each other.

<!-- TODO: add a graph screenshot -->

## What it does

- **Extracts content** from YouTube videos (subtitle transcription via `yt-dlp` + `ffmpeg`), articles and other sources
- **Builds a semantic graph**: nodes are concepts, edges are weighted by relevance, with scores you can inspect
- **Search and content processing** exposed through a REST API with token auth (Laravel Passport)
- Built for performance with **Laravel Octane (Swoole)** and containerized with **Docker / Laravel Sail**

## Stack

`PHP 8.2+` · `Laravel 12` · `Laravel Octane (Swoole)` · `Docker / Laravel Sail` · `Laravel Passport` · `MySQL / SQLite` · `yt-dlp` · `ffmpeg`

## Quickstart

Requirements: Docker and Docker Compose.

```bash
git clone https://github.com/marrano92/neuro-graph.git
cd neuro-graph
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
```

YouTube transcription needs `yt-dlp` and `ffmpeg`, already configured in the custom
Dockerfile. On the first run, build the image:

```bash
./vendor/bin/sail build --no-cache
```

Sail alias for everyday use:

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
sail up -d      # containers in background
sail artisan    # artisan commands
sail composer   # composer commands
```

## API

Authentication endpoints (Laravel Passport):

| Endpoint | Description |
|---|---|
| `POST /api/auth/register` | register a new user |
| `POST /api/auth/login` | get an access token |
| `POST /api/auth/logout` | revoke the token (auth required) |
| `GET /api/auth/user` | current user (auth required) |

A Postman collection ships in the repo (`postman_collection.json`): import it, set
`base_url` and `auth_token`, and explore the API.

## Email

MailHog is the default mailer for development (captured at `http://localhost:8025`, nothing
really sent). For real delivery either run `./setup-brevo.sh` (Brevo SMTP or API — see
[docs/email/brevo-integration.md](docs/email/brevo-integration.md)) or configure Gmail SMTP in
`.env`. Test the configuration with `./vendor/bin/sail artisan email:test`.

## Octane

```bash
./vendor/bin/sail composer dev-octane   # or: ./sail-octane start|stop|status|reload
```

The app is served at `http://localhost:8000` with Octane, `http://localhost` with the
standard web server.

## Tests

```bash
./vendor/bin/sail artisan test
```

CI runs the suite on every push and pull request.

## Contributing

This project follows [Conventional Commits](https://www.conventionalcommits.org/) for commit
messages.

## License

[MIT](LICENSE)
