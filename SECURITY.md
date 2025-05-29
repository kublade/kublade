# Security Policy

## Supported Versions

We currently support the following versions with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.x.x   | :white_check_mark: |

## Reporting a Vulnerability

We take the security of Kublade seriously. If you believe you have found a security vulnerability, please report it to us using GitHub's private vulnerability reporting feature.

**Please do not report security vulnerabilities through public GitHub issues.**

Instead, please report them by:
1. Going to the [Security tab](https://github.com/kublade/kublade/security) of this repository
2. Clicking on "Report a vulnerability"
3. Following the instructions in the form

This will create a private security advisory that only you and the repository maintainers can see. This allows us to work together to fix the issue before it becomes public.

Please include the following information in your report:
- Type of issue (e.g., buffer overflow, SQL injection, cross-site scripting, etc.)
- Full paths of source file(s) related to the manifestation of the issue
- The location of the affected source code (tag/branch/commit or direct URL)
- Any special configuration required to reproduce the issue
- Step-by-step instructions to reproduce the issue
- Proof-of-concept or exploit code (if possible)
- Impact of the issue, including how an attacker might exploit it

This information will help us triage your report more quickly.

## Security Measures

### Code Security
- All code changes require review before being merged
- Automated security scanning is performed on pull requests
- Dependencies are regularly updated and monitored for known vulnerabilities

### Access Control
- Access to sensitive operations requires authentication
- Role-based access control (RBAC) is implemented where applicable
- API keys and secrets are managed securely

### Data Protection
- Sensitive data is encrypted at rest and in transit
- Regular security audits are performed
- Data backups are encrypted and stored securely

## Security Updates

Security updates will be released as patch versions (e.g., 1.0.1, 1.0.2) and will be clearly marked in the release notes. We recommend always running the latest patch version of your current minor version.

## Best Practices

When using Kublade, please follow these security best practices:
1. Keep your installation up to date
2. Use strong, unique passwords
3. Regularly rotate API keys and secrets
4. Follow the principle of least privilege
5. Monitor system logs for suspicious activity

## Contact

For any security-related questions or concerns, please use the [Security tab](https://github.com/kublade/kublade/security) of this repository. 
