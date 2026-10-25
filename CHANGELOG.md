# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial client for the Quikcast native management API (server, stats, mounts,
  sources, listeners, HLS, relays) over Guzzle with Bearer authentication.
- Source metadata operation (`mode=updinfo`) via HTTP Basic auth on the
  streaming listener.
- Lightweight health probes (`/health/live`, `/health/ready`).
- Typed readonly DTOs, enums, and per-error-code exception hierarchy.
- `history()` resource for `GET /api/history` (server-wide or per mount, `minutes` window).
- Full parity with current API fields: access/source-access/listener-auth policies,
  fallback and dynamic mounts, per-mount counters and disconnect reasons, embedded relay
  snapshot, source ring and `*_since` timestamps, relay failover counters, HLS object
  counts, playlist version, and the expanded `/api/stats` counters.
