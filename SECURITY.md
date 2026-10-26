# Security policy

Use GitHub **Security → Report a vulnerability** for a private report if enabled. Otherwise, contact the repository owner privately through an established channel before sharing exploit details. Do not post credentials, personal data or working production exploits in public issues.

Include the affected revision, prerequisites, minimal reproduction, impact and a proposed fix if available. Use a local checkout or an environment you own for testing; avoid load tests against the live service.

The maintained deployment is the current `main` branch. Keep PHP, Laravel, dependencies and hosting on supported security releases. Findings and remaining operational responsibilities are in [the audit](docs/SECURITY-AUDIT.md).
