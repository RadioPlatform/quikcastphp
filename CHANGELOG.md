# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `bans()` resource for `GET/POST/DELETE /api/bans` (list, create with ttl, reason and kick, lift).
- `revocations()` resource for `GET/POST/DELETE /api/revocations` (single signed-link revocation).
- `hls()->listeners($stream)` for directory HLS sessions (`GET /api/hls/{stream}/listeners`).
- Directory-stream fields on `StreamInfo` (`source`, `playlistCount`, `activeListeners`,
  `activeSessions`, request counters), the `empty` HLS state, and `Stats::$activeHlsListeners`.
- `BanNotFound`, `BanListFull`, `RevocationNotFound` and `RevocationListFull` exceptions.

### Changed
- Replaced Guzzle with [ElliePHP HttpClient](https://github.com/ElliePHP/HttpClient).
  `QuikcastClient` no longer accepts a custom HTTP client.
- **Breaking:** requires PHP `^8.4` (was `^8.2`).
- Updated dev tooling: PHPUnit 13, Pint 1.32, PHPStan 2.3.

### Added
- Initial client for the Quikcast native management API (server, stats, mounts,
  sources, listeners, HLS, relays) over HTTP with Bearer authentication.
- Source metadata operation (`mode=updinfo`) via HTTP Basic auth on the
  streaming listener.
- Lightweight health probes (`/health/live`, `/health/ready`).
- Typed readonly DTOs, enums, and per-error-code exception hierarchy.
- Fixed source metadata updates to use `/api/metadata?mount=…` on the streaming listener;
  added `updateTrack()` (artist/title) and a `metrics()` resource for `/metrics`.
- `history()` resource for `GET /api/history` (server-wide or per mount, `minutes` window).
- Full parity with current API fields: access/source-access/listener-auth policies,
  fallback and dynamic mounts, per-mount counters and disconnect reasons, embedded relay
  snapshot, source ring and `*_since` timestamps, relay failover counters, HLS object
  counts, playlist version, and the expanded `/api/stats` counters.
