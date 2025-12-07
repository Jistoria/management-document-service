php artisan tinker
exit
php artisan make:seeder DocenciaStudentDevelopmentSeeder
php artisan db:seed --class=Database\Seeders\DocenciaStudentDevelopmentSeeder
php artisan db:seed --class=DocenciaStudentDevelopmentSeeder
php artisan db:seed --class=DocenciaStudentDevelopmentSeeder
php artisan db:seed --class=DocenciaStudentDevelopmentSeeder
exit
php artisan l5-swagger:generate
php artisan make:seeder Pap01002MetadataSeeder
php artisan make:seeder Pap01002MetadataSeeder
php artisan tinker
exit
Nothing to modify in lock file
Writing lock file
Installing dependencies from lock file (including require-dev)
Nothing to install, update or remove
In Filesystem.php line 913:
                                                                                                       
  file_put_contents(/var/www/vendor/composer/installed.php): Failed to open stream: Permission denied  
                                                                                                       
require [--dev] [--dry-run] [--prefer-source] [--prefer-dist] [--prefer-install PREFER-INSTALL] [--fixed] [--no-suggest] [--no-progress] [--no-update] [--no-install] [--no-audit] [--audit-format AUDIT-FORMAT] [--update-no-dev] [-w|--update-with-dependencies] [-W|--update-with-all-dependencies] [--with-dependencies] [--with-all-dependencies] [--ignore-platform-req IGNORE-PLATFORM-REQ] [--ignore-platform-reqs] [--prefer-stable] [--prefer-lowest] [-m|--minimal-changes] [--sort-packages] [-o|--optimize-autoloader] [-a|--classmap-authoritative] [--apcu-autoloader] [--apcu-autoloader-prefix APCU-AUTOLOADER-PREFIX] [--] [<packages>...]
www-data@56aaf94e31b
exit
php artisan make:migration create_auth_mirror_tables
exit
php artisan make:migration fix_md_auth_users_schema
php artisan migrate
php artisan migrate
exit
