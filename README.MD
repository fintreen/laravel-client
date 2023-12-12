# Laravel client for fintreen.com.
Fintreen.com laravel client for crypto-payment gateway API

[![MIT License][license-shield]][license-url]


<h3 align="center">Fintreen.com laravel client</h3>

  <p align="center">
    <br />
    <a href="https://fintreen.docs.apiary.io/" target="_blank"><strong>Explore the docs on apiary »</strong></a>
    <br />
    <br />
    <a href="https://github.com/fintreen/laravel-client/issues">Report Bug</a>
    ·
    <a href="https://github.com/fintreen/laravel-client/issues">Request Feature</a>
  </p>
</div>

Tested on php 8.0, 8.1, 8.2. Should be also good with php 8.3.
Tested on laravel 9, 10.

Use composer2 for package installation.

### Installation:

#### 1. Add next env variables to your env file

```dotenv
#set true if you are using Laravel Backpack
FINTREEN_USE_BAKCPACK=false
FINTREEN_TOKEN=yourfintreentoken 
FINTREEN_EMAIL=yourfintreenemail 

```

#### 2. Install the client

```
composer req fintreen/laravel-client
```



<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[license-shield]: https://img.shields.io/github/license/othneildrew/Best-README-Template.svg?style=for-the-badge
[license-url]: https://github.com/fintreen/laravel-client/blob/main/LICENCE.txt