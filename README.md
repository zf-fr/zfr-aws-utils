ZfrAwsUtils
=============

[![Latest Stable Version](https://poser.pugx.org/zfr/zfr-aws-utils/v/stable.png)](https://packagist.org/packages/zfr/zfr-aws-utils)
[![Build Status](https://travis-ci.org/zf-fr/zfr-aws-utils.svg)](https://travis-ci.org/zf-fr/zfr-aws-utils)

ZfrAwsUtils is a tiny PHP library that provides some features on top of [AWS SDK for PHP](https://github.com/aws/aws-sdk-php)
such as:

* [container-interop](https://github.com/container-interop/container-interop) compatible factories;
* AWS credentials caching with [Doctrine Cache](https://github.com/doctrine/cache);
* Auto prefixer for DynamoDB table names;
* DynamoDB cursor-based paginator.

## Dependencies

* PHP 7.1
* [AWS SDK for PHP](https://github.com/aws/aws-sdk-php): ^3.22

## Installation

Installation of ZfrAwsUtils is only officially supported using Composer:

```sh
php composer.phar require zfr/zfr-aws-utils
```
