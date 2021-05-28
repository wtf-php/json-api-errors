# WTF-PHP - Json-Api-Errors Middleware

Json-Api-Errors provides two middlewares (PSR-7 or PSR-15 compliant), which format exceptions and returns 
JSON:API compliant errors.

You can either use the Middleware to return an error when it first occurs, or you can add them to an error bag
to handle multiple errors (e.g. validation errors).

When handling multiple errors, the response status code will be the most general code determined from the bag.

For example:
- 404 error and a 422 error, then 400 will be returned
- 500 error and a 400 error, then 500 will be returned

## Features
- [Json:API](https://jsonapi.org/format/) compliant
- Use middleware that meets your needs:
  - [PSR-7](https://www.php-fig.org/psr/psr-7/) compatible middleware
  - [PSR-15](https://www.php-fig.org/psr/psr-15/) compatible middleware
- Single or multiple exception handling
- Steer the level of error detail via a debug flag

## Usage
### Examples
How to start the examples:
- Start a development server and call the respective endpoint:
  - Single:
    - `php -S localhost:8080 examples/SingleExceptionMiddlewareExample.php`
    - GET http://localhost:8080/single
  - Multiple:
    - `php -S localhost:8080 examples/MultipleExceptionMiddlewareExample.php`
    - GET http://localhost:8080/multiple

### Proof of Concepts
There are two Proof of Concepts, that showcase the usage of the middlewares:
- [Proof of Concept](https://github.com/wtf-php/poc-error-mw) with PSR-15 for Slim
- [Proof of Concept](https://github.com/wtf-php/poc-error-mw2) with PSR-7 for Laravel
