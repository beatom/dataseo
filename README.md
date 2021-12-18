This endpoint will provide you with the list of domains pointing to the specified websites.

- [Installation](#installation)
- [Usage](#usage)

## Installation

Require this package with composer using the following command:

```bash
composer require beatom/dataseo
```

- Add the following class to the `providers` array in `config/app.php`:
       ```php
       \Beatom\DataSeo\HelperServiceProvider::class,
       ```
       
- publish files:
         ```php artisan vendor:publish --provider="Beatom\DataSeo\HelperServiceProvider"
         ```
         
- perform migration:
                  ```php artisan migrate
                  ```

- set API key and API login
  
> Note: Avoid caching the configuration in your development environment, it may cause issues after installing this package; respectively clear the cache beforehand via `php artisan cache:clear` if you encounter problems when running the commands

## Usage


```bash
php artisan dataseo:domain_intersection expressvpn.com,nordvpn.com protonvpn.com
```


dataseo:domain_intersection {targets} {exclude_targets?}';

{targets} - required. Domains, subdomains or webpages to get links for
            required field
            you can set up to 20 domains, subdomains or webpages
            a domain or a subdomain should be specified without https:// and www.
            a page should be specified with absolute URL (including http:// or https://)
            
{exclude_targets?} - not required. Domains, subdomains or webpages you want to exclude
                     optional field
                     you can specify up to 10 domains, subdomains or webpages
                     if you use this array, results will contain the referring domains that link to targets but don’t link to exclude_targets         

