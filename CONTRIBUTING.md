# Contributing to CoreWatch 🚀

First off, thank you for considering contributing to CoreWatch! It is people like you who make open source such a fantastic environment to learn, inspire, and create.

Here are the guidelines to help make the contribution process clear and effective.

---

## 🛠️ Local Development Workflow

To begin developing on CoreWatch, follow these steps:

### 1. Fork & Clone Repository
Fork the repository on GitHub, then clone your fork locally:

```bash
git clone https://github.com/your-username/corewatch.git
cd corewatch
```

### 2. Install Package Dependencies
Install the package dependencies using Composer:

```bash
composer install
```

### 3. Run Automated Tests
CoreWatch uses PHPUnit and Orchestra Testbench to run tests. Execute the test suite locally to verify everything is working:

```bash
vendor/bin/phpunit
```

### 4. Enforce Coding Standard Styles
We use **Laravel Pint** to format our PHP codebase automatically. Before submitting any pull request, check the styles and format the code:

```bash
# Check code style rules
vendor/bin/pint --test

# Automatically fix code styles
vendor/bin/pint
```

---

## 📬 Pull Request Guidelines

To keep the codebase clean, robust, and reliable, please ensure your PRs adhere to these guidelines:

1. **Strict Typing:** All new classes and files MUST declare strict typing at the very top:
   ```php
   <?php
   declare(strict_types=1);
   ```
2. **PHP Compatibility:** Code must target PHP `8.2` or higher and Laravel `11.x` through `13.x` features. Avoid using deprecated methods.
3. **Write Tests:** Every new feature, endpoint, or bug fix must be covered by a corresponding automated test inside the `tests/` directory.
4. **Descriptive Commits:** Use clear and descriptive commit messages (e.g. `feat: add database table listing`, `fix: parse older Nginx access log date patterns`).
5. **No Truncated Code:** Keep code complete, fully documented with correct DocBlocks, and clean.

---

## 🐞 Reporting Security Vulnerabilities

If you discover a security vulnerability within CoreWatch, please send an e-mail to **developer@hamzi.dev** instead of opening a public issue. All security vulnerabilities will be addressed immediately.
