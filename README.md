This is the core using Yii 2 Advanced and Dashkit template

## Getting started

1. Setup localhost point to folder `public`
2. Run `composer install` to install Yii vendor
3. Run `php init` to setup Yii environment
4. Run `php yii deploy` to run migration script to install Database

## What's the libs inside?

- Bootstrap 5.1.3
- Select2
- Media module using CKFinder
- TinyMCE Editor
- Flatpickr for Calendar

## DIRECTORY STRUCTURE

```
modules                  contains all modules
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    views/               contains view files for the Web application
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```
